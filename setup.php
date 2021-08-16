<?php
define('PLUGIN_WAKEONLAN_VERSION', '0.0.1');

function plugin_init_wakeonlan() {
   global $PLUGIN_HOOKS;
   $PLUGIN_HOOKS['csrf_compliant']['wakeonlan'] = true;
   // $PLUGIN_HOOKS['post_item_form']['wakeonlan'] = 'plugin_wakeonlan_postItemForm';
   $PLUGIN_HOOKS['use_massive_action']['wakeonlan'] = 1;
   $PLUGIN_HOOKS['MassiveActionsFieldsDisplay']['wakeonlan'] = 'plugin_wakeonlan_MassiveActionsFieldsDisplay';
   $PLUGIN_HOOKS['MassiveActions']['wakeonlan'] = 'plugin_wakeonlan_MassiveActions';
   $PLUGIN_HOOKS['config_page']['wakeonlan'] = 'front/config.php';
   Plugin::registerClass('PluginWakeonlanWOL');
   Plugin::registerClass('PluginWakeonlanConfig', ['addtabon' => 'Config']);
}

function plugin_version_wakeonlan() {
   return [
       'name' => 'Wake On LAN',
       'version' => PLUGIN_WAKEONLAN_VERSION,
       'author' => 'UB Mannheim',
       'requirements' => [
          'glpi' => [
             'min' => '9.5',
          ],
          'php' => [
              'min' => '7.4',
              'exts' => [
                 'curl' => [ 'required' => 'true' ],
              ]
          ],
       ]
   ];
}

function plugin_wakeonlan_check_prerequisites() {
   return true;
}

function plugin_wakeonlan_check_config($verbose = false) {
   return true;
   if ($verbose) {
      echo "Installed, but not configured";
   }
   return false;
}
