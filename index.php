<?php

if (!isset($_SESSION)) session_start(); //start session

require('apokemain.php'); //load main

?>



<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Language" content="en">
<title>Test Pokemon List</title>

<script src='apoke.js' type='text/javascript'></script>

<link rel="stylesheet" type="text/css" href="mainpagesstylepoke.css">

<style>
<!--

-->
</style>

</head>

<body marginwidth='0' marginheight='0' style='background-color:#fff; height:100%; overflow-y:scroll; margin-left:5px; margin-top:0px; margin-bottom:50px; margin:0; padding:0px; font-family:Verdana; font-size:13px;' bgcolor='#FFFFFF'>

<!-- form for search, previous, next -->
<form action="<?= basename($_SERVER['PHP_SELF']) ?>" id="form_search" name="form_search" method="get" style="margin: 0px; display:none;">
<input type="hidden" id="quick_name_search" name="quick_name_search" value="<?= $quick_name_search ?>">
<input type="hidden" id="offset" name="offset" value="<?= $sqloffset ?>">
</form>
<!-- form for reset -->
<form action="<?= basename($_SERVER['PHP_SELF']) ?>" id="form_reset" name="form_reset" method="get" style="margin: 0px; display:none;">
<input type="hidden" id="reset_quick_name_search" name="quick_name_search" value="<?= $quick_name_search ?>">
</form>

<div class='trandiv' style='opacity:0;' id='trandiv'>

 <!-- main table -->
 <div style='width:70%; height:100%; margin:0 auto; font-family:"Segoe UI", Arial, sans-serif;'>
  
  <!-- main title in page -->
  <div class='mg0a txtcenter pd1vw' style='height:4vw; margin-top:1vw; font-size:4vw;'>
   Pok&eacute;mon List
  </div>
  
  <div class='insidecontainer'>
   
   <!-- search, previous, next section -->
   <div class='h10vw'></div> <!-- make space for menu, as it loads after the main table list -->
   
   <!-- main table with pokemon list -->
   <?php ListOfPokemonsFromDB(); ?>
   
   
   <!-- search, previous, next section -->
   <div class='top_table mg0a w100p'> <!-- move to top of the insidecontainer -->
    <div class='d-table mg0a txtcenter w100p' style='height:2vw;'>
    
     <?php paginationForPokemons('prev-t'); ?>
     
     <?php SelectOptionsForPokemons(); ?>
     
     <?php paginationForPokemons('next-t'); ?>
      
    </div>
    
    <!-- numbering and selection/sorting info -->
    <?php paginationPageNumber(); ?>
    
   </div>
   
   
   <!-- bottom previous next section -->
   <div class='mg0a txtcenter pd1vw' style='height:4vw;'>
     
    <?php paginationForPokemons('prev-b'); ?>
    
    <?php paginationForPokemons('next-b'); ?>
    
   </div>
   
  </div>
  
 </div>

</div>

<!-- make message box -->
<?php ShowMessages(); ?>

<br>

<!-- start the fadein page fx and start message box -->
<script language='javascript'>trandiv(); HideMessageDiv();</script>

</body>
</html>
