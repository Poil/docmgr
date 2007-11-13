<?php
/***************************************************************/
//        FILE: dataformatting.inc.php
//              (replaces date_function.inc.php and merges
//               functions from other include files into this one)
//
// DESCRIPTION: Contains functions that handle how information
//              is formatted for display on the web page 
//              or db inserting/updating
//
//              Includes functions for formatting the output of 
//              Time, Date, Phone Number,and Monetary values
//
//    CREATION
//        DATE: 04-19-2006
//
//     HISTORY:
//
/***************************************************************/
function time_format($time_entry,$period_value) {

	$hour_value  	=   substr ($time_entry,0,2);
	$minute_value	=	substr ($time_entry,3,2);
	//$period_value	=	substr ($time_entry,7,4);

	//06:25 a.m.

	if ($period_value) {

		if ($period_value=="P.M." && $hour_value!="12") {

			$hour_value=12+$hour_value;

		}

	}

	$time_enter=$hour_value;
	$time_enter.=":";
	$time_enter.=$minute_value;
	$time_enter.=":";
	$time_enter.="00";

	return $time_enter;


}

function time_view($time_entry,$format) {

	$arr = explode(":",$time_entry);

	$hour = $arr[0];
	$min = $arr[1];
	$sec = $arr[2];

	if ($format=="standard") {

		if ($hour>="12") {

			$period="P.M.";
			if ($hour!=12) $hour=$hour-12;

		} else {
			if ($hour=="0" || $hour=="00") $hour = "12";
			$period="A.M.";
		}

	}

	$time_show=$hour;
	$time_show.=":";
	$time_show.=$min;

	if ($period) $time_show .= " ".$period;

	return $time_show;

}

function time_format_fix($start_hour,$start_min,$starttime_period,$dur_hour,$dur_min) {

	if ($starttime_period=="P.M.") 	$start_hour=$start_hour+12;

	if ($start_hour=="24") {

		if ($starttime_period=="P.M.") $start_hour = "12";
		else $start_hour = "0";

	}

	$start_time=$start_hour.":".$start_min;

	$end_hour=$start_hour+$dur_hour;
	$end_min=$start_min+$dur_min;

	if ($end_min=="0") $end_min="00";

	if ($end_min>=60) {
		$end_hour=$end_hour+1;
		$end_min=$end_min-60;
	}

	if ($end_min=="60") {
		if ($end_hour=="12") $end_hour=="01";
		else $end_hour=$end_hour+1;
		$end_min="00";
	}

	if ($end_hour>=24) $end_hour=$end_hour - 24;

	$end_time=$end_hour.":".$end_min;

	$time_array=array();
	$time_array[0]=$start_time;
	$time_array[1]=$end_time;
	return $time_array;

}


//this function returns the hours and minutes difference between tw times
function time_diff($start_time,$end_time) {

	//subtract our times to get the difference
	$calcStArray = explode(":",$start_time);
	$calcEtArray = explode(":",$end_time);

	$start_hour = $calcStArray[0];
	$start_min = $calcStArray[1];

	$end_hour = $calcEtArray[0];
	$end_min = $calcEtArray[1];

	//reduce our dates to timestamps.  We use a random date here, since it does not matter
	$ts1 = mktime($start_hour,$start_min,"0",1,1,2000);
	$ts2 = mktime($end_hour,$end_min,"0",1,1,2000);

	//get the number of seconds
	$diff = $ts2 - $ts1;

	$temp = $diff/3600;

	//get the place of the decimal point
	$pos = strpos($temp,".");

	//if we have pos, there is a decimal point
	if ($pos) {

		$dur_hour = intval($temp);
		$min = substr($temp,$pos);

		//convert to clock nums
		if ($min==".5") $dur_min = "30";
		elseif ($min==".25") $dur_min = "15";
		elseif ($min==".75") $dur_min = "45";

	}
	else {
		$dur_hour = $temp;
		$dur_min = "00";
	}

	return array($dur_hour,$dur_min);

}

//takes dates in the XXXX-XX-XX format
function dateFix($date) {

     $dateArray = explode("-",$date);

    $year = $dateArray[0];
    $month = $dateArray[1];
    $day = $dateArray[2];

    return date("Y-m-d",mktime(0,0,0,$month,$day,$year));

}

