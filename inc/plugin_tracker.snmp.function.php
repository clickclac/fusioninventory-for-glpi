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
// Original Author of file: David DURIEUX
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')){
	die("Sorry. You can't access directly to this file");
}



/**
 * Description
 *
 * @param
 * @param
 *
 * @return
 *
**/
function plugin_tracker_UpdateDeviceBySNMP_startprocess($ArrayListDevice,$FK_process = 0,$xml_auth_rep,$ArrayListType,$ArrayListAgentProcess)
{
	global $DB;
	
	$Thread = new Threads;
	$config_snmp_script = new glpi_plugin_tracker_config_snmp_script;
	
	$nb_process_query = $config_snmp_script->getValue('nb_process');

	// Prepare processes
	$while = 'while (';
	for ($i = 1;$i <= $nb_process_query;$i++)
	{
		if ($i == $nb_process_query){
			$while .= '$t['.$i.']->isActive()';
		}else{
			$while .= '$t['.$i.']->isActive() || ';
		}
	}
	
	$while .= ') {';
	for ($i = 1;$i <= $nb_process_query;$i++)
	{
		$while .= 'echo $t['.$i.']->listen();';
	}
	$while .= '}';
	
	$close = '';
	for ($i = 1;$i <= $nb_process_query;$i++)
	{
		$close .= 'echo $t['.$i.']->close();';
	}	
	// End processes
	
	$s = 0;
	foreach ( $ArrayListDevice as $num=>$IDDevice )
	{
		$s++;
		$t[$s] = $Thread->create("tracker_fullsync.php --update_device_process=1 --id=".$IDDevice." --FK_process=".$FK_process." --FK_agent_process=".$ArrayListAgentProcess[$num]." --type=".$ArrayListType[$num]);

		if ($nb_process_query == $s)
		{
			eval($while);
			eval($close);
			$s = 0;
		}
	}
	if ($s > 0)
	{
		$s++;
		for ($s;$s <= $nb_process_query ;$s++)
		{
			$while = str_replace("|| \$t[".$s."]->isActive()", "", $while);
			$while = str_replace("echo \$t[".$s."]->listen();", "", $while);
			$close = str_replace("echo \$t[".$s."]->close();", "", $close);
		}
		eval($while);
		eval($close);
		$s = 0;	
	}
}



/**
 * Get and update infos of networking and its ports  
 *
 * @param $ArrayListDeviceNetworking ID => IP of the network materiel
 * @param $type type of device (NETWORKING_TYPE, PRINTER_TYPE ...)
 *
 * @return nothing
 *
**/
function plugin_tracker_UpdateDeviceBySNMP_process($ID_Device,$FK_process = 0,$xml_auth_rep,$type,$FK_agent_process)
{
	$ifIP = "";
	$_SESSION['FK_process'] = $FK_process;
	
	$plugin_tracker_snmp_auth = new plugin_tracker_snmp_auth;
	$Threads = new Threads;
	$logs = new plugin_tracker_logs;
	$models = new plugin_tracker_model_infos;
	$walks = new plugin_tracker_walk;
	$plugin_tracker_snmp = new plugin_tracker_snmp;

	// Load XML Device ID
	$xml = simplexml_load_file(GLPI_PLUGIN_DOC_DIR."/tracker/".$FK_agent_process."-device.xml");
	foreach($xml->device as $device){
		if (($device->infos->id == $ID_Device) AND ($device->infos->type == $type))
		{
			$device_snmp = $device;		
			break;
		}			
	}

	// Get SNMP model oids
	$oidsModel = $models->oidlist($ID_Device,$type);
	ksort($oidsModel);

	if ((isset($oidsModel)) && ($ID_Device != ""))
	{
		// Get oidvalues from agents
		$oidvalues = $walks->GetoidValues($device_snmp);
		if (is_array($oidvalues))
			ksort($oidvalues);
		else
			return;
		// Update count Process server script
		switch ($type)
		{
			case NETWORKING_TYPE :
				$Threads->updateProcess($_SESSION['FK_process'],1);
				break;
			case PRINTER_TYPE :
				$Threads->updateProcess($_SESSION['FK_process'],0,1);
				break;
		}

		// ** Get oid of PortName
		$Array_Object_oid_ifName = $oidsModel[0][1]['ifName'];

		$Array_Object_oid_ifType = $oidsModel[0][1]['ifType'];
		
		// ** Get oid of vtpVlanName
		$Array_Object_oid_vtpVlanName = '';
		if (isset($oidsModel[0][0]['vtpVlanName']))
			$Array_Object_oid_vtpVlanName = $oidsModel[0][0]['vtpVlanName'];

		// ** Get from SNMP, description of equipment
		$sysdescr = $oidvalues[".1.3.6.1.2.1.1.1.0"][""];

		//**
		$ArrayPort_LogicalNum_SNMPName = $walks->GetoidValuesFromWalk($oidvalues,$oidsModel[0][1]['ifName']);

		// **
		$ArrayPort_LogicalNum_SNMPNum = $walks->GetoidValuesFromWalk($oidvalues,$oidsModel[0][1]['ifIndex'],1);

		// ** Get oid ports Counter
			//array with logic number => portsID from snmp
		$ArrayPort_Object_oid = tracker_snmp_GetOIDPorts($ID_Device,$type,$oidsModel,$oidvalues,$ArrayPort_LogicalNum_SNMPName,$ArrayPort_LogicalNum_SNMPNum);

		// ** Get link OID fields (oid => link)
		$Array_Object_TypeNameConstant = $plugin_tracker_snmp->GetLinkOidToFields($ID_Device,$type);

		if ($type == NETWORKING_TYPE)
			plugin_tracker_snmp_networking_ifaddr($ID_Device,$type,$oidsModel,$oidvalues);


		// ** Update fields of switchs
		tracker_snmp_UpdateGLPIDevice($ID_Device,$type,$oidsModel,$oidvalues,$Array_Object_TypeNameConstant);

		//** From DB Array : portName => glpi_networking_ports.ID
		$ArrayPortDB_Name_ID = $plugin_tracker_snmp->GetPortsID($ID_Device,$type);

		// ** Update ports fields of switchs
		if (!empty($ArrayPort_Object_oid))
			UpdateGLPINetworkingPorts($ID_Device,$type,$oidsModel,$oidvalues,$Array_Object_TypeNameConstant,$ArrayPort_Object_oid);
		$Array_trunk_ifIndex = array();

		if ($type == NETWORKING_TYPE)	
			$Array_trunk_ifIndex = cdp_trunk($ID_Device,$type,$oidsModel,$oidvalues,$ArrayPort_LogicalNum_SNMPNum);

		// ** Get MAC adress of connected ports
		$array_port_trunk = array();
		if (!empty($ArrayPort_Object_oid))
			$array_port_trunk = GetMACtoPort($ID_Device,$type,$oidsModel,$oidvalues,$array_port_trunk,$ArrayPortDB_Name_ID);

		if ($type ==  NETWORKING_TYPE)
		{
			// Foreach VLAN ID to GET MAC Adress on each VLAN
			$Array_vlan = $walks->GetoidValuesFromWalk($oidvalues,$oidsModel[0][1]['vtpVlanName'],1);
			foreach ($Array_vlan as $num=>$vlan_ID)
				GetMACtoPort($ID_Device,$type,$oidsModel,$oidvalues,$array_port_trunk,$ArrayPortDB_Name_ID,$vlan_ID);
		}
	}
}



