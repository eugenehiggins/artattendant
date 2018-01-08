<?php

function mwp_autoload($class)
{
    if (substr($class, 0, 8) === 'Dropbox_'
        || substr($class, 0, 8) === 'Symfony_'
        || substr($class, 0, 8) === 'Monolog_'
        || substr($class, 0, 5) === 'Gelf_'
        || substr($class, 0, 4) === 'MWP_'
        || substr($class, 0, 4) === 'MMB_'
        || substr($class, 0, 3) === 'S3_'
    ) {
        $file = dirname(__FILE__).'/src/'.str_replace('_', '/', $class).'.php';
        if (file_exists($file)) {
            include_once $file;
        }
    }
}

function mwp_register_autoload_google()
{
    static $registered;

    if ($registered) {
        return;
    } else {
        $registered = true;
    }

    if (version_compare(PHP_VERSION, '5.3', '<')) {
        spl_autoload_register('mwp_autoload_google');
    } else {
        spl_autoload_register('mwp_autoload_google', true, true);
    }
}

function mwp_autoload_google($class)
{
    if (substr($class, 0, 7) === 'Google_') {
        $file = dirname(__FILE__).'/src/'.str_replace('_', '/', $class).'.php';
        if (file_exists($file)) {
            include_once $file;
        }
    }
}

/**
 * @return Monolog_Psr_LoggerInterface
 */
function mwp_logger()
{
    return mwp_container()->getLogger();
}

/**
 * @return MWP_WordPress_Context
 */
function mwp_context()
{
    return mwp_container()->getWordPressContext();
}

/**
 * @param $appKey
 * @param $appSecret
 * @param $token
 * @param $tokenSecret
 *
 * @return Dropbox_Client
 */
function mwp_dropbox_oauth_factory($appKey, $appSecret, $token, $tokenSecret = null)
{
    if ($tokenSecret) {
        $oauthToken       = 'OAuth oauth_version="1.0", oauth_signature_method="PLAINTEXT", oauth_consumer_key="'.$appKey.'", oauth_token="'.$token.'", oauth_signature="'.$appSecret.'&'.$tokenSecret.'"';
        $clientIdentifier = $token;
    } else {
        $oauthToken       = 'Bearer '.$token;
        $clientIdentifier = 'PHP-ManageWp/1.0';
    }

    return new Dropbox_Client($oauthToken, $clientIdentifier);
}

function mwp_format_memory_limit($limit)
{
    if ((string)(int)$limit === (string)$limit) {
        // The number is numeric.
        return mwp_format_bytes($limit);
    }

    $units = strtolower(substr($limit, -1));

    if (!in_array($units, array('b', 'k', 'm', 'g'))) {
        // Invalid size unit.
        return $limit;
    }

    $number = substr($limit, 0, -1);

    if ((string)(int)$number !== $number) {
        // The number isn't numeric.
        return $number;
    }

    switch ($units) {
        case 'g':
            return $number.' GB';
        case 'm':
            return $number.' MB';
        case 'k':
            return $number.' KB';
        case 'b':
        default:
            return $number.' B';
    }
}

function mwp_format_bytes($bytes)
{
    $bytes = (int)$bytes;

    if ($bytes > 1024 * 1024 * 1024) {
        return round($bytes / 1024 / 1024 / 1024, 2).' GB';
    } elseif ($bytes > 1024 * 1024) {
        return round($bytes / 1024 / 1024, 2).' MB';
    } elseif ($bytes > 1024) {
        return round($bytes / 1024, 2).' KB';
    }

    return $bytes.' B';
}

function mwp_log_warnings()
{
    // If mbstring.func_overload is set, it changes the behavior of the standard string functions in
    // ways that makes external libraries like Dropbox break.
    $mbstring_func_overload = ini_get("mbstring.func_overload");
    if ($mbstring_func_overload & 2 == 2) {
        mwp_logger()->warning('"mbstring.func_overload" changes the behavior of the standard string functions in ways that makes external libraries like Dropbox break');
    }

    if (strlen((string)PHP_INT_MAX) < 19) {
        // Looks like we're running on a 32-bit build of PHP.  This could cause problems because some of the numbers
        // we use (file sizes, quota, etc) can be larger than 32-bit ints can handle.
        mwp_logger()->warning("Some external libraries rely on 64-bit integers, but it looks like we're running on a version of PHP that doesn't support 64-bit integers (PHP_INT_MAX=".((string)PHP_INT_MAX).").");
    }
}

function mmb_get_extended_info(&$stats, &$params)
{
    $filter                     = isset($params['plugins']['cleanup']) ? $params['plugins']['cleanup'] : array();
    $stats['num_revisions']     = mmb_num_revisions($filter['revisions']);
    $stats['overhead']          = mmb_handle_overhead(false);
    $stats['num_spam_comments'] = mmb_num_spam_comments();
}

/* Revisions */
function cleanup_delete_worker($params = array())
{
    $revision_params = get_option('mmb_stats_filter');
    $revision_filter = isset($revision_params['plugins']['cleanup']) ? $revision_params['plugins']['cleanup'] : array();

    $params_array = explode('_', $params['actions']);
    $return_array = array();

    foreach ($params_array as $param) {
        switch ($param) {
            case 'revision':
                if (mmb_delete_all_revisions($revision_filter['revisions'])) {
                    $return_array['revision'] = 'OK';
                } else {
                    $return_array['revision_error'] = 'OK, nothing to do';
                }
                break;
            case 'overhead':
                if (mmb_handle_overhead(true)) {
                    $return_array['overhead'] = 'OK';
                } else {
                    $return_array['overhead_error'] = 'OK, nothing to do';
                }
                break;
            case 'comment':
                if (mmb_delete_spam_comments()) {
                    $return_array['comment'] = 'OK';
                } else {
                    $return_array['comment_error'] = 'OK, nothing to do';
                }
                break;
            default:
                break;
        }
    }

    unset($params);

    mmb_response($return_array, true);
}

