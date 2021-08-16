<?php
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
   $wolConfig->showForm();
   Html::footer();
}
