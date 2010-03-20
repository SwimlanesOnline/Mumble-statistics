<?php

$noCache = FALSE;
// If a cached version is available, output it to the user. Save the one which
// is generated here as the new cache and deliver it next time it's called.
if(file_exists($CACHE.$page.".htm")) {

    if(count($_GET) < 2) {
        $fp = fopen($CACHE.$page.".htm", 'r');
        echo fread($fp, filesize($CACHE.$page.".htm"));
        ob_end_flush();
    }

} else {
    $noCache = TRUE;
}

// Save all outputs from now in the output buffer, so we can save it to the
// cache file at the end of the script.
ob_start();

// Check if we are included by index.php, stop script otherwise
$included = FALSE;

foreach (get_included_files() as $filename) {

  if(basename($filename) == $page.".php")
    $included = TRUE;

}

if(!$included)
  exit("Script can't run on its own.");

// Compose the WHERE clause of the queries if we want to filter
// and replace OS numbers by meaningful names for the header on the website
//
if(isset($_GET['item']) && isset($_GET['member']))
{

  $_GET['item'] = urldecode($_GET['item']);
  $_GET['member'] = urldecode($_GET['member']);

  $where = " AND ".mysql_real_escape_string($_GET['item'])." = '".
            mysql_real_escape_string($_GET['member'])."'";
  
  if($_GET['item'] == "osver")
    $osver = replaceOsVersion($_GET['member']);
  
}

// Check if we want to narrow results on a certain interval
// (use config default otherwise)
if(isset($_GET['interval']))
{

  if(is_numeric($_GET['interval']))
  {
    $interval = $_GET['interval']." day";
    $timewhere = " AND seen >= NOW() - INTERVAL ".$interval;
  }
  else
  {
    $err[] = "You entered an invalid date interval. Falling back to default.";
    $timewhere = "";
  }

}
else
  $timewhere = "";
  
// Count total # of datasets - narrow down if filters are applied  
$query = "SELECT COUNT(*) FROM musage WHERE client = 1".$timewhere.$where;
$total = $db->fetch_atom($query);

// Get all the stats...
$query = "SELECT SUBSTRING(compiled FROM 1 FOR 7) as compiled,COUNT(compiled) as count FROM musage WHERE seen >= NOW() - INTERVAL ".$interval." AND client = 1 AND compiled NOT LIKE 'Compiled %' ".$where." GROUP BY compiled HAVING count > 4 ORDER BY compiled DESC";
$snapshot = $db->fetch_table($query);
share($snapshot);

$query = "SELECT os,osver,COUNT(osver) as count FROM musage WHERE seen >= NOW() - INTERVAL ".$interval." AND client = 1 ".$where." GROUP BY os,osver HAVING count > 4 ORDER BY count DESC"; 
$os = $db->fetch_table($query);
share($os);

$query = "SELECT os,COUNT(os) as count FROM musage WHERE seen >= NOW() - INTERVAL ".$interval." AND client = 1 ".$where." GROUP BY os ORDER BY count DESC"; 
$platform = $db->fetch_table($query);
share($platform);

$query = "SELECT qt,COUNT(qt) as count FROM musage WHERE seen >= NOW() - INTERVAL ".$interval." AND client = 1 ".$where." GROUP BY qt ORDER BY count DESC"; 
$qt = $db->fetch_table($query);
share($qt);

$query = "SELECT insys,COUNT(insys) as count FROM musage WHERE seen >= NOW() - INTERVAL ".$interval." AND client = 1 AND insys NOT LIKE 'HASH(0x%' ".$where." GROUP BY insys ORDER BY count DESC"; 
$insys = $db->fetch_table($query);
share($insys);

$query = "SELECT outsys,COUNT(outsys) as count FROM musage WHERE seen >= NOW() - INTERVAL ".$interval." AND client = 1  AND outsys NOT LIKE 'HASH(0x%' ".$where." GROUP BY outsys ORDER BY count DESC"; 
$outsys = $db->fetch_table($query);
share($outsys);

$query = "SELECT is64,COUNT(is64) as count FROM musage WHERE seen >= NOW() - INTERVAL ".$interval." AND client = 1 ".$where." GROUP BY is64 ORDER BY count DESC"; 
$is64 = $db->fetch_table($query);
share($is64);

$query = "SELECT country,COUNT(country) as count FROM musage WHERE seen >= NOW() - INTERVAL ".$interval." AND client = 1 ".$where." GROUP BY country HAVING count > 4 ORDER BY count DESC"; 
$geo = $db->fetch_table($query);
share($geo);