function mmb_num_revisions($filter)
{
    global $wpdb;

    $num_rev = isset($filter['num_to_keep']) && !empty($filter['num_to_keep']) ? str_replace("r_", "", $filter['num_to_keep']) : 5;

    $query = "SELECT SUM(t.cnt) FROM (SELECT COUNT(ID) - {$num_rev} as cnt FROM {$wpdb->posts} WHERE post_type = 'revision' AND post_parent != 0 GROUP BY post_parent HAVING COUNT(ID) > {$num_rev}) as t";

    return $wpdb->get_var($query);
}

function mmb_select_all_revisions()
{
    global $wpdb;
    $sql       = "SELECT * FROM $wpdb->posts WHERE post_type = 'revision'";
    $revisions = $wpdb->get_results($sql);

    return $revisions;
}

function mmb_delete_all_revisions($filter)
{
    global $wpdb;

    $num_rev = isset($filter['num_to_keep']) ? (int)str_replace("r_", "", $filter['num_to_keep']) : 5;

    $allRevisions = $wpdb->get_results("SELECT post_parent FROM {$wpdb->posts} WHERE post_type = 'revision' AND post_parent != 0 GROUP BY post_parent HAVING COUNT(ID) > {$num_rev}");

    if (!is_array($allRevisions)) {
        return false;
    }

    foreach ($allRevisions as $revision) {
        $toKeep = $wpdb->get_results("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'revision' AND post_parent = '{$revision->post_parent}' ORDER BY post_date DESC LIMIT ".$num_rev);

        $keepArray = array();
        foreach ($toKeep as $keep) {
            $keepArray[] = $keep->ID;
        }

        if (empty($keepArray)) {
            continue;
        }

        $keepQuery = implode(', ', $keepArray);
        $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type = 'revision' AND post_parent = '{$revision->post_parent}' AND ID NOT IN ({$keepQuery})");
    }

    return true;
}

function mmb_handle_overhead($clear = false)
{
    /** @var wpdb $wpdb */
    global $wpdb;
    $query            = 'SHOW TABLE STATUS';
    $tables           = $wpdb->get_results($query, ARRAY_A);
    $tableOverhead    = 0;
    $tablesToOptimize = array();

    foreach ($tables as $table) {
        if (!isset($table['Engine']) || $table['Engine'] !== 'MyISAM' || $table['Data_free'] == 0) {
            continue;
        }

        if ($wpdb->base_prefix === $wpdb->prefix && !preg_match('/^'.preg_quote($wpdb->prefix).'/Ui', $table['Name'])) {
            continue;
        }

        if ($wpdb->base_prefix !== $wpdb->prefix && !preg_match('/^'.preg_quote($wpdb->prefix).'\d+_/Ui', $table['Name'])) {
            continue;
        }

        $tableOverhead      += $table['Data_free'] / 1024;
        $tablesToOptimize[] = $table['Name'];
    }

    if (!$clear) { // we should only return the overhead
        return round($tableOverhead, 3);
    }

    $optimize = true;

    foreach ($tablesToOptimize as $tableToOptimize) {
        $query    = 'OPTIMIZE TABLE '.$tableToOptimize;
        $optimize = ((bool)$wpdb->query($query)) && $optimize;
    }

    return $optimize;
}

/* Spam Comments */
function mmb_num_spam_comments()
{
    global $wpdb;
    $sql       = "SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = 'spam'";
    $num_spams = $wpdb->get_var($sql);

    return $num_spams;
}

function mmb_delete_spam_comments()
{
    global $wpdb;
    $spam  = 1;
    $total = 0;
    while (!empty($spam)) {
        $getCommentsQuery = "SELECT * FROM $wpdb->comments WHERE comment_approved = 'spam' LIMIT 1000";
        $spam             = $wpdb->get_results($getCommentsQuery);

        if (empty($spam)) {
            break;
        }

        $commentIds = array();
        foreach ($spam as $comment) {
            $commentIds[] = $comment->comment_ID;

            // Avoid queries to comments by caching the comment.
            // Plugins which hook to 'delete_comment' might call get_comment($id), which in turn returns the cached version.
            wp_cache_add($comment->comment_ID, $comment, 'comment');
            do_action('delete_comment', $comment->comment_ID);
            wp_cache_delete($comment->comment_ID, 'comment');
        }

        $commentIdsList = implode(', ', array_map('intval', $commentIds));
        $wpdb->query("DELETE FROM {$wpdb->comments} WHERE comment_ID IN ($commentIdsList)");
        $wpdb->query("DELETE FROM {$wpdb->commentmeta} WHERE comment_id IN ($commentIdsList)");

        $total += count($spam);
        if (!empty($spam)) {
            usleep(10000);
        }
    }

    return $total;
}

function mmb_get_spam_comments()
{
    global $wpdb;
    $sql   = "SELECT * FROM $wpdb->comments as a LEFT JOIN $wpdb->commentmeta as b WHERE a.comment_ID = b.comment_id AND a.comment_approved = 'spam'";
    $spams = $wpdb->get_results($sql);

    return $spams;
}

function mwp_is_nio_shell_available()
{
    static $check;
    if (isset($check)) {
        return $check;
    }
    try {
        $process = new Symfony_Process_Process("cd .", dirname(__FILE__), array(), null, 1);
        $process->run();
        $check = $process->isSuccessful();
    } catch (Exception $e) {
        $check = false;
    }

    return $check;
}

function mwp_is_shell_available()
{
    if (mwp_container()->getParameter('disable_shell')) {
        return false;
    }

    if (mwp_is_safe_mode()) {
        return false;
    }
    if (!function_exists('proc_open') || !function_exists('escapeshellarg')) {
        return false;
    }

    $neededFunction   = array('proc_get_status', 'proc_open');
    $disabledFunction = mwp_get_disabled_functions();

    if (count(array_diff($neededFunction, $disabledFunction)) != count($neededFunction)) {
        return false;
    }

    if (!mwp_is_nio_shell_available()) {
        return false;
    }

    return true;
}

