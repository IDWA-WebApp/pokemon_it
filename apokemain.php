<?php

if (!isset($_SESSION)) session_start(); //start session

require('apokeclass.php'); //class for curl calls and functions with endpoint for the API
require('apokedata.php'); //db connection information and various static data
require('apokecalls.php'); //main functions

//class for curl
use TestPokePHP\TestPokeApi;
$pokeapi = new TestPokeApi();

//connect to the local db
$conn_poke = new mysqli($servername_poke, $username_poke, $password_poke, $dbname_poke);
if ($conn_poke->connect_error) { die('DB Connection failed. Please try again in a while.1'); }
$conn_poke->set_charset('utf8');


//check for new pokemons and insert in local db (the command runs only once for a user session)
testForNewPokemons();


//requests
$sqloffset = request_get('offset', 'intval', 'no', '0');
$sqllimit = request_get('limit', 'intval', 'no', '20');


//main search and preview the list of pokemons
function ListOfPokemonsFromDB() {
 global $sql_stat_fields, $sqloffset, $sqllimit;
 
 //search in the favorites db for filters and marking the pokemons in the list
 getPokeFav($PokeFav);
 
 //get data from the local db
 getThePokemons($PokeCount, $PokeStats, $PokeData);
 
 //show pokemon list
 for ($i = 1; $i <= sizeof($PokeData); $i++) {
  //check for odd/even row for the back coloring
  echo('<div class="main_table main_table_' . oddeventext($i) . '">' . PHP_EOL);
  
  echo(' <div class="sub_table_left">' . PHP_EOL);
  
  //check for the pokemon id length in case it is 4digit to lower the font-size to fit
  $fnt = 'style="margin-top:1%;"';
  if ($PokeData[$i]['id'] > '999') $fnt = 'style="letter-spacing:-5px; font-size:7.5vw; margin-top:3%;"';
  echo('  <div class="poke_id" ' . $fnt . '>#' . $PokeData[$i]['id'] . '</div>' . PHP_EOL);
  
  //pokemon height in circle div top left
  echo('  <div class="poke_circle left0"><div class="poke_circle_text">' . number_format(intval($PokeData[$i]['height'])/10, 1) . 'm</div></div>' . PHP_EOL);
  //pokemon weight in circle div top right
  echo('  <div class="poke_circle right0"><div class="poke_circle_text">' . number_format(intval($PokeData[$i]['weight'])/10, 1) . 'Kg</div></div>' . PHP_EOL);
  
  //pokemon name, species
  echo('  <div class="poke_descs">' . PHP_EOL);
  echo('   <div><span class="poke_name">' . ucfirstletters($PokeData[$i]['name']) . '</div>' . PHP_EOL);
  echo('   <div><span class="poke_spec">' . $PokeData[$i]['genera'] . '</div>' . PHP_EOL);
  
  //pokemon type with coloring
  echo('   <div>');
  echo('    <span class="poke_type"><span class="poke_type_' . strtolower($PokeData[$i]['types2']) . '">' . strtoupper($PokeData[$i]['types2']) . '</span>');
  //check if there are two types to add dot between
  if (($PokeData[$i]['types2']!='') and ($PokeData[$i]['types1']!='')) echo('    <span style="color:#aaa;"> &bull; </span>');
  echo('    <span class="poke_type_' . strtolower($PokeData[$i]['types1']) . '">' . strtoupper($PokeData[$i]['types1']) . '</span>');
  echo('   </div>' . PHP_EOL);
  
  //abilities where in parenthesis the hidden abilities
  echo('   <div><span class="poke_abil">' . ucfirstletters($PokeData[$i]['abilities']) . '</div>' . PHP_EOL);
  echo('  </div>' . PHP_EOL);
  
  //front image of the pokemon
  echo('  <div class="poke_img_cont" title="#' . $PokeData[$i]['id'] . '"><img src="' . $PokeData[$i]['img'] . '" class="poke_img"></div>' . PHP_EOL);
  
  //favorites button with AJAX call
  echo('  <div style="position:relative; margin-left:-115%; margin-top:45%; z-index:10; width:10vw;">' . PHP_EOL);
  $isactive = 'inactive'; $actiontext = 'Add to Favorites';
  if ($PokeFav[$PokeData[$i]['id']]==1) { $isactive = 'active'; $actiontext = 'Remove from Favorites'; }
  $myhashstring = strval($PokeData[$i]['id']) . session_id() . 'myaslt'; //as 'myaslt' you may use any custom word or random number in session for greater hash security
  $myhash = hash('sha256', $myhashstring); //for checking and makeing the api submit only for the active sessions
  echo('   <div id="fav-pokemon-' . $i . '" class="fav-pokemon fav-pokemon-' . $isactive . ' trs3 mpointer" title="' . $actiontext . '" onmouseover="fav_over(' . $i . ');" onmouseout="fav_out(' . $i . ');" onclick="FavoriteCall(' . $PokeData[$i]['id'] . ', ' . $i . ', \'' . $myhash . '\');"><div class="fav-heart"></div></div>' . PHP_EOL);
  echo('   <div id="fav-pokemon-color-' . $i . '" class="half-circle half-circle-' . $isactive . ' trs3"></div>' . PHP_EOL);
  echo('  </div>' . PHP_EOL);
  
  echo(' </div>' . PHP_EOL);
  
  //stats list
  echo(' <div class="sub_table_right">' . PHP_EOL);
  echo('  <div class="poke_stats_cont">' . PHP_EOL);
  for ($x = 1; $x <= sizeof($PokeStats); $x++) {
   echo('   <div class="poke_stats_tbl">' . PHP_EOL);
   echo('    <div class="poke_stats_name poke_stats_name_text">' . ucwords($sql_stat_fields[$PokeStats[$x]]) . '</div>' . PHP_EOL);
   $statvalue = intval($PokeData[$i][$PokeStats[$x]]);
   echo('    <div class="poke_stats_value poke_stats_value_text">' . $statvalue . '</div>' . PHP_EOL);
   echo('    <div class="poke_stats_perc_cont">' . PHP_EOL);
   echo('     <div class="poke_stats_perc_back">' . PHP_EOL);
   $clr = 'bgc_per_' . calc_percentage($statvalue);
   if ($statvalue==255) $clr .= ' poke_stats_perc_full';
   echo('      <div style="width:' . intval($statvalue * 100 / 255) . '%;" class="poke_stats_perc_fill ' . $clr . '"></div>' . PHP_EOL);
   echo('     </div>' . PHP_EOL);
   echo('    </div>' . PHP_EOL);
   echo('   </div>' . PHP_EOL);
  }
  echo('  </div>' . PHP_EOL);
  echo(' </div>' . PHP_EOL);
  
  echo('</div>' . PHP_EOL);
 }
}


