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
    COUNT(tb_cart.transaction_id) as items,
	SUM(tb_cart.price*tb_cart.quantity) AS amount
     FROM tb_cart
     WHERE tb_cart.date NOT IN ('$except')
     GROUP BY tb_cart.date) AS B
ON A.date=B.date
GROUP BY B.date
ORDER BY B.date DESC";

$result = mysqli_query($con,$sql);

while(($row = mysqli_fetch_assoc($result)) == true){
	$data[]=$row;
}
echo json_encode($data);
?>