/**
 * Get port OID list for the SNMP model && create ports in DB if they don't exists 
 *
 * @param $ID_Device : ID of device
 * @param $type : type of device (NETWORKING_TYPE, PRINTER_TYPE ...)
 * @param $oidsModel : oid list from model SNMP
 * @param $oidvalues : list of values from agent query
 * @param $ArrayPort_LogicalNum_SNMPNum : array logical port number => SNMP port number (ifindex)
 * @param $ArrayPort_LogicalNum_SNMPName : array logical port number => SNMP Port name
 *
 * @return $oidList : array with logic number => portsID from snmp
 *
**/
function tracker_snmp_GetOIDPorts($ID_Device,$type,$oidsModel,$oidvalues,$ArrayPort_LogicalNum_SNMPName,$ArrayPort_LogicalNum_SNMPNum)
{
	global $DB,$LANG;

	$np=new Netport();
	$logs = new plugin_tracker_logs;

	$logs->write("tracker_fullsync",">>>>>>>>>> Get OID ports list (SNMP model) and create ports in DB if not exists <<<<<<<<<<",$type."][".$ID_Device,1);

	$portcounter = $oidvalues[$oidsModel[1][0][""]][""];
	$logs->write("tracker_fullsync","oid port counter : ".$oidsModel[1][0][""]." = ".$portcounter,$type."][".$ID_Device,1);

	$oid_ifType = $oidsModel[0][1]['ifType'];
	$logs->write("tracker_fullsync","type of port : ".$oid_ifType,$type."][".$ID_Device,1);

	// Get query SNMP to have number of ports
	if ((isset($portcounter)) AND (!empty($portcounter)))
	{
		// ** Add ports in DataBase if they don't exists
		for ($i = 0; $i < $portcounter; $i++)
		{
			// Get type of port
			$ifType = $oidvalues[$oid_ifType.".".$ArrayPort_LogicalNum_SNMPNum[$i]][""];
			$oidList[] = $ArrayPort_LogicalNum_SNMPNum[$i];
			
			if ((strstr($ifType, "ethernetCsmacd"))
				OR ($ifType == "6")
				OR ($ifType == "ethernet-csmacd(6)"))
			{
				// Increment number of port queried in process
//				$query = "UPDATE glpi_plugin_tracker_processes SET ports_queries = ports_queries + 1
//				WHERE process_id='".$FK_process."' ";
//				$DB->query($query);

				$query = "SELECT ID,name
				FROM glpi_networking_ports
				WHERE on_device='".$ID_Device."'
					AND device_type='".$type."'
					AND logical_number='".$i."' ";
				$result = $DB->query($query);
				if ( $DB->numrows($result) == 0 )
				{
					unset($array);
					$array["logical_number"] = $i;
					$array["name"] = $ArrayPort_LogicalNum_SNMPName[$i];
					$array["on_device"] = $ID_Device;
					$array["device_type"] = $type;

					$IDport = $np->add($array);
					logEvent(0, "networking", 5, "inventory", "Tracker ".$LANG["log"][70]);
					$logs->write("tracker_fullsync","Add port in DB (glpi_networking_ports) : ".$ArrayPort_LogicalNum_SNMPName[$i],$type."][".$ID_Device,1);
				}
				else
				{
					$IDport = $DB->result($result, 0, "ID");
					if ($DB->result($result, 0, "name") != $ArrayPort_LogicalNum_SNMPName[$i])
					{
						unset($array);
						$array["name"] = $ArrayPort_LogicalNum_SNMPName[$i];
						$array["ID"] = $DB->result($result, 0, "ID");
						$np->update($array);
						$logs->write("tracker_fullsync","Update port in DB (glpi_networking_ports) : ID".$DB->result($result, 0, "ID")." & name ".$ArrayPort_LogicalNum_SNMPName[$i],$type."][".$ID_Device,1);
					}
				}
				if ($type == NETWORKING_TYPE)
				{
					$queryTrackerPort = "SELECT ID
					FROM glpi_plugin_tracker_networking_ports
					WHERE FK_networking_ports='".$IDport."' ";

					$resultTrackerPort = $DB->query($queryTrackerPort);
					if ( $DB->numrows($resultTrackerPort) == 0 ) {

						$queryInsert = "INSERT INTO glpi_plugin_tracker_networking_ports
							(FK_networking_ports)
						VALUES ('".$IDport."') ";
						$DB->query($queryInsert);
						$logs->write("tracker_fullsync","Add port in DB (glpi_plugin_tracker_networking_ports) : ID ".$IDport,$type."][".$ID_Device,1);
					}
				}
			}
		}
	}
	return $oidList;
}