//pagination buttons
function paginationForPokemons($prevnext) {
 global $LastTableEntry, $sqloffset, $sqllimit, $cur_results;
 if (($sqloffset + $sqllimit) > $LastTableEntry) $sqloffset = $LastTableEntry - $sqllimit;
 if (($sqllimit<0) or ($sqllimit>50)) $sqllimit = 20;
 
 $isvisible = ' v-hidden';
 if ($sqloffset > 0) {
  $isvisible = '';
  $jscript1 = ' onclick="document.getElementById(\'offset\').value=\'' . ($sqloffset - $sqllimit) . '\'; document.getElementById(\'form_search\').submit();"';
  $jscript2 = ' onclick="document.getElementById(\'offset\').value=\'0\'; document.getElementById(\'form_search\').submit();"';
 }
 if ($prevnext=='prev-t') {
  echo(' <div class="d-table-cell mg0a txtcenter nwrap" style="height:1.5vw; width:10%;">' . PHP_EOL);
  echo('  <button title="First Page" class="d-inblock buttprevnext mgb1vw mpointer pdpagi2 ' . $isvisible . '" ' . $jscript2 . '>&#8249;&#8249;</button>&nbsp;' . PHP_EOL);
  echo('  <button title="Previous Page" class="d-inblock buttprevnext mgb1vw mpointer pdpagi1 ' . $isvisible . '" ' . $jscript1 . '>&nbsp;&#8249;&nbsp;</button>&nbsp;' . PHP_EOL);
  echo(' </div>' . PHP_EOL);
 }
 if ($prevnext=='prev-b') {
  echo(' <button title="First Page" class="d-inblock buttprevnext mpointer float-l pdpagi2 ' . $isvisible . '" style="margin-top:1vw; margin-left:5vw;"' . $jscript2 . '>&#8249;&#8249;</button>' . PHP_EOL);
  echo(' <button title="Previous Page" class="d-inblock buttprevnext mpointer float-l pdpagi1 ' . $isvisible . '" style="margin-top:1vw; margin-left:5vw;"' . $jscript1 . '>&nbsp;&#8249;&nbsp;</button>' . PHP_EOL);
 }
 
 $isvisible = ' v-hidden';
 if (($sqloffset + $sqllimit < $LastTableEntry) and ($sqloffset + $sqllimit < $cur_results)) {
  $isvisible = '';
  $jscript1 = ' onclick="document.getElementById(\'offset\').value=\'' . ($sqloffset + $sqllimit) . '\'; document.getElementById(\'form_search\').submit();"';
  $jscript2 = ' onclick="document.getElementById(\'offset\').value=\'' . (($LastTableEntry - $sqllimit) + 1) . '\'; document.getElementById(\'form_search\').submit();"';
 }
 if ($prevnext=='next-t') {
  echo(' <div class="btnnext d-table-cell mg0a txtcenter nwrap" style="height:1.5vw; width:10%;">' . PHP_EOL);
  echo('  <button title="Next Page" class="d-inblock buttprevnext mgb1vw mpointer pdpagi1 ' . $isvisible . '"' . $jscript1 . '><span class="nextone">&nbsp;&#8250;&nbsp;</span></button>&nbsp;' . PHP_EOL);
  echo('  <button title="Last Page" class="d-inblock buttprevnext mgb1vw mpointer pdpagi2 ' . $isvisible . '"' . $jscript2 . '>&#8250;&#8250;</button>' . PHP_EOL);
  echo(' </div>' . PHP_EOL);
 }
 if ($prevnext=='next-b') {
  echo(' <button title="Last Page" class="d-inblock buttprevnext mpointer float-r pdpagi2 ' . $isvisible . '" style="margin-top:1vw; margin-right:5vw;"' . $jscript2 . '>&#8250;&#8250;</button>' . PHP_EOL);
  echo(' <button title="Next Page" class="d-inblock buttprevnext mpointer float-r pdpagi1 ' . $isvisible . '" style="margin-top:1vw; margin-right:5vw;"' . $jscript1 . '>&nbsp;&#8250;&nbsp;</button>' . PHP_EOL);
 }
}


//show number of results and details/reset
function paginationPageNumber() {
 global $LastTableEntry, $sqloffset, $sqllimit, $cur_results;
 if (($sqloffset + $sqllimit) > $LastTableEntry) $sqloffset = $LastTableEntry - $sqllimit;
 if (($sqllimit<0) or ($sqllimit>50)) $sqllimit = 20;
 echo(' <div class="pos-rel txtcenter mg0a" style="height:2vw; font-size:1vw; font-weight:600; color:#555;">' . ($LastTableEntry > 0 ? ('Showing ' . ($sqloffset + 1) . ' to ' . ($sqloffset + ($LastTableEntry > $sqllimit ? $sqllimit : $LastTableEntry)) . ' (from ' . $LastTableEntry . ')') : 'No Results') . '</div>' . PHP_EOL);
}

?>