function date_diff($firstDate,$lastDate) {

	$fdArray = explode("-",$firstDate);
	$ldArray = explode("-",$lastDate);

	//get how many days the difference is
	$diff = mktime(0,0,0,$fdArray[1],$fdArray[2],$fdArray[0]) - mktime(0,0,0,$ldArray[1],$ldArray[2],$ldArray[0]);
	$diff = abs($diff / 86400);

	return $diff;
}

//this makes dates viewable as passed from postgresql's date format
function date_time_view($datetime) {

	if (!$datetime) return true;

	$datetime = trim($datetime);

	$pos = strpos($datetime," ");

	//divide up our string into date and time
	$date_value=substr($datetime,0,$pos);
	$date_value = date_view($date_value,"slash");

	$time_value = substr($datetime,$pos);
	$time_value = time_view($time_value,"standard");

	$datetime=array($date_value,$time_value);
	return $datetime;
}

//this function takes a date and puts it into a format that
//the database will accept
function dateFormat($date,$altDateFormat) {

	if (strpos($date,"-")==4) {
		return $date;
	}
	elseif (strpos($date,"/")==4) {
		$date=eregi_replace("/","-",$date);
		return $date;
	}
	else {

		if (strpos($date,"-")) $date=eregi_replace("-","/",$date);

		//swap the year and date if we are using alternate format
		if ($altDateFormat && strpos($date,"/")) {

			$temp1 = strpos($date,"/");
			$temp2 = strrpos($date,"/");
			$len = $temp2-$temp1-1;
			$day = substr($date,0,$temp1);
			$month = substr($date,$temp1+1,$len);
			$year = substr($date,$temp2+1);

			$date = $month."/".$day."/".$year;
		}

		$date = date("Y-m-d",strtotime("$date"));
		return $date;
	}

}

function dateProcess($date) {

	if (defined("DATE_FORMAT")) $layout = DATE_FORMAT;
	else $layout = "mm/dd/yyyy";

	$layoutArray = explode("/",$layout);
	
	$monthKey = array_search("mm",$layoutArray);
	$dayKey = array_search("dd",$layoutArray);
	$yearKey = array_search("yyyy",$layoutArray);

	if (strpos($date,"/")) $sep = "/";
	else $sep = "-";

	$dateArray = explode($sep,$date);
	
	$month = $dateArray[$monthKey];
	$day = $dateArray[$dayKey];
	$year = $dateArray[$yearKey];

	return $year."-".$month."-".$day;	

}

function date_view($date,$format = "slash") {

	if (defined("DATE_FORMAT")) $layout = DATE_FORMAT;
	else $layout = "mm/dd/yyyy";

	$layout = str_replace("mm","m",$layout);
	$layout = str_replace("dd","d",$layout);
	$layout = str_replace("yyyy","Y",$layout);

	if ($format=="slash" && $date) {

		$dateArray = explode("-",$date);

		$date = date("$layout",mktime(0,0,0,$dateArray[1],$dateArray[2],$dateArray[0]));

	}
	else if ($format=="space" && $date) {

		$dateArray = explode("-",$date);

		$date = date("M d, Y",mktime(0,0,0,$dateArray[1],$dateArray[2],$dateArray[0]));

	}

	return $date;
}

function dateView($date) {

	$arr = date_time_view($date);
	
	$str = $arr[0]." "._AT." ".$arr[1];
	return $str;

}

function phoneProcess($num) {

    $num = eregi_replace("[^0-9]","",$num);

    return $num;

}


function phoneView($num) {

	$num = ereg_replace("[^0-9]","",$num);

	if ($num) {

		if (strlen($num)=="10") {

			$area = substr($num,0,3);
			$prefix = substr($num,3,3);
			$number = substr($num,6);

			return "(".$area.") ".$prefix."-".$number;

		} else {

			$prefix = substr($num,0,3);
			$number = substr($num,3);

			return $prefix."-".$number;

		}

	} else return false;
	
}


function formatPhone($num) {

	$num = trim($num);

	$len = strlen($num);

	if ($len==10) {

		$area = substr($num,0,3);
		$prefix = substr($num,3,3);
		$ident = substr($num,6,4);

		$number = "(".$area.") ".$prefix."-".$ident;

	} else {

		$prefix = substr($num,0,3);
		$ident = substr($num,3,4);

		$number = $prefix."-".$ident;
	
	}

	if ($number) return $number;
	else return false;


}

function priceView($num) {

  $num = "$".number_format($num);
  return $num;

}

