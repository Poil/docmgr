<?

/********************************************************************
	finder.inc.php
	
	this file contains functions for the browse and find
	modules
********************************************************************/

function createFindFunction($searchCount) {

  $functionBar = null;

  //searchCount, curPage, limitResults,
  if ($searchCount > 0) {
    $functionBar .= "&nbsp;&nbsp;";
    $functionBar .= "<a href=\"javascript:void(0);\" onClick=\"return moveObject();\">"._MOVE."</a>";
    $functionBar .= "&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;";

    if (!defined("RESTRICTED_DELETE") || bitset_compare(BITSET,ADMIN,null)) {
    	$functionBar .= "<a href=\"javascript:deleteObjects();\" >"._DELETE."</a>";
    	$functionBar .= "&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;";
    }
    
  }

  return $functionBar;

}

function createBrowseFunction($searchCount,$view_parent) {

	//can the user even create objects?
	if (!bitset_compare(BITSET,INSERT_OBJECTS,ADMIN)) return false;

	//make sure we can add objects at the root level
	if (!$view_parent && defined("ADMIN_ROOTLEVEL") && !bitset_compare(BITSET,ADMIN,null)) return false;

	//make sure the user has permissions to add objects here	
	if (	$view_parent &&
		!bitset_compare(CUSTOM_BITSET,OBJ_EDIT,OBJ_ADMIN) &&
		!bitset_compare(CUSTOM_BITSET,OBJ_MANAGE,OBJ_ADMIN)) 	return false;

	$functionBar .= createObjectDropdown($view_parent);

	//only show this if we have file results
	if ($searchCount > 0) {
		
		$functionBar .= "&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;";
		$functionBar .= "<a href=\"javascript:void(0);\" onClick=\"return moveObject();\">"._MOVE."</a>";

		if (!defined("RESTRICTED_DELETE") || bitset_compare(BITSET,ADMIN,null)) {
			$functionBar .= "&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;";
			$functionBar .= "<a href=\"javascript:deleteObjects();\">"._DELETE."</a>";
		}

		$functionBar .= "&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;";

        }

        return $functionBar;

}

function createFindToolbar($searchString,$searchCount,$curPage,$timeCount) {

  $limitResults = RESULTS_PER_PAGE;

  if ($limitResults!==NULL) $numPages = $searchCount / $limitResults;
  else $numPages = 1;

  if (!$curPage) $curPage = 1;

  $first = (($curPage - 1) * $limitResults) + 1;
  $second = $curPage * $limitResults;

  $toolBar = "<b>"._DISPLAYING.": </b>";
  $toolBar .= _RESULTS." $first - $second "._OF." ".$searchCount." "._FOR." \"".stripsan($searchString)."\".";
  $toolBar .= "&nbsp;&nbsp;"._SEARCH_TOOK." ".$timeCount." "._SECONDS.". ";
  $toolBar .= "<a href=\"index.php?module=find&searchAgain=yes\" class=main>"._SEARCH_AGAIN."</a>.";

  return $toolBar;

}

function createBrowseToolbar($searchCount,$curPage) {

  //get out if disabled
  if (!defined("PAGE_BROWSE_RESULTS")) return false;

  $limitResults = RESULTS_PER_PAGE;
  $numPages = $searchCount / $limitResults;

  if (!$curPage) $curPage = 1;

  $first = (($curPage - 1) * $limitResults) + 1;
  $second = $curPage * $limitResults;

  //show result string correctly when showing less than our increment
  if ($second > $searchCount) $second = $searchCount;

  $toolBar = _RESULTS." $first - $second "._OF." ".$searchCount;

  return $toolBar;

}

//creates pages and arrows to navigate paginated results in the finder
function createFinderNav($searchCount,$curPage) {

  //don't show anything if there's nothing here
  if (!$searchCount) return false;

  //init the number of pages we'll have and what page we're starting on
  $numPages = $searchCount / RESULTS_PER_PAGE;
  if (!$curPage) $curPage = 1;
  
  $first = (($curPage - 1) * RESULTS_PER_PAGE) + 1;
  $second = $curPage * RESULTS_PER_PAGE;

  //show result string correctly when showing less than our increment
  if ($second > $searchCount) $second = $searchCount;

  if ($second>$searchCount) {
    $num = $second - $searchCount;
    $diff = RESULTS_PER_PAGE - $num;
    $second = $first + $diff - 1;
  }

  if (strstr($numPages,".")) $numPages = intVal($numPages) + 1;

  //if we have more pages than the config limit
  if ($numPages > PAGE_RESULT_LIMIT) {

    //start from the middle
    $pageHalf = intVal(PAGE_RESULT_LIMIT / 2);

    //figure out which page we are starting on and ending on
    if ($curPage>$pageHalf) $pageBegin = $curPage - $pageHalf;
    else $pageBegin = 1;

    $pageEnd = $curPage + $pageHalf - 1;

    //show num count on the last page
    if ($pageEnd>$numPages) $pageEnd = $numPages;

    //only show up to the limit if we have less pages than our increment count
    if ($pageEnd < PAGE_RESULT_LIMIT) $pageEnd = PAGE_RESULT_LIMIT;

  //if we have less pages than our limit
  } else {

    $pageBegin = 1;
    $pageEnd = $numPages;

  }

  $pageBar = null;
  $nextPage = $curPage + 1;	//jump to the next page
  $prevPage = $curPage - 1;	//jump to the previous page

  //setup the previous/first page arrows
  if ($curPage>1 && $numPages > 1) {
    $pageBar .= " 	<a href=\"javascript:jumpPage('1');\">
                  	<img src=\"".THEME_PATH."/images/active_firstpage.gif\" border=0>
                  	</a>
                  	&nbsp;
                  	<a href=\"javascript:jumpPage('".$prevPage."');\">
                  	<img src=\"".THEME_PATH."/images/active_prevpage.gif\" border=0>
                  	</a>
                  	&nbsp;
                ";

  } else {
    $pageBar .= " <img src=\"".THEME_PATH."/images/inactive_firstpage.gif\" border=0>
                  &nbsp;
                  <img src=\"".THEME_PATH."/images/inactive_prevpage.gif\" border=0>
                  &nbsp;
                  ";
  }

  //create the toolbar for jumping from page to page
  for ($row=$pageBegin;$row<=$pageEnd;$row++) {

    if ($row==$curPage) $pageBar .= "&nbsp;<b>".$row."</b>&nbsp;";
    else $pageBar .= "&nbsp;<a href=\"javascript:jumpPage('".$row."');\" class=main>".$row."</a>&nbsp;";

  }

  $pageBar .= "&nbsp;";

  //setup the next/last page arrows
  if ($numPages > 1 && $curPage < $numPages) {
    $pageBar .= " <a href=\"javascript:jumpPage('".$nextPage."');\">
                  <img src=\"".THEME_PATH."/images/active_nextpage.gif\" border=0>
                  </a>
                  &nbsp;
                  <a href=\"javascript:jumpPage('".$numPages."');\">
                  <img src=\"".THEME_PATH."/images/active_lastpage.gif\" border=0>
                  </a>
                  &nbsp;
                  ";
  } else {
    $pageBar .= "&nbsp;<img src=\"".THEME_PATH."/images/inactive_nextpage.gif\" border=0>
                  &nbsp;
                  <img src=\"".THEME_PATH."/images/inactive_lastpage.gif\" border=0>
                  &nbsp;
                  ";
  }

  return $pageBar;
		
}

