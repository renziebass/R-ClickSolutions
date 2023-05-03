<?php
require('config_app.php');
$except= $_GET['except'];
 
$sql="SELECT * 
FROM (SELECT DATE_FORMAT(tb_payments.date,'%M %d,%Y') AS date1,
        COUNT(tb_payments.payment) AS customers,
      CONCAT(FORMAT(SUM(tb_payments.total), 2)) AS amount
        FROM tb_payments
        WHERE tb_payments.date NOT IN ('$except')
     	GROUP BY tb_payments.date) AS A
JOIN (SELECT DATE_FORMAT(tb_payments.date,'%M %d,%Y') AS date1,
     SUM(tb_cart.quantity) AS items
     FROM tb_payments
     JOIN tb_cart ON tb_payments.id=tb_cart.transaction_id
     GROUP BY tb_payments.date DESC) AS B
ON A.date1=B.date1
GROUP BY A.date1 DESC";

$result = mysqli_query($con,$sql);

while(($row = mysqli_fetch_assoc($result)) == true){
	$data[]=$row;
}
echo json_encode($data);
?>