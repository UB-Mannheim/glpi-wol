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


class PluginWakeonlanWOL extends CommonDBTM
{

   public function calc_broadcast($ip, $mask) {
      $wcmask = long2ip(~ip2long($mask));
      $subnet = long2ip(ip2long($ip) & ip2long($mask));
      $broad = long2ip(ip2long($ip) | ip2long($wcmask));
      return $broad;
   }

   public function get_netinfo($items_id, $item) {
      global $DB;
      $query = [
         'SELECT' => ['glpi_networkports.mac', 'glpi_ipaddresses.name', 'glpi_ipnetworks.netmask'],
         'FROM' => 'glpi_networkports',
         'INNER JOIN' => [
            'glpi_networknames' => [
               'ON' => [
                  'glpi_networknames' => 'items_id',
                  'glpi_networkports' => 'id',
                  ['AND' => ['glpi_networknames.itemtype' => 'NetworkPort']],
               ]
            ],
            'glpi_ipaddresses' => [
               'ON' => [
                  'glpi_ipaddresses' => 'items_id',
                  'glpi_networknames' => 'id',
                  ['AND' => ['glpi_ipaddresses.itemtype' => 'NetworkName']]
               ]
            ],
            'glpi_ipaddresses_ipnetworks' => [
               'ON' => [
                  'glpi_ipaddresses_ipnetworks' => 'ipaddresses_id',
                  'glpi_ipaddresses' => 'id'
               ]
            ],
            'glpi_ipnetworks' => [
               'ON' => [
                  'glpi_ipnetworks' => 'id',
                  'glpi_ipaddresses_ipnetworks' => 'ipnetworks_id'
               ]
            ]
         ],
         'WHERE' => [
            'glpi_networkports.items_id' => $DB->escape($items_id),
            'glpi_networkports.itemtype' => $DB->escape($item->getType()),
            'glpi_ipaddresses.version' => "4",
            // TODO: support IPv6
         ]
      ];

      $result = [];
      foreach ($DB->request($query) as $netinfo) {
         if (filter_var($netinfo['name'], FILTER_VALIDATE_IP) && filter_var($netinfo['netmask'], FILTER_VALIDATE_IP)) {
            $netinfo['broad'] = $this->calc_broadcast($netinfo['name'], $netinfo['netmask']);
         }
         if (filter_var($netinfo['mac'], FILTER_VALIDATE_MAC) && filter_var($netinfo['broad'], FILTER_VALIDATE_IP)) {
             array_push($result, $netinfo);
         }
      }
      return $result;
   }

   public function wake($mac, $broad) {
      $wolConfig = new PluginWakeonlanConfig();
      $wolConfig->getConfig();
      $wolmethod = $wolConfig->fields['wolmethod'];
      if (!$wolmethod || $wolmethod === '') {
         return [false, "No WOL method configured!"];
      }

      if ($wolmethod === 'local') {
         $hwaddr = pack('H*', preg_replace('/[^0-9a-fA-F]/', '', $mac));
         $packet = sprintf(
            '%s%s',
            str_repeat(chr(255), 6),
            str_repeat($hwaddr, 16)
         );

         $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
         if ($sock !== false) {
            $options = socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, true);
            if ($options !== false) {
                $result = socket_sendto($sock, $packet, strlen($packet), 0, $broad, 7);
                socket_close($sock);
                $success = $result !== false;
            }
         }
         if ($success) {
            $response = "Sending magic packet to $broad with $mac!";
         } else {
            $response = "Unsuccessful attempt sending magic packet to $broad with $mac!";
         }
      } else if ($wolmethod === 'remote') {
         $url = $wolConfig->fields['remoteurl'];
         $macfield = $wolConfig->fields['macfield'];
         $broadfield = $wolConfig->fields['broadfield'];
         if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return [false, "Remote URL invalid!"];
         }
         if (!$macfield || $macfield === '') {
            $macfield = 'mac';
         }
         if (!$broadfield || $broadfield === '') {
            $broadfield = 'broad';
         }
         $ch = curl_init($url);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt($ch, CURLOPT_POST, 1);
         $poststr = "$macfield=$mac&$broadfield=$broad";
         $custom_post = htmlspecialchars_decode($wolConfig->fields['custom_post']);
         if ($custom_post) {
            $poststr .= $custom_post;
         }
         curl_setopt($ch, CURLOPT_POSTFIELDS, $poststr);
         $response = curl_exec($ch);
         $curlinfo = curl_getinfo($ch);
         curl_close($ch);
         $httpcode = $curlinfo['http_code'];
         if ($httpcode != "200") {
            $success = false;
            $response = "Remote server returned HTTP code $httpcode";
         } else {
            $arr = json_decode($response, true);
            $success = $arr['success'];
            $response = htmlentities($arr['msg'], ENT_QUOTES);
         }
      }
      return [$success, $response];
   }

   static function showMassiveActionsSubForm($ma) {
      if ($ma->getAction() === 'wake') {
          echo Html::submit('Send WOL Signal', ['name' => 'massiveaction']);
          return true;
      }
      return parent::showMassiveActionsSubForm($ma);
   }

   static function processMassiveActionsForOneItemtype($ma, $item, $ids) {
      if ($ma->getAction() === 'wake') {
         $wol = new self();
         foreach ($ids as $id) {
            $netinfos = $wol->get_netinfo($id, $item);
            if (count($netinfos) < 1) {
               $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
               $ma->addMessage("Could not retrieve suitable netinfo for item ID $id.");
               return;
            }
            foreach ($netinfos as $ni) {
               list($success, $response) = $wol->wake($ni['mac'], $ni['broad']);
               if ($success) {
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
               } else {
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                  $ma->addMessage($response);
               }
            }
         }
         return;
      } else {
         parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
      }
   }

}