function mwp_get_disabled_functions()
{
    $list = array_merge(explode(',', ini_get('disable_functions')), explode(',', ini_get('suhosin.executor.func.blacklist')));
    $list = array_map('trim', $list);
    $list = array_map('strtolower', $list);
    $list = array_filter($list);

    return $list;
}

function mwp_is_safe_mode()
{
    $value = ini_get("safe_mode");
    if ((int)$value === 0 || strtolower($value) === "off") {
        return false;
    }

    return true;
}

// Everything below was moved from init.php

function mmb_response($response = false, $success = true)
{
    if (!$success) {
        if (!is_scalar($response)) {
            $response = json_encode($response);
        }
        throw new MWP_Worker_Exception(MWP_Worker_Exception::GENERAL_ERROR, $response);
    }

    throw new MWP_Worker_ActionResponse($response);
}

function mmb_remove_site($params)
{
    extract($params);
    global $mmb_core;
    $mmb_core->deactivate($deactivate);

    include_once ABSPATH.'wp-admin/includes/plugin.php';
    $plugin_slug = 'worker/init.php';

    if ($deactivate) {
        deactivate_plugins($plugin_slug, true);
    } else {
        // Prolong the worker deactivation upon site removal.
        update_option('mmb_worker_activation_time', time());
    }

    if (!is_plugin_active($plugin_slug)) {
        mmb_response(
            array(
                'deactivated' => 'Site removed successfully. <br /><br />ManageWP Worker plugin successfully deactivated.',
            ),
            true
        );
    } else {
        mmb_response(
            array(
                'removed_data' => 'Site removed successfully. <br /><br /><b>ManageWP Worker plugin was not deactivated.</b>',
            ),
            true
        );
    }
}

function mmb_stats_get($params)
{
    mmb_response(mmb_pre_init_stats($params), true);
}


function mmb_pre_init_stats($params)
{
    global $mmb_core;

    mwp_context()->requireWpRewrite();
    mwp_context()->requireTaxonomies();
    mwp_context()->requirePostTypes();
    mwp_context()->requireTheme();

    $mmb_core->get_stats_instance();

    mwp_logger()->debug('Starting get_stats after everything was required');

    return $mmb_core->stats_instance->pre_init_stats($params);
}

function mwp_datasend($params = array())
{
    global $mmb_core, $_mmb_options;

    $_mmb_remoteurl = get_option('home');
    $_mmb_remoteown = isset($_mmb_options['dataown']) && !empty($_mmb_options['dataown']) ? $_mmb_options['dataown'] : false;

    if (empty($_mmb_remoteown)) {
        return;
    }

    $mmb_core->get_stats_instance();

    $filter = array(
        'refresh'     => 'transient',
        'item_filter' => array(
            'get_stats' => array(
                array('updates', array('plugins' => true, 'themes' => true, 'premium' => true)),
                array('core_update', array('core' => true)),
                array('posts', array('numberposts' => 5)),
                array('drafts', array('numberposts' => 5)),
                array('scheduled', array('numberposts' => 5)),
                array('hit_counter'),
                array('comments', array('numberposts' => 5)),
                array('backups'),
                'plugins' => array(
                    'cleanup' => array(
                        'overhead'  => array(),
                        'revisions' => array('num_to_keep' => 'r_5'),
                        'spam'      => array(),
                    ),
                ),
            ),
        ),
    );

    $data              = $mmb_core->stats_instance->pre_init_stats($filter);
    $data['server_ip'] = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : null;
    $data['uhost']     = php_uname('n');
    $hash              = $mmb_core->get_secure_hash();

    if (mwp_datasend_trigger($data)) { // adds trigger to check if really need to send something
        $configurationService = new MWP_Configuration_Service();
        $configuration        = $configurationService->getConfiguration();

        set_transient("mwp_cache_notifications", $data);
        set_transient("mwp_cache_notifications_time", time());

        $datasend['datasend']               = $mmb_core->encrypt_data($data);
        $datasend['sitehome']               = base64_encode($_mmb_remoteown.'[]'.$_mmb_remoteurl);
        $datasend['sitehash']               = md5($hash.$_mmb_remoteown.$_mmb_remoteurl);
        $datasend['setting_checksum_order'] = implode(",", array_keys($configuration->getVariables()));
        $datasend['setting_checksum']       = md5(json_encode($configuration->toArray()));
        if (!class_exists('WP_Http')) {
            include_once ABSPATH.WPINC.'/class-http.php';
        }

        $remote            = array();
        $remote['body']    = $datasend;
        $remote['timeout'] = 20;

        $result = wp_remote_post($configuration->getMasterCronUrl(), $remote);
        if (!is_wp_error($result)) {
            if (isset($result['body']) && !empty($result['body'])) {
                $settings = @unserialize($result['body']);

                if ($settings === false) {
                    return;
                }

                /* rebrand worker or set default */
                $brand              = '';
                $worker_brand       = get_option('mwp_worker_brand');
                $current_from_orion = !empty($worker_brand['from_orion']) ? $worker_brand['from_orion'] : false;

                if (isset($settings['worker_brand']) && $settings['worker_brand']) {
                    $brand = $settings['worker_brand'];
                }

                if (!$current_from_orion) {
                    update_option("mwp_worker_brand", $brand);
                }

                /* change worker version */
                $w_version = @$settings['worker_updates']['version'];
                $w_url     = @$settings['worker_updates']['url'];
                if (version_compare($GLOBALS['MMB_WORKER_VERSION'], $w_version, '<')) {
                    //automatic update
                    $mmb_core->update_worker_plugin(array("download_url" => $w_url));
                }

                if (!empty($settings['mwp_worker_configuration'])) {
                    if (!class_exists('Crypt_RSA', false)) {
                        require_once dirname(__FILE__).'/src/PHPSecLib/Crypt/RSA.php';
                    }
                    $rsa     = new Crypt_RSA();
                    $keyName = $configuration->getKeyName();
                    $rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1);
                    $rsa->loadKey(file_get_contents(dirname(__FILE__)."/publickeys/$keyName.pub")); // public key
                    $signature = base64_decode($settings['mwp_worker_configuration_signature']);
                    if ($rsa->verify(json_encode($settings['mwp_worker_configuration']), $signature)) {
                        $configuration = new MWP_Configuration_Conf($settings['mwp_worker_configuration']);
                        $configurationService->saveConfiguration($configuration);
                    }
                }
            }
        } else {
            //$mmb_core->_log($result);
        }
    }
}

