<?php
$sql1="DELETE FROM tb_transactions WHERE tb_transactions.id='" .$_GET['id']. "'";
$sql2="DELETE FROM tb_payments
WHERE tb_payments.id='" .$_GET['id']. "'";

if (($db->query($sql1)) && ($db->query($sql2)) === TRUE) {
    echo "Transaction Void Successfully";
  } else {
    echo "Error Voiding Transaction: " . $db->error;
  }
?>