// Takes string in currency format ($xxx,yyy.zz)
// and converts it into a floating point number
// so that it can be inserted into a db precision field.
function MoneyToFloat($zMoney) {

       /*--- Local Variables ---*/

        $laundermoney = null;
        $dollars = null;
        $cents = null;

       /*--- Start of Processing ---*/

        $zMoney = str_replace("$","",$zMoney);

        if(strrchr($zMoney,",") )
        {
            $gMoney = str_replace(",","",$zMoney); 
            if( strrchr($gMoney,".") )
            {
                $dollars = strtok($gMoney,".");
                $cents= strtok(".");
            }
            else
                $dollars = $gMoney;
        }
        else if( strrchr($zMoney,".") )
        {
            $dollars = strtok($zMoney,".");
            $cents= strtok(".");
        }
        else
        {
            $dollars = $zMoney;
            $cents = 0;
        }    

        $laundermoney = $dollars.".".$cents;

        (float)$laundermoney = $laundermoney;
 
        return($laundermoney);
}

function formatName($e) {

  if ($e["cb_first_name"]) {

    if ($e["cb_last_name"] == $e["last_name"] || !$e["cb_last_name"])
      $name = $e["first_name"]." & ".$e["cb_first_name"]." ".$e["last_name"];
    else
      $name = $e["first_name"]." ".$e["last_name"]." & ".$e["cb_first_name"]." ".$e["cb_last_name"];

  }
  else $name = $e["first_name"]." ".$e["last_name"];

  return $name;

}
/***********************************************************
************************************************************/
function organizeName($conn,$str) {

    $ret = array();
    $str = trim($str);
   
    //if it has a comma, assume it's ln,fn
    if (strstr($str,",")!=NULL) {
   
        $str = str_replace(",","",$str);
        $arr = explode(" ",$str);
        $num = count($arr);

        if ($num > 2) {
            $ret["ln"] = $arr[0];
            $ret["fn"] = $arr[1];
            $ret["mn"] = $arr[2];
        }
        else {
            $ret["ln"] = $arr[0];
            $ret["fn"] = $arr[1];
        }

    }
    else {

        $arr = explode(" ",$str);
        $num = count($arr);

        if ($num==1) {
            $ret["ln"] = $arr[0];
        }
        else if ($num > 2) {
            $ret["ln"] = $arr[2];
            $ret["fn"] = $arr[0];
            $ret["mn"] = $arr[1];
        }
        else {
            $ret["ln"] = $arr[1];
            $ret["fn"] = $arr[0];
        }

    }

    return $ret;

}
/***********************************************************
************************************************************/
function formatAddr($e) {

  $str = null;
  if ($e["address"]) $str .= $e["address"]."<br>";

  if ($e["city"] || $e["state"] || $e["zip"]) {
    if ($e["city"]) $str .= $e["city"].", ";
    if ($e["state"]) $str .= $e["state"]." ";
    if ($e["zip"]) $str .= $e["zip"];
    $str .= "<br>";
  }

  return $str;

}
/*********************************************************
*********************************************************/
function getmicrotime() {

    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);

}
/*********************************************************
//this is a smart addslashes function.  It will determine if magic_quotes_gpc
//is on.  If it is not on, it will addslashes to the string.  You will only
//want to use this on variables directly submitted from a form.
*********************************************************/
function smartslashes($string) {

    $int = get_magic_quotes_gpc();
   
    //magic_quotes is on, return the string as is
    if ($int=="1")  return $string;

    //mq is off, escape the string and return it
    else return addslashes($string);

}
/*********************************************************
*********************************************************/
function delcheck ($string) {

/*  This function checks to see if the string has anything bad that could
erase more than it is supposed to */

    if (stristr ($string,"*")) return false;
    else if (stristr ($string,"?")) return false;
    else if (stristr ($string,"%")) return false;
    else if (stristr ($string,"!")) return false;
    else if (stristr ($string,"#")) return false;
    else if (stristr ($string,"@")) return false;
    else return true;
}


/*********************************************************
  charConv	
  character encoding conversion.uses iconv
  if available.If not, returns the string
  unaltered
*********************************************************/
function charConv($string,$in,$out) {

  $str = null;

  //make them both lowercase
  $in = strtolower($in);
  $out = strtolower($out);

  //sanity checking
  if (!$in || !$out) return $string;

  if ($in==$out) return $string;

  //return string if we don't have this function
  if (!function_exists("iconv")) return $string;

  //this tells php to ignore characters it doesn't know
  $out .= "//IGNORE";

  return iconv($in,$out,$string);

}