// trigger function, returns true if notifications should be sent
function mwp_datasend_trigger($stats)
{
    $configurationService = new MWP_Configuration_Service();
    $configuration        = $configurationService->getConfiguration();

    $cachedData = get_transient("mwp_cache_notifications");
    $cacheTime  = (int)get_transient("mwp_cache_notifications_time");

    $returnValue = false;
    if (false == $cachedData || empty($configuration)) {
        $returnValue = true;
    }
    /**
     * Cache lifetime check
     */
    if (!$returnValue) {
        $now = time();
        if ($now - $configuration->getNotiCacheLifeTime() >= $cacheTime) {
            $returnValue = true;
        }
    }

    /**
     * Themes difference check section
     * First check if array differ in size. If same size,then check values difference
     */
    if (!$returnValue && empty($stats['upgradable_themes']) != empty($cachedData['upgradable_themes'])) {
        $returnValue = true;
    }
    if (!$returnValue && !empty($stats['upgradable_themes'])) {
        $themesArr       = mwp_std_to_array($stats['upgradable_themes']);
        $cachedThemesArr = mwp_std_to_array($cachedData['upgradable_themes']);
        if ($themesArr != $cachedThemesArr) {
            $returnValue = true;
        }
    }

    /**
     * Plugins difference check section
     * First check if array differ in size. If same size,then check values difference
     */
    if (!$returnValue && empty($stats['upgradable_plugins']) != empty($cachedData['upgradable_plugins'])) {
        $returnValue = true;
    }

    if (!$returnValue && !empty($stats['upgradable_plugins'])) { //we have hear  stdclass
        $pluginsArr       = mwp_std_to_array($stats['upgradable_plugins']);
        $cachedPluginsArr = mwp_std_to_array($cachedData['upgradable_plugins']);
        if ($pluginsArr != $cachedPluginsArr) {
            $returnValue = true;
        }
    }

    /**
     * Premium difference check section
     * First check if array differ in size. If same size,then check values difference
     */
    if (!$returnValue && empty($stats['premium_updates']) != empty($cachedData['premium_updates'])) {
        $returnValue = true;
    }
    if (!$returnValue && !empty($stats['premium_updates'])) {
        $premiumArr       = mwp_std_to_array($stats['premium_updates']);
        $cachedPremiumArr = mwp_std_to_array($cachedData['premium_updates']);
        if ($premiumArr != $cachedPremiumArr) {
            $returnValue = true;
        }
    }
    /**
     * Comments
     * Check if we have configs first, then check trasholds
     */
    if (!$returnValue && (int)$stats['num_spam_comments'] >= $configuration->getNotiTresholdSpamComments() && $stats['num_spam_comments'] != (int)$cachedData['num_spam_comments']) {
        $returnValue = true;
    }
    if (!$returnValue && (int)$stats['num_spam_comments'] < (int)$cachedData['num_spam_comments']) {
        $returnValue = true;
    }

    if (!$returnValue && !empty($stats['comments'])) {
        if (!empty($stats['comments']['pending']) && count($stats['comments']['pending']) >= $configuration->getNotiTresholdPendingComments()) {
            $pendingArr       = mwp_std_to_array($stats['comments']['pending']);
            $cachedPendingArr = mwp_std_to_array($cachedData['comments']['pending']);
            if ($pendingArr != $cachedPendingArr) {
                $returnValue = true;
            }
        }

        if (!empty($stats['comments']['approved']) && count($stats['comments']['approved']) >= $configuration->getNotiTresholdApprovedComments()) {
            $approvedArr       = mwp_std_to_array($stats['comments']['approved']);
            $cachedApprovedArr = mwp_std_to_array($cachedData['comments']['approved']);
            if ($approvedArr != $cachedApprovedArr) {
                $returnValue = true;
            }
        }
    }

    /**
     * Drafts, posts
     */

    if (!$returnValue && !empty($stats['drafts']) && count($stats['drafts']) >= $configuration->getNotiTresholdDrafts()) {
        if (count($stats['drafts']) > $configuration->getNotiTresholdDrafts() && empty($cachedData['drafts'])) {
            $returnValue = true;
        } else {
            $draftsArr       = mwp_std_to_array($stats['drafts']);
            $cachedDraftsArr = mwp_std_to_array($cachedData['drafts']);
            if ($draftsArr != $cachedDraftsArr) {
                $returnValue = true;
            }
        }
    }

    if (!$returnValue && !empty($stats['posts']) && count($stats['posts']) >= $configuration->getNotiTresholdPosts()) {
        if (count($stats['posts']) > $configuration->getNotiTresholdPosts() && empty($cachedData['posts'])) {
            $returnValue = true;
        } else {
            $postsArr       = mwp_std_to_array($stats['posts']);
            $cachedPostsArr = mwp_std_to_array($cachedData['posts']);
            if ($postsArr != $cachedPostsArr) {
                $returnValue = true;
            }
        }
    }

    /**
     * Core updates & backups
     */
    if (!$returnValue && empty($stats['core_updates']) != empty($cachedData['core_updates'])) {
        $returnValue = true;
    }
    if (!$returnValue && !empty($stats['core_updates'])) {
        $coreArr       = mwp_std_to_array($stats['core_updates']);
        $cachedCoreArr = mwp_std_to_array($cachedData['core_updates']);
        if ($coreArr != $cachedCoreArr) {
            $returnValue = true;
        }
    }

    if (!$returnValue && empty($stats['mwp_backups']) != empty($cachedData['mwp_backups'])) {
        $returnValue = true;
    }
    if (!$returnValue && !empty($stats['mwp_backups'])) {
        $backupArr       = mwp_std_to_array($stats['mwp_backups']);
        $cachedBackupArr = mwp_std_to_array($cachedData['mwp_backups']);
        if ($backupArr != $cachedBackupArr) {
            $returnValue = true;
        }
    }

    return $returnValue;
}

