<?php

define('PHPUnit_MAIN_METHOD', 'Plugins_Fusioninventory_InventorySNMP::main');
    
if (!defined('GLPI_ROOT')) {
   define('GLPI_ROOT', '../../../..');

   require_once GLPI_ROOT."/inc/includes.php";
   $_SESSION['glpi_use_mode'] = 2;
   $_SESSION['glpiactiveprofile']['id'] = 4;

   ini_set('display_errors','On');
   error_reporting(E_ALL | E_STRICT);
   set_error_handler("userErrorHandler");

   // Backup present DB
   include_once("inc/backup.php");
   backupMySQL();

   $_SESSION["glpilanguage"] = 'fr_FR';

   // Install
   include_once("inc/installation.php");
   installGLPI();
   installFusionPlugins();

   loadLanguage();
   include_once(GLPI_ROOT."/locales/fr_FR.php");
   include_once(GLPI_ROOT."/plugins/fusinvsnmp/locales/fr_FR.php");
   $CFG_GLPI["root_doc"] = GLPI_ROOT;
}
include_once('emulatoragent.php');

/**
 * Test class for MyFile.
 * Generated by PHPUnit on 2010-08-06 at 12:05:09.
 */
class Plugins_Fusioninventory_InventorySNMP extends PHPUnit_Framework_TestCase {

    public static function main() {
        require_once 'PHPUnit/TextUI/TestRunner.php';

        $suite  = new PHPUnit_Framework_TestSuite('Plugins_Fusioninventory_InventorySNMP');
        $result = PHPUnit_TextUI_TestRunner::run($suite);

    }

   public function testSetModuleInventoryOff() {
      global $DB;

      // Create rule
      $rulecollection = new PluginFusinvsnmpRuleInventoryCollection();
      $input = array();
      $input['is_active']=1;
      $input['name']='serial';
      $input['match']='AND';
      $input['sub_type'] = 'PluginFusinvsnmpRuleInventory';
      $rule_id = $rulecollection->add($input);

         // Add criteria
         $rule = $rulecollection->getRuleClass();
         $rulecriteria = new RuleCriteria(get_class($rule));
         $input = array();
         $input['rules_id'] = $rule_id;
         $input['criteria'] = "globalcriteria";
         $input['pattern']= 1;
         $input['condition']=0;
         $rulecriteria->add($input);

         // Add action
         $ruleaction = new RuleAction(get_class($rule));
         $input = array();
         $input['rules_id'] = $rule_id;
         $input['action_type'] = 'assign';
         $input['field'] = '_import';
         $input['value'] = '1';
         $ruleaction->add($input);

     // set in config module inventory = yes by default
     $query = "UPDATE `glpi_plugin_fusioninventory_agentmodules`
        SET `is_active`='0'
        WHERE `modulename`='SNMPQUERY' ";
     $result = $DB->query($query);

   }



   public function testSetModuleInventoryOn() {
      global $DB;

      $query = "UPDATE `glpi_plugin_fusioninventory_agentmodules`
         SET `is_active`='1'
         WHERE `modulename`='SNMPQUERY' ";
      $DB->query($query);

   }



    public function testSendinventories() {
       
      $MyDirectory = opendir("xml/inventory_snmp");
      while(false !== ($Entry = readdir($MyDirectory))) {
         if(is_dir('xml/inventory_snmp/'.$Entry)&& $Entry != '.' && $Entry != '..') {
            $myVersion = opendir("xml/inventory_snmp/".$Entry);
            while(false !== ($xmlFilename = readdir($myVersion))) {
               if ($xmlFilename != '.' && $xmlFilename != '..') {

                  // We have the XML of each computer inventory
                  $xml = simplexml_load_file("xml/inventory_snmp/".$Entry."/".$xmlFilename,'SimpleXMLElement', LIBXML_NOCDATA);

                  // Send all of xml
                  $this->testSendinventory("xml/inventory_snmp/".$Entry."/".$xmlFilename);
                  foreach ($xml->CONTENT->DEVICE as $child) {
                     // Get device information in GLPI and items_id
                     $array = $this->testGetGLPIDevice("xml/inventory_snmp/".$Entry."/".$xmlFilename, $child);
                     $items_id = $array[0];
                     $itemtype = $array[1];
                     $unknown  = $array[2];
                     // test Infos
                     $this->testInfo($child, "xml/inventory_snmp/".$Entry."/".$xmlFilename, $items_id, $itemtype, $unknown);

                     $this->testIPs($child, "xml/inventory_snmp/".$Entry."/".$xmlFilename,$items_id,$itemtype);

                     $this->testPorts($child, "xml/inventory_snmp/".$Entry."/".$xmlFilename,$items_id,$itemtype);

                     $this->testPortsinfo($child, "xml/inventory_snmp/".$Entry."/".$xmlFilename,$items_id,$itemtype);

                     $this->testPortsVlan($child, "xml/inventory_snmp/".$Entry."/".$xmlFilename,$items_id,$itemtype);

                     $this->testPortsConnections($child, "xml/inventory_snmp/".$Entry."/".$xmlFilename,$items_id,$itemtype);

                  }
               }
            }
         }
      }
    }


