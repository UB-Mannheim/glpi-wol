<?php
function plugin_wakeonlan_install() {
   global $DB;

   //instanciate migration with version
   $migration = new Migration(100);

   //Create table only if it does not exists yet!
   if (!$DB->tableExists('glpi_plugin_wakeonlan_configs')) {
         $query = "CREATE TABLE `glpi_plugin_wakeonlan_configs` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `unicity` (`type`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
      $DB->queryOrDie($query, $DB->error());
   }
   $migration->executeMigration();
   return true;
}

function plugin_wakeonlan_uninstall() {
   global $DB;

   $tables = [
      'configs'
   ];

   foreach ($tables as $table) {
      $tablename = 'glpi_plugin_wakeonlan_' . $table;
      if ($DB->tableExists($tablename)) {
         $DB->queryOrDie(
            "DROP TABLE `$tablename`",
            $DB->error()
         );
      }
   }

   return true;
}

function plugin_wakeonlan_postItemForm($params) {
   if (isset($params['item']) && $params['item'] instanceof CommonDBTM) {
      if (in_array(get_class($params['item'], ['Computer', 'Printer', 'Peripheral']))) {
         PluginWakeonlanWOL::show_button($params['item']);
      }
   }
}

function plugin_wakeonlan_MassiveActions($type) {
   $actions = [];
   if (in_array($type, ['Computer', 'Printer', 'Peripheral'])) {
      $myclass      = "PluginWakeonlanWOL";
      $action_key   = 'wake';
      $action_label = "Wake On LAN";
      $actions[$myclass.MassiveAction::CLASS_ACTION_SEPARATOR.$action_key] = $action_label;
   }
   return $actions;
}