function mwp_std_to_array($obj)
{
    if (is_object($obj)) {
        $objArr = clone $obj;
    } else {
        $objArr = $obj;
    }
    if (!empty($objArr)) {
        foreach ($objArr as &$element) {
            if ($element instanceof stdClass || is_array($element)) {
                $element = mwp_std_to_array($element);
            }
        }
        $objArr = (array)$objArr;
    }

    return $objArr;
}

function mmb_post_create($params)
{
    global $mmb_core;

    mwp_context()->requireWpRewrite();
    mwp_context()->requireTaxonomies();
    mwp_context()->requirePostTypes();

    $mmb_core->get_post_instance();
    $return = $mmb_core->post_instance->create($params);
    if (is_int($return)) {
        mmb_response($return, true);
    } else {
        if (isset($return['error'])) {
            mmb_response($return['error'], false);
        } else {
            mmb_response($return, false);
        }
    }
}

function mmb_change_post_status($params)
{
    global $mmb_core;
    $mmb_core->get_post_instance();
    $return = $mmb_core->post_instance->change_status($params);
    if (is_wp_error($return)) {
        mmb_response($return->get_error_message(), false);
    } elseif (empty($return)) {
        mmb_response("Post status can not be changed", false);
    } else {
        mmb_response($return, true);
    }
}

function mmb_backup_now($params)
{
    global $mmb_core;

    $mmb_core->get_backup_instance();
    $return = $mmb_core->backup_instance->backup($params);

    if (is_array($return) && array_key_exists('error', $return)) {
        mmb_response($return['error'], false);
    } else {
        mmb_response($return, true);
    }
}

function mwp_ping_backup($params)
{
    global $mmb_core;

    $mmb_core->get_backup_instance();
    $return = $mmb_core->backup_instance->ping_backup($params);

    if (is_array($return) && array_key_exists('error', $return)) {
        mmb_response($return['error'], false);
    } else {
        mmb_response($return, true);
    }
}

function mmb_run_task_now($params)
{
    global $mmb_core;
    $mmb_core->get_backup_instance();

    $task_name          = isset($params['task_name']) ? $params['task_name'] : false;
    $google_drive_token = isset($params['google_drive_token']) ? $params['google_drive_token'] : false;
    $resultUuid         = !empty($params['resultUuid']) ? $params['resultUuid'] : false;

    if ($task_name) {
        $return = $mmb_core->backup_instance->task_now($task_name, $google_drive_token, $resultUuid);
        if (is_array($return) && array_key_exists('error', $return)) {
            mmb_response($return['error'], false);
        } else {
            mmb_response($return, true);
        }
    } else {
        mmb_response("Task name is not provided.", false);
    }
}

function mmb_get_backup_req($params)
{
    global $mmb_core;
    $mmb_core->get_stats_instance();
    $return = $mmb_core->stats_instance->get_backup_req($params);

    mmb_response($return, true);
}

// Fires when Backup Now, or some backup task is saved.
function mmb_scheduled_backup($params)
{
    global $mmb_core;
    $mmb_core->get_backup_instance();
    $return = $mmb_core->backup_instance->set_backup_task($params);
    mmb_response($return, $return);
}

function mmm_delete_backup($params)
{
    global $mmb_core;
    $mmb_core->get_backup_instance();
    $return = $mmb_core->backup_instance->delete_backup($params);
    mmb_response($return, $return);
}

function mmb_restore_now($params)
{
    global $mmb_core;
    $mmb_core->get_backup_instance();
    $return = $mmb_core->backup_instance->restore($params);
    if (is_array($return) && array_key_exists('error', $return)) {
        mmb_response($return['error'], false);
    } else {
        mmb_response($return, true);
    }
}

function mmb_remote_backup_now($params)
{
    global $mmb_core;
    $backup_instance = $mmb_core->get_backup_instance();
    $return          = $mmb_core->backup_instance->remote_backup_now($params);
    if (is_array($return) && array_key_exists('error', $return)) {
        mmb_response($return['error'], false);
    } else {
        mmb_response($return, true);
    }
}

function mmb_run_forked_action()
{
    if (!isset($_POST['mmb_fork_nonce'])) {
        return false;
    }

    $originalUser = wp_get_current_user();
    $usernameUsed = array_key_exists('username', $_POST) ? $_POST : null;

    if ($usernameUsed && !is_user_logged_in()) {
        $user = function_exists('get_user_by') ? get_user_by('login', $_POST['username']) : get_user_by('login', $_POST['username']);
    }

    if (isset($user) && isset($user->ID)) {
        wp_set_current_user($user->ID);
        // Compatibility with All In One Security
        update_user_meta($user->ID, 'last_login_time', current_time('mysql'));
    }

    if (!wp_verify_nonce($_POST['mmb_fork_nonce'], 'mmb-fork-nonce')) {
        wp_set_current_user($originalUser->ID);

        return false;
    }

    $public_key = get_option('_worker_public_key');
    if (!isset($_POST['public_key']) || $public_key !== $_POST['public_key']) {
        wp_set_current_user($originalUser->ID);

        return false;
    }
    $args           = @json_decode(stripslashes($_POST['args']), true);
    $args['forked'] = true;

    if (!isset($args)) {
        wp_set_current_user($originalUser->ID);

        return false;
    }
    $cron_action = isset($_POST['mwp_forked_action']) ? $_POST['mwp_forked_action'] : false;
    if ($cron_action) {
        do_action($cron_action, $args);
    }
    //unset($_POST['public_key']);
    unset($_POST['mmb_fork_nonce']);
    unset($_POST['args']);
    unset($_POST['mwp_forked_action']);

    wp_set_current_user($originalUser->ID);

    return true;
}

