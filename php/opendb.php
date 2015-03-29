<?php

include 'conf.php';

$conn_string = "host=$dbhost port=$dbport dbname=$dbname user=$dbuser password=$dbpass";
$dbconn = pg_connect($conn_string);

?>