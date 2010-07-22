<?php
/*
 * @version $Id$
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copynetwork (C) 2003-2006 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org/
 ----------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 ------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: DURIEUX David
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}


class PluginFusioninventoryConfig extends CommonDBTM {

	function __construct() {
		$this->table="glpi_plugin_fusioninventory_configs";
	}

	/* Function to get the value of a field */
	function getValue($p_plugins_id, $p_type) {
		global $DB;

      $data = $this->find("`plugins_id`='".$p_plugins_id."'
                          AND `type`='".$p_type."'");
      $config = current($data);
		if (isset($config['value'])) {
         return $config['value'];
		}
		return false;
	}

	// Confirm if the functionality is activated, or not
//	function isActivated($p_type, $p_plugins_id=0) {
	function is_active($p_plugins_id, $p_type) {
		if (!($this->getValue($p_plugins_id, $p_type))) {
			return false;
      } else {
			return true;
      }
	}

	function showForm($options=array()) {
		global $LANG,$CFG_GLPI;

      $plugins_id = PluginFusioninventoryModule::getModuleId('fusioninventory');

      echo "<form name='form' method='post' action='".$options['target']."'>";
      echo "<div class='center' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe'>";

		echo "<tr class='tab_bg_1'>";
		echo "<td>".$LANG['plugin_fusioninventory']["functionalities"][27]."&nbsp;:</td>";
		echo "<td width='20%'>";
		Dropdown::showYesNo("ssl_only", $this->is_active($plugins_id, 'ssl_only'));
		echo "</td>";
		echo "<td>".$LANG['plugin_fusioninventory']["functionalities"][16]."&nbsp;:</td>";
		echo "<td width='20%'>";
		$ArrayValues = array();
		$ArrayValues['DB']= $LANG['plugin_fusioninventory']["functionalities"][17];
		$ArrayValues['file']= $LANG['plugin_fusioninventory']["functionalities"][18];
		Dropdown::showFromArray('storagesnmpauth', $ArrayValues,
                              array('value'=>$this->getValue($plugins_id, 'storagesnmpauth')));
		echo "</td>";
      echo "</tr>";

 		echo "<tr class='tab_bg_1'>";
		echo "<td>".$LANG['plugin_fusioninventory']['config'][0]."&nbsp;:</td>";
		echo "<td>";
      Dropdown::showInteger("inventory_frequence",
                            $this->getValue($plugins_id, 'inventory_frequence'),1,240);
		echo "</td>";
		echo "<td>".$LANG['plugin_fusioninventory']["functionalities"][32]."</td>";
		echo "<td>";
      Dropdown::showInteger("delete_agent_process",
                            $this->getValue($plugins_id, 'delete_agent_process'),1,240);
      echo " ".$LANG['gmt'][1];
		echo "</td>";
      echo "</tr>";

      if (PluginFusioninventoryProfile::haveRight("fusioninventory", "configuration", "w")) {
         echo "<tr class='tab_bg_2'><td align='center' colspan='4'>
               <input class='submit' type='submit' name='plugin_fusioninventory_config_set'
                      value='" . $LANG['buttons'][7] . "'></td></tr>";
      }
      echo "</table>";
      echo "</form>";

      return true;
	}

   /**
    * Add config
    *
    *@param $p_plugins_id Plugin id
    *@param $p_type Config type ('ssl_only', 'URL_agent_conf'...)
    *@param $p_value Value
    *@return integer the new id of the added item (or false if fail)
    **/
   function addConfig($p_plugins_id, $p_type, $p_value) {
      return $this->add(array('plugins_id'=>$p_plugins_id, 
                              'type'=>$p_type,
                              'value'=>$p_value));
   }

   /**
    * Update config
    *
    *@param $p_id Config id
    *@param $p_value Value
    *@return boolean : true on success
    **/
   function updateConfig($p_id, $p_value) {
      return $this->update(array('id'=>$p_id, 'value'=>$p_value));
   }

   /**
    * Update config type
    *
    *@param $p_plugins_id Plugin id
    *@param $p_type Config type ('ssl_only', 'URL_agent_conf'...)
    *@param $p_value Value
    *@return boolean : true on success
    **/
   function updateConfigType($p_plugins_id, $p_type, $p_value) {
      $data = $this->find("`plugins_id`='".$p_plugins_id."'
                          AND `type`='".$p_type."'");
      $config = current($data);
		if (isset($config['id'])) {
         return $this->updateConfig($config['id'], $p_value);
      }
      return false;
   }

//   /**
//    * Update config
//    *
//    *@param $p_id Config id
//    *@param $p_modules_id Module id (0 for Fusioninventory)
//    *@param $p_type Config type ('ssl_only', 'URL_agent_conf'...)
//    *@param $p_value Value
//    *@return boolean : true on success
//    **/
//   function updateConfig($p_id, $p_modules_id, $p_type, $p_value) {
//      return $this->update(array('id'=>$p_id, 'type'=>$p_type, `value`=>$p_value,
//                       'plugin_fusioninventory_modules_id'=>$p_modules_id));
//   }
//
   /**
    * Delete config
    *
    *@param $p_id Config id
    *@return boolean : true on success
    **/
   function deleteConfig($p_id) {
      return $this->delete(array('id'=>$p_id));
   }

   /**
    * Clean config
    *
    *@param $p_plugins_id Plugin id
    *@return boolean : true on success
    **/
   function cleanConfig($p_plugins_id) {
      global $DB;

      $delete = "DELETE FROM `".$this->table."`
                 WHERE `plugins_id`='".$p_plugins_id."';";
      return $DB->query($delete);
   }

   /**
    * Init config
    *
    *@param $p_plugins_id Plugin id
    *@param $p_insert Array('type'=>'value')
    *@return nothing
    **/
	function initConfig($p_plugins_id, $p_insert) {
		global $DB;

      $plugins_id = PluginFusioninventoryModule::getModuleId('fusinvsnmp');
      foreach ($p_insert as $type=>$value) {
         $this->addConfig($p_plugins_id, $type, $value);
      }
   }
}

?>