function mmb_update_worker_plugin($params)
{
    global $mmb_core;
    if (!empty($params['version'])) {
        $recoveryKit = new MwpRecoveryKit();
        update_option('mwp_incremental_update_active', time());
        try {
            $files = $recoveryKit->recover($params['version']);
        } catch (Exception $e) {
            update_option('mwp_incremental_update_active', '');
            throw $e;
        }
        update_option('mwp_incremental_update_active', '');
        mmb_response(array('files' => $files, 'success' => 'ManageWP Worker plugin successfully updated'), true);
    } else {
        mmb_response($mmb_core->update_worker_plugin($params), true);
    }
}

function mmb_install_addon($params)
{
    global $mmb_core;

    mwp_context()->requireTheme();
    mwp_load_required_components();

    $mmb_core->get_installer_instance();
    $return = $mmb_core->installer_instance->install_remote_file($params);
    mmb_response($return, true);
}

function mmb_do_upgrade($params)
{
    global $mmb_core, $mmb_upgrading;

    mwp_context()->requireTheme();

    $mmb_core->get_installer_instance();
    $return = $mmb_core->installer_instance->do_upgrade($params);
    mmb_response($return, true);
}

function mmb_get_comments($params)
{
    global $mmb_core;
    $mmb_core->get_comment_instance();
    $return = $mmb_core->comment_instance->get_comments($params);
    if (is_array($return) && array_key_exists('error', $return)) {
        mmb_response($return['error'], false);
    } else {
        mmb_response($return, true);
    }
}

function mmb_bulk_action_comments($params)
{
    global $mmb_core;
    $mmb_core->get_comment_instance();

    $return = $mmb_core->comment_instance->bulk_action_comments($params);
    if (is_array($return) && array_key_exists('error', $return)) {
        mmb_response($return['error'], false);
    } else {
        mmb_response($return, true);
    }
}

function mmb_reply_comment($params)
{
    global $mmb_core;
    $mmb_core->get_comment_instance();

    $return = $mmb_core->comment_instance->reply_comment($params);
    if (is_array($return) && array_key_exists('error', $return)) {
        mmb_response($return['error'], false);
    } else {
        mmb_response($return, true);
    }
}

function mmb_add_user($params)
{
    global $mmb_core;
    $mmb_core->get_user_instance();
    $return = $mmb_core->user_instance->add_user($params);
    if (is_array($return) && array_key_exists('error', $return)) {
        mmb_response($return['error'], false);
    } else {
        mmb_response($return, true);
    }
}

function mmb_get_users($params)
{
    global $mmb_core;
    $mmb_core->get_user_instance();
    $return = $mmb_core->user_instance->get_users($params);
    if (is_array($return) && array_key_exists('error', $return)) {
        mmb_response($return['error'], false);
    } else {
        mmb_response($return, true);
    }
}

function mmb_edit_users($params)
{
    global $mmb_core;
    $mmb_core->get_user_instance();
    $users       = $mmb_core->user_instance->edit_users($params);
    $response    = 'User updated.';
    $check_error = false;
    foreach ($users as $username => $user) {
        $check_error = is_array($user) && array_key_exists('error', $user);
        if ($check_error) {
            $response = $username.': '.$user['error'];
        }
    }
    mmb_response($response, !$check_error);
}

function mmb_get_posts($params)
{
    global $mmb_core;
    $mmb_core->get_post_instance();

    $return = $mmb_core->post_instance->get_posts($params);
    if (is_array($return) && array_key_exists('error', $return)) {
        mmb_response($return['error'], false);
    } else {
        mmb_response($return, true);
    }
}

function mmb_delete_post($params)
{
    global $mmb_core;
    $mmb_core->get_post_instance();

    $return = $mmb_core->post_instance->delete_post($params);
    if (is_array($return) && array_key_exists('error', $return)) {
        mmb_response($return['error'], false);
    } else {
        mmb_response($return, true);
    }
}

function mmb_delete_posts($params)
{
    global $mmb_core;
    $mmb_core->get_post_instance();

    $return = $mmb_core->post_instance->delete_posts($params);
    if (is_array($return) && array_key_exists('error', $return)) {
        mmb_response($return['error'], false);
    } else {
        mmb_response($return, true);
    }
}

function mmb_get_pages($params)
{
    global $mmb_core;
    $mmb_core->get_post_instance();

    $return = $mmb_core->post_instance->get_pages($params);
    if (is_array($return) && array_key_exists('error', $return)) {
        mmb_response($return['error'], false);
    } else {
        mmb_response($return, true);
    }
}

function mmb_delete_page($params)
{
    global $mmb_core;
    $mmb_core->get_post_instance();

    $return = $mmb_core->post_instance->delete_page($params);
    if (is_array($return) && array_key_exists('error', $return)) {
        mmb_response($return['error'], false);
    } else {
        mmb_response($return, true);
    }
}

function mmb_iframe_plugins_fix($update_actions)
{
    foreach ($update_actions as $key => $action) {
        $update_actions[$key] = str_replace('target="_parent"', '', $action);
    }

    return $update_actions;
}

function mmb_execute_php_code($params)
{
    ob_start();
    $errorHandler = new MWP_Debug_EvalErrorHandler();
    set_error_handler(array($errorHandler, 'handleError'));

    $returnValue = eval($params['code']); // This code handles the "Execute PHP Snippet" functionality on ManageWP and is not a security issue.
    $errors      = $errorHandler->getErrorMessages();
    restore_error_handler();
    $return = array('output' => ob_get_clean(), 'returnValue' => $returnValue);

    if (count($errors)) {
        $return['errorLog'] = $errors;
    }

    $lastError  = error_get_last();
    $fatalError = null;

    if (($lastError !== null)
        && ($lastError['type'] & (E_PARSE | E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR))
        && (strpos($lastError['file'], __FILE__) !== false)
        && (strpos($lastError['file'], 'eval()') !== false)
    ) {
        $return['fatalError'] = $lastError;
    }

    mmb_response($return, true);
}

