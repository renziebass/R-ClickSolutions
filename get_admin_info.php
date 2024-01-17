<?php
require('config_app.php');

$userid  = $_GET['userid'];
 
$sql = "SELECT
CONCAT(tb_users.last_name,',',tb_users.first_name,' ',tb_users.middle_name) AS fullname,
tb_accounts.acc_type
FROM tb_accounts
JOIN tb_users ON tb_accounts.userid=tb_users.userid
WHERE tb_accounts.userid = '$userid'";

 
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