/**
 * Update devices with values get by SNMP 
 *
 * @param $ID_Device : ID of device
 * @param $type : type of device (NETWORKING_TYPE, PRINTER_TYPE ...)
 * @param $oidsModel : oid list from model SNMP
 * @param $oidvalues : list of values from agent query
 * @param $Array_Object_TypeNameConstant : array with oid => constant in relation with fields to update 
 *
 * @return $oidList : array with ports object name and oid
 *
**/
function tracker_snmp_UpdateGLPIDevice($ID_Device,$type,$oidsModel,$oidvalues,$Array_Object_TypeNameConstant)
{
	global $DB,$LANG,$CFG_GLPI,$TRACKER_MAPPING;

	$logs = new plugin_tracker_logs;
	$logs->write("tracker_fullsync",">>>>>>>>>> Update devices values <<<<<<<<<<",$type."][".$ID_Device,1);

	// Update 'last_tracker_update' field 
	$query = "UPDATE ";
	if ($type == NETWORKING_TYPE) 
		$query .= "glpi_plugin_tracker_networking 
		SET last_tracker_update='".date("Y-m-d H:i:s")."',
		last_PID_update='".$_SESSION['FK_process']."'
		WHERE FK_networking='".$ID_Device."' ";
	if ($type == PRINTER_TYPE) 
		$query .= "glpi_plugin_tracker_printers 
		SET last_tracker_update='".date("Y-m-d H:i:s")."' 
		WHERE FK_printers='".$ID_Device."' ";
	$DB->query($query);
	
	foreach($Array_Object_TypeNameConstant as $oid=>$link)
	{
		if (!preg_match("/\.$/",$oid)) // SNMPGet ONLY
		{
			if ((isset($TRACKER_MAPPING[$type][$link]['dropdown'])) AND ($TRACKER_MAPPING[$type][$link]['dropdown'] != ""))
			{
				$oidvalues[$oid][""] = plugin_tracker_hex_to_string($oidvalues[$oid][""]);
				$oidvalues[$oid][""] = externalImportDropdown($TRACKER_MAPPING[$type][$link]['dropdown'],$oidvalues[$oid][""],0);			
			}

			switch ($type)
			{
				case NETWORKING_TYPE :
					$Field = "FK_networking";
					if ($TRACKER_MAPPING[$type][$link]['table'] == "glpi_networking")
						$Field = "ID";
					break;
				case PRINTER_TYPE :
					$Field = "FK_printers";
					if ($TRACKER_MAPPING[$type][$link]['table'] == "glpi_printers")
						$Field = "ID";
					break;
			}
			$logs->write("tracker_fullsync",$link." = ".$oidvalues[$oid][""],$type."][".$ID_Device,1);

			// * Memory
			if (($link == "ram") OR ($link == "memory"))
			{
				$oidvalues[$oid][""] = ceil(($oidvalues[$oid][""] / 1024) / 1024) ;
				if ($type == PRINTER_TYPE)
					$oidvalues[$oid][""] .= " MB";
			}

				if ($link == 'macaddr')
				{
					$MacAddress = str_replace("0x","",$oidvalues[$oid][""]);
					$MacAddress_tmp = str_split($MacAddress, 2);
					$MacAddress = $MacAddress_tmp[0];
					for($i = 1; $i < count($MacAddress_tmp); $i++)
						$MacAddress .= ":".$MacAddress_tmp[$i];
					$oidvalues[$oid][""] = $MacAddress;
				}

			// Convert hexa in string
			$oidvalues[$oid][""] = plugin_tracker_hex_to_string($oidvalues[$oid][""]);

			if (strstr($oidvalues[$oid][""], "noSuchName"))
			{
				// NO Update field in GLPI
			}
			else if ($TRACKER_MAPPING[$type][$link]['table'] == "glpi_plugin_tracker_printers_cartridges")
			{
				// * Printers cartridges
				$object_name_clean = str_replace("MAX", "", $link);
				$object_name_clean = str_replace("REMAIN", "", $object_name_clean);
				if (strstr($link, "MAX"))
					$printer_cartridges_max_remain[$object_name_clean]["MAX"] = $oidvalues[$oid][""];
				if (strstr($link, "REMAIN"))
					$printer_cartridges_max_remain[$object_name_clean]["REMAIN"] = $oidvalues[$oid][""];
				if ((isset($printer_cartridges_max_remain[$object_name_clean]["MAX"])) AND (isset($printer_cartridges_max_remain[$object_name_clean]["REMAIN"])))
				{
					$pourcentage = ceil((100 * $printer_cartridges_max_remain[$object_name_clean]["REMAIN"]) / $printer_cartridges_max_remain[$object_name_clean]["MAX"]);
					// Test existance of row in MySQl
						$query_sel = "SELECT * FROM ".$TRACKER_MAPPING[$type][$link]['table']."
						WHERE ".$Field."='".$ID_Device."'
							AND object_name='".$object_name_clean."' ";
						$result_sel = $DB->query($query_sel);
						if ($DB->numrows($result_sel) == "0")
						{
							$queryInsert = "INSERT INTO ".$TRACKER_MAPPING[$type][$link]['table']."
							(".$Field.",object_name)
							VALUES('".$ID_Device."', '".$object_name_clean."') ";
				
							$DB->query($queryInsert);
						}
					$queryUpdate = "UPDATE ".$TRACKER_MAPPING[$type][$link]['table']."
					SET ".$TRACKER_MAPPING[$type][$link]['field']."='".$pourcentage."' 
					WHERE ".$Field."='".$ID_Device."'
						AND object_name='".$object_name_clean."' ";
	
					$DB->query($queryUpdate);
					unset($printer_cartridges_max_remain[$object_name_clean]["MAX"]);
					unset($printer_cartridges_max_remain[$object_name_clean]["REMAIN"]);
				}
				else
				{
					$queryUpdate = "UPDATE ".$TRACKER_MAPPING[$type][$link]['table']."
					SET ".$TRACKER_MAPPING[$type][$link]['field']."='".$oidvalues[$oid][""]."' 
					WHERE ".$Field."='".$ID_Device."'
						AND object_name='".$link."' ";
			
					$DB->query($queryUpdate);
				}
			}
			else if (strstr($link, "pagecounter"))
			{
				// Detect if the script has wroten a line for the counter today (if yes, don't touch, else add line)
				$today = strftime("%Y-%m-%d", time());
				$query_line = "SELECT * FROM glpi_plugin_tracker_printers_history
				WHERE date LIKE '".$today."%'
					AND FK_printers='".$ID_Device."' ";
				$result_line = $DB->query($query_line);
				if ($DB->numrows($result_line) == "0")
				{
					if (empty($oidvalues[$oid][""]))
						$oidvalues[$oid][""] = 0;
					$queryInsert = "INSERT INTO ".$TRACKER_MAPPING[$type][$link]['table']."
					(".$TRACKER_MAPPING[$type][$link]['field'].",".$Field.", date)
					VALUES('".$oidvalues[$oid][""]."','".$ID_Device."', '".$today."') ";
		
					$DB->query($queryInsert);
				}
				else
				{
					$data_line = $DB->fetch_assoc($result_line);
					if ($data_line[$TRACKER_MAPPING[$type][$link]['field']] == "0")
					{
						if (empty($oidvalues[$oid][""]))
							$oidvalues[$oid][""] = 0;
						$queryUpdate = "UPDATE ".$TRACKER_MAPPING[$type][$link]['table']."
						SET ".$TRACKER_MAPPING[$type][$link]['field']."='".$oidvalues[$oid][""]."' 
						WHERE ".$Field."='".$ID_Device."'
							AND date LIKE '".$today."%' ";			
					
						$DB->query($queryUpdate);
					}
				}
			}
			else if (($link == "cpuuser") OR ($link ==  "cpusystem"))
			{
				if ($object_name == "cpuuser")
					$cpu_values['cpuuser'] = $oidvalues[$oid][""];
				if ($object_name ==  "cpusystem")
					$cpu_values['cpusystem'] = $oidvalues[$oid][""];
	
				if ((isset($cpu_values['cpuuser'])) AND (isset($cpu_values['cpusystem'])))
				{
					$queryUpdate = "UPDATE ".$TRACKER_MAPPING[$type][$link]['table']."
					SET ".$TRACKER_MAPPING[$type][$link]['field']."='".($cpu_values['cpuuser'] + $cpu_values['cpusystem'])."' 
					WHERE ".$Field."='".$ID_Device."'";
	
					$DB->query($queryUpdate);
					unset($cpu_values);
				}
			}
			else if ($TRACKER_MAPPING[$type][$link]['table'] != "")
			{
				if (($TRACKER_MAPPING[$type][$link]['field'] == "cpu") AND ($oidvalues[$oid][""] == ""))
					$SNMPValue = 0;
				
				if (strstr($TRACKER_MAPPING[$type][$link]['table'], "glpi_plugin_tracker"))
				{
					$queryUpdate = "UPDATE ".$TRACKER_MAPPING[$type][$link]['table']."
					SET ".$TRACKER_MAPPING[$type][$link]['field']."='".$oidvalues[$oid][""]."' 
					WHERE ".$Field."='".$ID_Device."'";
	
					$DB->query($queryUpdate);
				}
				else
				{
					$commonitem = new commonitem;
					$commonitem->setType($type,true);
	
					$tableau[$Field] = $ID_Device;
					$tableau[$TRACKER_MAPPING[$type][$link]['field']] = $oidvalues[$oid][""];
					$commonitem->obj->update($tableau);
				}
			}
		}
	}
}



