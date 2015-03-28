<?php
$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');
$selected = mysql_select_db($dbname,$conn);
?>