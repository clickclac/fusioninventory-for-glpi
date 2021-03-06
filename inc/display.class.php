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
   Original Author of file: Vincent MAZZONI
   Co-authors of file:
   Purpose of file:
   ----------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginFusioninventoryDisplay extends CommonDBTM {

   /**
   * Display static progress bar (used for SNMP cartridge state)
   *
   *@param $pourcentage integer pourcentage to display
   *@param $message value message to display on this bar
   *@param $order if empty <20% display in red, if not empty, >80% display in red
   *
   *@return nothing
   **/
   static function bar($pourcentage,$message='',$order='') {
      if ((!empty($pourcentage)) AND ($pourcentage < 0)) {
         $pourcentage = "";
      } else if ((!empty($pourcentage)) AND ($pourcentage > 100)) {
         $pourcentage = "";
      }
      echo "<div>
               <table class='tab_cadre' width='400'>
                  <tbody>
                     <tr>
                        <td align='center' width='400'>";

      if ((!empty($pourcentage)) OR ($pourcentage == "0")) {
         echo $pourcentage."% ".$message;
      }

      echo                  "</td>
                     </tr>
                     <tr>
                        <td>
                           <div>
                           <table cellpadding='0' cellspacing='0'>
                              <tbody>
                                 <tr>
                                    <td width='400' height='0' colspan='2'></td>
                                 </tr>
                                 <tr>";
      if (empty($pourcentage)) {
         echo "<td></td>";
      } else {
         echo "                              <td bgcolor='";
         if ($order!= '') {
            if ($pourcentage > 80) {
               echo "red";
            } else if($pourcentage > 60) {
               echo "orange";
            } else {
               echo "green";
            }
         } else {
            if ($pourcentage < 20) {
               echo "red";
            } else if($pourcentage < 40) {
               echo "orange";
            } else {
               echo "green";
            }
         }
         if ($pourcentage == 0) {
            echo "' height='20' width='1'>&nbsp;</td>";
         } else {
            echo "' height='20' width='".(4 * $pourcentage)."'>&nbsp;</td>";
         }
      }
      if ($pourcentage == 0) {
         echo "                           <td height='20' width='1'></td>";
      } else {
         echo "                           <td height='20' width='".(400 - (4 * $pourcentage))."'></td>";
      }
      echo "                        </tr>
                              </tbody>
                           </table>
                        </td>
                     </tr>
                  </tbody>
               </table>
            </div>";
   }



   /**
   * Disable debug mode to not see php errors
   *
   **/
   static function disableDebug() {
      error_reporting(0);
      set_error_handler("plugin_fusioninventory_empty");
   }



   /**
   * Enable debug mode if user is in debug mode
   *
   **/
   static function reenableusemode() {
      if ($_SESSION['glpi_use_mode']==DEBUG_MODE){
         ini_set('display_errors','On');
         error_reporting(E_ALL | E_STRICT);
         set_error_handler("userErrorHandler");
      }
   }



   /**
   * Display progress bar
   *
   *@param $width integer width of the html array/bar
   *@param $percent interger pourcentage of the bar
   *@param $options array
   *     - title value title of the progressbar to display
   *     - simple bool simple display or not
   *     - forcepadding bool
   *
   *@return value code of this bar
   **/
   static function getProgressBar($width,$percent,$options=array()) {
      global $CFG_GLPI,$LANG;

      $param = array();
      $param['title']=$LANG['common'][47];
      $param['simple']=false;
      $param['forcepadding']=false;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $param[$key]=$val;
         }
      }

      $percentwidth=floor($percent*$width/100);
      $output="<div class='center'><table class='tab_cadre' width='".($width+20)."px'>";
      if (!$param['simple']) {
         $output.="<tr><th class='center'>".$param['title']."&nbsp;".$percent."%</th></tr>";
      }
      $output.="<tr><td>
                <table><tr><td class='center' style='background:url(".$CFG_GLPI["root_doc"].
                "/pics/loader.png) repeat-x;' width='.$percentwidth' height='12'>";
      if ($param['simple']) {
         $output.=$percent."%";
      } else {
         $output.='&nbsp;';
      }
      $output.="</td></tr></table></td>";
      $output.="</tr></table>";
      $output.="</div>";
      return $output;
   }
}

?>