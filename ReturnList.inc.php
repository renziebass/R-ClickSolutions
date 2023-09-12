<?php

if (!empty($_POST['search'])) {


    $Search_Query = $conn->real_escape_string($_POST['search']);


    $query = "SELECT distinct(tb_products.category) FROM tb_products
    WHERE tb_products.category LIKE '%{$Search_Query}%'; ";
    $result = $conn->query($query) or die($conn->error);

    
    $html ='<ul class="list-group" style="margin-top:-15px;">';

    if (mysqli_num_rows($result) > 0) {

        while ($row = mysqli_fetch_assoc($result)) {

            $html .= "<li class='list-group-item'><a>" . $row['category'] . "</a></li>";
            
        }
        
    } else {
          $html .= '<li class="list-group-item">Sorry! No record found</li>';
    }

    $html .= "</ul>";
    echo $html;
} 

$conn->close();