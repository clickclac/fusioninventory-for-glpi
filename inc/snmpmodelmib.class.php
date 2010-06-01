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

class PluginFusioninventorySNMPModelMib extends CommonDBTM {
   
	function __construct() {
		$this->table="glpi_plugin_fusioninventory_snmpmodelmibs";
	}



	function showForm($id, $options=array()) {
		include (GLPI_ROOT . "/plugins/fusioninventory/inc_constants/snmp.mapping.constant.php");

		global $DB,$CFG_GLPI,$LANG,$FUSIONINVENTORY_MAPPING,$IMPORT_TYPES;
		
		if (!PluginFusioninventoryAuth::haveRight("snmp_models","r")) {
			return false;
      } else if ((isset($id)) AND (!empty($id))) {
			$query = "SELECT `itemtype`
                   FROM `glpi_plugin_fusioninventory_snmpmodels`
                   WHERE `id`='".$id."';";
			$result = $DB->query($query);		
			$data = $DB->fetch_assoc($result);
			$type_model = $data['itemtype'];		
		
			$query = "SELECT `glpi_plugin_fusioninventory_snmpmodels`.`itemtype`,
                          `glpi_plugin_fusioninventory_snmpmodelmibs`.*
                   FROM `glpi_plugin_fusioninventory_snmpmodelmibs`
                        LEFT JOIN `glpi_plugin_fusioninventory_snmpmodels`
                        ON `glpi_plugin_fusioninventory_snmpmodelmibs`.`plugin_fusioninventory_snmpmodels_id`=
                           `glpi_plugin_fusioninventory_snmpmodels`.`id`
                   WHERE `glpi_plugin_fusioninventory_snmpmodels`.`id`='".$id."';";
			
			if ($result = $DB->query($query)) {
				$object_used = array();
				$linkoid_used = array();
				
				echo "<br>";
				$this->showTabs($options);
            $this->showFormHeader($options);
				
				echo "<tr class='tab_bg_1'>";
				echo "<th align='center'></th>";
				echo "<th align='center'>".$LANG['plugin_fusioninventory']["mib"][1]."</th>";
				echo "<th align='center'>".$LANG['plugin_fusioninventory']["mib"][2]."</th>";
				echo "<th align='center'>".$LANG['plugin_fusioninventory']["mib"][3]."</th>";
				echo "<th align='center'>".$LANG['plugin_fusioninventory']["mib"][6]."</th>";
				echo "<th align='center'>".$LANG['plugin_fusioninventory']["mib"][7]."</th>";
				echo "<th align='center' width='250'>".$LANG['plugin_fusioninventory']["mib"][8]."</th>";
				if ($data['itemtype'] == NETWORKING_TYPE) {
					echo "<th align='center'>".$LANG['plugin_fusioninventory']["mib"][9]."</th>";
            }
				echo "<th align='center'>".$LANG['plugin_fusioninventory']["model_info"][11]."</th>";
				
				echo "</tr>";
				while ($data=$DB->fetch_array($result)) {
					if ($data["activation"] == "0") {
						echo "<tr class='tab_bg_1' style='color: grey; '>";
               } else {
						echo "<tr class='tab_bg_1'>";
               }
					echo "<td align='center'>";
					echo "<input name='item_coche[]' value='".$data["id"]."' type='checkbox'>";
					echo "</td>";
	
					echo "<td align='center'>";
					echo Dropdown::getDropdownName("glpi_plugin_fusioninventory_miblabels",$data["plugin_fusioninventory_mib_label_id"]);
					echo "</td>";
					
					echo "<td align='center'>";
					$object_used[] = $data["plugin_fusioninventory_mibobjects_id"];
					echo Dropdown::getDropdownName("glpi_plugin_fusioninventory_mibobjects",
                                    $data["plugin_fusioninventory_mibobjects_id"]);
					echo "</td>";
					
					echo "<td align='center'>";
					echo Dropdown::getDropdownName("glpi_plugin_fusioninventory_miboids",$data["plugin_fusioninventory_miboids_id"]);
					echo "</td>";
					
					echo "<td align='center'>";
					if ($data["oid_port_counter"] == "1") {
						if ($data["activation"] == "1") {
							echo "<img src='".$CFG_GLPI["root_doc"]."/pics/bookmark.png'/>";
                  } else if ($data["activation"] == "0") {
							echo "<img src='".$CFG_GLPI["root_doc"].
                              "/plugins/fusioninventory/pics/bookmark_off.png'/>";
                  }
               }
					echo "</td>";
					
					echo "<td align='center'>";
					if ($data["oid_port_dyn"] == "1") {
						if ($data["activation"] == "1") {
							echo "<img src='".$CFG_GLPI["root_doc"]."/pics/bookmark.png'/>";
                  } else if ($data["activation"] == "0") {
							echo "<img src='".$CFG_GLPI["root_doc"].
                              "/plugins/fusioninventory/pics/bookmark_off.png'/>";
                  }
               }
					echo "</td>";
					
					echo "<td align='center'>";
               $mapping = new PluginFusioninventoryMapping;
               $mappings = $mapping->find("`itemtype`='".$data['mapping_type']."'
                                          AND `name`='".$data['mapping_name']."'");
               if ($mappings) {
                  echo $LANG['plugin_fusioninventory']['mapping'][$mappings->fields['locale']]." ( ".$data["mapping_name"]." )";
						$linkoid_used[$data['mapping_type']."||".$data["mapping_name"]] = 1;
               }
					echo "</td>";
					
					if ($data['itemtype'] == NETWORKING_TYPE) {
						echo "<td align='center'>";
						if ($data["vlan"] == "1") {
							if ($data["activation"] == "1") {
								echo "<img src='".$CFG_GLPI["root_doc"]."/pics/bookmark.png'/>";
                     } else if ($data["activation"] == "0") {
								echo "<img src='".$CFG_GLPI["root_doc"].
                                 "/plugins/fusioninventory/pics/bookmark_off.png'/>";
                     }
                  }
						echo "</td>";
					}
					
					echo "<td align='center'>";
					echo "<a href='".$target."?id=".$id."&activation=".$data["id"]."'>";
					if ($data["activation"] == "1") {
						echo "<img src='".$CFG_GLPI["root_doc"]."/pics/bookmark.png'/>";
               } else if ($data["activation"] == "0") {
						echo "<img src='".$CFG_GLPI["root_doc"].
                           "/plugins/fusioninventory/pics/bookmark_off.png'/>";
               }
					echo "</a>";
					echo "</td>";
					
					echo "</tr>";
				}
				echo "</table>";
				
				echo "<div align='center'>";
				echo "<table class='tab_cadre_fixe'>";
				echo "<tr>"; 
				echo "<td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''></td>
                  <td align='center'><a onclick= \"if ( markCheckboxes('oid_list') ) return false;\"
                      href='".$_SERVER['PHP_SELF']."?select=all'>".$LANG["buttons"][18]."</a></td>";
				echo "<td>/</td><td align='center'><a onclick= \"if ( unMarkCheckboxes('oid_list') ) 
                     return false;\" href='".$_SERVER['PHP_SELF']."?select=none'>".
                     $LANG["buttons"][19]."</a>";
				echo "</td><td align='left' colspan='6' width='80%'>";
            if(PluginFusioninventoryAuth::haveRight("snmp_models","w")) {
   				echo "<input class='submit' type='submit' name='delete_oid' value='" .
                     $LANG["buttons"][6] . "'>";
            }
				echo "</td>";
				echo "</tr>";
				echo "</table></div>";


				// ********** Ajout d'un tableau pour ajouter nouveau OID ********** //
				echo "<br/>";
				echo "<table class='tab_cadre_fixe'>";
				
				echo "<tr class='tab_bg_1'><th colspan='7'>".$LANG['plugin_fusioninventory']["mib"][4].
                     "</th></tr>";

				echo "<tr class='tab_bg_1'>";
				echo "<th align='center'>".$LANG['plugin_fusioninventory']["mib"][1]."</th>";
				echo "<th align='center'>".$LANG['plugin_fusioninventory']["mib"][2]."</th>";
				echo "<th align='center'>".$LANG['plugin_fusioninventory']["mib"][3]."</th>";
				echo "<th align='center'>".$LANG['plugin_fusioninventory']["mib"][6]."</th>";
				echo "<th align='center'>".$LANG['plugin_fusioninventory']["mib"][7]."</th>";
				echo "<th align='center' width='250'>".$LANG['plugin_fusioninventory']["mib"][8]."</th>";
				if ($type_model == NETWORKING_TYPE) {
					echo "<th align='center'>".$LANG["networking"][56]."</th>";
            }
				echo "</tr>";

				echo "<td align='center'>";
				Dropdown::show("PluginFusioninventoryMibLabel",
                           array('name' => "plugin_fusioninventory_mib_label_id",
                                 'value' => 0));
				echo "</td>";
				
				echo "<td align='center'>";
				Dropdown::show("PluginFusioninventoryMibObject",
                           array('name' => "plugin_fusioninventory_mibobjects_id",
                                 'value' => 0));
				echo "</td>";

				echo "<td align='center'>";
				Dropdown::show("PluginFusioninventoryMibOid",
                           array('name' => "plugin_fusioninventory_miboids_id",
                                 'value' => 0));
				echo "</td>";
				
				echo "<td align='center'>";
				//echo "<input name='oid_port_counter' value='0' type='checkbox'>";
				Dropdown::showYesNo("oid_port_counter");	
				echo "</td>";
				
				echo "<td align='center'>";
				//echo "<input name='oid_port_dyn' value='0' type='checkbox'>";
				Dropdown::showYesNo("oid_port_dyn");
				echo "</td>";
				
				echo "<td align='center'>";
				//echo "<select name='links_oid_fields' size='1'>";
				$types = array();
				$types[] = "-----";
				foreach ($FUSIONINVENTORY_MAPPING as $type=>$mapping43) {
					if (($type_model == $type) OR ($type_model == "0")) {
						if (isset($FUSIONINVENTORY_MAPPING[$type])) {
							foreach ($FUSIONINVENTORY_MAPPING[$type] as $name=>$mapping) {
								$types[$type."||".$name]=$FUSIONINVENTORY_MAPPING[$type][$name]["name"];
							}
						}
					}
				}

				Dropdown::showFromArray("links_oid_fields",$types,
                                    array('used'=>$linkoid_used));

				echo "</td>";

				if ($type_model == NETWORKING_TYPE) {
					echo "<td align='center'>";
					Dropdown::showYesNo("vlan");
					echo "</td>";
				}
				
				echo "</tr>";
				
				$this->showFormButtons($options);

            echo "<div id='tabcontent'></div>";
            echo "<script type='text/javascript'>loadDefaultTab();</script>";

            return true;
			}		
		}
	}

	function prepareInputForUpdate($input) {
		$explode = explode("||",$input["links_oid_fields"]);
		$input["mapping_type"] = $explode[0];
		$input["mapping_name"] = $explode[1];
		return $input;
	}

	function prepareInputForAdd($input) {
		$explode = explode("||",$input["links_oid_fields"]);
		$input["mapping_type"] = $explode[0];
		$input["mapping_name"] = $explode[1];
		return $input;
	}

	

	
	function deleteMib($item_coche) {
		global $DB;
		
		PluginFusioninventoryAuth::checkRight("snmp_models","w");
		
		for ($i = 0; $i < count($item_coche); $i++) {
         $this->deleteFromDB($item_coche[$i],1);
		}
	}



	function activation($id) {
		global $DB;
		
		$mib_networking = new PluginFusioninventorySNMPModelMib;
		
		$mib_networking->getFromDB($id);
		$data['id'] = $id;
		$data = $mib_networking->fields;
		if ($mib_networking->fields["activation"] == "1") {
			$data['activation'] = 0;
      } else {
			$data['activation'] = 1;
      }
		$mib_networking->update($data);
	}

   function oidList($p_sxml_node,$p_id) {
		global $DB;

      $ptc = new PluginFusioninventoryCommunication();

      // oid GET
		$query = "SELECT `glpi_plugin_fusioninventory_mappings`.`name` AS `mapping_name`,
                       `glpi_plugin_fusioninventory_miboids`.*
                FROM `glpi_plugin_fusioninventory_snmpmodelmibs`
                     LEFT JOIN `glpi_plugin_fusioninventory_mappings`
                               ON `glpi_plugin_fusioninventory_snmpmodelmibs`.`plugin_fusioninventory_mappings_id`=
                                  `glpi_plugin_fusioninventory_mappings`.`id`
                WHERE `plugin_fusioninventory_snmpmodels_id`='".$p_id."'
                  AND `activation`='1'
                  AND `oid_port_counter`='0';";

      $result=$DB->query($query);
		while ($data=$DB->fetch_array($result)) {
         switch ($data['oid_port_dyn']) {
            case 0:
               $ptc->addGet($p_sxml_node,
                  $data['mapping_name'],
                  Dropdown::getDropdownName('glpi_plugin_fusioninventory_miboids',$data['plugin_fusioninventory_miboids_id']),
                  $data['mapping_name'], $data['vlan']);
               break;
            
            case 1:
               $ptc->addWalk($p_sxml_node,
                  $data['mapping_name'],
                  Dropdown::getDropdownName('glpi_plugin_fusioninventory_miboids',$data['plugin_fusioninventory_miboids_id']),
                  $data['mapping_name'], $data['vlan']);
               break;
            
         }

      }




      // oid WALK
   }

}

?>