<?php

define('PHPUnit_MAIN_METHOD', 'Plugins_Fusioninventory_InventorySNMPHub::main');

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
class Plugins_Fusioninventory_InventorySNMPHub extends PHPUnit_Framework_TestCase {

    public static function main() {
        require_once 'PHPUnit/TextUI/TestRunner.php';

        $suite  = new PHPUnit_Framework_TestSuite('Plugins_Fusioninventory_InventorySNMPHub');
        $result = PHPUnit_TextUI_TestRunner::run($suite);

    }

   public function testSetModuleInventoryOff() {
      global $DB;

     // set in config module inventory = yes by default
     $query = "UPDATE `glpi_plugin_fusioninventory_agentmodules`
        SET `is_active`='0'
        WHERE `modulename`='SNMPQUERY' ";
     $DB->query($query);

   }



   public function testSetModuleInventoryOn() {
      $DB = new DB();

      $query = "UPDATE `glpi_plugin_fusioninventory_agentmodules`
         SET `is_active`='1'
         WHERE `modulename`='SNMPQUERY' ";
      $DB->query($query);

   }


   public function testSendinventories() {
      // Add task and taskjob
      $pluginFusioninventoryTask = new PluginFusioninventoryTask();
      $pluginFusioninventoryTaskjob = new PluginFusioninventoryTaskjob();
      $pluginFusioninventoryTaskjobstatus = new PluginFusioninventoryTaskjobstatus();

      $input = array();
      $input['entities_id'] = '0';
      $input['name'] = 'snmpquery';
      $tasks_id = $pluginFusioninventoryTask->add($input);

      $input = array();
      $input['plugin_fusioninventory_tasks_id'] = $tasks_id;
      $input['method'] = 'snmpquery';
      $input['status'] = 1;
      $taskjobs_id = $pluginFusioninventoryTaskjob->add($input);

      $input = array();
      $input['plugin_fusioninventory_taskjobs_id'] = $taskjobs_id;
      $input['itemtype'] = 'NetworkEquipment';
      $input['items_id'] = '1';
      $input['state'] = 1;
      $input['plugin_fusioninventory_agents_id'] = 1;
      $pluginFusioninventoryTaskjobstatus->add($input);
      $input['items_id'] = '2';
      $pluginFusioninventoryTaskjobstatus->add($input);

      $switch1 = '<?xml version="1.0" encoding="UTF-8"?>
<REQUEST>
  <CONTENT>
    <DEVICE>
      <INFO>
        <COMMENTS>Cisco IOS Software, C2960 Software (C2960-LANBASEK9-M), Version 12.2(52)SE, RELEASE SOFTWARE (fc3)
Copyright (c) 1986-2009 by Cisco Systems, Inc.
Compiled Fri 25-Sep-09 08:49 by sasyamal</COMMENTS>
        <CPU>11</CPU>
        <ID>1</ID>
        <IPS>
          <IP>192.168.20.80</IP>
        </IPS>
        <MAC>00:1a:6c:9a:fc:80</MAC>
        <NAME>switch2960-001</NAME>
        <SERIAL>FOC1757ZFMY</SERIAL>
        <TYPE>NETWORKING</TYPE>
      </INFO>
      <PORTS>
        <PORT>
          <CONNECTIONS>
            <CONNECTION>
              <MAC>00:23:18:cf:0d:93</MAC>
            </CONNECTION>
            <CONNECTION>
              <MAC>f0:ad:4e:00:19:f7</MAC>
            </CONNECTION>
            <CONNECTION>
              <MAC>f0:ad:4e:10:39:f9</MAC>
            </CONNECTION>
          </CONNECTIONS>
          <IFDESCR>FastEthernet0/1</IFDESCR>
          <IFNAME>Fa0/1</IFNAME>
          <IFNUMBER>10001</IFNUMBER>
          <IFSTATUS>1</IFSTATUS>
          <IFTYPE>6</IFTYPE>
          <MAC>00:1a:6c:9a:fc:85</MAC>
          <TRUNK>0</TRUNK>
          <VLANS>
            <VLAN>
              <NAME>VLAN0020</NAME>
              <NUMBER>20</NUMBER>
            </VLAN>
          </VLANS>
        </PORT>
      </PORTS>
    </DEVICE>
    <MODULEVERSION>1.3</MODULEVERSION>
    <PROCESSNUMBER>1</PROCESSNUMBER>
  </CONTENT>
  <DEVICEID>port004.bureau.siprossii.com-2010-12-30-12-24-14</DEVICEID>
  <QUERY>SNMPQUERY</QUERY>
</REQUEST>';

      $switch2 = '<?xml version="1.0" encoding="UTF-8"?>
<REQUEST>
  <CONTENT>
    <DEVICE>
      <INFO>
        <COMMENTS>Cisco IOS Software, C2960 Software (C2960-LANBASEK9-M), Version 12.2(52)SE, RELEASE SOFTWARE (fc3)
Copyright (c) 1986-2009 by Cisco Systems, Inc.
Compiled Fri 25-Sep-09 08:49 by sasyamal</COMMENTS>
        <CPU>11</CPU>
        <ID>2</ID>
        <IPS>
          <IP>192.168.20.81</IP>
        </IPS>
        <MAC>00:1a:6c:9a:fa:80</MAC>
        <NAME>switch2960-002</NAME>
        <SERIAL>FOC1040ZFNU</SERIAL>
        <TYPE>NETWORKING</TYPE>
      </INFO>
      <PORTS>
        <PORT>
          <CONNECTIONS>
          </CONNECTIONS>
          <IFDESCR>FastEthernet0/1</IFDESCR>
          <IFNAME>Fa0/1</IFNAME>
          <IFNUMBER>10001</IFNUMBER>
          <IFSTATUS>1</IFSTATUS>
          <IFTYPE>6</IFTYPE>
          <MAC>00:1a:6c:9a:fa:85</MAC>
          <TRUNK>0</TRUNK>
          <VLANS>
            <VLAN>
              <NAME>VLAN0020</NAME>
              <NUMBER>20</NUMBER>
            </VLAN>
          </VLANS>
        </PORT>
        <PORT>
          <CONNECTIONS>
          </CONNECTIONS>
          <IFDESCR>FastEthernet0/2</IFDESCR>
          <IFNAME>Fa0/2</IFNAME>
          <IFNUMBER>10002</IFNUMBER>
          <IFSTATUS>1</IFSTATUS>
          <IFTYPE>6</IFTYPE>
          <MAC>00:1a:6c:9a:fa:86</MAC>
          <TRUNK>0</TRUNK>
       </PORT>
        <PORT>
          <CONNECTIONS>
          </CONNECTIONS>
          <IFDESCR>FastEthernet0/3</IFDESCR>
          <IFNAME>Fa0/3</IFNAME>
          <IFNUMBER>10003</IFNUMBER>
          <IFSTATUS>1</IFSTATUS>
          <IFTYPE>6</IFTYPE>
          <MAC>00:1a:6c:9a:fa:87</MAC>
          <TRUNK>0</TRUNK>
       </PORT>
      </PORTS>
    </DEVICE>
    <MODULEVERSION>1.3</MODULEVERSION>
    <PROCESSNUMBER>2</PROCESSNUMBER>
  </CONTENT>
  <DEVICEID>port004.bureau.siprossii.com-2010-12-30-12-24-14</DEVICEID>
  <QUERY>SNMPQUERY</QUERY>
</REQUEST>';

      $pluginFusioninventoryUnknownDevice = new PluginFusioninventoryUnknownDevice();
      $pluginFusinvsnmpNetworkPortConnectionLog = new PluginFusinvsnmpNetworkPortConnectionLog();
      $networkEquipment = new NetworkEquipment();
      $networkPort = new NetworkPort();

      // * 1. Create switchs
      $this->testSendinventory("toto", $switch1, 1);
      $this->testSendinventory("toto", $switch2, 1);
         // CHECK 1 : verify hub created on port1 of switch 1
         $a_list = $networkEquipment->find("`serial`='FOC1757ZFMY'");
         $this->assertEquals(count($a_list), 1, 'switch 1 not added in GLPI');
         $a_switch = current($a_list);
         $a_ports = $networkPort->find("`itemtype`='NetworkEquipment'
               AND `items_id`='".$a_switch['id']."'");
         $this->assertEquals(count($a_ports), 1, 'switch 1 haven\'t port fa0/1 added in GLPI');
         $a_port = current($a_ports);
         $contactport_id = $networkPort->getContact($a_port['id']);
         $networkPort->getFromDB($contactport_id);
         if ($networkPort->fields['itemtype'] == 'PluginFusioninventoryUnknownDevice') {
            $pluginFusioninventoryUnknownDevice->getFromDB($networkPort->fields['items_id']);
            $this->assertEquals($pluginFusioninventoryUnknownDevice->fields['hub'],
                              '1', 'No hub connected on port fa0/1 of switch 1');
         } else {
            $t = 0;
            $this->assertEquals($t, '1', 'No hub port connected on port fa0/1 of switch 1');
         }
         // CHECK 2 : Verify number of networkportconnectionslog
         $a_conn = $pluginFusinvsnmpNetworkPortConnectionLog->find("`creation` = '1'");
         $this->assertEquals(count($a_conn), '1', '(1) Connections logs not equal to 1 ('.count($a_conn).')');
      
      $switch1 = str_replace("            <CONNECTION>
              <MAC>00:23:18:cf:0d:93</MAC>
            </CONNECTION>", "", $switch1);

      // * 2. Update switchs
      $this->testSendinventory("toto", $switch1);
      $this->testSendinventory("toto", $switch2);
         // CHECK 1 : verify hub always here and connected
         $a_ports = $networkPort->find("`itemtype`='NetworkEquipment'
               AND `items_id`='".$a_switch['id']."'");
         $this->assertEquals(count($a_ports), 1, '(2)switch 1 haven\'t port fa0/1 added in GLPI');
         $a_port = current($a_ports);
         $contactport_id = $networkPort->getContact($a_port['id']);
         $networkPort->getFromDB($contactport_id);
         if ($networkPort->fields['itemtype'] == 'PluginFusioninventoryUnknownDevice') {
            $pluginFusioninventoryUnknownDevice->getFromDB($networkPort->fields['items_id']);
            $this->assertEquals($pluginFusioninventoryUnknownDevice->fields['hub'],
                              '1', '(2)No hub connected on port fa0/1 of switch 1');
         } else {
            $t = 0;
            $this->assertEquals($t, '1', '(2)No hub port connected on port fa0/1 of switch 1');
         }
         // CHECK 2 : verify hub has always the 3 ports connected (3 mac addresses)
         $a_portshub = $networkPort->find("`itemtype`='PluginFusioninventoryUnknownDevice'
            AND `items_id`='".$networkPort->fields['items_id']."'");
         $this->assertEquals(count($a_portshub),
                           '4', '(2)Don\'t have the 4 ports connected to hub');
         // CHECK 3 : Verify number of networkportconnectionslog
         $a_conn = $pluginFusinvsnmpNetworkPortConnectionLog->find("`creation` = '1'");
         $this->assertEquals(count($a_conn), '1', '(2) Connections logs not equal to 1 ('.count($a_conn).')');
 

      
      $switch2 = str_replace("</CONNECTIONS>
          <IFDESCR>FastEthernet0/1</IFDESCR>", "               <CONNECTION>
               <MAC>00:23:18:cf:0d:93</MAC>
               </CONNECTION>
            </CONNECTIONS>
          <IFDESCR>FastEthernet0/1</IFDESCR>", $switch2);

      // * 3. Update switchs
      $this->testSendinventory("toto", $switch1);
      $this->testSendinventory("toto", $switch2);
         // CHECK 1 : verify hub always here and connected
         $a_ports = $networkPort->find("`itemtype`='NetworkEquipment'
               AND `items_id`='".$a_switch['id']."'");
         $this->assertEquals(count($a_ports), 1, '(3)switch 1 haven\'t port fa0/1 added in GLPI');
         $a_port = current($a_ports);
         $contactport_id = $networkPort->getContact($a_port['id']);
         $networkPort->getFromDB($contactport_id);
         if ($networkPort->fields['itemtype'] == 'PluginFusioninventoryUnknownDevice') {
            $pluginFusioninventoryUnknownDevice->getFromDB($networkPort->fields['items_id']);
            $this->assertEquals($pluginFusioninventoryUnknownDevice->fields['hub'],
                              '1', '(3)No hub connected on port fa0/1 of switch 1');
         } else {
            $t = 0;
            $this->assertEquals($t, '1', '(3)No hub port connected on port fa0/1 of switch 1');
         }
         // CHECK 2 : verify hub has loose one port (2 mac addresses)
         $a_portshub = $networkPort->find("`itemtype`='PluginFusioninventoryUnknownDevice'
            AND `items_id`='".$networkPort->fields['items_id']."'");
         $this->assertEquals(count($a_portshub),
                           '3', '(3)Don\'t have the 3 ports connected to hub');
         // CHECK 3 : verify port disconnected has been connected to port1 of switch 2
         $a_ports = $networkPort->find("`itemtype`='PluginFusioninventoryUnknownDevice'
               AND `mac`='00:23:18:cf:0d:93'");
         $this->assertEquals(count($a_ports), 1, '(3)port with mac 00:23:18:cf:0d:93 is not in GLPI');
         $a_port = current($a_ports);
         $contactport_id = $networkPort->getContact($a_port['id']);
         $networkPort->getFromDB($contactport_id);
         if ($networkPort->fields['itemtype'] == 'NetworkEquipment') {
            $this->assertEquals($networkPort->fields['items_id'],
                              '2', '(3)port with mac 00:23:18:cf:0d:93 not connected with swith 2');
         } else {
            $t = 0;
            $this->assertEquals($t, '1', '(3)port with mac 00:23:18:cf:0d:93 not connected to a switch');
         }
         // CHECK 4 : Verify number of networkportconnectionslog
         $a_conn = $pluginFusinvsnmpNetworkPortConnectionLog->find("`creation` = '1'");
         $this->assertEquals(count($a_conn), '2', '(3) Connections logs not equal to 2 ('.count($a_conn).')');
         
         

      $switch1bis = $switch1;
      $switch1 = str_replace("<CONNECTION>
              <MAC>f0:ad:4e:00:19:f7</MAC>
            </CONNECTION>", "", $switch1);

      // * 4. Update switchs
      $this->testSendinventory("toto", $switch1);
      $this->testSendinventory("toto", $switch2);
         // CHECK 1 : verify hub deleted and port 1 of switch 1 connected directly to port
         $a_ports = $networkPort->find("`itemtype`='NetworkEquipment'
               AND `items_id`='".$a_switch['id']."'");
         $this->assertEquals(count($a_ports), 1, '(4)switch 1 haven\'t port fa0/1 added in GLPI');
         $a_port = current($a_ports);
         $contactport_id = $networkPort->getContact($a_port['id']);
         $networkPort->getFromDB($contactport_id);
         if ($networkPort->fields['itemtype'] == 'PluginFusioninventoryUnknownDevice') {
            $pluginFusioninventoryUnknownDevice->getFromDB($networkPort->fields['items_id']);
            $this->assertEquals($pluginFusioninventoryUnknownDevice->fields['hub'],
                              '0', '(4)Hub connected on port fa0/1 of switch 1');
         }



      $switch2 = str_replace("</CONNECTIONS>
          <IFDESCR>FastEthernet0/2</IFDESCR>", "               <CONNECTION>
               <MAC>f0:ad:4e:00:19:f7</MAC>
               </CONNECTION>
            </CONNECTIONS>
          <IFDESCR>FastEthernet0/2</IFDESCR>", $switch2);
      
      // * 5. Update switchs
      $this->testSendinventory("toto", $switch1);
      $this->testSendinventory("toto", $switch2);
         // TODO : verify hub deleted
         // TODO : verify port 1 of switch 1 connected directly to port
         // TODO: Verify port connected to port 2 of switch 2
         
         // CHECK 4 : Verify number of networkportconnectionslog
         $a_conn = $pluginFusinvsnmpNetworkPortConnectionLog->find("`creation` = '1'");
         $this->assertEquals(count($a_conn), '4', '(5) Connections logs not equal to 4 ('.count($a_conn).')');
         // CHECK 5 : Verify number of networkportconnectionslog
         $a_conn = $pluginFusinvsnmpNetworkPortConnectionLog->find("`creation` = '0'");
         $this->assertEquals(count($a_conn), '1', '(5) Connections logs not equal to 1 ('.count($a_conn).')');




      // * 6. Update switchs
      $this->testSendinventory("toto", $switch1bis);
      $this->testSendinventory("toto", $switch2);
         // CHECK 1 : Verify no hub on port 1 of switch 1
         $a_ports = $networkPort->find("`itemtype`='NetworkEquipment'
               AND `items_id`='".$a_switch['id']."'");
         $this->assertEquals(count($a_ports), 1, '(6)switch 1 haven\'t port fa0/1 added in GLPI');
         $a_port = current($a_ports);
         $contactport_id = $networkPort->getContact($a_port['id']);
         $networkPort->getFromDB($contactport_id);
         if ($networkPort->fields['itemtype'] == 'PluginFusioninventoryUnknownDevice') {
            $pluginFusioninventoryUnknownDevice->getFromDB($networkPort->fields['items_id']);
            $this->assertEquals($pluginFusioninventoryUnknownDevice->fields['hub'],
                              '0', '(6)Hub connected on port fa0/1 of switch 1');
         }
         
   }



   function testSendinventory($xmlFile='', $xmlstring='', $create='0') {

      if (empty($xmlFile)) {
         echo "testSendinventory with no arguments...\n";
         return;
      }

      $emulatorAgent = new emulatorAgent;
      $emulatorAgent->server_urlpath = "/glpi080/plugins/fusioninventory/front/communication.php";
      if (empty($xmlstring)) {
         $xml = simplexml_load_file($xmlFile,'SimpleXMLElement', LIBXML_NOCDATA);
      } else {
         $xml = simplexml_load_string($xmlstring);
      }

      if ($create == '1') {
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
               if ($child2->TYPE == 'NETWORKING') {
                  // Create switch in asset
                  $NetworkEquipment = new NetworkEquipment();
                  $input = array();
                  if (isset($child2->SERIAL)) {
                     $input['serial']=$child2->SERIAL;
                  } else {
                     $input['name']=$child2->NAME;
                  }
                  $input['entities_id'] = 0;
                  $NetworkEquipment->add($input);
               }
            }
         }
      }
      $input_xml = $xml->asXML();
      $code = $emulatorAgent->sendProlog($input_xml);
      echo $code."\n";
   }

}

// Call Plugins_Fusioninventory_Discovery_Newdevices::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'Plugins_Fusioninventory_InventorySNMPHub::main') {
    Plugins_Fusioninventory_InventorySNMPHub::main();

}

?>