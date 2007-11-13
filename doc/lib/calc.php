<?php
/*********************************************************
//        FILE: calc.inc.php
// DESCRIPTION: Contains functions that have that are for
//              some sort of mathematical calculations
//              or binary manipulation.
//
//    CREATION
//        DATE: 04-19-2006
//
//     HISTORY:
//
*********************************************************/
function bitset_compare($bit1,$bit2,$admin) {

    $auth = null;

    if ( (int)$bit1 & (int)$bit2 ) $auth = 1;

    if ($admin) {

        if ( (int)$bit1 & (int)$admin ) $auth = 1;

    }

    if (!$auth) return false;
    else return true;

}
/*********************************************************
*********************************************************/
function bitCal($limit) {

    $num = 1;

    for ($row=0;$row<$limit;$row++) {
        if ($limit!=0) $num = $num * 2;
    }

    return $num;
}
/*********************************************************
*********************************************************/
function revBitCal($limit) {

    $counter = "0";

    while ($limit!="1") {

        $counter++;
        $limit = $limit/2;
    }

    return $counter;

}
/*********************************************************
*********************************************************/
function intPercent($num,$total) {

    if ($total != 0) {

        $percent = ($num/$total) * 100;
        $temp = intval($percent) + ".5";

        //round up if necessary
        if ($percent >= $temp) {
            $value = intval($percent) + 1;
        }
        else {
            $value = intval($percent);
        }

    }
    else $value = "0";

    return $value;

}
/*********************************************************
*********************************************************/
function intValue($num) {

    $temp = intval($num) + ".5";

    //round up if necessary
    if ($num >= $temp) {
        $value = intval($num) + 1;
    }
    else {
        $value = intval($num);
    }

    return $value;

}
/*********************************************************
*********************************************************/
function floatValue($num,$count) {

  $pos = strpos($num,".") + 1 + $count;

  $floatNum = substr($num,0,$pos);
  $checkDigit = substr($num,strlen($floatNum),1);

  $changeDigit = substr($floatNum,strlen($floatNum)-1,1);

  //round up if necessary
  if ($checkDigit>=5) $last = $changeDigit + 1;
  else $last = $changeDigit;

  $value = substr($floatNum,0,strlen($floatNum)-1).$last;

  return $value;

}




