<?php
require('config_app.php');
$except= $_GET['except'];
 
$sql="SELECT *
FROM (SELECT
      	tb_payments.date,
        DATE_FORMAT(tb_payments.date,'%M %d,%Y') AS date1,
        COUNT(tb_payments.payment) AS customers,
        CONCAT(FORMAT(SUM(tb_payments.total), 2)) AS amount
        FROM tb_payments
      	WHERE tb_payments.date NOT IN ('$except')
     	GROUP BY tb_payments.date) AS A
JOIN (SELECT
      SUM(tb_cart.quantity) AS items,
      DATE_FORMAT(tb_cart.date,'%M %d,%Y') AS date1
      FROM tb_cart
      GROUP BY tb_cart.date) AS B
ON A.date1=B.date1
ORDER BY A.date DESC";

$result = mysqli_query($con,$sql);

while(($row = mysqli_fetch_assoc($result)) == true){
	$data[]=$row;
}
echo json_encode($data);
?>