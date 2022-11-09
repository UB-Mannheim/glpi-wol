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

require "../../../inc/includes.php";

Session::checkRight("config", READ);

$plugin = new Plugin();
if (!$plugin->isInstalled('wakeonlan') || !$plugin->isActivated('wakeonlan')) {
   Html::displayNotFoundError();
}

$wolConfig = new PluginWakeonlanConfig();
$wolConfig->getConfig();

if ($wolConfig::canView()) {
   Html::header(
      __('Wake on LAN Config', 'wakeonlan'),
      $_SERVER['PHP_SELF'],
      "config",
      "plugins"
   );
   $wolConfig->showForm($wolConfig->getID());
   Html::footer();
}
