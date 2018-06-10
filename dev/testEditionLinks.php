<?php
  /**
* This file is part of the Research Environment for Ancient Documents (READ). For information on the authors
* and copyright holders of READ, please refer to the file AUTHORS in this distribution or
* at <https://github.com/readsoftware>.
*
* READ is free software: you can redistribute it and/or modify it under the terms of the
* GNU General Public License as published by the Free Software Foundation, either version 3 of the License,
* or (at your option) any later version.
*
* READ is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
* without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
* See the GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along with READ.
* If not, see <http://www.gnu.org/licenses/>.
*/
  /**
  * testEditionLinks
  *
  *  A script that checks the edition molecule is linked properly and returns a report on the results.
  * it can be called from the browse like this:
  * http://localhost/kanishka/dev/testEditionLinks.php?db=vp2sk&ednIDs=1
  * to check an individual edition
  * http://localhost/kanishka/dev/testEditionLinks.php?db=vp2sk&ednIDs=1,2,3,4
  * to check multiple edition
  * http://localhost/kanishka/dev/testEditionLinks.php?db=vp2sk&ednIDs=all
  * to check all editions
  * @author      Stephen White  <stephenawhite57@gmail.com>
  * @copyright   @see AUTHORS in repository root <https://github.com/readsoftware/read>
  * @link        https://github.com/readsoftware
  * @version     1.0
  * @license     @see COPYING in repository root or <http://www.gnu.org/licenses/>
  * @package     READ Research Environment for Ancient Documents
  * @subpackage  Utility Classes
  */
  define('ISSERVICE',1);
  ini_set("zlib.output_compression_level", 5);
//  ob_start('ob_gzhandler');

  header("Content-type: text/javascript");
  header('Cache-Control: no-cache');
  header('Pragma: no-cache');

  require_once (dirname(__FILE__) . '/../common/php/userAccess.php');//get user access control
  if (@$argv) {
    // handle command-line queries
    $cmdParams = array();
    for ($i=0; $i < count($argv); ++$i) {
      if ($argv[$i][0] === '-') {
        if (@$argv[$i+1] && $argv[$i+1][0] != '-') {
        $cmdParams[$argv[$i]] = $argv[$i+1];
        ++$i;
        }else{ // map non-value dash arguments into state variables
          $cmdParams[$argv[$i]] = true;
        }
      } else {//capture others args
        array_push($cmdParams, $argv[$i]);
      }
    }
    if(@$cmdParams['-d']) $_REQUEST["db"] = $cmdParams['-d'];
    if (@$cmdParams['-e']) $_REQUEST['ednID'] = $cmdParams['-e'];
    //commandline access for setting userid  TODO review after migration
    if (@$cmdParams['-u'] && !isset($_SESSION['ka_userid'])) $_SESSION['ka_userid'] = $cmdParams['-u'];
    echo 'db = '.$_REQUEST['db'].' ednID = '.$_REQUEST['ednID'].' uID = '.$_SESSION['ka_userid']."\n";
  }
  require_once (dirname(__FILE__) . '/../common/php/DBManager.php');//get database interface
  require_once (dirname(__FILE__) . '/../common/php/utils.php');//get utilies
  require_once dirname(__FILE__) . '/../model/entities/Editions.php';
  require_once dirname(__FILE__) . '/../model/entities/Sequences.php';
  require_once dirname(__FILE__) . '/../model/entities/Segments.php';
  require_once dirname(__FILE__) . '/../model/entities/Graphemes.php';
  require_once dirname(__FILE__) . '/../model/entities/Tokens.php';
  require_once dirname(__FILE__) . '/../model/entities/Compounds.php';
  require_once dirname(__FILE__) . '/../model/entities/SyllableClusters.php';
  require_once dirname(__FILE__) . '/../model/entities/Annotations.php';
  require_once dirname(__FILE__) . '/../model/entities/Attributions.php';
  require_once dirname(__FILE__) . '/../model/entities/JsonCache.php';


  $dbMgr = new DBManager();
  $retVal = array();

  $ednIDs = (array_key_exists('ednIDs',$_REQUEST)? $_REQUEST['ednIDs']:null);
  $ednID = (array_key_exists('ednID',$_REQUEST)? $_REQUEST['ednID']:null);
  $compact = (array_key_exists('compact',$_REQUEST)? true:false);
  if ($ednID && !$ednIDs) {
     echo checkEditionHealth($ednID,!$compact);
  } else if ($ednIDs) {// edns
    if (is_string($ednIDs) && strpos($ednIDs,',')) {// list of numbers
      $ednIDs = explode(",",$ednIDs);
    }
    if (is_array($ednIDs)) {
      foreach ($ednIDs as $ednID) {
        echo checkEditionHealth($ednID);
      }
    } else if (is_numeric($ednIDs)) {// single edition id case
     echo checkEditionHealth($ednIDs);
    } else if ($ednIDs == "all") {
      $editions = new Editions(null,"edn_id",null,null);
      foreach ($editions as $edition) {
        echo checkEditionHealth($edition->getID(),!$compact);
      }
    }
  }

?>