function mmb_more_reccurences($schedules)
{
    $schedules['halfminute']  = array('interval' => 30, 'display' => 'Once in a half minute');
    $schedules['minutely']    = array('interval' => 60, 'display' => 'Once in a minute');
    $schedules['fiveminutes'] = array('interval' => 300, 'display' => 'Once every five minutes');
    $schedules['tenminutes']  = array('interval' => 600, 'display' => 'Once every ten minutes');
    $schedules['sixhours']    = array('interval' => 21600, 'display' => 'Every six hours');
    $schedules['fourhours']   = array('interval' => 14400, 'display' => 'Every four hours');
    $schedules['threehours']  = array('interval' => 10800, 'display' => 'Every three hours');

    return $schedules;
}

function mmb_call_scheduled_remote_upload($args)
{
    global $mmb_core;

    $mmb_core->get_backup_instance();
    if (isset($args['task_name'])) {
        $mmb_core->backup_instance->remote_backup_now($args);
    }
}

function mwp_check_notifications()
{
    global $mmb_core;

    $mmb_core->get_stats_instance();
    $mmb_core->stats_instance->check_notifications();
}

function mmb_get_plugins_themes($params)
{
    global $mmb_core;

    mwp_context()->requireTheme();

    $mmb_core->get_installer_instance();
    $return = $mmb_core->installer_instance->get($params);
    mmb_response($return, true);
}

function mmb_get_autoupdate_plugins_themes($params)
{
    mwp_context()->requireTheme();

    $return = MMB_Updater::getSettings($params);
    mmb_response($return, true);
}

function mmb_edit_plugins_themes($params)
{
    global $mmb_core;
    $mmb_core->get_installer_instance();
    $return = $mmb_core->installer_instance->edit($params);
    mmb_response($return, true);
}

function mmb_edit_autoupdate_plugins_themes($params)
{
    $return = MMB_Updater::setSettings($params);
    mmb_response($return, true);
}

function mmb_worker_brand($params)
{
    $worker_brand       = get_option('mwp_worker_brand');
    $current_from_orion = !empty($worker_brand['from_orion']) ? $worker_brand['from_orion'] : false;
    $from_orion         = !empty($params['brand']['from_orion']) ? $params['brand']['from_orion'] : false;

    if ($from_orion === false && $current_from_orion !== $from_orion) {
        mmb_response(true, true); //@TODO: Maybe return mmb_response(true, false)
        return;
    }

    update_option("mwp_worker_brand", $params['brand']);
    mmb_response(true, true);
}

function mmb_maintenance_mode($params)
{
    global $wp_object_cache;

    $default = get_option('mwp_maintenace_mode');
    $params  = empty($default) ? $params : array_merge($default, $params);
    update_option("mwp_maintenace_mode", $params);

    if (!empty($wp_object_cache)) {
        @$wp_object_cache->flush();
    }
    mmb_response(true, true);
}

