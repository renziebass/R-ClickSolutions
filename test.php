<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Auto Search</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
  </head>
  <script>
        $(document).ready(function() {


            $("#search").keyup(function(e) {
                $("#card").html("");
                var search_query = $(this).val();
                if (search_query != "") {

                    $.ajax({

                        url: "includes/ReturnList.inc.php",
                        type: "POST",
                        data: {
                            search: search_query
                        },
                        success: function($data) {

                            $("#list").fadeIn('fast').html($data);

                        }

                    });

                } else {
                    $("#list").fadeOut();
                }

            });

        });
    </script>
  <body>
    <div class="row justify-content-center my-5  ">
        <div class="col-6 text-center">
            <form>
                <div class="input-group mb-3">
                    <input type="text" id="search" class="form-control form-control-lg" placeholder="Search Here" autocomplete="off">
                    <button type="submit" id="submit" class="input-group-text btn-success px-4"><i class="fa fa-search"></i></button>
                </div>
            </form>

            <div id="list"></div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-6">
            <div id="card"> </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script> 
  </body>
</html>