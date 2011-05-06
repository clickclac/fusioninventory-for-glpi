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

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

$iprange = new PluginFusioninventoryIPRange();

commonHeader($LANG['plugin_fusioninventory']['title'][0], $_SERVER["PHP_SELF"], "plugins", 
             "fusioninventory", "iprange");

PluginFusioninventoryProfile::checkRight("fusioninventory", "iprange", "r");

PluginFusioninventoryMenu::displayMenu("mini");

if (isset ($_POST["add"])) {
   PluginFusioninventoryProfile::checkRight("fusioninventory", "iprange","w");
   if ($iprange->checkip($_POST)) {
      $_POST['ip_start']  = $_POST['ip_start0'].".".$_POST['ip_start1'].".";
      $_POST['ip_start'] .= $_POST['ip_start2'].".".$_POST['ip_start3'];
      $_POST['ip_end']    = $_POST['ip_end0'].".".$_POST['ip_end1'].".";
      $_POST['ip_end']   .= $_POST['ip_end2'].".".$_POST['ip_end3'];
      $newID = $iprange->add($_POST);
      glpi_header(getItemTypeFormURL('PluginFusioninventoryIPRange')."?id=$newID");
   } else {
      glpi_header($_SERVER['HTTP_REFERER']);
   }
} else if (isset ($_POST["update"])) {
   if (isset($_POST['communication'])) {
      //task permanent update
      $task = new PluginFusioninventoryTask();
      $taskjob = new PluginFusioninventoryTaskjob();
      $task->getFromDB($_POST['task_id']);
      $input_task = array();
      $input_task['id'] = $task->fields['id'];
      $taskjob->getFromDB($_POST['taskjob_id']);
      $input_taskjob                   = array();
      $input_taskjob['id']             = $taskjob->fields['id'];
      $input_task["is_active"]         = $_POST['is_active'];
      $input_task["periodicity_count"] = $_POST['periodicity_count'];
      $input_task["periodicity_type"]  = $_POST['periodicity_type'];
      if (!empty($_POST['action'])) {
         $a_actionDB                                 = array();
         $a_actionDB[]['PluginFusioninventoryAgent'] = $_POST['action'];
         $input_taskjob["action"]                    = exportArrayToDB($a_actionDB);
      } else {
         $input_taskjob["action"] = '';
      }
      $a_definition = array();
      $a_definition[]['PluginFusioninventoryIPRange'] = $_POST['iprange'];
      $input_taskjob['definition'] = exportArrayToDB($a_definition);
      $input_task["communication"] = $_POST['communication'];

      $task->update($input_task);
      $taskjob->update($input_taskjob);
   } else {
      PluginFusioninventoryProfile::checkRight("fusioninventory", "iprange","w");
      if ($iprange->checkip($_POST)) {
         $_POST['ip_start']  = $_POST['ip_start0'].".".$_POST['ip_start1'].".";
         $_POST['ip_start'] .= $_POST['ip_start2'].".".$_POST['ip_start3'];
         $_POST['ip_end']    = $_POST['ip_end0'].".".$_POST['ip_end1'].".";
         $_POST['ip_end']   .= $_POST['ip_end2'].".".$_POST['ip_end3'];
         $iprange->update($_POST);
      }
   }
   glpi_header($_SERVER['HTTP_REFERER']);
} else if (isset ($_POST["delete"])) {
   if (isset($_POST['communication'])) {
      $task = new PluginFusioninventoryTask();
      $task->delete(array('id' => $_POST['task_id']), 1);
      $_SERVER['HTTP_REFERER'] = str_replace("&allowcreate=1", "", $_SERVER['HTTP_REFERER']);
      glpi_header($_SERVER['HTTP_REFERER']);
   } else {
      PluginFusioninventoryProfile::checkRight("fusioninventory", "iprange","w");

      $iprange->delete($_POST);
      glpi_header(getItemTypeSearchURL('PluginFusioninventoryIPRange'));
   }
}

$id = "";
if (isset($_GET["id"])) {
   $id = $_GET["id"];
}
$allowcreate = 0;
if (isset($_GET['allowcreate'])) {
   $allowcreate = $_GET['allowcreate'];
}

$iprange->showForm($id, array( "allowcreate" => $allowcreate));

commonFooter();

?>