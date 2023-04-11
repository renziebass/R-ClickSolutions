<?php
include('user_session.php');
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

    <title>DASHBOARD</title>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load("current", {packages:["corechart","bar"]});
      google.charts.setOnLoadCallback(drawChart1);

      function drawChart1() {
        ///chart1
        var data1 = google.visualization.arrayToDataTable([
          ['Task', 'Hours per Day'],
          ['PAID',     15232],
          ['UNPAID',      27030],
        ]);
        var options1 = {
          title: 'CURRENT SALES : 9,235.20',
        };
        var chart = new google.visualization.PieChart(document.getElementById('chart1'));
        chart.draw(data1, options1);
        ///chart2
        var data2 = google.visualization.arrayToDataTable([
          ['Task', 'Hours per Day'],
          ['PAID',     13382.64],
          ['UNPAID',      1800.00],
        ]);
        var options2 = {
          title: 'CUSTOMERS: 19',
        };
        var chart = new google.visualization.PieChart(document.getElementById('chart2'));
        chart.draw(data2, options2);
        ///chart3
      
        ///chart4
        var data4 = google.visualization.arrayToDataTable([
          ['Year', 'Sales', 'Expenses'],
          ['February 11', 500, 90],
          ['February 10', 670, 120],
          ['February 9', 926.23, 90],
          ['February 8', 1139.00, 40],
          ['February 7', 200, 0],
        ]);
        var options4 = {
          chart: {
            title: 'Previous Days Financial Report',
            subtitle: 'Sales & Expenses',
          }
        };
        var chart = new google.charts.Bar(document.getElementById('chart7'));
        chart.draw(data4, google.charts.Bar.convertOptions(options4));
        //chart5
        var data5 = google.visualization.arrayToDataTable([
          ['Year', 'Sales', 'Expenses'],
          ['FEBRUARY 2023', 1000, 400],
          ['JANUARY 2023', 1170, 460],
          ['DECEMBER 2022', 660, 1120],
          ['NOVEMBER 2022', 1030, 540]
        ]);
        var options5 = {
          chart: {
            title: 'Monthly Financial Report',
            subtitle: 'Sales & Expenses',
          }
        };
        var chart = new google.charts.Bar(document.getElementById('chart8'));
        chart.draw(data5, google.charts.Bar.convertOptions(options5));
      }
    </script>
  </head>
  <body style="background-color: #c7c7c7;" onload = "navbar();">
  <script type="text/javascript">
      function navbar(){
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function(){
          document.getElementById("navbar").innerHTML = this.responseText;
        }
        xhttp.open("GET", "admin_navbar.php");
        xhttp.send();
      }
      setInterval(function(){
        navbar();
      }, 10000);
    </script>
    <div style="background-color: #ffc067;" id="navbar"></div><!--navbar -->

    <div class="mx-5">
      <div class="m-3 bg-light rounded">
     
      
      </div><!-- last div-->
      <footer class="m-3 mt-4 text-muted border-top">
      <p>&copy; R-Click Solutions 2023</p>
      </footer>
    </div>
    

    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: jQuery and Bootstrap Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
    -->
  </body>
</html>