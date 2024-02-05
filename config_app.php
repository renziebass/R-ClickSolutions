<?php
define('HOST','151.106.117.0');
define('USER','u186319490_admin123');
define('PASS','Kg9182022');
define('DB','u186319490_kg_db');

   
$db = mysqli_connect(HOST,USER,PASS,DB);
$con = mysqli_connect(HOST,USER,PASS,DB);

date_default_timezone_set("Asia/Manila");

/*
define('HOST','localhost');
define('USER','u186319490_admin123');
define('PASS','Kg9182022');
define('DB','u186319490_kg_db');

define('HOST','localhost');
define('USER','root');
define('PASS','');
define('DB','kg_db');
*/
?>