<?php

// For further script execution time evaluation, save the time
$start = microtime(true);

require_once 'config.php';
require_once $SYS.'lib.db.mysql.php';
require_once $SYS.'lib.misc.php';


// Save error messages in this array
$err = array();



// Set a default page to view
if(empty($_REQUEST['page']))
    $page = 'main';
else
    $page = $_REQUEST['page'];



// Initialize database class as $db
$db = new db($db_name, $db_host, $db_user, $db_pass);
unset($db_user); unset($db_pass); unset($db_name);



// Check if requested file exists, include and execute the script from $_REQUEST
if(in_array($page, $PAGES) && file_exists($page.".php")) {

    // If a cached version exists and it's younger than a day, use it
    if(count($_GET) < 2 && file_exists($CACHE.$page.".htm") &&
       filemtime($CACHE.$page.".htm") > mktime(date("H"), date("i"), date("s"), date("n"), date("j")-1))
        require($CACHE.$page.".htm");
    
    else // Otherwise, generate/update cache
        require($page.".php");

} else {
    require("404.php");
}

// Evaluate execution times
if(!$SILENCE) {

    if(!is_writable($CACHE))
        echo "Your cache directory is not writable by the webserver.
              Caching may not work.";

    $end = microtime(true);
    $run = $end - $start;

    $sql = 0;
    $log = $db->getLog();
    foreach($log as $row) $sql += $row['duration'];

    $runstats = sprintf('<div>script exec time: <b>%.3f</b> + mysql time:
                         <b>%.3f</b> (total: <b>%.3f</b> seconds)',
                         $run-$sql, $sql, $run);
    $runstats .= ", ".count($log)." mysql quer".((count($log) == 1) ? "y" : "ies");
    $runstats .= '<br />user agent: '. $_SERVER['HTTP_USER_AGENT']."</div><br />";
    
    echo "<br /><br />".$runstats;

}

?>
