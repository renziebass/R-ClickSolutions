<?php
   session_start();

   /* Redirect to a different page in the current directory that was requested */

   
   if(session_destroy()) {
      header("Location: ./index.php");
      
   }
?>