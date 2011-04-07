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
   Original Author of file: Walid Nouh
   Co-authors of file:
   Purpose of file:
   ----------------------------------------------------------------------
 */

// Update from 2.2.1 to 2.3.0
function update230to240() {
   global $DB, $CFG_GLPI, $LANG;

   ini_set("max_execution_time", "0");

   echo "<strong>Update 2.3.0 to 2.4.0</strong><br/>";
   echo "</td>";
   echo "</tr>";

   echo "<tr class='tab_bg_1'>";
   echo "<td align='center'>";

   plugin_fusioninventory_displayMigrationMessage("240"); // Start
   
   if (TableExists("`glpi_plugin_fusinvsnmp_ipranges`")) {
      //Rename table
      $query = "RENAME TABLE  `glpi_plugin_fusinvsnmp_ipranges` " .
               "TO `glpi_plugin_fusioninventory_ipranges` ;";
      $DB->query($query) or die ("Rename glpi_plugin_fusinvsnmp_ipranges " .
                                 "to glpi_plugin_fusioninventory_ipranges".
                                 $LANG['update'][90] . $DB->error());
      
      //Migrate itemtype in all tables
      //First taskjobstatus
      $query = "UPDATE `glpi_plugin_fusioninventory_taskjobstatus` " .
               "SET `itemtype`='PluginFusioninventoryIPRange' " .
               "WHERE `itemtype`='PluginFusinvsnmpIPRange'";
      $DB->query($query) or die ("Rename itemtype in glpi_plugin_fusioninventory_taskjobstatus".
                                 $LANG['update'][90] . $DB->error());
      
      //Now taskjob
      $job = new PluginFusioninventoryTaskjob();
      foreach (getAllDatasFromTable('glpi_plugin_fusioninventory_taskjobs') as $taskjob) {
         $definition = json_decode($taskjob['definition']);
         if (isset($definition['PluginFusinvsnmpIPRange'])) {
            $definition['PluginFusioninventoryIPRange'] = $definition['PluginFusinvsnmpIPRange'];
            unset($definition['PluginFusinvsnmpIPRange']);
            $taskjob['definition'] = $definition;
            $job->update($taskjob);
         }
      }
   }
   plugin_fusioninventory_displayMigrationMessage("240"); // End
}

?>