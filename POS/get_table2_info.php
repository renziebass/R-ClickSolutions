<?php
require('config_app.php');
 
$sql="SELECT *
FROM (SELECT
        tb_transactions.id,
        tb_transactions.time,
        tb_transactions.date
        FROM tb_transactions WHERE tb_transactions.status='paid') AS A
JOIN (SELECT
        tb_payments.id,
        SUM(tb_cart.quantity) as items,
        CONCAT(FORMAT(SUM(tb_cart.price*tb_cart.quantity), 2)) AS total,
        CONCAT(FORMAT(tb_payments.payment, 2)) AS payment,
          tb_payments.change1
        FROM tb_payments
      JOIN tb_cart ON tb_payments.id=tb_cart.transaction_id
      WHERE tb_payments.date='".$_GET['date']."'
        GROUP BY tb_payments.id) AS B
ON A.id=B.id
GROUP BY A.id
ORDER BY A.time DESC";

$result = mysqli_query($con,$sql);

while(($row = mysqli_fetch_assoc($result)) == true){
	$data[]=$row;
}
echo json_encode($data);
?>