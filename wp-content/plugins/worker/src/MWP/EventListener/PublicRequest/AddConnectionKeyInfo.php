<?php
/*
 * This file is part of the ManageWP Worker plugin.
 *
 * (c) ManageWP LLC <contact@managewp.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MWP_EventListener_PublicRequest_AddConnectionKeyInfo implements Symfony_EventDispatcher_EventSubscriberInterface
{
    private $context;

    private $slug = 'worker/init.php';

    function __construct(MWP_WordPress_Context $context)
    {
        $this->context = $context;
    }

    public static function getSubscribedEvents()
    {
        return array(
            MWP_Event_Events::PUBLIC_REQUEST => 'onPublicRequest',
        );
    }

    public function onPublicRequest()
    {
        $this->context->addAction('admin_init', array($this, 'enqueueConnectionModalOpenScripts'));
        $this->context->addAction('admin_init', array($this, 'enqueueConnectionModalOpenStyles'));
        $this->context->addFilter('plugin_row_meta', array($this, 'addConnectionKeyLink'), 10, 2);
        $this->context->addAction('admin_head', array($this, 'printConnectionModalOpenScript'));
        $this->context->addAction('admin_footer', array($this, 'printConnectionModalDialog'));
    }

    public function enqueueConnectionModalOpenScripts()
    {
        $this->context->enqueueScript('jquery');
        $this->context->enqueueScript('jquery-ui-core');
        $this->context->enqueueScript('jquery-ui-dialog');
    }

    public function enqueueConnectionModalOpenStyles()
    {
        $this->context->enqueueStyle('wp-jquery-ui');
        $this->context->enqueueStyle('wp-jquery-ui-dialog');
    }

    public function printConnectionModalOpenScript()
    {
        if (!$this->userCanViewConnectionKey()) {
            return;
        }

        ob_start()
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var $connectionKeyDialog = $('#mwp_connection_key_dialog');
                $('#mwp-view-connection-key').click(function(e) {
                    e.preventDefault();
                    $connectionKeyDialog.dialog({
                        draggable: false,
                        resizable: false,
                        modal: true,
                        width: '530px',
                        height: 'auto',
                        title: 'Connection Key',
                        close: function() {
                            $(this).dialog("destroy");
                        }
                    });
                });
            });
        </script>
        <?php

        $content = ob_get_clean();
        $this->context->output($content);
    }

    public function printConnectionModalDialog()
    {
        if (!$this->userCanViewConnectionKey()) {
            return;
        }

        ob_start();
        ?>
        <div id="mwp_connection_key_dialog" style="display: none;">
            <p>There are two ways to connect your website to the management dashboard:</p>

            <h2>Automatic</h2>
            <ol>
                <li>Log into your <a href="https://managewp.com/" target="_blank">ManageWP</a> or <a href="https://godaddy.com/pro" target="_blank">Pro Sites</a> account</li>
                <li>Click the Add website icon at the top left</li>
                <li>Enter this website's URL, admin username and password, and the system will take care of everything</li>
            </ol>

            <h2>Manual</h2>
            <ol>
                <li>Install and activate the <strong>Worker</strong> plugin</li>
                <li>Copy the connection key below</li>
                <li>Log into your <a href="https://managewp.com/" target="_blank">ManageWP</a> or <a href="https://godaddy.com/pro" target="_blank">Pro Sites</a> account</li>
                <li>Click the Add website icon at the top left</li>
                <li>Enter this website's URL. When prompted, paste the connection key</li>
            </ol>

            <div style="text-align: center;font-weight: bold;"><p style="margin-bottom: 4px;margin-top: 20px;">Connection Key</p></div>
            <div style="padding: 10px;background-color: #fafafa;border: 1px solid black;border-radius: 10px;font-weight: bold;font-size: 14px;text-align: center;"><?php echo mwp_get_potential_key(); ?></div>
        </div>
        <?php

        $content = ob_get_clean();
        $this->context->output($content);
    }

    /**
     * @wp_filter
     */
    public function addConnectionKeyLink($meta, $slug)
    {
        if ($slug !== $this->slug) {
            return $meta;
        }

        if (!$this->userCanViewConnectionKey()) {
            return $meta;
        }

        $meta[] = '<a href="#" id="mwp-view-connection-key" mwp-key="'.mwp_get_potential_key().'">View connection key</a>';

        return $meta;
    }

    private function userCanViewConnectionKey()
    {
        return $this->context->isGranted('activate_plugins') &&
            $this->context->isGranted('update_plugins') &&
            $this->context->isGranted('install_plugins');
    }
}
