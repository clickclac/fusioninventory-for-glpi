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

if (!defined('GLPI_ROOT')) {
   define('GLPI_ROOT', '../../..');
}

include (GLPI_ROOT."/inc/includes.php");

checkLoginUser();

if (isset($_GET["popup"])) {
   $_SESSION["glpipopup"]["name"] = $_GET["popup"];
}

if (isset($_SESSION["glpipopup"]["name"])) {
   switch ($_SESSION["glpipopup"]["name"]) {


      case "test_rule" :
         popHeader($LANG['buttons'][50],$_SERVER['PHP_SELF']);
         include "rule.test.php";
         break;

      case "test_all_rules" :
         popHeader($LANG['rulesengine'][84],$_SERVER['PHP_SELF']);
         include "rulesengine.test.php";
         break;

   }
   echo "<div class='center'><br><a href='javascript:window.close()'>".$LANG['buttons'][13]."</a>";
   echo "</div>";
   popFooter();
}

?>