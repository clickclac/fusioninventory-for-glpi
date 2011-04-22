<?php
/*
 * @version $Id: fieldunicity.form.php 14033 2011-03-06 16:14:51Z yllen $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

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
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------


define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

$item = new PluginFusioninventoryCredential_Item();
if (isset($_POST['add'])) {
   $item->add($_POST);
   
} elseif (isset($_POST['delete'])) {

   if (isset($_POST['item']) && !empty($_POST['item'])) {
      foreach ($_POST['item'] as $id => $value) {
         if ($value == 1) {
            $item->delete(array('id' => $id));
            
         }
      }
      
   }
}
glpi_header($_SERVER['HTTP_REFERER']);
?>