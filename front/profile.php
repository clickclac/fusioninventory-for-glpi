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
   Original Author of file: David Durieux
   Co-authors of file: 
   Purpose of file:
   ----------------------------------------------------------------------
 */

define('GLPI_ROOT', '../../..'); 

include (GLPI_ROOT."/inc/includes.php");

$prof=new PluginFusioninventoryProfile();

//Save profile
if (isset ($_POST)) {
   if (haveRight("profile", "w")) {
      $prof->updateProfiles($_POST);
   }
   glpi_header($_SERVER['HTTP_REFERER']);
}

?>