   function testAddNetworkEquipmentCDP() {
      // Add a networkequipment which are already created but in unknwon device
      global $DB;

      $PluginFusioninventoryUnknownDevice = new PluginFusioninventoryUnknownDevice();
      $NetworkPort = new NetworkPort();
      $a_networkport = $NetworkPort->find("`itemtype`='PluginFusioninventoryUnknownDevice'
         AND `name` like 'GigabitEthernet%'", 'id', '1');
      foreach($a_networkport as $datas) {
         
      }
      $PluginFusioninventoryUnknownDevice->getFromDB($datas['items_id']);
      $xml = new SimpleXMLElement("<?xml version='1.0' encoding='UTF-8'?><REQUEST></REQUEST>");
      $xml->addChild('DEVICEID', 'testCDP.toto.local');
      $xml->addChild('QUERY', 'SNMPQUERY');
      $xml_content = $xml->addChild('CONTENT');
      $xml_device = $xml_content->addChild('DEVICE');
      $xml_info = $xml_device->addChild('INFO');
      $xml_info->addChild('NAME', 'testCDP');
      $xml_info->addChild('SERIAL', 'GTFD6IYJHGTFTY7');
      $xml_info->addChild('TYPE', 'NETWORKING');
      $xml_ips = $xml_info->addChild('IPS');
      $xml_ips->addChild('IP', $datas['ip']);

      $xml_ports = $xml_device->addChild('PORTS');

      $xml_port = $xml_ports->addChild('PORT');
      $xml_port->addChild('IFDESCR', 'GigabitEthernet45/1');
      $xml_port->addChild('IFTYPE', '6');
      $xml_port->addChild('IFNAME', 'GigabitEthernet45/1');
      $xml_port->addChild('IFSTATUS', '1');
      $xml_port->addChild('IFNUMBER', '9');
      $xml_port->addChild('IFINTERNALSTATUS', '1');

      $xml_port = $xml_ports->addChild('PORT');
      $xml_port->addChild('IFDESCR', $datas['name']);
      $xml_port->addChild('IFTYPE', '6');
      $xml_port->addChild('IFNAME', $datas['name']);
      $xml_port->addChild('IFSTATUS', '1');
      $xml_port->addChild('IFNUMBER', '10');
      $xml_port->addChild('IFINTERNALSTATUS', '1');

      $xml_port = $xml_ports->addChild('PORT');
      $xml_port->addChild('IFDESCR', 'GigabitEthernet10/54');
      $xml_port->addChild('IFTYPE', '6');
      $xml_port->addChild('IFNAME', 'GigabitEthernet10/54');
      $xml_port->addChild('IFSTATUS', '1');
      $xml_port->addChild('IFNUMBER', '11');
      $xml_port->addChild('IFINTERNALSTATUS', '1');


      $this->testSendinventory('test', $xml);

      $array = $this->testGetGLPIDevice("networkequipment-testcdp.xml", $xml_device);
      $items_id = $array[0];
      $itemtype = $array[1];
      $unknown  = $array[2];

      $a_unknown = $PluginFusioninventoryUnknownDevice->find("`id` = '".$datas['items_id']."'");

      $this->assertEquals(count($a_unknown), 0, 'Switch has been added in GLPI but unknown device with CDP yet added is not fusionned with switch');

      // Test if port is moved from unknown device to switch
      $NetworkPort->getFromDB($datas['id']);
      $this->assertEquals($NetworkPort->fields['itemtype'], 'NetworkEquipment', 'Port has not been transfered from unknown device to switch port');
      // Test if extension of port informations have been right created
      $query = "SELECT * FROM `glpi_plugin_fusinvsnmp_networkports`
         WHERE `networkports_id`='".$datas['id']."'";
      $result = $DB->query($query);
      $this->assertEquals($DB->numrows($result), 1, 'Port extension has not been created');

      // Test if port connected on unknown device is connected on switch port

      
   }
                  

      function testInfo($xml='', $xmlFile='', $items_id=0, $itemtype='', $unknown=0) {

         if (empty($xmlFile)) {
            echo "testInfo with no arguments...\n";
            return;
         }
         $class = new $itemtype;
         $class->getFromDB($items_id);

         foreach ($xml->INFO as $child2) {
            $this->assertEquals($class->fields['name'], (string)$child2->NAME , 'Difference of Hardware name, have '.$class->fields['name'].' instead '.$child2->NAME.' ['.$xmlFile.']');
            $this->assertEquals($class->fields['serial'], (string)$child2->SERIAL , 'Difference of Hardware serial, have '.$class->fields['serial'].' instead '.$child2->SERIAL.' ['.$xmlFile.']');

            if ($child2->TYPE == 'PRINTER') {
               if (isset($child2->MODEL)) {
                  $PrinterModel = new PrinterModel();
                  $this->assertEquals($class->fields['printermodels_id'], $PrinterModel->import(array('name'=>(string)$child2->MODEL)) , 'Difference of Hardware model, have '.$class->fields['printermodels_id'].' instead '.$PrinterModel->import(array('name'=>$child2->MODEL)).' ['.$xmlFile.']');
               }
               if (isset($child2->MANUFACTURER)) {
                  $Manufacturer = new Manufacturer();
                  $this->assertEquals($class->fields['manufacturers_id'], $Manufacturer->import(array('name'=>(string)$child2->MANUFACTURER)) , 'Difference of Hardware manufacturer, have '.$class->fields['manufacturers_id'].' instead '.$Manufacturer->import(array('name'=>$child2->MANUFACTURER)).' ['.$xmlFile.']');
               }
               $this->assertEquals($class->fields['memory_size'], (string)$child2->MEMORY , 'Difference of Hardware memory size, have '.$class->fields['memory_size'].' instead '.$child2->MEMORY.' ['.$xmlFile.']');
            } else if ($child2->TYPE == 'NETWORKING') {
               $this->assertEquals($class->fields['ram'], (string)$child2->RAM , 'Difference of Hardware ram size, have '.$class->fields['ram'].' instead '.$child2->RAM.' ['.$xmlFile.']');
            }
            if (isset($child2->LOCATION)) {
               $Location = new Location();
               $this->assertEquals($class->fields['locations_id'], $Location->import(array('name' => (string)$child2->LOCATION, 'entities_id' => '0')) , 'Difference of Hardware location, have '.$class->fields['locations_id'].' instead '.$Location->import(array('name' => $child2->LOCATION)).' ['.$xmlFile.']');
            }
            /*
 *         <COMMENTS>Xerox WorkCentre M20i ; OS 1.22   Engine 4.1.08 NIC V2.22(M20i) DADF 1.04</COMMENTS>
 */
         }


//      foreach ($xml->CONTENT->DEVICE[0]->INFO as $child) {
//         $this->assertEquals($data['name'], $child->NAME , 'Difference of Hardware name, have '.$data['name'].' instead '.$child->NAME.' []');
//         $this->assertEquals($data['ip'], '192.168.0.80' , 'Problem on update ip of switch');
//         $this->assertEquals($data['mac'], $child->MAC , 'Problem on update mac of switch');
//         $this->assertEquals($data['ram'], $child->RAM , 'Problem on update ram of switch');
//         $this->assertEquals(Dropdown::getDropdownName('glpi_networkequipmentmodels',
//                              $data['networkequipmentmodels_id']), $child->MODEL , 'Problem on update model of switch');
//         $this->assertEquals(Dropdown::getDropdownName('glpi_networkequipmentfirmwares',
//                              $data['networkequipmentfirmwares_id']), $child->FIRMWARE , 'Problem on update firmware of switch');
//         $this->assertEquals(Dropdown::getDropdownName('glpi_locations',
//                              $data['locations_id']), $child->LOCATION , 'Problem on update location of switch');
//
//         $this->assertEquals($data['comment'], '' , 'Comment must be empty');
//
//
//         $fusinvsnmp_networkequipments = new PluginFusinvsnmpCommonDBTM("glpi_plugin_fusinvsnmp_networkequipments");
//         $a_snmpswitch = $fusinvsnmp_networkequipments->find("`networkequipments_id`='".$data['id']."' ");
//         $this->assertEquals(count($a_snmpswitch), 1 , 'Extension of switch informations are missing');
//         foreach($a_snmpswitch as $idsnmp=>$datasnmp){
//            $this->assertEquals($datasnmp['sysdescr'], $child->COMMENTS);
//            $this->assertEquals($datasnmp['memory'], $child->MEMORY , 'Problem on update memory of switch');
//            $this->assertEquals($datasnmp['uptime'], $child->UPTIME , 'Problem on update uptime of switch');
//         }
      }



   function testSendinventory($xmlFile='', $xml='') {

      if (empty($xmlFile)) {
         echo "testSendinventory with no arguments...\n";
         return;
      }

      $emulatorAgent = new emulatorAgent;
      $emulatorAgent->server_urlpath = "/glpi078/plugins/fusioninventory/front/communication.php";
      if (empty($xml)) {
         $xml = simplexml_load_file($xmlFile,'SimpleXMLElement', LIBXML_NOCDATA);
      }
      
      // Send prolog for creation of agent in GLPI
      $input_xml = '<?xml version="1.0" encoding="UTF-8"?>
<REQUEST>
  <DEVICEID>'.$xml->DEVICEID.'</DEVICEID>
  <QUERY>PROLOG</QUERY>
  <TOKEN>CBXTMXLU</TOKEN>
</REQUEST>';
      $emulatorAgent->sendProlog($input_xml);

      foreach ($xml->CONTENT->DEVICE as $child) {
         foreach ($child->INFO as $child2) {
            if ($child2->TYPE == 'PRINTER') {
               // Create switch in asset
               $Printer = new Printer();
               $input = array();
               $input['serial']=$child2->SERIAL;
               $Printer->add($input);
            } else if ($child2->TYPE == 'NETWORKING') {
               // Create switch in asset
               $NetworkEquipment = new NetworkEquipment();
               $input = array();
               $input['serial']=$child2->SERIAL;
               $NetworkEquipment->add($input);
            }
         }
      }
      $input_xml = $xml->asXML();
      $emulatorAgent->sendProlog($input_xml);

   }


   function testGetGLPIDevice($xmlFile='', $xml='') {
      
      if (empty($xmlFile)) {
         echo "testGetGLPIDevice with no arguments...\n";
         return;
      }

      $input = array();
      if ((string)$xml->INFO->TYPE == 'PRINTER') {
         $input['serial']=(string)$xml->INFO->SERIAL;
         $name = (string)$xml->INFO->NAME;
      } else if ((string)$xml->INFO->TYPE == 'NETWORKING') {
         $input['serial']=(string)$xml->INFO->SERIAL;
         $name = (string)$xml->INFO->NAME;
      }
 
      $serial = "`serial` IS NULL";

      if ((isset($input['serial'])) && (!empty($input["serial"]))) {
         $serial = "`serial`='".$input['serial']."'";
      }
      
      $itemtype = '';
      $a_devices = array();
      if (strstr($xmlFile, 'printer')) {
         $itemtype = 'printer';
         $Printer = new Printer();
         $a_devices = $Printer->find("`name`='".$name."' AND ".$serial);
      } else if (strstr($xmlFile, 'networkequipment')) {
         $itemtype = 'networkequipment';
         $NetworkEquipment = new NetworkEquipment();
         $a_devices = $NetworkEquipment->find("`name`='".$name."' AND ".$serial);
      }
      $unknown = 0;
      if (count($a_devices) == 0) {
         // Search in unknown device
         $PluginFusioninventoryUnknownDevice = new PluginFusioninventoryUnknownDevice();
         $a_devices = $PluginFusioninventoryUnknownDevice->find("`name`='".$name."'");
         $unknown = 1;
      }
      $this->assertEquals(count($a_devices), 1 , 'Problem on creation device, not created ('.$xmlFile.')');
      foreach($a_devices as $items_id => $data) {
         return array($items_id, $itemtype, $unknown);
      }
   }


   function testIPs($xml='', $xmlFile='',$items_id=0,$itemtype='') {

      if (empty($xmlFile)) {
         echo "testIPs with no arguments...\n";
         return;
      }

      if ($itemtype != 'networkequipment') {
         echo "testIPs with itemtype not networkequipment...\n";
         return;
      }

      $count_ips = 0;
      foreach ($xml->INFO->IPS->IP as $child) {
         if ($child != "127.0.0.1") {
            $count_ips++;
         }
      }

      $PluginFusinvsnmpNetworkEquipmentIP = new PluginFusinvsnmpNetworkEquipmentIP();
      $a_ips = $PluginFusinvsnmpNetworkEquipmentIP->find("`networkequipments_id`='".$items_id."'");
      $this->assertEquals(count($a_ips), $count_ips , 'Problem on manage IPs of the switch, '.count($a_ips).' instead '.$count_ips.' ['.$xmlFile.']');
   }


   public function testPorts($xml='', $xmlFile='',$items_id=0,$itemtype='') {

      if (empty($xmlFile)) {
         echo "testPorts with no arguments...\n";
         return;
      }

      $NetworkPort = new NetworkPort();
      $PluginFusinvsnmpNetworkPort = new PluginFusinvsnmpNetworkPort($itemtype);
      $count_ports = 0;
      foreach ($xml->PORTS->PORT as $child) {
         if ($PluginFusinvsnmpNetworkPort->isReal($child->IFTYPE)) {
            $count_ports++;
         }
      }
      $a_ports = $NetworkPort->find("`itemtype`='".$itemtype."' AND `items_id`='".$items_id."'");

      $this->assertEquals(count($a_ports), $count_ports , 'Problem on creation of ports, '.count($a_ports).' instead '.$count_ports.' ['.$xmlFile.']');
   }


   public function testPortsinfo($xml='', $xmlFile='',$items_id=0,$itemtype='') {

      if (empty($xmlFile)) {
         echo "testPorts with no arguments...\n";
         return;
      }

      $NetworkPort = new NetworkPort();
      $PluginFusinvsnmpNetworkPort = new PluginFusinvsnmpNetworkPort();

      if ((string)$xml->INFO->TYPE == 'NETWORKING') {
         foreach ($xml->PORTS->children() as $name=>$child) {
            if ((string)$child->IFTYPE == '6') {
               $a_ports = array();
               if (isset($child->IFNAME)) {
                  $a_ports = $NetworkPort->find("`itemtype`='".$itemtype."' AND `items_id`='".$items_id."'
                                             AND `name`='".(string)$child->IFNAME."'");
               } else {
                  $a_ports = $NetworkPort->find("`itemtype`='".$itemtype."' AND `items_id`='".$items_id."'
                                             AND `name`='".(string)$child->IFDESCR."'");
               }
               $data = array();
               foreach ($a_ports as $id => $data) {

               }
               $oFusioninventory_networkport = new PluginFusinvsnmpCommonDBTM("glpi_plugin_fusinvsnmp_networkports");
               $a_portsExt = $oFusioninventory_networkport->find("`networkports_id`='".$id."'");
               $dataExt = array();
               foreach ($a_portsExt as $idExt => $dataExt) {

               }
               if (isset($child->IFNAME)) {
                  $this->assertEquals($data['name'], (string)$child->IFNAME , 'Name of port not good ("'.$data['name'].'" instead of "'.(string)$child->IFNAME.'")['.$xmlFile.']');
               } else {
                  $this->assertEquals($data['name'], (string)$child->IFDESCR , 'Name of port not good ("'.$data['name'].'" instead of "'.(string)$child->IFDESCR.'")['.$xmlFile.']');
               }
               if (!strstr((string)$child->MAC, '00:00:00')) {
                  $this->assertEquals($data['mac'], (string)$child->MAC , 'Mac of port not good ("'.$data['mac'].'" instead of "'.(string)$child->MAC.'")['.$xmlFile.']');
               }
               $this->assertEquals($data['logical_number'], (string)$child->IFNUMBER , 'Number of port not good ("'.$data['logical_number'].'" instead of "'.(string)$child->IFNUMBER.'")['.$xmlFile.']');
               if (isset($child->IFDESCR)) {
                  $this->assertEquals($dataExt['ifdescr'], (string)$child->IFDESCR , 'Description of port not good ("'.$dataExt['ifdescr'].'" instead of "'.(string)$child->IFDESCR.'")['.$xmlFile.']');
               }
               if (isset($child->IFMTU)) {
                  $this->assertEquals($dataExt['ifmtu'], (string)$child->IFMTU , 'MTU of port not good ("'.$dataExt['ifmtu'].'" instead of "'.(string)$child->IFMTU.'")['.$xmlFile.']');
               }
               if (isset($child->IFSPEED)) {
                  $this->assertEquals($dataExt['ifspeed'], (string)$child->IFSPEED , 'Speed of port not good ("'.$dataExt['ifspeed'].'" instead of "'.(string)$child->IFSPEED.'")['.$xmlFile.']');
               }
               if (isset($child->IFINTERNALSTATUS)) {
                  $this->assertEquals($dataExt['ifinternalstatus'], (string)$child->IFINTERNALSTATUS , 'Internal status of port not good ("'.$dataExt['ifinternalstatus'].'" instead of "'.(string)$child->IFINTERNALSTATUS.'")['.$xmlFile.']');
               }
               if (isset($child->IFLASTCHANGE)) {
                  $this->assertEquals($dataExt['iflastchange'], (string)$child->IFLASTCHANGE , 'Last change of port not good ("'.$dataExt['iflastchange'].'" instead of "'.(string)$child->IFLASTCHANGE.'")['.$xmlFile.']');
               }
               if (isset($child->IFINOCTETS)) {
                  $this->assertEquals($dataExt['ifinoctets'], (string)$child->IFINOCTETS , 'In octets of port not good ("'.$dataExt['ifinoctets'].'" instead of "'.(string)$child->IFINOCTETS.'")['.$xmlFile.']');
               }
               if (isset($child->IFINERRORS)) {
                  $this->assertEquals($dataExt['ifinerrors'], (string)$child->IFINERRORS , 'In errors of port not good ("'.$dataExt['ifinerrors'].'" instead of "'.(string)$child->IFINERRORS.'")['.$xmlFile.']');
               }
               if (isset($child->IFOUTOCTETS)) {
                  $this->assertEquals($dataExt['ifoutoctets'], (string)$child->IFOUTOCTETS , 'Out octets of port not good ("'.$dataExt['ifoutoctets'].'" instead of "'.(string)$child->IFOUTOCTETS.'")['.$xmlFile.']');
               }
               if (isset($child->IFOUTERRORS)) {
                  $this->assertEquals($dataExt['ifouterrors'], (string)$child->IFOUTERRORS , 'out errors of port not good ("'.$dataExt['ifouterrors'].'" instead of "'.(string)$child->IFOUTERRORS.'")['.$xmlFile.']');
               }
               if (isset($child->IFSTATUS)) {
                  $this->assertEquals($dataExt['ifstatus'], (string)$child->IFSTATUS , 'Status of port not good ("'.$dataExt['ifstatus'].'" instead of "'.(string)$child->IFSTATUS.'")['.$xmlFile.']');
               }
            }
         }
      }

   }

   function testPortsVlan($xml='', $xmlFile='',$items_id=0,$itemtype='') {

      if (empty($xmlFile)) {
         echo "testPortsVlan with no arguments...\n";
         return;
      }


      $NetworkPort = new NetworkPort();
      $PluginFusinvsnmpNetworkPort = new PluginFusinvsnmpNetworkPort();


      foreach ($xml->PORTS->children() as $name=>$child) {
         if ((string)$child->IFTYPE == '6') {

            $a_ports = $NetworkPort->find("`itemtype`='".$itemtype."' AND `items_id`='".$items_id."'
                                          AND `name`='".(string)$child->IFNAME."'");
            $data = array();
            foreach ($a_ports as $id => $data) {
            }

            $vlanDB = NetworkPort_Vlan::getVlansForNetworkPort($id);
            $vlanDB_Name_Comment = array();
            foreach ($vlanDB as $vlans_id=>$datas) {
               $temp = Dropdown::getDropdownName('glpi_vlans', $vlans_id, 1);
               $vlanDB_Name_Comment[$temp['name']."-".$temp['comment']] = 1;
            }
            $nb_errors = 0;
            $forgotvlan = '';
            if (isset($child->VLANS)) {
               foreach ($child->VLANS->children() as $namevlan => $childvlan) {
                  if (!isset($vlanDB_Name_Comment[strval($childvlan->NUMBER)."-".strval($childvlan->NAME)])) {
                     $nb_errors++;
                     $forgotvlan .= strval($childvlan->NUMBER)."-".strval($childvlan->NAME)." | ";
                  } else {
                     unset($vlanDB_Name_Comment[strval($childvlan->NUMBER)."-".strval($childvlan->NAME)]);
                  }
               }
            }
            $this->assertEquals($forgotvlan, '' , 'Vlans not in DB ("'.$forgotvlan.'")['.$xmlFile.']');
            $this->assertEquals(count($vlanDB_Name_Comment), 0 , 'Vlans in DB but not in the XML ("'.print_r($vlanDB_Name_Comment, true).'")['.$xmlFile.']');
         }
      }
   }


   public function testPortsConnections($xml='', $xmlFile='',$items_id=0,$itemtype='') {

      if (empty($xmlFile)) {
         echo "testPortsConnections with no arguments...\n";
         return;
      }

      $NetworkPort = new NetworkPort();
      $PluginFusinvsnmpNetworkPort = new PluginFusinvsnmpNetworkPort();
      $NetworkPort_NetworkPort = new NetworkPort_NetworkPort();

      foreach ($xml->PORTS->children() as $name=>$child) {
         if ((string)$child->IFTYPE == '6') {

            $a_ports = $NetworkPort->find("`itemtype`='".$itemtype."' AND `items_id`='".$items_id."'
                                          AND `name`='".(string)$child->IFNAME."'");
            $data = array();
            foreach ($a_ports as $id => $data) {
            }

            if (isset($child->CONNECTIONS)) {
               foreach ($child->CONNECTIONS->children() as $nameconnect => $childconnect) {
                  if (isset($child->CONNECTIONS->CDP)) { // Manage CDP

                     


                  } else { // Manage tradictionnal connections
                     // Search in DB if MAC exist

                     $a_port = $NetworkPort->find("`mac`='".strval($childconnect->MAC)."'
                                                   AND `itemtype`='PluginFusioninventoryUnknownDevice' ");
                     $this->assertEquals(count($a_port), 1 , 'Port (connection) not good created, '.count($a_port).' instead of 1 port ('.strval($childconnect->MAC).' (test on mac : '.$childconnect->MAC.' on portname '.$child->IFNAME.')['.$xmlFile.']');
                     foreach($a_port as $ports_id => $datas) {
                     }
                     if (count($child->CONNECTIONS->CONNECTION->children()) > 1) {
                        // Hub management
                        $hubLink_id = $NetworkPort_NetworkPort->getOppositeContact($ports_id);
                        $NetworkPort->getFromDB($hubLink_id);
                        $a_portHub = $NetworkPort->find("`items_id`='".$NetworkPort->fields['items_id']."'
                                                   AND `itemtype`='PluginFusioninventoryUnknownDevice' ");
                        $this->assertEquals(count($a_portHub), count($child->CONNECTIONS->CONNECTION->children()) + 1 , 'Number of ports on hub not correct, '.count($a_portHub).' instead of '.(count($child->CONNECTIONS->CONNECTION->children()) + 1).' port (hub id : '.$NetworkPort->fields['items_id'].') ['.$xmlFile.']');

                     } else {
                        
                        $this->assertTrue($NetworkPort_NetworkPort->getFromDBForNetworkPort($ports_id) , 'Unknown port connection not connected with an other device['.$xmlFile.']');

                     }
                  }
               }
            }
         }
      }
   }

   

}

// Call Plugins_Fusioninventory_Discovery_Newdevices::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'Plugins_Fusioninventory_InventorySNMP::main') {
    Plugins_Fusioninventory_InventorySNMP::main();

}

//restoreMySQL();
//unlink('backup/backup.sql');
?>