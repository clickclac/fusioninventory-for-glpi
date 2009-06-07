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

function plugin_tracker_dropdownDefaultYesNo($name,$value) {
	global $LANG,$LANGTRACKER;
	
	echo "<select name='$name' id='dropdownyesno_$name'>\n";
	echo "<option value='-1' ".($value==-1?" selected ":"").">".$LANGTRACKER["cron"][3]."</option>\n";
	echo "<option value='0' ".(!$value?" selected ":"").">".$LANG["choice"][0]."</option>\n";
	echo "<option value='1' ".($value==1?" selected ":"").">".$LANG["choice"][1]."</option>\n";
	echo "</select>\n";
}


?>