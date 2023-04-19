<?php
  define('HOST','https://auth-db445.hstgr.io/');
  define('USER','u186319490_admin123');
  define('PASS','Kg9182022');
  define('DB','u186319490_kg_db');s

 
$con = mysqli_connect(HOST,USER,PASS,DB);
$userid  = $_GET['userid'];
 
$sql = "SELECT
CONCAT(tb_admins.last_name,',',tb_admins.first_name,' ',tb_admins.middle_name) AS fullname,
tb_accounts.acc_type
FROM tb_admins
JOIN tb_accounts ON tb_admins.userid=tb_accounts.userid
WHERE tb_admins.userid = '$userid'";

 
$res = mysqli_query($con,$sql);
 
$result = array();
 
while($row = mysqli_fetch_array($res)){
array_push($result,
array('fullname'=>$row[0]),
array('acc_type'=>$row[1]));
}
 
echo json_encode(array("result"=>$result));
 
mysqli_close($con);
 
?>