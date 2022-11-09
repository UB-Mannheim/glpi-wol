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


class PluginWakeonlanConfig extends CommonDBTM
{
   static $rightname = "config";

   function updateValue($name, $value) {
      // retrieve current config
      $config = current($this->find(['type' => $name]));
      // set in db
      if (isset($config['id'])) {
         $result = $this->update(['id'=> $config['id'], 'value'=>$value]);
      } else {
         $result = $this->add(['type' => $name, 'value' => $value]);
      }

      // set cache
      if ($result) {
         $this->fields[$name] = $value;
      }

      return $result;
   }

   function getConfig() {
      global $DB;
      $rows = $DB->request('glpi_plugin_wakeonlan_configs');
      if (count($rows) > 0) {
         foreach ($rows as $row) {
            $this->fields[$row['type']] = $row['value'];
         }
      }
   }

   function showForm($ID, array $options = []) {
      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr>";
      echo "<td>".__('Method', 'wakeonlan')."</td>";
      echo "<td>";
      foreach (['local', 'remote'] as $m) {
         $checked = (array_key_exists('wolmethod', $this->fields) && $this->fields['wolmethod'] === $m) ? "checked" : "";
         echo "<input type='radio' id='$m' value='$m' name='wolmethod' $checked>";
         echo "<label for='local'>" . __($m, 'wakeonalan') . "</label>";
         echo "<br/>";
      }
      echo "</td>";
      echo "</tr>";

      echo "<tr><td colspan='2'>When using remote</td></tr>";

      echo "<tr>";
      echo "<td>".__('Remote URL', 'wakeonlan')."</td>";
      echo "<td>";
      echo HTML::input('remoteurl', ['value' => array_key_exists('remoteurl', $this->fields) ? $this->fields['remoteurl'] : ""]);
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>".__('MAC Field', 'wakeonlan')."</td>";
      echo "<td>";
      echo HTML::input('macfield', ['value' => array_key_exists('macfield', $this->fields) ? $this->fields['macfield'] : ""]);
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>".__('Broadcast Field', 'wakeonlan')."</td>";
      echo "<td>";
      echo HTML::input('broadfield', ['value' => array_key_exists('broadfield', $this->fields) ? $this->fields['broadfield'] : ""]);
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>".__('Custom Post String', 'wakeonlan')."</td>";
      echo "<td>";
      echo HTML::input('custom_post', ['value' => array_key_exists('custom_post', $this->fields) ? $this->fields['custom_post'] : ""]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td class='center' colspan='2'>";
      echo Html::submit(_x('button', 'Save'), ['name' => 'update']);
      echo "</td>";
      echo "</tr>\n";
      echo "</table></div>";
      Html::closeForm();

      // $this->showFormButtons($options);
      return true;
   }
}