function mmb_plugin_actions()
{
    global $pagenow, $current_user, $mmode;
    if (!is_admin() && !in_array($pagenow, array('wp-login.php'))) {
        $mmode = get_option('mwp_maintenace_mode');
        if (!empty($mmode)) {
            if (isset($mmode['active']) && $mmode['active'] == true) {
                $status_code = empty($mmode['status_code']) ? 503 : $mmode['status_code'];
                if (!empty($current_user->ID) && !empty($mmode['hidecaps'])) {
                    $usercaps = array();
                    if (isset($current_user->caps) && !empty($current_user->caps)) {
                        $usercaps = $current_user->caps;
                    }
                    foreach ($mmode['hidecaps'] as $cap => $hide) {
                        if (!$hide) {
                            continue;
                        }

                        foreach ($usercaps as $ucap => $val) {
                            if ($ucap == $cap) {
                                ob_end_clean();
                                ob_end_flush();
                                if (!headers_sent()) {
                                    if ($status_code == 503) {
                                        header(sprintf('%s 503 Service Unavailable', isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1'), true, $status_code);
                                    } else {
                                        header(sprintf('%s %d Service Unavailable', isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1', $status_code), true, $status_code);
                                    }
                                }
                                die($mmode['template']);
                            }
                        }
                    }
                } else {
                    if (!headers_sent()) {
                        if ($status_code == 503) {
                            header(sprintf('%s 503 Service Unavailable', isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1'), true, 503);
                        } else {
                            header(sprintf('%s %d Service Unavailable', isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1', $status_code), true, $status_code);
                        }
                    }
                    die($mmode['template']);
                }
            }
        }
    }

    if (file_exists(dirname(__FILE__).'/log')) {
        unlink(dirname(__FILE__).'/log');
    }
}

function mwp_return_core_reference()
{
    global $mmb_core, $mmb_core_backup;
    if (!$mmb_core instanceof MMB_Core) {
        $mmb_core = $mmb_core_backup;
    }
}

function mwb_edit_redirect_override($location = false, $comment_id = false)
{
    if (isset($_COOKIE[MMB_XFRAME_COOKIE])) {
        $location = get_site_url().'/wp-admin/edit-comments.php';
    }

    return $location;
}

function mwp_set_plugin_priority()
{
    $pluginBasename = 'worker/init.php';
    $activePlugins  = get_option('active_plugins');

    if (reset($activePlugins) === $pluginBasename) {
        return;
    }

    $workerKey = array_search($pluginBasename, $activePlugins);

    if ($workerKey === false) {
        return;
    }

    unset($activePlugins[$workerKey]);
    array_unshift($activePlugins, $pluginBasename);
    update_option('active_plugins', array_values($activePlugins));
}

/**
 * @return MMB_Core
 */
function mwp_core()
{
    static $core;

    global $mmb_core;

    if (!$mmb_core instanceof MMB_Core) {
        $mmb_core = new MMB_Core();
        $core     = $mmb_core;
    }

    return $core;
}

/**
 * Auto-loads classes that may not exists after this plugin's update.
 */
function mwp_load_required_components()
{
    class_exists('MWP_Http_ResponseInterface');
    class_exists('MWP_Http_Response');
    class_exists('MWP_Http_LegacyWorkerResponse');
    class_exists('MWP_Http_JsonResponse');
    class_exists('MWP_Worker_ActionResponse');
    class_exists('MWP_Worker_Exception');
    class_exists('MWP_Event_ActionResponse');
    class_exists('MWP_Event_MasterResponse');
}

function mmb_change_comment_status($params)
{
    global $mmb_core;
    $mmb_core->get_comment_instance();
    $return = $mmb_core->comment_instance->change_status($params);
    if ($return) {
        $mmb_core->get_stats_instance();
        mmb_response($mmb_core->stats_instance->get_comments_stats($params), true);
    } else {
        mmb_response('Comment not updated', false);
    }
}

function mwp_uninstall()
{
    delete_option('mwp_core_autoupdate');
    delete_option('mwp_recovering');
    delete_option('mwp_container_parameters');
    delete_option('mwp_container_site_parameters');
    $loaderName = '0-worker.php';
    try {
        $mustUsePluginDir = rtrim(WPMU_PLUGIN_DIR, '/');
        $loaderPath       = $mustUsePluginDir.'/'.$loaderName;

        if (!file_exists($loaderPath)) {
            return;
        }

        $removed = @unlink($loaderPath);

        if (!$removed) {
            $error = error_get_last();
            throw new Exception(sprintf('Unable to remove loader: %s', $error['message']));
        }
    } catch (Exception $e) {
        mwp_logger()->error('Unable to remove loader', array('exception' => $e));
    }
}

function mwp_get_service_key()
{
    $serviceKey = get_option('mwp_service_key');
    if (empty($serviceKey)) {
        $serviceKey = mwp_generate_uuid4();
        update_option('mwp_service_key', $serviceKey, true);
    }

    return $serviceKey;
}

function mwp_get_communication_key()
{
    return get_option('mwp_communication_key');
}

function mwp_accept_potential_key()
{
    $potentialKey = mwp_get_potential_key();

    update_option('mwp_communication_key', $potentialKey, true);
    delete_option('mwp_potential_key');
    delete_option('mwp_potential_key_time');

    return $potentialKey;
}

function mwp_get_potential_key()
{
    $potentialKey     = get_option('mwp_potential_key');
    $potentialKeyTime = get_option('mwp_potential_key_time');
    $now              = time();

    if (empty($potentialKey) || empty($potentialKeyTime) || ($now - $potentialKeyTime) > 86400) {
        $potentialKey     = mwp_generate_uuid4();
        $potentialKeyTime = $now;
        update_option('mwp_potential_key', $potentialKey, true);
        update_option('mwp_potential_key_time', $potentialKeyTime, true);
    }

    return $potentialKey;
}

function mwp_provision_keys()
{
    mwp_get_service_key();
    mwp_get_potential_key();
}

function mwp_generate_uuid4()
{
    $data = null;
    if (function_exists('openssl_random_pseudo_bytes')) {
        $data = openssl_random_pseudo_bytes(16);
    }

    if (empty($data)) {
        $data = '';
        for ($i = 0; $i < 16; ++$i) {
            $data .= chr(mt_rand(0, 255));
        }
    }

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function mwp_refresh_live_public_keys($params = array())
{
    $liveContent = mwp_get_public_keys_from_live();
    $liveKeys    = !empty($liveContent) ? @json_decode($liveContent, true) : null;

    if (empty($liveKeys)) {
        return;
    }

    update_option('mwp_public_keys', $liveKeys, true);
}

function mwp_get_public_keys_from_live()
{
    $result = file_get_contents('https://cdn.managewp.com/public-keys', false, stream_context_create(array(
        'ssl' => array(
            'verify_peer'       => true,
            'verify_peer_name'  => true,
            'allow_self_signed' => false,
            'cafile'            => dirname(__FILE__).'/publickeys/godaddy_g2_root.cer',
        ),
    )));

    if ($result === false) {
        return mwp_get_public_keys_from_live_fallback();
    }

    return $result;
}

function mwp_get_public_keys_from_live_fallback()
{
    $transports         = array_flip(stream_get_transports());
    $preferredTransport = array(
        'tls',
        'tlsv1.2',
        'tlsv1.1',
        'tlsv1.0',
    );

    $transportToUse = null;

    foreach ($preferredTransport as $transport) {
        if (!empty($transportToUse) || !isset($transports[$transport])) {
            continue;
        }

        $transportToUse = $transport;
    }

    $socket = stream_socket_client("$transportToUse://cdn.managewp.com:443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, stream_context_create(array(
        'ssl' => array(
            'verify_peer'       => true,
            'verify_peer_name'  => true,
            'allow_self_signed' => false,
            'cafile'            => dirname(__FILE__).'/publickeys/godaddy_g2_root.cer',
        ),
    )));

    if (!$socket) {
        return null;
    }

    $requestContent = <<<EOL
GET /public-keys HTTP/1.1
Host: cdn.managewp.com
Accept-Language: en-US,en;q=0.9,hr;q=0.8,sr;q=0.7
Upgrade-Insecure-Requests: 1
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8
Cache-Control: max-age=0
Authority: cdn.managewp.com
Connection: close


EOL;


    if (fwrite($socket, $requestContent) === false) {
        return null;
    }

    do {
        $line = fgets($socket);
    } while ($line !== false && $line !== "\n" && $line !== "\r\n");

    if ($line === false) {
        return null;
    }

    $content = stream_get_contents($socket);

    fclose($socket);

    if ($content === false || !is_string($content)) {
        return null;
    }

    return $content;
}
