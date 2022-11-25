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

define('PLUGIN_WAKEONLAN_VERSION', '0.0.4');

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
             'min' => '10.0',
          ],
          'php' => [
              'min' => '8.1',
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