/**
 * Update Networking ports from devices SNMP queries 
 *
 * @param $ID_Device : ID of device
 * @param $type : type of device (NETWORKING_TYPE, PRINTER_TYPE ...)
 * @param $oidsModel : oid list from model SNMP
 * @param $oidvalues : list of values from agent query
 * @param $Array_Object_TypeNameConstant : array with oid => constant in relation with fields to update 
 * @param $ArrayPort_Object_oid : array with logic number => portsID from snmp
 *
**/
function UpdateGLPINetworkingPorts($ID_Device,$type,$oidsModel,$oidvalues,$Array_Object_TypeNameConstant,$ArrayPort_Object_oid)
{
	global $DB,$LANG,$TRACKER_MAPPING;	
	
	$snmp_queries = new plugin_tracker_snmp;
	$logs = new plugin_tracker_logs;
	$walks = new plugin_tracker_walk;

	$logs->write("tracker_fullsync",">>>>>>>>>> Update ports device values <<<<<<<<<<",$type."][".$ID_Device,1);

	foreach($Array_Object_TypeNameConstant as $oid=>$link)
	{
		if ((preg_match("/\.$/",$oid)) AND (!empty($TRACKER_MAPPING[$type][$link]['field']))) // SNMPWalk ONLY (ports)
		{
//			print "OID : ".$oid."\n";
			
			// For each port
			if ($TRACKER_MAPPING[$type][$link]['field'] == 'ifmac')
				$query = "SELECT glpi_networking_ports.ID, logical_number, glpi_networking_ports.ifmac as ifmac FROM glpi_networking_ports
				LEFT JOIN glpi_plugin_tracker_networking_ports ON FK_networking_ports=glpi_networking_ports.ID
				WHERE on_device='".$ID_Device."'
					AND device_type='".$type."'
				ORDER BY logical_number";
			else
				$query = "SELECT glpi_networking_ports.ID, logical_number, ".$TRACKER_MAPPING[$type][$link]['field']." FROM glpi_networking_ports
				LEFT JOIN glpi_plugin_tracker_networking_ports ON FK_networking_ports=glpi_networking_ports.ID
				WHERE on_device='".$ID_Device."'
					AND device_type='".$type."'
				ORDER BY logical_number";

			$result=$DB->query($query);
			
			while ($data=$DB->fetch_array($result))
			{
				// Update Last UP
				if (($link == 'ifstatus') AND ($oidvalues[$oid.$ArrayPort_Object_oid[$data['logical_number']]][""] == "1"))
				{
					$query_update = "UPDATE glpi_plugin_tracker_networking_ports
					SET lastup='".date("Y-m-d H:i:s")."'
					WHERE FK_networking_ports='".$data["ID"]."' ";
					$DB->query($query_update);
				}

				if (($link == 'ifPhysAddress') AND (!strstr($oidvalues[$oid.$ArrayPort_Object_oid[$data['logical_number']]][""], ":")))
				{
					$MacAddress = str_replace("0x","",$oidvalues[$oid.$ArrayPort_Object_oid[$data['logical_number']]][""]);
					$MacAddress_tmp = str_split($MacAddress, 2);
					$MacAddress = $MacAddress_tmp[0];
					for($i = 1; $i < count($MacAddress_tmp); $i++)
						$MacAddress .= ":".$MacAddress_tmp[$i];
					$oidvalues[$oid.$ArrayPort_Object_oid[$data['logical_number']]][""] = $MacAddress;
					if (empty($oidvalues[$oid.$ArrayPort_Object_oid[$data['logical_number']]][""]))
						$oidvalues[$oid.$ArrayPort_Object_oid[$data['logical_number']]][""] = "00:00:00:00:00:00";
				}
				$logs->write("tracker_fullsync","****************",$type."][".$ID_Device,1);
				$logs->write("tracker_fullsync","Oid : ".$oid,$type."][".$ID_Device,1);
				$logs->write("tracker_fullsync","Link : ".$link,$type."][".$ID_Device,1);

				if ( ($link == "ifPhysAddress") AND ($oidvalues[$oid.$ArrayPort_Object_oid[$data['logical_number']]][""] != "") )
						$oidvalues[$oid.$ArrayPort_Object_oid[$data['logical_number']]][""] = $snmp_queries->MAC_Rewriting($oidvalues[$oid.$ArrayPort_Object_oid[$data['logical_number']]][""]);

				if ($link == "ifaddr")
				{
					$ArrayPort_Object_oid2 = $ArrayPort_Object_oid;
					$Arrayifaddr = $walks->GetoidValuesFromWalk($oidvalues,$oidsModel[0][1]['ifaddr'],1);
					for($j=0;$j < count($Arrayifaddr);$j++)
					{
						if ($oidvalues[$oid.$Arrayifaddr[$j]][""] == $ArrayPort_Object_oid[$data['logical_number']])
							$ArrayPort_Object_oid[$data['logical_number']] = $Arrayifaddr[$j];
					}
					$logs->write("tracker_fullsync","=> ".$ArrayPort_Object_oid[$data['logical_number']],$type."][".$ID_Device,1);
				}
				else
					if (isset($oidvalues[$oid.$ArrayPort_Object_oid[$data['logical_number']]][""]))
						$logs->write("tracker_fullsync","=> ".$oidvalues[$oid.$ArrayPort_Object_oid[$data['logical_number']]][""],$type."][".$ID_Device,1);

				if (isset($oidvalues[$oid.$ArrayPort_Object_oid[$data['logical_number']]][""]))
					if ($data[$TRACKER_MAPPING[$type][$link]['field']] != $oidvalues[$oid.$ArrayPort_Object_oid[$data['logical_number']]][""])
					{
						if ($TRACKER_MAPPING[$type][$link]['table'] == "glpi_networking_ports")
							$ID_field = "ID";
						else
							$ID_field = "FK_networking_ports";

						if ($link == "ifaddr")
							$queryUpdate = "UPDATE ".$TRACKER_MAPPING[$type][$link]['table']."
							SET ".$TRACKER_MAPPING[$type][$link]['field']."='".$ArrayPort_Object_oid[$data['logical_number']]."'
							WHERE ".$ID_field."='".$data["ID"]."'";
						else
							$queryUpdate = "UPDATE ".$TRACKER_MAPPING[$type][$link]['table']."
							SET ".$TRACKER_MAPPING[$type][$link]['field']."='".$oidvalues[$oid.$ArrayPort_Object_oid[$data['logical_number']]][""]."'
							WHERE ".$ID_field."='".$data["ID"]."'";

						$DB->query($queryUpdate);
						// Delete port wire if port is internal disable
						if (($link == "ifinternalstatus") AND (($oidvalues[$oid.$ArrayPort_Object_oid[$data['logical_number']]][""] == "2") OR ($oidvalues[$oid.$ArrayPort_Object_oid[$data['logical_number']]][""] == "down(2)")))
						{
							$netwire=new Netwire;
							addLogConnection("remove",$netwire->getOppositeContact($data["ID"]),$FK_process);
							addLogConnection("remove",$data["ID"],$FK_process);
							removeConnector($data["ID"]);

						}
						// Add log because snmp value change
						tracker_snmp_addLog($data["ID"],$TRACKER_MAPPING[$type][$link]['name'],$data[$TRACKER_MAPPING[$type][$link]['field']],$oidvalues[$oid.$ArrayPort_Object_oid[$data['logical_number']]][""],$_SESSION['FK_process']);

					}
				if ($link == "ifaddr")
					$ArrayPort_Object_oid = $ArrayPort_Object_oid2;
			}
		}
	}
}



