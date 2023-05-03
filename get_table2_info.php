<?php
require('config_app.php');
$except= $_GET['except'];
 
$sql="SELECT *
FROM (SELECT
        tb_payments.date,
        COUNT(tb_payments.id) AS customers
        FROM tb_transactions
        LEFT JOIN tb_payments ON tb_transactions.id=tb_payments.id
        WHERE tb_transactions.status='paid'
        AND tb_payments.date NOT IN ('$except')
        GROUP BY tb_transactions.date) AS A
JOIN (SELECT
      tb_cart.date,
      CONCAT(FORMAT(SUM(tb_cart.price*tb_cart.quantity), 2)) AS amount,
      DATE_FORMAT(tb_cart.date,'%M %d,%Y') AS date1,
    COUNT(tb_cart.transaction_id) as items
     FROM tb_transactions
     JOIN tb_cart ON tb_transactions.id=tb_cart.transaction_id
     WHERE tb_transactions.date NOT IN ('$except')
     AND tb_transactions.status='paid'
     GROUP BY tb_transactions.date) AS B
ON A.date=B.date
GROUP BY B.date DESC";

$result = mysqli_query($con,$sql);

while(($row = mysqli_fetch_assoc($result)) == true){
	$data[]=$row;
}
echo json_encode($data);
?>