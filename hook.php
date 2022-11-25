<?php

/**
 * Copyright (C) 2021  Mannheim University Library
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along
 * with this program. If not, see <http://www.gnu.org/licenses/>. 
 */

function plugin_wakeonlan_install() {
   global $DB;

   //instantiate migration with version
   $migration = new Migration(100);

   if (!$DB->tableExists('glpi_plugin_wakeonlan_configs')) {
      //Create table if it does not exists yet
      $query = "CREATE TABLE `glpi_plugin_wakeonlan_configs` (
            `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
            `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unicity` (`type`)
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1";
      $DB->queryOrDie($query, $DB->error());
      $query = "INSERT INTO `glpi_plugin_wakeonlan_configs` (type, value) VALUES('entities_id', 0)";
      $DB->queryOrDie($query, $DB->error());
      $query = "INSERT INTO `glpi_plugin_wakeonlan_configs` (type, value) VALUES('wolmethod', 'local')";
      $DB->queryOrDie($query, $DB->error());
   } else {
      //Make sure existing tables have desired properties
      $query = "ALTER TABLE `glpi_plugin_wakeonlan_configs` MODIFY COLUMN `id` int UNSIGNED NOT NULL AUTO_INCREMENT";
      $DB->queryOrDie($query, $DB->error());
      $query = "ALTER TABLE `glpi_plugin_wakeonlan_configs` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
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
