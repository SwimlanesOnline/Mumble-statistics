<?php

/**
 * Calculate the percentage a certain item takes up within its group
 *
 * @global int $multiplier The multiplier defining the width of the diagrams
 * @param array $ar The data to be worked with
 * @return int Status code, 1 if successfull, NULL if not
 */
function share(&$ar)
{
  if(count($ar)>0)
  {
    global $multiplier;
    
    if($multiplier == NULL)
      $multiplier = 100;
    
    for($i=0;$i<count($ar);$i++)
      $sum += $ar[$i]['count'];
    
    if($sum == 0)
      return 0;
    
    for($i=0;$i<count($ar);$i++)
      $ar[$i]['share'] = (int)round($ar[$i]['count']/$sum*$multiplier);
    
    return 1;
  }
  else return NULL;
}


/**
 * Create the table rows for displaying the statistics
 *
 * @global int $multiplier The multiplier defining the width of the diagrams
 * @param array $ar Containing the data which needs to be formatted
 * @param string $mode If the data needs to get special treatment, this
 *                     indicates it
 * @return string The assembled table, organizing the data
 */
function mkTbl(&$ar, $mode = NULL)
{

  global $multiplier;

  if(is_numeric($_GET['interval']))
    $interval = "&interval=".$_GET['interval'];

  $tbl = "<table>";
  
  for($i=0;$i<count($ar);$i++)
  {
  
    // If we arrived at the osvers, replace the buildNOs by meaningful OS names
    if($ar[$i]['osver'])
      $osver = replaceOsVersion($ar[$i]);
    
    // If we arrived at the OS precision, replace the bool by 32/64bit
    if($ar[$i]['is64'] == '1')
      $precision = "64bit";
    elseif($ar[$i]['is64'] == '0')
      $precision = "32bit";
    else
      $precision = NULL;
    
    if($ar[$i]['lcd'] == '1')
      $lcd = "Yes";
    elseif($ar[$i]['lcd'] == '0')
      $lcd = "No";
    else
      $lcd = NULL;
    
    // Get the real percentage for the bar image's alt tag
    if($multiplier != 100 && $multiplier != 0)
      $alt = round($ar[$i]['share']/$multiplier*100, 2);
    
    $row = "<tr>
	    <td>
	      <a href=\"index.php?page=main&item="
	      .($mode ? $mode : urlencode(key(current($ar)))).
	      "&member="
	      .($mode == "osver" ? urlencode($ar[$i]['osver']) :
                urlencode($ar[$i][key(current($ar))])).$interval."\">"
	      .(($ar[$i]['is64'] != NULL || $ar[$i]['lcd'] != NULL) ?
                $precision.$lcd : $ar[$i][key(current($ar))])." ".$osver.
	      "</a>
	    </td>
	    <td>
	      <img src=\"blue.png\" valign=\"middle\" height=\"14\" alt=\"".
              $alt."%\" title=\"".$alt."%\" width=\"".$ar[$i]['share'].
              "\" /> <b>".number_format($ar[$i]['count'])."</b>
	    </td>
	  </tr>";

    // Append rXX snapshots, prepend official releases
    if($ar[$i]['compiled'])
    {
      if($ar[$i]['compiled'][0] == "r")
	$tbl .= $row;
      else
	$tbl = $row.$tbl;
    }
    else
      $tbl .= $row;
    
  }
  
  $tbl = "<table>".$tbl;
  $tbl .= "</table>";
  
  return $tbl;
  
}


/**
 * In arrays or strings, replace the string from the db's "osver" col by
 * human-readable names
 *
 * @global array $builds Associating OS build numbers with readable OS versions
 * @param array $osvar Array containing OS build numbers as given from the db
 * @return array The os array with build numbers replaced with os versions
 */
function replaceOsVersion($osvar)
{

  global $builds;
  
  if(is_array($osvar))
    $osver = ($builds[$osvar['osver']] ? $builds[$osvar['osver']] : $osvar['osver']);
  else
    $osver = ($builds[$osvar] ? $builds[$osvar] : $osvar);
  
  return $osver;
}


/**
 * If the $err array has been filled with error(s), assemble a list to output
 * it/them
 *
 * @global array $err The global array containing error messages
 * @return string a HTML representation of the errors
 */
function displayErrors()
{
  global $err;
  
  if($err)
  {
    $errlist = "<p style=\"color:red;\">";
    
    for($i=0;$i<count($err);$i++)
      $errlist .= $err[$i]."<br />";
    
    $errlist .= "</p>";
    
    return $errlist;
  }
  else
    return NULL;
}

/**
 * Save the output buffer to a cache file
 *
 * @global string $CACHE The cache location
 * @param string $page The page name to cache
 */
function mkCache($page) {
    global $CACHE;
    
    $fp = fopen($CACHE.$page.".htm", 'w');
    fwrite($fp, ob_get_contents());
    fclose($fp);
}

?>