/**
 * Associate a MAC address of a device to switch port 
 *
 * @param $IP : ip of the device
 * @param $ArrayPortsID : array with port name and port ID (from DB)
 * @param $IDNetworking : ID of device
 * @param $snmp_version : version of SNMP (1, 2c or 3)
 * @param $snmp_auth : array with snmp authentification parameters
 * @param $FK_process : PID of the process (script run by console)
 * @param $Array_trunk_ifIndex : array with SNMP port ID => 1 (from CDP)
 * @param $vlan : VLAN number
 * @param $array_port_trunk : array with SNMP port ID => 1 (from trunk oid)
 * @param $vlan_name : VLAN name
 *
 * @return $array_port_trunk : array with SNMP port ID => 1 (from trunk oid)
 *
**/
function GetMACtoPort($ID_Device,$type,$oidsModel,$oidvalues,$array_port_trunk,$ArrayPortsID,$vlan="")
{
	global $DB;
	
	$logs = new plugin_tracker_logs;
	$processes = new Threads;
	$netwire = new Netwire;
	$snmp_queries = new plugin_tracker_snmp;
	$walks = new plugin_tracker_walk;
	$unknown = new plugin_tracker_unknown;

	// If Cisco
//	if(strstr($oidvalues[".1.3.6.1.2.1.1.1.0"][""],"Cisco"))
//	{
		$logs->write("tracker_fullsync",">>>>>>>>>> Networking : Get MAC associate to Port [Vlan ".$vlan."] <<<<<<<<<<",$type."][".$ID_Device,1);

		$ArrayMACAdressTableVerif = array();
	
		if(strstr($oidvalues[".1.3.6.1.2.1.1.1.0"][""],"Cisco"))
		{
			// Get by SNMP query the mac addresses and IP (ipNetToMediaPhysAddress)
			$ArrayIPMACAdressePhys = $walks->GetoidValuesFromWalk($oidvalues,$oidsModel[0][1]['ipNetToMediaPhysAddress'],1,$vlan);

			if (empty($ArrayIPMACAdressePhys))
				return;

			// Array : num => dynamic data
			$ArrayMACAdressTable = $walks->GetoidValuesFromWalk($oidvalues,$oidsModel[0][1]['dot1dTpFdbAddress'],1,$vlan);

			foreach($ArrayMACAdressTable as $num=>$dynamicdata)
			{
				$oidExplode = explode(".", $dynamicdata);
				// Get by SNMP query the port number (dot1dTpFdbPort)
				if (((count($oidExplode) > 3)) AND (isset($oidvalues[$oidsModel[0][1]['dot1dTpFdbPort'].".".$dynamicdata][$vlan])))
				{
					$BridgePortNumber = $oidvalues[$oidsModel[0][1]['dot1dTpFdbPort'].".".$dynamicdata][$vlan];

					$logs->write("tracker_fullsync","****************",$type."][".$ID_Device,1);
					$logs->write("tracker_fullsync","BRIDGEPortNumber = ".$BridgePortNumber,$type."][".$ID_Device,1);

					$BridgePortifIndex = $oidvalues[$oidsModel[0][1]['dot1dBasePortIfIndex'].".".$BridgePortNumber][$vlan];
					$stop = 0;
					if (($BridgePortifIndex == "") OR ($BridgePortifIndex == "No Such Instance currently exists at this OID"))
						$stop = 1;

					if ((isset($Array_trunk_ifIndex[$BridgePortifIndex])) AND ($Array_trunk_ifIndex[$BridgePortifIndex] == "1"))
						$stop = 1;

					if ($stop == "0")
					{
						$logs->write("tracker_fullsync","BridgePortifIndex = ".$BridgePortifIndex,$type."][".$ID_Device,1);

						$ifName = $oidvalues[$oidsModel[0][1]['ifName'].".".$BridgePortifIndex][""];

						$logs->write("tracker_fullsync","** Interface = ".$ifName,$type."][".$ID_Device,1);

						// Convert MAC HEX in Decimal
						$MacAddress = str_replace("0x","",$oidvalues[$oidsModel[0][1]['dot1dTpFdbAddress'].".".$dynamicdata][$vlan]);
						$MacAddress_tmp = str_split($MacAddress, 2);
						$MacAddress = $MacAddress_tmp[0];
						for($i = 1; $i < count($MacAddress_tmp); $i++)
							$MacAddress .= ":".$MacAddress_tmp[$i];

						// Verify Trunk

						$logs->write("tracker_fullsync","Vlan = ".$vlan,$type."][".$ID_Device,1);
						if (isset($oidvalues[$oidsModel[0][1]['vlanTrunkPortDynamicStatus'].".".$BridgePortifIndex][$vlan]))
							$logs->write("tracker_fullsync","TrunkStatus = ".$oidvalues[$oidsModel[0][1]['vlanTrunkPortDynamicStatus'].".".$BridgePortifIndex][$vlan],$type."][".$ID_Device,1);
						else
							$logs->write("tracker_fullsync","TrunkStatus = ",$type."][".$ID_Device,1);
						$logs->write("tracker_fullsync","Mac address = ".$MacAddress,$type."][".$ID_Device,1);

						$queryPortEnd = "";
						if ((!isset($oidvalues[$oidsModel[0][1]['vlanTrunkPortDynamicStatus'].".".$BridgePortifIndex][$vlan]))
							OR (empty($oidvalues[$oidsModel[0][1]['vlanTrunkPortDynamicStatus'].".".$BridgePortifIndex][$vlan]))
							OR ($oidvalues[$oidsModel[0][1]['vlanTrunkPortDynamicStatus'].".".$BridgePortifIndex][$vlan] == "2"))
						{
							$logs->write("tracker_fullsync","Mac address OK",$type."][".$ID_Device,1);

							$queryPortEnd = "SELECT * FROM glpi_networking_ports
							WHERE ifmac IN ('".$MacAddress."','".strtoupper($MacAddress)."')
								AND on_device!='".$ID_Device."' ";
						}
						else if (($oidvalues[$oidsModel[0][1]['vlanTrunkPortDynamicStatus'].".".$BridgePortifIndex][$vlan] == "1") AND ($vlan != "")) // It's a trunk port
						{
							$logs->write("tracker_fullsync","Mac address FAILED(1)",$type."][".$ID_Device,1);
							$queryPortEnd = "";
							$array_port_trunk[$ArrayPortsID[$ifName]] = 1;
						}
						else if ($oidvalues[$oidsModel[0][1]['vlanTrunkPortDynamicStatus'].".".$BridgePortifIndex][$vlan] == "1") // It's a trunk port
						{
							$logs->write("tracker_fullsync","Mac address FAILED(2)",$type."][".$ID_Device,1);
							$queryPortEnd = "";
							$array_port_trunk[$ArrayPortsID[$ifName]] = 1;
						}

						if (($queryPortEnd != ""))
						{
							$resultPortEnd=$DB->query($queryPortEnd);
							$traitement = 1;
							if ($vlan != "")
							{
								if (isset($array_port_trunk[$ArrayPortsID[$ifName]]) && $array_port_trunk[$ArrayPortsID[$ifName]] == "1")
									$traitement = 0;
							}

							if (!isset($ArrayPortsID[$ifName]))
								$traitement = 0;

							if (isset($ArrayPortsID[$ifName]))
							{
								$sport = $ArrayPortsID[$ifName]; // Networking_Port
								if ( ($DB->numrows($resultPortEnd) != 0) && ($traitement == "1") )
								{
									$dport = $DB->result($resultPortEnd, 0, "ID"); // Port of other materiel (Computer, printer...)

									// Connection between ports (wire table in DB)
									$snmp_queries->PortsConnection($sport, $dport,$_SESSION['FK_process']);
								}
								else if ( $traitement == "1" )
								{

									// Mac address unknown
									if ($_SESSION['FK_process'] != "0")
									{
										$ip_unknown = '';
										$MacAddress_Hex = str_replace(":","",$MacAddress);
										$MacAddress_Hex = "0x".$MacAddress_Hex;
										foreach ($ArrayIPMACAdressePhys as $num=>$ips)
										{
											if ($oidvalues[$oidsModel[0][1]['ipNetToMediaPhysAddress'].".".$ips][$vlan] == $MacAddress_Hex)
												$ip_unknown = preg_replace("/^1\./","",$ips);
										}

										// Search IP in OCS IPdiscover if OCS servers specified
										if (empty($ip_unknown))
											$ip_unknown = plugin_tracker_search_ip_ocs_servers($macaddress);
										$name_unknown = plugin_tracker_search_name_ocs_servers($macaddress);
										// Add unknown device
										$unknown_infos["name"] = $name_unknown;
										$newID=$unknown->add($unknown_infos);
										// Add networking_port
										$np=new Netport();
										$port_add["on_device"] = $newID;
										$port_add["device_type"] = PLUGIN_TRACKER_MAC_UNKNOWN;
										$port_add["ifaddr"] = $ip_unknown;
										$port_add['ifmac'] = $MacAddress;
										$port_ID = $np->add($port_add);
										//$processes->unknownMAC($_SESSION['FK_process'],$ArrayPortsID[$ifName],$MacAddress,$sport,$ip_unknown);
									}
								}
							}
						}
					}
				}
			}
		}
		else if(strstr($oidvalues[".1.3.6.1.2.1.1.1.0"][""],"3Com IntelliJack NJ225"))
		{
			// Get by SNMP query the mac addresses and IP (ipNetToMediaPhysAddress)
			$ArrayMACAdressTable = $walks->GetoidValuesFromWalk($oidvalues,$oidsModel[0][1]['dot1dTpFdbAddress'],1,$vlan);
			
			foreach($ArrayMACAdressTable as $num=>$dynamicdata)
			{
				$oidExplode = explode(".", $dynamicdata);
				// Get by SNMP query the port number (dot1dTpFdbPort)
				if (((count($oidExplode) > 3)) AND (isset($oidvalues[$oidsModel[0][1]['dot1dTpFdbPort'].".".$dynamicdata][$vlan])))
				{
					$BridgePortNumber = $oidvalues[$oidsModel[0][1]['dot1dTpFdbPort'].".".$dynamicdata][$vlan];
					if ($BridgePortNumber > 1)
					{
						echo $num." : ".$BridgePortNumber."\n";
						// Convert MAC HEX in Decimal
						$MacAddress = str_replace("0x","",$oidvalues[$oidsModel[0][1]['dot1dTpFdbAddress'].".".$dynamicdata][$vlan]);
						$MacAddress_tmp = str_split($MacAddress, 2);
						$MacAddress = $MacAddress_tmp[0];
						for($i = 1; $i < count($MacAddress_tmp); $i++)
							$MacAddress .= ":".$MacAddress_tmp[$i];
$BridgePortifIndex = $oidvalues[$oidsModel[0][1]['dot1dBasePortIfIndex'].".".$BridgePortNumber][$vlan];

$ifName = $oidvalues[$oidsModel[0][1]['ifName'].".".$BridgePortifIndex][""];
						$queryPortEnd = "SELECT * FROM glpi_networking_ports
							WHERE ifmac IN ('".$MacAddress."','".strtoupper($MacAddress)."')
								AND on_device!='".$ID_Device."' ";
					$resultPortEnd=$DB->query($queryPortEnd);
						$sport = $ArrayPortsID[$ifName]; // Networking_Port
						if ( ($DB->numrows($resultPortEnd) != 0)  )
						{
							$dport = $DB->result($resultPortEnd, 0, "ID"); // Port of other materiel (Computer, printer...)

							// Connection between ports (wire table in DB)
							$snmp_queries->PortsConnection($sport, $dport,$_SESSION['FK_process']);
						}
						else 
						{

							// Mac address unknown
							if ($_SESSION['FK_process'] != "0")
							{
								$ip_unknown = '';
								$MacAddress_Hex = str_replace(":","",$MacAddress);
								$MacAddress_Hex = "0x".$MacAddress_Hex;
								foreach ($ArrayIPMACAdressePhys as $num=>$ips)
								{
									if ($oidvalues[$oidsModel[0][1]['ipNetToMediaPhysAddress'].".".$ips][$vlan] == $MacAddress_Hex)
										$ip_unknown = preg_replace("/^1\./","",$ips);
								}
								if (empty($ip_unknown))
									$ip_unknown = plugin_tracker_search_ip_ocs_servers($macaddress);
								$name_unknown = plugin_tracker_search_name_ocs_servers($macaddress);
								// Add unknown device
								$unknown_infos["name"] = $name_unknown;
								$newID=$unknown->add($unknown_infos);
								// Add networking_port
								$np=new Netport();
								$port_add["on_device"] = $newID;
								$port_add["device_type"] = PLUGIN_TRACKER_MAC_UNKNOWN;
								$port_add["ifaddr"] = $ip_unknown;
								$port_add['ifmac'] = $MacAddress;
								$port_ID = $np->add($port_add);
								//$processes->unknownMAC($_SESSION['FK_process'],$ArrayPortsID[$ifName],$MacAddress,$sport,$ip_unknown);
							}
						}







					}
				}
			}
		}
		else if(strstr($oidvalues[".1.3.6.1.2.1.1.1.0"][""],"ProCurve"))
		{
			// Array : num => dynamic data
			$ArrayMACAdressTable = $walks->GetoidValuesFromWalk($oidvalues,$oidsModel[0][1]['dot1dTpFdbAddress'],1,$vlan);

			foreach($ArrayMACAdressTable as $num=>$dynamicdata)
			{
				$oidExplode = explode(".", $dynamicdata);
				// Get by SNMP query the port number (dot1dTpFdbPort)
				if (((count($oidExplode) > 3)) AND (isset($oidvalues[$oidsModel[0][1]['dot1dTpFdbPort'].".".$dynamicdata][$vlan]) AND ($oidvalues[$oidsModel[0][1]['dot1dTpFdbPort'].".".$dynamicdata][$vlan] != "0")))
				{

					// Convert MAC HEX in Decimal
					$MacAddress = str_replace("0x","",$oidvalues[$oidsModel[0][1]['dot1dTpFdbAddress'].".".$dynamicdata][$vlan]);
					$MacAddress_tmp = str_split($MacAddress, 2);
					$MacAddress = $MacAddress_tmp[0];
					for($i = 1; $i < count($MacAddress_tmp); $i++)
						$MacAddress .= ":".$MacAddress_tmp[$i];

					$BridgePortNumber = $oidvalues[$oidsModel[0][1]['dot1dTpFdbPort'].".".$dynamicdata][$vlan];
					$BridgePortifIndex = $oidvalues[$oidsModel[0][1]['dot1dBasePortIfIndex'].".".$BridgePortNumber][$vlan];
					$ifName = $oidvalues[$oidsModel[0][1]['ifName'].".".$BridgePortifIndex][""];

					$queryPortEnd = "SELECT * FROM glpi_networking_ports
						WHERE ifmac IN ('".$MacAddress."','".strtoupper($MacAddress)."')
							AND on_device!='".$ID_Device."' ";
					$resultPortEnd=$DB->query($queryPortEnd);
					$sport = $ArrayPortsID[$ifName]; // Networking_Port
					if ( ($DB->numrows($resultPortEnd) != 0)  )
					{
						$dport = $DB->result($resultPortEnd, 0, "ID"); // Port of other materiel (Computer, printer...)

						// Connection between ports (wire table in DB)
						$snmp_queries->PortsConnection($sport, $dport,$_SESSION['FK_process']);
					}
					elseif ($_SESSION['FK_process'] != "0") // Mac address unknown
					{
						$ip_unknown = '';
						$MacAddress_Hex = str_replace(":","",$MacAddress);
						$MacAddress_Hex = "0x".$MacAddress_Hex;
						if (empty($ip_unknown))
							$ip_unknown = plugin_tracker_search_ip_ocs_servers($macaddress);
						$name_unknown = plugin_tracker_search_name_ocs_servers($macaddress);
						// Add unknown device
						$unknown_infos["name"] = $name_unknown;
						$newID=$unknown->add($unknown_infos);
						// Add networking_port
						$np=new Netport();
						$port_add["on_device"] = $newID;
						$port_add["device_type"] = PLUGIN_TRACKER_MAC_UNKNOWN;
						$port_add["ifaddr"] = $ip_unknown;
						$port_add['ifmac'] = $MacAddress;
						$port_ID = $np->add($port_add);
						//$processes->unknownMAC($_SESSION['FK_process'],$ArrayPortsID[$ifName],$MacAddress,$sport,$ip_unknown);
					}
				}
			}
		}

		if ($vlan == "")
			return $array_port_trunk;
//	}
}



