<?php
/******************************************************************
	This file contains all my specialty array functions

	Modified 04-18-2005.
		Added arrayStringSearch function

	Modified 04-20-2005
		added reduceArray function

	Modified 09-12-2005
		Modified reduceArray to handle associative arrays
		
******************************************************************/

function cs_in_array($string,$object) {

	$string = strtolower($string);

	$returnValue = null;

	for ($row=0;$row<count($object);$row++) {

		$tempValue = strtolower($object[$row]);

		if ($tempValue == $string) $returnValue = $tempValue;  break;
	}

	if ($returnValue) return $returnValue;
	else return false;

}


function multi_array_unique($array1,$array2) {

	$newArray1 = array();
	$newArray2 = array();

	for ($row=0;$row<count($array1);$row++) {

		if (!in_array($array1[$row],$newArray1)) {

			$newArray1[] = $array1[$row];
			$newArray2[] = $array2[$row];

		}

	}

	return array($newArray1,$newArray2);

}



//This function compares two arrays, and returns an array w/ the differences
function array_difference($array1,$array2) {
	$newrow=0;
	$final_array = array();
	for ($row=0;$row<count($array1);$row++) {
			if (!in_array($array1[$row],$array2))  {
				$final_array[$newrow]=$array1[$row];
				$newrow++;
			}
	}
	return $final_array;
}


//creates an array from an array w/ the specified keys
function createKeyArray($arr,$keys) {


	$new = array();

	$num = count($keys);

	for ($row=0;$row<$num;$row++) {

		$key = $keys[$row];

		$new[] = $arr[$key];



	}

	return $new;

}

//creates an array from an array w/ the specified keys
function createAssocKeyArray($arr,$keys) {

	$new = array();

	$num = count($keys);

	for ($row=0;$row<$num;$row++) {

		$key = $keys[$row];
		if (!is_numeric($key)) $new[$key] = $arr[$key];

	}

	return $new;

}


/*************************************************
	this function returns all information
	for an id from an array, similar to the way
	single_result returns info from a database
*************************************************/

function returnObjectInfo($arr,$id) {

	if (!is_array($arr)) return false;

	$key = array_search($id,$arr["id"]);
	
	$fields = array_keys($arr);
	$num = count($fields);
	
	$val = array();
	
	for ($row=0;$row<$num;$row++) {
	
		$field = $fields[$row];
		$val[$field] = $arr[$field][$key];

	}

	return $val;

}

//creates an array out of one array containing
//keys, and another containing array values.
//should be replaced by array_combine in php5

if (!function_exists("array_combine")) {

	function array_combine($keyArray,$valArray) {

		$arr = array();
		$num = count($keyArray);

		for ($row=0;$row<$num;$row++) {
			$key = $keyArray[$row];
			$arr[] = $valArray[$key];
		}

		return $arr;

	}
}

function arrayCombine($keyArray,$valArray) {

	$arr = array();
	$num = count($keyArray);

	for ($row=0;$row<$num;$row++) {
		$key = $keyArray[$row];
		$arr[] = $valArray[$key];
	}

	return $arr;

}

function transposeArray($arr) {

	foreach ($arr AS $keymaster => $value) {
                
		foreach($value AS $key => $element) {
			$returnArray[$key][$keymaster] = $element;
       		}                 
	}

	return $returnArray;                                                        
}

/******************************************************
	arrayMultiSort
	-Sort an associative array by a key in that 
	 array
	-modArray is the array we are sorting
	-sort is the key we sort by
	-dir is either ASC or DESC, and is optional.
	 it's the direction of our sort
******************************************************/

function arrayMultiSort($modArray,$sort,$dir="ASC") {

	$newArray = array();

	//parameter sanity checking
	if (!$sort) return false;
	if (!is_array($modArray)) return false;

	//sort by our sort field
	$arr = $modArray[$sort];

	if (!is_array($arr)) return false;

	//get the keys in the array.  We will use these as field names later
	$fields = array_keys($modArray);

	//we will assume the first key in the array is the index key.  That means
	//there should be one of these in every array entry.  This way, if some of
	//our sort fields are empty, we can pad the sort array to the required length
	$idx = $fields[0];
	$realSize = count($modArray[$idx]);

	//sort our array 
	if ($dir=="DESC") arsort($arr);
	else asort($arr);

	//the size of our sorted field
	$sortSize = count($arr);

	//if our sort size is smaller than our actual size, pad to the real size so we don't lose any modules.
	//the value we pad it with should ensure it gets placed last whether it's a numeric or alpha sort
	if ($sortSize < $realSize) {

		//pad with something that should put the entry at the end whether it's number or alpha
		$pad = "zzzzzzzz";

		//loop through the array and pad the empty values.  We can't use array_pad because
		//we will lose our key order
		for ($row=0;$row<$realSize;$row++) if (!$arr[$row]) $arr[$row] = $pad;

	}

	//get all the keys from our sorted array.  We need to loop by these to maintain
	//index association with the rest of the array
	$sortArray = array_keys($arr);
	$fieldCount = count($fields);

	//resort our original array.  Map the new field->key to it's original index	
	foreach ($sortArray AS $key) {

		for ($i=0;$i<$fieldCount;$i++) {

			$field = $fields[$i];
			$newArray[$field][] = $modArray[$field][$key];
					
		}

	}

	return $newArray;

}

/********************************************************
	arrayStringSearch:
	
	this function checks all values in an array
	to see if they contain a string.  The value
	does not have to = the string, just contain
	the string.  Setting icase = to a value, will
	make the search case insensitive.  This function
	returns the key of the first value that matches 
	the string.  If no matches are found, it returns false
********************************************************/
function arrayStringSearch($str,$arr,$icase = null) {

	if (!is_array($arr)) return false;

	//we use array_keys so this works on associative arrays as well
	$keys = array_keys($arr);
	$ret = array();
	
	//loop through and search for a matching string	
	foreach ($keys AS $key) {

		if ($icase) $func = "stripos";
		else $func = "strpos";
		
		if ($func($arr[$key],$str)!==FALSE) return $key;

	}

	//if we get to here, return false;
	return false;

}

//shrinks an array down by removing all null values in the array
function reduceArray($arr) {

	$newArr = array();
	$keys = array_keys($arr);

	foreach ($keys AS $key) {

		if ($arr[$key]!=NULL) {
			if (is_numeric($key)) $newArr[] = $arr[$key];
			else $newArr[$key] = $arr[$key];
		}

	}

	return $newArr;

}
