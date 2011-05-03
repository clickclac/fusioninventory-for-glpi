<?php

/*
   ----------------------------------------------------------------------
   FusionInventory
   Copyright (C) 2010-2011 by the FusionInventory Development Team.

   http://www.fusioninventory.org/   http://forge.fusioninventory.org/
   ----------------------------------------------------------------------

   LICENSE

   This file is part of FusionInventory.

   FusionInventory is free software: you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation, either version 2 of the License, or
   any later version.

   FusionInventory is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with FusionInventory.  If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------
   Original Author of file: David DURIEUX
   Co-authors of file:
   Purpose of file:
   ----------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}


class PluginFusioninventoryWizard {

   function filAriane($a_list) {
      if (count($a_list) == '0') {
         return;
      }
      echo "<table class='tab_cadre' width='250'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th>";
      echo "<strong>Fil d'ariane</strong>";
      echo "</th>";
      echo "</tr>";
      foreach ($a_list as $name=>$link) {
         echo "<tr class='tab_bg_1'>";
         echo "<td>";
         $split = explode("/", $_SERVER["PHP_SELF"]);
         if (strstr($link, array_pop($split))) {
            echo "<img src='".GLPI_ROOT."/pics/right.png'/>";
         } else {
            echo "<img src='".GLPI_ROOT."/pics/right_off.png'/>";
         }
         echo " <a href='".$link."'>".$name."</a>";
         echo "</td>";
         echo "</tr>";
      }

      echo "</table>";
   }


   static function displayButtons($a_buttons, $a_filariane) {

      $pluginFusioninventoryWizard = new PluginFusioninventoryWizard();

      echo "<style type='text/css'>
      .bgout {
         background-image: url(".GLPI_ROOT."/plugins/fusioninventory/pics/wizard_button.png);
      }
      .bgover {
         background-image: url(".GLPI_ROOT."/plugins/fusioninventory/pics/wizard_button_active.png);
      }
      </style>";
      echo "<center><table width='950'>";
      echo "<tr>";
      echo "<td rowspan='2' align='center'>";
         echo "<table cellspacing='10'>";
         echo "<tr>";
         foreach ($a_buttons as $array) {
            echo "<td class='bgout'
               onmouseover='this.className=\"bgover\"' onmouseout='this.className=\"bgout\"'
               onClick='location.href=\"".$array[1]."\"'
               width='240' height='155' align='center'>";
            echo "<strong>".$array[0]."</strong><br/><br/>";
            if ($array[2] != '') {
               echo "<img src='".GLPI_ROOT."/plugins/fusioninventory/pics/".$array[2]."'/>";
            }
            echo "</td>";
         }
         echo "</tr>";
         echo "</table>";
      echo "</td>";
      echo "<td height='8'></td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td valign='top'>";
      $pluginFusioninventoryWizard->filAriane($a_filariane);
      echo "</td>";
      echo "</tr>";

      echo "</table></center>";
   }


   function displayShowForm($a_button, $a_filariane, $classname) {

      echo "<style type='text/css'>
      .bgout {
         background-image: url(".GLPI_ROOT."/plugins/fusioninventory/pics/wizard_button.png);
      }
      .bgover {
         background-image: url(".GLPI_ROOT."/plugins/fusioninventory/pics/wizard_button_active.png);
      }
      </style>";
      echo "<center><table width='950'>";
      echo "<tr>";
      echo "<td>";
      $class = new $classname;
      $class->showForm(array('target'=>$_SERVER["PHP_SELF"]));
      echo "</td>";
      echo "<td valign='top'>";
      $this->filAriane($a_filariane);
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td align='right'>";
      echo "<input class='submit' type='submit' name='next' value='".$a_button['name']." >'/>";
      echo "</td>";
      echo "<td></td>";
      echo "</tr>";

      echo "</table></center>";
   }



   function filInventoryComputer() {
      return array(
      "choix de l'action"              => "w_start",
      "Type de matériel à inventorier" => GLPI_ROOT."/plugins/fusioninventory/front/wizard_inventory.php",
      "Options d'importation"          => GLPI_ROOT."/plugins/fusioninventory/front/wizard_inventorycomputeroptions.php",
      "Règles d'import d'ordinateurs"  => "",
      "Règles de sélection de l'entité"=> "",
      "Configuration des agents"       => "");
   }



   function filInventoryESX() {
      return array(
      "choix de l'action"                  => "w_start",
      "Type de matériel à inventorier"     => GLPI_ROOT."/plugins/fusioninventory/front/wizard_inventory.php",
      "Gestion des mots de passe"          => "",
      "Gestion des serveur ESX"            => "",
      "Règles d'import d'ordinateurs"      => "",
      "Création de taches d'exécution"     => "",
      "Affichage des inventaires réalisés" => "");
   }


   
   function filInventorySNMP() {
      return array(
      "choix de l'action"                  => "w_start",
      "Type de matériel à inventorier"     => GLPI_ROOT."/plugins/fusioninventory/front/wizard_inventory.php",
      "Choix (decouverte ou inventaire)"   => "",
      "Authentification SNMP"              => "",
      "Règles d'import"                    => "",
      "Création de taches d'exécution"     => "",
      "Affichage des inventaires réalisés" => "");
   }


   function filNetDiscovery() {
      $array = array(
      "choix de l'action"                  => "w_start");
      return array_merge($array, $this->fil_Part_NetDiscovery());
   }


   function filInventorySNMP_Netdiscovery() {
      $array = array(
      "choix de l'action"                  => "w_start",
      "Type de matériel à inventorier"     => GLPI_ROOT."/plugins/fusioninventory/front/wizard_inventory.php",
      "Choix (decouverte ou inventaire)"   => "");
      return array_merge($array, $this->fil_Part_NetDiscovery());
  }


   function fil_Part_NetDiscovery() {
      return array(
      "Authentification SNMP"              => "",
      "Règles d'import"                    => "",
      "Création de taches d'exécution"     => "",
      "Affichage des inventaires réalisés" => "");
   }

   

  // ********************* All wizard display **********************//

   /*
    * First panel of wizard
    */
   static function w_start($ariane='') {
      $a_buttons = array(array('Découvrir le matériel sur le réseau',
                               'w_authsnmp',
                               'networkscan.png'),
                         array('Inventorier des matériels',
                                'w_wiz_inventory',
                                'general_inventory.png'));

      echo "<center>Bienvenue dans FusionInventory. Commencer la configuration ?</center><br/>";

      PluginFusioninventoryWizard::displayButtons($a_buttons, $ariane);
   }


   static function w_authsnmp($ariane='') {

      
   }

}

?>