$query = "SELECT lcd,COUNT(lcd) as count FROM musage WHERE seen >= NOW() - INTERVAL ".$interval." AND client = 1 ".$where." GROUP BY lcd ORDER BY count DESC"; 
$lcd = $db->fetch_table($query);
share($lcd);

#echo var_dump($err);
?>

<html>
  <head>
    <link rel="stylesheet" type="text/css" media="screen" href="style.css" />
    
    <title>Usage statistics for Mumble - Raw data</title>
  </head>
<body>

  <div style="height:1.6em; border-bottom:0.1em solid black;">
  
    <a class="menu menu_shown">
      Raw data
    </a>
    
    &nbsp;

    <a class="menu menu_hidden menu_margin" href="mum.htm">
      Mumble User Map
    </a>
    
  </div>
  
<br />

  <div>
    <h1 style="float:left;">
      Usage statistics for <a href="http://mumble.sourceforge.net">Mumble</a>
      <?php
      if(isset($_GET["item"]) && isset($_GET["member"]))
      {
          echo 'filtered for systems using '.
          htmlspecialchars($columns[$_GET["item"]]).' '.
          ($_GET["item"] == 'osver' ?
                  htmlspecialchars($osver) : htmlspecialchars($_GET["member"]));
      }
      ?>
    </h1>

    <a style="float:right;" href="javascript:window.open('about.htm','about',width=200,height=300);window.focus();">What are these statistics?</a>
  </div>
  
  <?php
  // Assemble the date-interval links and carry along filters if some are activated
  if(isset($_GET['item']))
    $link = $_SERVER['PHP_SELF']."?page=main&item=".urlencode($_GET['item']).
      "&member=".urlencode($_GET['member'])."&interval=";
  else
    $link = $_SERVER['PHP_SELF']."?page=main&interval=";
  ?>
  
  <p style="clear:both;">
    Show stats up to... 
    <a href="<?php echo $link."1"; ?>">1 day</a> ::
    <a href="<?php echo $link."7"; ?>">1 week</a> ::
    <a href="<?php echo $link."31"; ?>">1 month</a> ::
    ago.
  </p>
  
  <strong>Based on <?php echo number_format($total); ?> users.</strong>
  <br />
  
  <?php 
  
  // If filters are applied, offer to remove them
  if( ( isset($_GET['item']) && isset($_GET['member']) ) || isset($_GET['interval']) )
    echo "<br /><a href=\"index.php?page=main\">Remove filter</a>";
  
  // If we have errors to show, do so
  echo displayErrors();
  
  ?>
  <div style="width:75em">
  <div style="float:left; display:inline;">
    <h2>Mumble version/snapshot</h2>
    <?php echo mkTbl($snapshot); ?>
    
    <h2>Operating System</h2>
    <?php echo mkTbl($os, "osver"); ?>
    
    <h2>Platform</h2>
    <?php echo mkTbl($platform, "os"); ?>
    
    <h2>Qt Version</h2>
    <?php echo mkTbl($qt); ?>
    
    <h2>Input System</h2>
    <?php echo mkTbl($insys); ?>
    
    <h2>Output System</h2>
    <?php echo mkTbl($outsys); ?>
  </div>
  
  <div style="float:right; display:inline;">
    <h2>Geo distribution</h2>
    <?php echo mkTbl($geo); ?>
      
    <h2>OS Type</h2>
    <?php echo mkTbl($is64); ?>
    
    <h2>LCD enabled</h2>
    <?php echo mkTbl($lcd); ?>
  </div>
  </div>
  
  <br style="clear:both" />
  <br />

  <?php
      if( (isset($_GET['item']) && isset($_GET['member']) ) ||
           isset($_GET['interval']) )
      {
        echo "<a href=\"index.php?page=main\">Remove filter</a>";
      }
  ?>
  
  <?php
  
  echo $piwik;

  if(count($_GET) < 2)
    echo '<br /><br />Generated '.date("F j, Y, H:i:s").' CET';

  ?>
  
</body>
</html>

<?php
    // Cache the current run, if it wasn't filtered
    if(count($_GET) < 2)
        mkCache($page);
    
    // Clean the output buffer, and ...
    if($noCache || count($_GET) > 1) // If no cache was available at site call
        // ... forward it to the browser
        ob_end_flush();
    else
        // ... don't forward it to the browser
        ob_end_clean();
?>