/**
 * Determine CDP ports (trunk)
 *
 * @param $ID_Device : ID of device
 * @param $type type of device (NETWORKING_TYPE, PRINTER_TYPE ...)
 * @param $oidsModel : oid list from model SNMP
 * @param $oidvalues : list of values from agent query
 * @param $ArrayPort_LogicalNum_SNMPNum : array logical port number => SNMP port number (ifindex)
 *
 * @return array of trunk ports
 *
**/
function cdp_trunk($ID_Device,$type,$oidsModel,$oidvalues,$ArrayPort_LogicalNum_SNMPNum)
{
	global $DB;
	$snmp_queries = new plugin_tracker_snmp;
	$logs = new plugin_tracker_logs;
	$walks = new plugin_tracker_walk;
	$Threads = new Threads;
		
	$logs->write("tracker_fullsync",">>>>>>>>>> Networking : Get cdp trunk ports <<<<<<<<<<",$type."][".$ID_Device,1);

	$Array_trunk_ifIndex = array();

	if((strstr($oidvalues[".1.3.6.1.2.1.1.1.0"][""],"Cisco")) OR (strstr($oidvalues[".1.3.6.1.2.1.1.1.0"][""],"ProCurve J")))
	{
		// Get trunk port directly from oid
		$Arraytrunktype = $walks->GetoidValuesFromWalk($oidvalues,$oidsModel[0][1]['vlanTrunkPortDynamicStatus'],1);
		foreach($Arraytrunktype as $IDtmp=>$snmpportID)
		{
			if ($oidvalues[$oidsModel[0][1]['vlanTrunkPortDynamicStatus'].".".$snmpportID][""] == "1")
			{
				$Array_trunk_ifIndex[$snmpportID] = 1;
				$logs->write("tracker_fullsync","Trunk = ".$snmpportID,$type."][".$ID_Device,1);
				$trunk_no_cdp[$snmpportID] = 1;
			}
		}

		$ArrayPort_LogicalNum_SNMPNum = array_flip($ArrayPort_LogicalNum_SNMPNum);

		// Get trunk port from CDP
		// Get by SNMP query the IP addresses of the switch connected ($Array_trunk_IP_hex)
		$Array_trunk_IP_hex_result = $walks->GetoidValuesFromWalk($oidvalues,$oidsModel[0][1]['cdpCacheAddress'],1);

		// Get by SNMP query the Name of port (ifDescr) : snmp port ID => ifDescr of port of switch connected on this port
		//$Array_trunk_ifDescr_result = $walks->GetoidValuesFromWalk($oidvalues,$oidsModel[0][1]['cdpCacheDevicePort'],1);
		if (!empty($Array_trunk_IP_hex_result))
		{
			foreach($Array_trunk_IP_hex_result AS $num=>$snmpportID)
			{

				$trunk_IP = $oidvalues[$oidsModel[0][1]['cdpCacheAddress'].".".$snmpportID][""];

				// Convert IP HEX in Decimal
				if (preg_match("/^0x/",$trunk_IP))
				{
					$trunk_IP = str_replace("0x","",$trunk_IP);
					$hex_ = preg_replace("/[^0-9a-fA-F]/","", $trunk_IP);
					$trunk_IP_tmp = '';
					for($i = 0; $i < strlen($hex_); $i = $i + 2)
						$trunk_IP_tmp .= chr(hexdec(substr($hex_, $i, 2)));
					$ip_switch_trunk = ord(substr($trunk_IP_tmp, 0, 1));
					for($i = 1; $i < strlen($trunk_IP_tmp); $i = $i + 1)
						$ip_switch_trunk .= ".".ord(substr($trunk_IP_tmp, $i, 1));
				}

				$explode = explode(".", $snmpportID);
				$ifIndex = $explode[0];
				$end_Number = $explode[1];

				$Array_trunk_ifIndex[$ifIndex] = 1;
				$logs->write("tracker_fullsync","ifIndex = ".$ifIndex,$type."][".$ID_Device,1);
				$logs->write("tracker_fullsync","ifIndex num logic = ".$ArrayPort_LogicalNum_SNMPNum[$ifIndex],$type."][".$ID_Device,1);
		//		$logs->write("tracker_fullsync","ifIndex name logic = ".$ArrayPort_LogicalNum_SNMPName[$ArrayPort_LogicalNum_SNMPNum[$ifIndex]],$type."][".$ID_Device,1);

				// Search port of switch connected on this port and connect it if not connected
				$logs->write("tracker_fullsync","ip = ".$ip_switch_trunk." / ifdescr = ".$oidvalues[$oidsModel[0][1]['cdpCacheDevicePort'].".".$snmpportID][""],$type."][".$ID_Device,1);
				$PortID = $snmp_queries->getPortIDfromDeviceIP($ip_switch_trunk, $oidvalues[$oidsModel[0][1]['cdpCacheDevicePort'].".".$snmpportID][""]);

				$query = "SELECT glpi_networking_ports.ID FROM glpi_networking_ports
				WHERE logical_number='".$ArrayPort_LogicalNum_SNMPNum[$ifIndex]."'
					AND device_type='".NETWORKING_TYPE."'
					AND on_device='".$ID_Device."' ";
				$result = $DB->query($query);
				$data = $DB->fetch_assoc($result);

				if ((!empty($data["ID"])) AND (!empty($PortID)))
					$snmp_queries->PortsConnection($data["ID"], $PortID,$_SESSION['FK_process']);
				else if ((!empty($data["ID"])) AND (empty($PortID))) // Unknow IP of switch connected to this port
					$Threads->unknownMAC($_SESSION['FK_process'],$data["ID"],$ip_switch_trunk,$data["ID"]);

				unset($trunk_no_cdp[$ifIndex]);
			}
		}
		// ** Update for all ports on this network device the field 'trunk' in glpi_plugin_tracker_networking_ports
		foreach($ArrayPort_LogicalNum_SNMPNum AS $ifIndex=>$logical_num)
		{
			$query = "SELECT *,glpi_plugin_tracker_networking_ports.id AS sid  FROM glpi_networking_ports
				LEFT JOIN glpi_plugin_tracker_networking_ports
				ON glpi_plugin_tracker_networking_ports.FK_networking_ports = glpi_networking_ports.id
				WHERE device_type='2'
					AND on_device='".$ID_Device."'
					AND logical_number='".$logical_num."' ";
			$result=$DB->query($query);
			while ($data=$DB->fetch_array($result))
			{
				if ((isset($Array_trunk_ifIndex[$ifIndex])) AND ($Array_trunk_ifIndex[$ifIndex] == "1"))
				{
					if ($data['trunk'] == "0")
					{
						$query_update = "UPDATE glpi_plugin_tracker_networking_ports
						SET trunk='1'
						WHERE id='".$data['sid']."' ";
						$DB->query($query_update);
						tracker_snmp_addLog($data["FK_networking_ports"],"trunk","0","1",$_SESSION['FK_process']);
					}
				}
				else if($data['trunk'] == "1")
				{
					$query_update = "UPDATE glpi_plugin_tracker_networking_ports
					SET trunk='0'
					WHERE id='".$data['sid']."' ";
					$DB->query($query_update);
					tracker_snmp_addLog($data["FK_networking_ports"],"trunk","1","0",$_SESSION['FK_process']);
				}

			}
		}
	}
	else if(strstr($oidvalues[".1.3.6.1.2.1.1.1.0"][""],"3Com IntelliJack NJ225"))
	{
		$trunk_no_cdp["1"] = 1;
		$ArrayPort_LogicalNum_SNMPNum = array_flip($ArrayPort_LogicalNum_SNMPNum);
		$query = "SELECT *,glpi_plugin_tracker_networking_ports.id AS sid  FROM glpi_networking_ports
			LEFT JOIN glpi_plugin_tracker_networking_ports
			ON glpi_plugin_tracker_networking_ports.FK_networking_ports = glpi_networking_ports.id
			WHERE device_type='2'
				AND on_device='".$ID_Device."'
				AND logical_number='0' ";
		$result=$DB->query($query);
		while ($data=$DB->fetch_array($result))
		{
			if ($data['trunk'] == "0")
			{
				$query_update = "UPDATE glpi_plugin_tracker_networking_ports
				SET trunk='1'
				WHERE id='".$data['sid']."' ";
				$DB->query($query_update);
				tracker_snmp_addLog($data["FK_networking_ports"],"trunk","0","1",$_SESSION['FK_process']);
			}
		}
	}

	// If no Ip or mac in this trunks port, we try to search switch connected to this
	//var_dump($trunk_no_cdp);
	foreach($trunk_no_cdp AS $ifIndex=>$activation)
	{
		// list all mac addresses connected to this ports
		if(strstr($oidvalues[".1.3.6.1.2.1.1.1.0"][""],"Cisco"))
		{
			$Array_vlan = $walks->GetoidValuesFromWalk($oidvalues,$oidsModel[0][1]['vtpVlanName'],1);
			foreach ($Array_vlan as $num=>$vlan)
			{
				unset($ArrayMACAdressTable);
				$ArrayMACAdressTable = $walks->GetoidValuesFromWalk($oidvalues,$oidsModel[0][1]['dot1dTpFdbAddress'],1,$vlan);

				foreach($ArrayMACAdressTable as $num=>$dynamicdata)
				{
					$oidExplode = explode(".", $dynamicdata);
					if (((count($oidExplode) > 3)) AND (isset($oidvalues[$oidsModel[0][1]['dot1dTpFdbPort'].".".$dynamicdata][$vlan])))
					{
						$BridgePortNumber = $oidvalues[$oidsModel[0][1]['dot1dTpFdbPort'].".".$dynamicdata][$vlan];
						$BridgePortifIndex = $oidvalues[$oidsModel[0][1]['dot1dBasePortIfIndex'].".".$BridgePortNumber][$vlan];

						if (($BridgePortifIndex == $ifIndex))
						{
							$MacAddress = str_replace("0x","",$oidvalues[$oidsModel[0][1]['dot1dTpFdbAddress'].".".$dynamicdata][$vlan]);
							$MacAddress_tmp = str_split($MacAddress, 2);
							$MacAddress = $MacAddress_tmp[0];
							for($i = 1; $i < count($MacAddress_tmp); $i++)
								$MacAddress .= ":".$MacAddress_tmp[$i];

							// On vérifie si l'adresse MAC appartient à un port réseau de switch
							$query = "SELECT *  FROM glpi_networking_ports
							WHERE device_type='".NETWORKING_TYPE."'
							 AND ifmac='".$MacAddress."' ";
							$result = $DB->query($query);		
							if (mysql_num_rows($result) > 0)
							{
								$data = $DB->fetch_assoc($result);
								
								$query2 = "SELECT glpi_networking_ports.ID FROM glpi_networking_ports
								WHERE logical_number='".$ArrayPort_LogicalNum_SNMPNum[$ifIndex]."'
									AND device_type='".NETWORKING_TYPE."'
									AND on_device='".$ID_Device."' ";
								$result2 = $DB->query($query2);
								$data2 = $DB->fetch_assoc($result2);

								$snmp_queries->PortsConnection($data["ID"], $data2["ID"],$_SESSION['FK_process']);
							}
						}
					}

				}

			}
		}
		else if(strstr($oidvalues[".1.3.6.1.2.1.1.1.0"][""],"3Com IntelliJack NJ225"))
		{
			// =-=-=-=-=-=-= Don't work =-=-=-=-=-=-= \\
//			unset($ArrayMACAdressTable);
//			$ArrayMACAdressTable = $walks->GetoidValuesFromWalk($oidvalues,$oidsModel[0][1]['dot1dTpFdbAddress'],1);
//			foreach($ArrayMACAdressTable as $num=>$dynamicdata)
//			{
//
//				$oidExplode = explode(".", $dynamicdata);
//				if (((count($oidExplode) > 3)) AND (isset($oidvalues[$oidsModel[0][1]['dot1dTpFdbPort'].".".$dynamicdata][""])))
//				{
//					$BridgePortifIndex = $oidvalues[$oidsModel[0][1]['dot1dTpFdbPort'].".".$dynamicdata][""];
//
//					if (($BridgePortifIndex == "1"))
//					{
//						$MacAddress = str_replace("0x","",$oidvalues[$oidsModel[0][1]['dot1dTpFdbAddress'].".".$dynamicdata][""]);
//						$MacAddress_tmp = str_split($MacAddress, 2);
//						$MacAddress = $MacAddress_tmp[0];
//						for($i = 1; $i < count($MacAddress_tmp); $i++)
//							$MacAddress .= ":".$MacAddress_tmp[$i];
//
//						// On vérifie si l'adresse MAC appartient à un port réseau de switch
//						$query = "SELECT *  FROM glpi_networking_ports
//						WHERE device_type='".NETWORKING_TYPE."'
//						 AND ifmac='".$MacAddress."' ";
//						$result = $DB->query($query);
//						if (mysql_num_rows($result) > 0)
//						{
//							$data = $DB->fetch_assoc($result);
//
//							$query2 = "SELECT glpi_networking_ports.ID FROM glpi_networking_ports
//							WHERE logical_number='".$ArrayPort_LogicalNum_SNMPNum["1"]."'
//								AND device_type='".NETWORKING_TYPE."'
//								AND on_device='".$ID_Device."' ";
//
//							$result2 = $DB->query($query2);
//							$data2 = $DB->fetch_assoc($result2);
//
//							$snmp_queries->PortsConnection($data["ID"], $data2["ID"],$_SESSION['FK_process']);
//						}
//					}
//				}
//
//			}
		}
	}

return $Array_trunk_ifIndex;
}

