<?php

// Database information
$db_name = '';
$db_host = '';
$db_user = '';
$db_pass = '';

// Output performance info or not
$SILENCE = /**/ TRUE /*/ FALSE /**/;
//            ^
// Add or remove a forward-slash at this position to switch between true/false

// Save cached pages in this dir
$CACHE = "cache/";

// Script libraries locatoin
$SYS = "sys/";

// Length a 100% bar would have (in px)
$multiplier = 300;

// Default timespan for statistics (use MySQL date format)
$interval = "31 day"; // half a year

// Translations for OS build numbers
$builds = array(
  "30"	       => "XP",
  "80"	       => "Vista",
  "6.0.6000.1" => "Vista",
  "6.0.6001.1" => "Vista SP1",
  "6.0.6002.1" => "Vista SP2",
  "6.0.6001.0" => "Server 2008",
  "5.2.3790.0" => "Server 2003",
  "5.1.2600.1" => "XP SP 2",
  "5.2.3790.1" => "XP Pro 64",
  "6.1.7000.1" => "7 Beta",
);

// Translations for db columns
$columns = array(
  "compiled" 	=> "version/snapshot",
  "osver"	=> "OS",
  "qt"		=> "Qt",
  "insys"	=> "input system",
  "outsys"	=> "output system",
  "is64"	=> "OS precision",
  "lcd"		=> "LCD",
);

// Piwik code for inclusion in the sites
$piwik = <<<MBL
<!-- Piwik -->
<script type="text/javascript">
var pkBaseURL = (("https:" == document.location.protocol) ? "https://apps.sourceforge.net/piwik/mumble/" : "http://apps.sourceforge.net/piwik/mumble/");
document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
</script><script type="text/javascript">
piwik_action_name = '';
piwik_idsite = 2;
piwik_url = pkBaseURL + "piwik.php";
piwik_log(piwik_action_name, piwik_idsite, piwik_url);
</script>
<object><noscript><p><img src="http://apps.sourceforge.net/piwik/mumble/piwik.php?idsite=2" alt="piwik"/></p></noscript></object>
<!-- End Piwik Tag -->
MBL;
?>
