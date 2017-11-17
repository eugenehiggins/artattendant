<?php

/*************************************************************
 * stats.class.php
 * Get Site Stats
 * Copyright (c) 2011 Prelovac Media
 * www.prelovac.com
 **************************************************************/
class MMB_Stats extends MMB_Core
{
    /*************************************************************
     * FACADE functions
     * (functions to be called after a remote call from Master)
     **************************************************************/

    public function get_site_statistics($stats, $options = array())
    {
        /** @var wpdb $wpdb */
        global $wpdb;
        $siteStatistics = array();
        $prefix         = $wpdb->prefix;
        $basePrefix     = $wpdb->base_prefix;

        if (!empty($options['users'])) {
            if (!$this->mmb_multisite) {
                $siteStatistics['users'] = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$basePrefix}users");
            } else {
                $siteStatistics['users'] = count(get_users(
                    array(
                        'blog_id' => $wpdb->blogid,
                    )
                ));
            }
        }

        if (!empty($options['approvedComments'])) {
            $siteStatistics['approvedComments'] = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$prefix}comments c INNER JOIN {$prefix}posts p ON c.comment_post_ID = p.ID WHERE comment_approved = '1' AND p.post_status = 'publish'");
        }

        if (!empty($options['activePlugins'])) {
            $siteStatistics['activePlugins'] = count((array)get_option('active_plugins', array()));
        }

        if (!empty($options['publishedPosts'])) {
            $siteStatistics['publishedPosts'] = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$prefix}posts WHERE post_type='post' AND post_status='publish'");
        }

        if (!empty($options['draftPosts'])) {
            $siteStatistics['draftPosts'] = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$prefix}posts WHERE post_type='post' AND post_status='draft'");
        }

        if (!empty($options['publishedPages'])) {
            $siteStatistics['publishedPages'] = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$prefix}posts WHERE post_type='page' AND post_status='publish'");
        }

        if (!empty($options['draftPages'])) {
            $siteStatistics['draftPages'] = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$prefix}posts WHERE post_type='page' AND post_status='draft'");
        }

        $stats['site_statistics'] = $siteStatistics;

        return $stats;
    }

    public function get_core_update($stats, $options = array())
    {
        global $wp_version;
        $current_transient = null;
        if (isset($options['core']) && $options['core']) {
            $locale = get_locale();
            $core   = $this->mmb_get_transient('update_core');
            if (isset($core->updates) && !empty($core->updates)) {
                foreach ($core->updates as $update) {
                    if ($update->locale == $locale && strtolower($update->response) == "upgrade") {
                        $current_transient = $update;
                        break;
                    }
                }
                //fallback to first
                if (!$current_transient) {
                    $current_transient = $core->updates[0];
                }
                // WordPress can actually have an update to the same version and locale if locale has not been updated
                if ($current_transient->response == 'development' || $current_transient->response == 'upgrade' || version_compare($wp_version, $current_transient->current, '<') || $locale !== $current_transient->locale) {
                    $current_transient->current_version = $wp_version;
                    $stats['core_updates']              = $current_transient;
                } else {
                    $stats['core_updates'] = false;
                }
            } else {
                $stats['core_updates'] = false;
            }
        }

        return $stats;
    }

    public function get_hit_counter($stats, $options = array())
    {
        $stats['hit_counter'] = get_option('user_hit_count');

        return $stats;
    }

    public function get_comments($stats, $options = array())
    {
        $nposts  = isset($options['numberposts']) ? (int)$options['numberposts'] : 20;
        $trimlen = isset($options['trimcontent']) ? (int)$options['trimcontent'] : 200;

        if ($nposts) {
            $comments = get_comments('status=hold&number='.$nposts);
            if (!empty($comments)) {
                foreach ($comments as &$comment) {
                    $commented_post           = get_post($comment->comment_post_ID);
                    $comment->post_title      = $commented_post->post_title;
                    $comment->comment_content = $this->trim_content($comment->comment_content, $trimlen);
                    $comment->comment_content = seems_utf8($comment->comment_content) ? $comment->comment_content : utf8_encode($comment->comment_content);
                    unset($comment->comment_author_IP);
                    unset($comment->comment_karma);
                    unset($comment->comment_agent);
                    unset($comment->comment_type);
                    unset($comment->comment_parent);
                }
                $stats['comments']['pending'] = $comments;
            }

            $comments = get_comments('status=approve&number='.$nposts);
            if (!empty($comments)) {
                foreach ($comments as &$comment) {
                    $commented_post           = get_post($comment->comment_post_ID);
                    $comment->post_title      = $commented_post->post_title;
                    $comment->comment_content = $this->trim_content($comment->comment_content, $trimlen);
                    $comment->comment_content = seems_utf8($comment->comment_content) ? $comment->comment_content : utf8_encode($comment->comment_content);
                    unset($comment->comment_author_IP);
                    unset($comment->comment_karma);
                    unset($comment->comment_agent);
                    unset($comment->comment_type);
                    unset($comment->comment_parent);
                }
                $stats['comments']['approved'] = $comments;
            }
        }

        return $stats;
    }

    public function get_posts($stats, $options = array())
    {
        $nposts    = isset($options['numberposts']) ? (int)$options['numberposts'] : 20;
        $user_info = $this->getUsersIDs();

        if ($nposts) {
            $posts        = get_posts('post_status=publish&numberposts='.$nposts.'&orderby=post_date&order=desc');
            $recent_posts = array();
            if (!empty($posts)) {
                foreach ($posts as $id => $recent_post) {
                    $recent                 = new stdClass();
                    $recent->post_permalink = get_permalink($recent_post->ID);
                    $recent->ID             = $recent_post->ID;
                    $recent->post_date      = $recent_post->post_date;
                    $recent->post_title     = $recent_post->post_title;
                    $recent->post_type      = $recent_post->post_type;
                    $recent->comment_count  = (int)$recent_post->comment_count;

                    $author_name              = isset($user_info[$recent_post->post_author]) ? $user_info[$recent_post->post_author] : '';
                    $recent->post_author_name = array('author_id' => $recent_post->post_author, 'author_name' => $author_name);

                    $recent_posts[] = $recent;
                }
            }

            $posts                  = get_pages('post_status=publish&numberposts='.$nposts.'&orderby=post_date&order=desc');
            $recent_pages_published = array();
            if (!empty($posts)) {
                foreach ((array)$posts as $id => $recent_page_published) {
                    $recent                 = new stdClass();
                    $recent->post_permalink = get_permalink($recent_page_published->ID);
                    $recent->post_type      = $recent_page_published->post_type;
                    $recent->ID             = $recent_page_published->ID;
                    $recent->post_date      = $recent_page_published->post_date;
                    $recent->post_title     = $recent_page_published->post_title;

                    $author_name         = isset($user_info[$recent_page_published->post_author]) ? $user_info[$recent_page_published->post_author] : '';
                    $recent->post_author = array('author_id' => $recent_page_published->post_author, 'author_name' => $author_name);

                    $recent_posts[] = $recent;
                }
            }
            if (!empty($recent_posts)) {
                usort(
                    $recent_posts,
                    array(
                        $this,
                        'cmp_posts_worker',
                    )
                );
                $stats['posts'] = array_slice($recent_posts, 0, $nposts);
            }
        }

        return $stats;
    }

    public function get_drafts($stats, $options = array())
    {
        $nposts = isset($options['numberposts']) ? (int)$options['numberposts'] : 20;

        if ($nposts) {
            $drafts        = get_posts('post_status=draft&numberposts='.$nposts.'&orderby=post_date&order=desc');
            $recent_drafts = array();
            if (!empty($drafts)) {
                foreach ($drafts as $id => $recent_draft) {
                    $recent                 = new stdClass();
                    $recent->post_permalink = get_permalink($recent_draft->ID);
                    $recent->post_type      = $recent_draft->post_type;
                    $recent->ID             = $recent_draft->ID;
                    $recent->post_date      = $recent_draft->post_date;
                    $recent->post_title     = $recent_draft->post_title;

                    $recent_drafts[] = $recent;
                }
            }
            $drafts              = get_pages('post_status=draft&numberposts='.$nposts.'&orderby=post_date&order=desc');
            $recent_pages_drafts = array();
            if (!empty($drafts)) {
                foreach ((array)$drafts as $id => $recent_pages_draft) {
                    $recent                 = new stdClass();
                    $recent->post_permalink = get_permalink($recent_pages_draft->ID);
                    $recent->ID             = $recent_pages_draft->ID;
                    $recent->post_type      = $recent_pages_draft->post_type;
                    $recent->post_date      = $recent_pages_draft->post_date;
                    $recent->post_title     = $recent_pages_draft->post_title;

                    $recent_drafts[] = $recent;
                }
            }
            if (!empty($recent_drafts)) {
                usort($recent_drafts, array($this, 'cmp_posts_worker',));
                $stats['drafts'] = array_slice($recent_drafts, 0, $nposts);
            }
        }

        return $stats;
    }

    public function get_scheduled($stats, $options = array())
    {
        $numberOfItems  = isset($options['numberposts']) ? (int)$options['numberposts'] : 20;
        $scheduledItems = array();

        if (!$numberOfItems) {
            return $stats;
        }
        $scheduledPosts = get_posts('post_status=future&numberposts='.$numberOfItems.'&orderby=post_date&order=desc');
        foreach ($scheduledPosts as $id => $scheduledPost) {
            $recentPost                 = new stdClass();
            $recentPost->post_permalink = get_permalink($scheduledPost->ID);
            $recentPost->ID             = $scheduledPost->ID;
            $recentPost->post_date      = $scheduledPost->post_date;
            $recentPost->post_type      = $scheduledPost->post_type;
            $recentPost->post_title     = $scheduledPost->post_title;

            $scheduledItems[] = $recentPost;
        }
        $scheduledPages = get_pages('post_status=future&numberposts='.$numberOfItems.'&orderby=post_date&order=desc');
        foreach ((array)$scheduledPages as $id => $scheduledPage) {
            $recentPage                 = new stdClass();
            $recentPage->post_permalink = get_permalink($scheduledPage->ID);
            $recentPage->ID             = $scheduledPage->ID;
            $recentPage->post_type      = $scheduledPage->post_type;
            $recentPage->post_date      = $scheduledPage->post_date;
            $recentPage->post_title     = $scheduledPage->post_title;

            $scheduledItems[] = $recentPage;
        }
        if (!empty($scheduledItems)) {
            usort($scheduledItems, array($this, 'cmp_posts_worker'));
            $stats['scheduled'] = array_slice($scheduledItems, 0, $numberOfItems);
        }

        return $stats;
    }

    public function get_backup_stats()
    {
        $tasks = get_option('mwp_backup_tasks');

        if (empty($tasks) || !is_array($tasks)) {
            return array();
        }

        $stats = array();

        foreach ($tasks as $task_name => $info) {
            if (empty($info['task_results']) || !is_array($info['task_results'])) {
                continue;
            }

            foreach ($info['task_results'] as $key => $result) {
                if (!isset($result['server']) || isset($result['error'])) {
                    continue;
                }

                if (!isset($result['server']['file_path']) || $info['task_args']['del_host_file']) {
                    continue;
                }

                if (file_exists($result['server']['file_path'])) {
                    continue;
                }

                $info['task_results'][$key]['error'] = 'Backup created but manually removed from server.';
            }

            $stats[$task_name] = $info['task_results'];
        }

        return $stats;
    }

    public function get_backups($stats, $options = array())
    {
        $stats['mwp_backups'] = $this->get_backup_stats();

        return $stats;
    }

    public function get_backup_req($stats = array(), $options = array())
    {
        $stats['mwp_backups'] = $this->get_backup_stats();

        if ($_SERVER['HTTP_MWP_PROTOCOL']) {
            $stats['mwp_backup_req'] = $this->get_backup_instance()->check_backup_compat();
        }

        return $stats;
    }

    public function get_updates($stats, $options = array())
    {
        if (isset($options['themes']) && $options['themes']) {
            $this->get_installer_instance();
            $upgrades = $this->installer_instance->get_upgradable_themes();
            if (!empty($upgrades)) {
                $stats['upgradable_themes'] = $upgrades;
            }
        }

        if (isset($options['plugins']) && $options['plugins']) {
            $this->get_installer_instance();
            $upgrades = $this->installer_instance->get_upgradable_plugins();
            if (!empty($upgrades)) {
                $stats['upgradable_plugins'] = $upgrades;
            }
        }

        if (isset($options['translations']) && $options['translations']) {
            $this->get_installer_instance();
            $upgrades = $this->installer_instance->get_upgradable_translations();
            if (!empty($upgrades)) {
                $stats['upgradable_translations'] = $upgrades;
            }
        }

        return $stats;
    }

    public function get_errors($stats, $options = array())
    {
        $period     = isset($options['days']) ? (int)$options['days'] * 86400 : 86400;
        $maxerrors  = isset($options['max']) ? (int)$options['max'] : 100;
        $last_bytes = isset($options['last_bytes']) ? (int)$options['last_bytes'] : 20480; //20KB
        $errors     = array();
        if (isset($options['get']) && $options['get'] == true) {
            if (function_exists('ini_get')) {
                $logpath = ini_get('error_log');
                if (!empty($logpath) && file_exists($logpath)) {
                    $logfile    = @fopen($logpath, 'r');
                    $filesize   = @filesize($logpath);
                    $read_start = 0;
                    if (is_resource($logfile) && $filesize > 0) {
                        if ($filesize > $last_bytes) {
                            $read_start = $filesize - $last_bytes;
                        }
                        fseek($logfile, $read_start, SEEK_SET);
                        while (!feof($logfile)) {
                            $line = fgets($logfile);
                            preg_match('/\[(.*)\]/Ui', $line, $match);
                            if (!empty($match) && (strtotime($match[1]) > ((int)time() - $period))) {
                                $key = str_replace($match[0], '', $line);
                                if (!isset($errors[$key])) {
                                    $errors[$key] = 1;
                                } else {
                                    $errors[$key] = $errors[$key] + 1;
                                }
                                if (count($errors) >= $maxerrors) {
                                    break;
                                }
                            }
                        }
                    }
                    if (is_resource($logfile)) {
                        fclose($logfile);
                    }
                    if (!empty($errors)) {
                        $stats['errors']  = $errors;
                        $stats['logpath'] = $logpath;
                        $stats['logsize'] = $filesize;
                    }
                }
            }
        }

        return $stats;
    }

    public function getUserList()
    {
        $filter = array(
            'user_roles'      => array(
                'administrator',
            ),
            'username'        => '',
            'username_filter' => '',
        );
        $users  = $this->get_user_instance()->get_users($filter);

        if (empty($users['users']) || !is_array($users['users'])) {
            return array();
        }

        $userList = array();
        foreach ($users['users'] as $user) {
            $userList[] = $user['user_login'];
        }

        return $userList;
    }

    private function remove_filter_by_plugin_class($tag, $class_name)
    {
        if (!class_exists($class_name)) {
            return null;
        }

        global $wp_filter;

        if (empty($wp_filter[$tag][10])) {
            return null;
        }

        foreach ($wp_filter[$tag][10] as $callable) {
            if (empty($callable['function']) || !is_array($callable['function'])) {
                continue;
            }

            if (!is_a($callable['function'][0], $class_name)) {
                continue;
            }

            remove_filter($tag, $callable['function']);

            return $callable['function'];
        }

        return null;
    }

    public function pre_init_stats($params)
    {
        include_once ABSPATH.'wp-includes/update.php';
        include_once ABSPATH.'wp-admin/includes/update.php';

        mwp_logger()->debug('Started initializing stats');

        $stats = $this->mmb_parse_action_params('pre_init_stats', $params, $this);

        mwp_logger()->debug('Finished initializing stats');

        extract($params);

        mwp_logger()->debug('Extracted parameters...');

        /** @var $wpdb wpdb */
        global $wpdb, $wp_version, $mmb_plugin_dir;

        $stats['worker_version']        = $GLOBALS['MMB_WORKER_VERSION'];
        $stats['worker_revision']       = $GLOBALS['MMB_WORKER_REVISION'];
        $stats['wordpress_version']     = $wp_version;
        $stats['wordpress_locale_pckg'] = get_locale();
        $stats['php_version']           = phpversion();
        $stats['mysql_version']         = $wpdb->db_version();
        $stats['wp_multisite']          = $this->mmb_multisite;
        $stats['network_install']       = $this->network_admin_install;

        mwp_logger()->debug('Started encrypting cookies...');

        $stats['cookies'] = $this->get_stat_cookies();

        mwp_logger()->debug('Finished encrypting cookies...');

        $stats['admin_usernames']       = $this->getUserList();
        $stats['site_title']            = get_bloginfo('name');
        $stats['site_tagline']          = get_bloginfo('description');
        $stats['blog_public']           = get_option('blog_public');
        $stats['timezone']              = get_option('timezone_string');
        $stats['timezone_offset']       = get_option('gmt_offset');
        $stats['server_ip']             = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : null;
        $stats['hostname']              = php_uname('n');
        $stats['db_name']               = $this->get_active_db();
        $stats['db_prefix']             = $wpdb->prefix;
        $stats['content_path']          = WP_CONTENT_DIR;
        $stats['absolute_path']         = ABSPATH;
        $stats['worker_path']           = $mmb_plugin_dir;
        $stats['site_home']             = get_option('home');

        $fs = new Symfony_Filesystem_Filesystem();
        if (defined('WP_CONTENT_DIR')) {
            $contentDir = WP_CONTENT_DIR;
            if (substr($contentDir, 0, 1) != '/' && strpos($contentDir, ABSPATH) === false) {
                $contentDir = ABSPATH.$contentDir;
            }
            $stats['content_relative_path'] = $fs->makePathRelative($contentDir, ABSPATH);
        }

        if (defined('WP_PLUGIN_DIR')) {
            $pluginDir = WP_PLUGIN_DIR;
            if (substr($pluginDir, 0, 1) != '/' && strpos($pluginDir, ABSPATH) === false) {
                $pluginDir = ABSPATH.$pluginDir;
            }
            $stats['plugin_relative_path'] = $fs->makePathRelative($pluginDir, ABSPATH);
        }

        if (defined('WPMU_PLUGIN_DIR')) {
            $muPluginDir = WPMU_PLUGIN_DIR;
            if (substr($muPluginDir, 0, 1) != '/' && strpos($muPluginDir, ABSPATH) === false) {
                $muPluginDir = ABSPATH.$muPluginDir;
            }
            $stats['mu_plugin_relative_path'] = $fs->makePathRelative($muPluginDir, ABSPATH);
        }

        $uploadDirArray                 = wp_upload_dir();
        if (false === $uploadDir = realpath($uploadDirArray['basedir'])) {
            $uploadDir = $uploadDirArray['basedir'];
        }

        $stats['uploads_relative_path'] = $fs->makePathRelative($uploadDir, ABSPATH);

        $stats['writable']  = $this->is_server_writable();
        $stats['fs_method'] = !$this->check_if_pantheon() ? get_filesystem_method() : '';

        $mmode = get_option('mwp_maintenace_mode');

        if (!empty($mmode) && isset($mmode['active']) && $mmode['active'] == true) {
            $stats['maintenance'] = true;
        }

        if ($this->mmb_multisite) {
            $stats = array_merge($stats, $this->get_multisite($stats));
        }

        mwp_logger()->debug('Started getting extended stats (overhead)');

        update_option('mmb_stats_filter', $params['item_filter']['get_stats']);
        mmb_get_extended_info($stats, $params['item_filter']['get_stats']);

        mwp_logger()->debug('Finished getting extended stats (overhead)');

        return $stats;
    }

    public function get_multisite()
    {
        /** @var $wpdb wpdb */
        global $current_user, $wpdb;
        $user_blogs    = get_blogs_of_user($current_user->ID);
        $network_blogs = $wpdb->get_results("select `blog_id`, `site_id` from `{$wpdb->blogs}`");
        $user_id       = !empty($GLOBALS['mwp_user_id']) ? $GLOBALS['mwp_user_id'] : false;
        $mainBlogId    = defined('BLOG_ID_CURRENT_SITE') ? BLOG_ID_CURRENT_SITE : false;
        $stats         = array();

        if ($this->network_admin_install == '1' && is_super_admin($user_id)) {
            if (!empty($network_blogs)) {
                foreach ($network_blogs as $details) {
                    if (($mainBlogId !== false && $details->blog_id == $mainBlogId) || ($mainBlogId === false && $details->site_id == $details->blog_id)) {
                        continue;
                    } else {
                        $data = get_blog_details($details->blog_id);
                        if (in_array($details->blog_id, array_keys($user_blogs))) {
                            $stats['network_blogs'][] = $data->siteurl;
                        } else {
                            $user = get_users(
                                array(
                                    'blog_id' => $details->blog_id,
                                    'number'  => 1,
                                )
                            );
                            if (!empty($user)) {
                                $stats['other_blogs'][$data->siteurl] = $user[0]->user_login;
                            }
                        }
                    }
                }
            }
        }

        return $stats;
    }

    public function get_comments_stats()
    {
        $num_pending_comments  = 3;
        $num_approved_comments = 3;
        $pending_comments      = get_comments('status=hold&number='.$num_pending_comments);
        foreach ($pending_comments as &$comment) {
            $commented_post      = get_post($comment->comment_post_ID);
            $comment->post_title = $commented_post->post_title;
        }
        $stats['comments']['pending'] = $pending_comments;

        $approved_comments = get_comments('status=approve&number='.$num_approved_comments);
        foreach ($approved_comments as &$comment) {
            $commented_post      = get_post($comment->comment_post_ID);
            $comment->post_title = $commented_post->post_title;
        }
        $stats['comments']['approved'] = $approved_comments;

        return $stats;
    }

    public function get_auth_cookies($user_id)
    {
        $cookies = array();
        $secure  = is_ssl();
        $secure  = apply_filters('secure_auth_cookie', $secure, $user_id);

        if ($secure) {
            $auth_cookie_name = SECURE_AUTH_COOKIE;
            $scheme           = 'secure_auth';
        } else {
            $auth_cookie_name = AUTH_COOKIE;
            $scheme           = 'auth';
        }

        $expiration = time() + 2592000;

        $cookies[$auth_cookie_name] = wp_generate_auth_cookie($user_id, $expiration, $scheme);
        $cookies[LOGGED_IN_COOKIE]  = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');

        if (defined('WPE_APIKEY')) {
            $cookies['wpe-auth'] = md5('wpe_auth_salty_dog|'.WPE_APIKEY);
        }

        return $cookies;
    }

    public function get_stat_cookies()
    {
        if (!defined('WPE_APIKEY')) {
            return array();
        }

        global $current_user;

        $cookies = $this->get_auth_cookies($current_user->ID);

        $publicKey = $this->get_master_public_key();

        if (empty($cookies)) {
            return $cookies;
        }

        if (!class_exists('Crypt_RSA', false)) {
            require_once dirname(__FILE__).'/../../src/PHPSecLib/Crypt/RSA.php';
        }

        $rsa = new Crypt_RSA();
        $rsa->setEncryptionMode(CRYPT_RSA_SIGNATURE_PKCS1);
        $rsa->loadKey($publicKey, CRYPT_RSA_PUBLIC_FORMAT_PKCS1);

        foreach ($cookies as &$cookieValue) {
            $cookieValue = base64_encode($rsa->encrypt($cookieValue));
        }

        return $cookies;
    }

    public function get_initial_stats()
    {
        global $mmb_plugin_dir, $wpdb;

        $stats = array(
            'email'           => get_option('admin_email'),
            'no_openssl'      => $this->get_random_signature(),
            'content_path'    => WP_CONTENT_DIR,
            'worker_path'     => $mmb_plugin_dir,
            'worker_version'  => $GLOBALS['MMB_WORKER_VERSION'],
            'worker_revision' => $GLOBALS['MMB_WORKER_REVISION'],
            'site_title'      => get_bloginfo('name'),
            'site_tagline'    => get_bloginfo('description'),
            'db_name'         => $this->get_active_db(),
            'site_home'       => get_option('home'),
            'admin_url'       => admin_url(),
            'wp_multisite'    => $this->mmb_multisite,
            'network_install' => $this->network_admin_install,
            'cookies'         => $this->get_stat_cookies(),
            'timezone'        => get_option('timezone_string'),
            'timezone_offset' => get_option('gmt_offset'),
            'db_prefix'       => $wpdb->prefix,
        );

        if ($this->mmb_multisite) {
            $details = get_blog_details($this->mmb_multisite);
            if (isset($details->site_id)) {
                $details = get_blog_details($details->site_id);
                if (isset($details->siteurl)) {
                    $stats['network_parent'] = $details->siteurl;
                }
            }
        }

        $stats['writable'] = $this->is_server_writable();

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
                    array('site_statistics', array('users' => true, 'approvedComments' => true, 'activePlugins' => true, 'publishedPosts' => true, 'draftPosts' => true, 'publishedPages' => true, 'draftPages' => true)),
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

        $stats['initial_stats'] = $this->pre_init_stats($filter);

        return $stats;
    }

    public function get_active_db()
    {
        global $wpdb;
        $sql = 'SELECT DATABASE() as db_name';

        $sqlresult = $wpdb->get_row($sql);
        $active_db = $sqlresult->db_name;

        return $active_db;
    }

    public function get_hit_count()
    {
        return get_option('user_hit_count');
    }

    public function set_notifications($params)
    {
        if (empty($params)) {
            return false;
        }

        extract($params);

        if (!isset($delete)) {
            $mwp_notifications = array(
                'plugins'          => $plugins,
                'themes'           => $themes,
                'wp'               => $wp,
                'backups'          => $backups,
                'url'              => $url,
                'notification_key' => $notification_key,
            );
            update_option('mwp_notifications', $mwp_notifications);
        } else {
            delete_option('mwp_notifications');
        }

        return true;
    }

    //Cron update check for notifications
    public function check_notifications()
    {
        global $wp_version;

        $mwp_notifications = get_option('mwp_notifications', true);

        $args    = array();
        $updates = array();
        $send    = 0;
        if (is_array($mwp_notifications) && $mwp_notifications != false) {
            include_once ABSPATH.'wp-includes/update.php';
            include_once ABSPATH.'/wp-admin/includes/update.php';
            extract($mwp_notifications);

            //Check wordpress core updates
            if ($wp) {
                @wp_version_check();
                if (function_exists('get_core_updates')) {
                    $wp_updates = get_core_updates();
                    if (!empty($wp_updates)) {
                        $current_transient = $wp_updates[0];
                        if ($current_transient->response == "development" || version_compare($wp_version, $current_transient->current, '<')) {
                            $current_transient->current_version = $wp_version;
                            $updates['core_updates']            = $current_transient;
                        } else {
                            $updates['core_updates'] = array();
                        }
                    } else {
                        $updates['core_updates'] = array();
                    }
                }
            }

            //Check plugin updates
            if ($plugins) {
                @wp_update_plugins();
                $this->get_installer_instance();
                $updates['upgradable_plugins'] = $this->installer_instance->get_upgradable_plugins();
            }

            //Check theme updates
            if ($themes) {
                @wp_update_themes();
                $this->get_installer_instance();

                $updates['upgradable_themes'] = $this->installer_instance->get_upgradable_themes();
            }

            if ($backups) {
                $this->get_backup_instance();
                $backups            = $this->backup_instance->get_backup_stats();
                $updates['backups'] = $backups;
                foreach ($backups as $task_name => $backup_results) {
                    foreach ($backup_results as $k => $backup) {
                        if (isset($backups[$task_name][$k]['server']['file_path'])) {
                            unset($backups[$task_name][$k]['server']['file_path']);
                        }
                    }
                }
                $updates['backups'] = $backups;
            }

            if (!empty($updates)) {
                $args['body']['updates']          = $updates;
                $args['body']['notification_key'] = $notification_key;
                $send                             = 1;
            }
        }

        $alert_data = get_option('mwp_pageview_alerts', true);
        if (is_array($alert_data) && $alert_data['alert']) {
            $pageviews                           = get_option('user_hit_count');
            $args['body']['alerts']['pageviews'] = $pageviews;
            $args['body']['alerts']['site_id']   = $alert_data['site_id'];
            if (!isset($url)) {
                $url = $alert_data['url'];
            }
            $send = 1;
        }

        if ($send) {
            if (!class_exists('WP_Http')) {
                include_once ABSPATH.WPINC.'/class-http.php';
            }
            $result = wp_remote_post($url, $args);

            if (is_array($result) && $result['body'] == 'mwp_delete_alert') {
                delete_option('mwp_pageview_alerts');
            }
        }
    }

    public function cmp_posts_worker($a, $b)
    {
        return ($a->post_date < $b->post_date);
    }

    public function trim_content($content = '', $length = 200)
    {
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            $content = (mb_strlen($content) > ($length + 3)) ? mb_substr($content, 0, $length).'...' : $content;
        } else {
            $content = (strlen($content) > ($length + 3)) ? substr($content, 0, $length).'...' : $content;
        }

        return $content;
    }
}