function plugin_tracker_hex_to_string($value)
{
	if (strstr($value, "0x0115"))
	{
		$hex = str_replace("0x0115","",$value);
		$string='';
		for ($i=0; $i < strlen($hex)-1; $i+=2)
			$string .= chr(hexdec($hex[$i].$hex[$i+1]));
		$value = $string;
	}
	if (strstr($value, "0x"))
	{
		$hex = str_replace("0x","",$value);
		$string='';
		for ($i=0; $i < strlen($hex)-1; $i+=2)
			$string .= chr(hexdec($hex[$i].$hex[$i+1]));
		$value = $string;
	}
	return $value;
}



function plugin_tracker_snmp_networking_ifaddr($ID_Device,$type,$oidsModel,$oidvalues)
{
	global $DB;

	$walks = new plugin_tracker_walk;
	$logs = new plugin_tracker_logs;

	$logs->write("tracker_fullsync",">>>>>>>>>> List of IP addresses of device <<<<<<<<<<",$type."][".$ID_Device,1);

	$ifaddr_add = array();
	$ifaddr = array();

	$query = "SELECT * FROM glpi_plugin_tracker_networking_ifaddr
	WHERE FK_networking='".$ID_Device."' ";
	if ( $result=$DB->query($query) )
	{
		while ( $data=$DB->fetch_array($result) )
			$ifaddr[$data["ifaddr"]] = $data["FK_networking"];
	}

	$ifaddr_switch = $walks->GetoidValuesFromWalk($oidvalues,$oidsModel[0][1]['ipAdEntAddr']);

	foreach($ifaddr as $ifIP=>$FK_networking)
	{
		foreach($ifaddr_switch as $num_switch=>$ifIP_switch)
		{
			if ($ifIP == $ifIP_switch)
			{
				unset ($ifaddr[$ifIP]);
				unset ($ifaddr_switch[$num_switch]);
			}
		}
	}

	foreach($ifaddr as $ifaddr_snmp=>$FK_networking)
	{
		$query_delete = "DELETE FROM glpi_plugin_tracker_networking_ifaddr
		WHERE FK_networking='".$ID_Device."'
			AND ifaddr='".$ifaddr_snmp."' ";
		$DB->query($query_delete);
	}
	foreach($ifaddr_switch as $num_snmp=>$ifaddr_snmp)
	{
		$query_insert = "INSERT INTO glpi_plugin_tracker_networking_ifaddr
		(FK_networking,ifaddr)
		VALUES('".$ID_Device."','".$ifaddr_snmp."') ";
		$DB->query($query_insert);
	}
}
?>