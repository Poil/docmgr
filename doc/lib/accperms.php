<?php
/*********************************************************
//        FILE: accperms.inc.php
// DESCRIPTION: Contains functions that queries for
//              user/employee account information
//              and permssions.
//
//    CREATION
//        DATE: 04-19-2006
//
//     HISTORY: 05-29-2006
//			removed app-specific functions
//
*********************************************************/
function permInsert($conn,$table,$field,$objectId,$level) {

     if (!$level) $level = "0";

     //see if any permissions entries exist
     $sql = "SELECT * FROM $table WHERE $field='$objectId' ";
     if(defined("APP_ID"))
        $sql .= " AND app_id = '".APP_ID."' ";

     $num = num_result($conn,$sql);

     //create our query
     $opt = null;
     $opt["bitset"] = $level;	//the permissions level we are storing
     if (defined("APP_ID")) $opt["app_id"] = APP_ID;	//add this field if application specific

     //if there are no entries, add the new ones, otherwise update
     if ($num=="0") {
       $opt[$field] = $objectId;
       dbInsertQuery($conn,$table,$opt);
     } else {
       $opt["where"] = $field."='".$objectId."'";
       dbUpdateQuery($conn,$table,$opt);
     }

}
/************************************************************************************
    This function displays a permissions list for an object, and checks
    any boxes the user belongs to
************************************************************************************/
function groupPerm($conn,$optionArray) {

    $table = $optionArray["table"];
    $filter = $optionArray["filter"];
    $filterValue = $optionArray["filterValue"];
    $permArray = $optionArray["permArray"];
    $prefix = $optionArray["prefix"];
    $bitValue = $optionArray["bitValue"];

    //display all permissions that belong to this app
    $sql = "SELECT * FROM $table";

    if ($filter) $sql .= " WHERE $filter='$filterValue'";

    $sql .= " ORDER BY bitpos";

    $list = total_result($conn,$sql);

    $perm_id_array  =   &$list["id"];
    $perm_name_array    =   &$list["name"];
    $perm_bitpos_array  =   &$list["bitpos"];
    $perm_owner_array   =   &$list["owner"];

    $string = "<table border=0 cellpadding=0 cellspacing=0>";

    //display an error message if no perms exist for this app
    if (count($perm_id_array)==0) $string .= "<tr><td>No permissions are defined.</td></tr>";
    else {

        for ($num=0;$num<count($perm_id_array);$num++) {

            $bitSet = bitCal($perm_bitpos_array[$num]);

            $hideCheckbox = null;

            //if ($bitSet == $bitAdmin) if (!(bitset_section(BITSET,ADMIN,null))) $hideCheckbox = 1;

            if (!$hideCheckbox) {

                if ($bitValue & $bitSet) $checked = " CHECKED ";
                else $checked = null;

                $string .= "<tr><td valign=top >";
                $string .= "<table cellpadding=0 cellspacing=0><tr><td>";

                //add extra cells if this is a subPerm
                if ($perm_owner_array[$num]!="0") $string .= getOwner(  $bitpos,
                                            $perm_owner_array,
                                            $perm_bitpos_array,
                                            null);

                //create id of checkbox
                $idValue = $prefix."Perm".$perm_bitpos_array[$num];

                //get id of checkbox owner if there is one
                if ($perm_owner_array[$num]!=0) $passId = $prefix."Perm".$perm_owner_array[$num];
                else $passId="0";

                //print out the checkbox
                $string .= "<input type=checkbox
                    id=\"".$idValue."\"
                    name=\"".$prefix."Permission[]\"
                    ".$checked."
                    value=\"".$bitSet."\">&nbsp;";

                $string .= "</td><td colspan=5>";

                $string .= $perm_name_array[$num];

                $string .= "</td></tr></table>";
                $string .= "</td></tr>";
            }
        }
    }
    $string .= "</table>";

    return $string;

}
//this function returns a combined list of accounts and groups, sorted by name, to display
//in the list when selecting permissions for an account
function returnPermAccounts($conn) {

    $option = null;
    $option["conn"] = $conn;
    $option["sort"] = "login";

    //get our accounts sorted by login
    $accountList = returnAccountList($option);

    //get our groups sorted by name
    $sql = "SELECT * FROM auth_groups ORDER BY name";
    $groupList = total_result($conn,$sql);

    //create a new array with the keys named like we want
    $aType = array_fill(0,$accountList["count"], "account");
    $gType = array_fill(0,$groupList["count"], "group");

    //merge our arrays into an array with a common key name
    $ret["id"] = array_merge($groupList["id"],$accountList["id"]);
    $ret["name"] = array_merge($groupList["name"],$accountList["login"]);
    $ret["type"] = array_merge($gType,$aType);

    return $ret;

}

