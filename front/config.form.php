<?php
include ('../../../inc/includes.php');

Session::checkRight("config", READ);

$plugin = new Plugin();
if (!$plugin->isInstalled('wakeonlan') || !$plugin->isActivated('wakeonlan')) {
   Html::displayNotFoundError();
}

$wolConfig = new PluginWakeonlanConfig();

if (isset($_POST["update"]) || isset($_POST["add"])) {
   $data = $_POST;
   unset($data['update']);
   unset($data['add']);
   unset($data['id']);
   unset($data['_glpi_csrf_token']);
   foreach ($data as $key=>$value) {
      $wolConfig->updateValue($key, $value);
   }
   Html::back();
} else {
   Html::header(
      __('Wake on LAN Config', 'wakeonlan'),
      $_SERVER['PHP_SELF'],
      "config",
      "plugins"
   );
   $wolConfig->showForm();
   Html::footer();
}
