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
$quick_name_search = request_get('quick_name_search', 'string', '', '');
$quick_name_search = sanitize_variable($quick_name_search);
$filter_sort = request_get('filter_sort', 'string', 'no', ' weight desc');
$filter_type = request_get('filter_type', 'string', '', '');
$filter_type = sanitize_variable($filter_type);


//main search and preview the list of pokemons
function ListOfPokemonsFromDB() {
 global $sql_stat_fields, $sql_sort_filter, $filter_sort, $filter_type, $quick_name_search, $sqloffset, $sqllimit;
 
 //check filters for accepted values
 $sqlfilter_sort = check_string_filter($filter_sort);
 
 //search in the favorites db for filters and marking the pokemons in the list
 getPokeFav($PokeFav);
 if ($filter_sort=='favorites desc') $sqlfilter_sort = 'name';
 
 //get data from the local db
 getThePokemons($PokeCount, $PokeStats, $PokeData, $quick_name_search, $filter_sort, $sqlfilter_sort, $filter_type);
 
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
 global $LastTableEntry, $sqloffset, $sqllimit, $filter_sort, $filter_type, $quick_name_search, $cur_results;
 if (($quick_name_search!='') or ($filter_sort=='favorites desc') or ($filter_type!='')) {
  if ($quick_name_search!='') echo(' <div class="pos-rel txtcenter mg0a" style="height:2vw; font-size:1vw; font-weight:600; color:#555;">Search results for "<i>' . $quick_name_search . '</i>" ' . ($cur_results > 0 ? (($sqloffset + 1) . ' to ' . ($sqloffset + ($cur_results > $sqllimit ? $sqllimit : $cur_results)) . ' (from ' . $cur_results . ')') : 'No Results') . ' (<span style="color:#006699;" class="mpointer" onclick="document.getElementById(\'reset_quick_name_search\').value=\'\'; document.getElementById(\'form_reset\').submit();">Click to Reset</span>)</div>' . PHP_EOL);
  if ($filter_sort=='favorites desc') echo(' <div class="pos-rel txtcenter mg0a" style="height:2vw; font-size:1vw; font-weight:600; color:#555;">Showing your Favorites (<span style="color:#006699;" class="mpointer" onclick="document.getElementById(\'reset_filter_sort\').value=\'\'; document.getElementById(\'form_reset\').submit();">Click to Reset</span>)</div>' . PHP_EOL);
  if ($filter_type!='') echo(' <div class="pos-rel txtcenter mg0a" style="height:2vw; font-size:1vw; font-weight:600; color:#555;">Showing <span class="poke_type_' . $filter_type . '">' . ucfirstletters($filter_type) . '</span> ' . ($cur_results > 0 ? (($sqloffset + 1) . ' to ' . ($sqloffset + ($cur_results > $sqllimit ? $sqllimit : $cur_results)) . ' (from ' . $cur_results . ')') : 'No Results') . ' (<span style="color:#006699;" class="mpointer" onclick="document.getElementById(\'reset_filter_type\').value=\'\'; document.getElementById(\'form_reset\').submit();">Click to Reset</span>)</div>' . PHP_EOL);
 } else {
  if (($sqloffset + $sqllimit) > $LastTableEntry) $sqloffset = $LastTableEntry - $sqllimit;
  if (($sqllimit<0) or ($sqllimit>50)) $sqllimit = 20;
  echo(' <div class="pos-rel txtcenter mg0a" style="height:2vw; font-size:1vw; font-weight:600; color:#555;">' . ($LastTableEntry > 0 ? ('Showing ' . ($sqloffset + 1) . ' to ' . ($sqloffset + ($LastTableEntry > $sqllimit ? $sqllimit : $LastTableEntry)) . ' (from ' . $LastTableEntry . ')') : 'No Results') . '</div>' . PHP_EOL);
 }
}


//searches and filters
function SelectOptionsForPokemons() {
 global $slc_sort_filter, $filter_sort, $filter_type;
 
 //select for sorting
 $opt_selected = array();
 $opt_selected[$filter_sort] = ' selected';
 echo(' <div class="d-table-cell mg0a txtcenter" style="height:1.5vw; width:28%;">' . PHP_EOL);
 echo(' <select name="filter_sort" class="dropdown dropbox mpointer" style="width:18vw;" onchange="document.getElementById(\'filter_sort\').value=this.value; document.getElementById(\'form_sort\').submit();">' . PHP_EOL);
 foreach ($slc_sort_filter as $slc=>$val) {
  echo('  <option value="' . $slc . '" ' . $opt_selected[$slc] . '>' . $val . '</option>' . PHP_EOL);
 }
 echo(' </select>' . PHP_EOL);
 echo(' </div>' . PHP_EOL);
 echo(' &nbsp;' . PHP_EOL);
 
 //create array with the pokemon types
 $PokeTypes = getPokemonTypes('');
 if (!in_array($filter_type, $PokeTypes)) $filter_type = '';
 
 //select for pokemon types
 /* simple select with no fx/colors
 //$opt_selected = array();
 //$opt_selected[$filter_type . '-'] = ' selected';
 //echo(' <div class="d-table-cell mg0a txtcenter" style="height:1.5vw; width:24%;">' . PHP_EOL);
 //echo(' <select name="filter_type" class="dropdown dropbox mpointer" style="width:16vw;" onchange="document.getElementById(\'filter_type\').value=this.value; document.getElementById(\'form_sort\').submit();">' . PHP_EOL);
 //echo('  <option value="" ' . $opt_selected['-'] . '>List by Type</option>' . PHP_EOL);
 //for ($i = 0; $i < sizeof($PokeTypes); $i++) {
 // echo('  <option class="fntBold" value="' . $PokeTypes[$i] . '" ' . $opt_selected[$PokeTypes[$i] . '-'] . '>' . ucfirstletters($PokeTypes[$i]) . '</option>' . PHP_EOL);
 //}
 //echo(' </select>' . PHP_EOL);
 //echo(' </div>' . PHP_EOL);
 */
 //list with color fx per type
 echo(' <div class="d-table-cell mg0a txtcenter" style="height:1.5vw; width:24%;">' . PHP_EOL);
 echo(' <input type="hidden" id="xdropdownstateP2" name="xdropdownstateP2" value="">' . PHP_EOL);
 echo(' <div class="xdropdownP" style="width:14vw; border-radius:10px;">' . PHP_EOL);
 $text = 'List by Type &#8628;';
 if ($filter_type!='') $text = ucfirstletters($filter_type);
 echo('  <button type="button" name="xdropdown1P2" onclick="xdropdownFunctionP(\'2\')" onkeydown="xdropdownEscCheckP(event);" class="xdropbtnP dropbox dropdown" style="width:14vw; border-radius:10px;">' . $text . '</button>' . PHP_EOL);
 echo('  <div id="xmyDropdownP2" class="xdropdownP-content" style="width:14vw; min-width:10vw; height:37vw; overflow-y:auto; overflow-x:hidden;">' . PHP_EOL);
 echo('   <input class="d-none" type="text" name="xdropdown2P2" placeholder="Type and Press Enter to search..." id="xmyInputP2" onkeyup="xfilterFunctionP(\'2\')" onkeydown="xdropdownEscCheckP(event);">' . PHP_EOL);
 if ($filter_type!='') echo('    <a href="#" onclick="document.getElementById(\'filter_type\').value=\'' . '' . '\'; document.getElementById(\'form_sort\').submit();">' . 'Show All' . '</a>' . PHP_EOL);
 for ($i = 0; $i < sizeof($PokeTypes); $i++) {
  echo('    <a href="#" onclick="document.getElementById(\'filter_type\').value=\'' . $PokeTypes[$i] . '\'; document.getElementById(\'form_sort\').submit();"><div class="d-inblock pokebox" style="background-color:var(--color-type-' . $PokeTypes[$i] . ');"></div> &nbsp;' . ucfirstletters($PokeTypes[$i]) . '</a>' . PHP_EOL);
 }
 echo('  </div>' . PHP_EOL);
 echo(' </div>' . PHP_EOL);
 echo(' </div>' . PHP_EOL);
 echo(' &nbsp;' . PHP_EOL);
 
 //create array with the pokemon names
 $PokeNames = getPokemonNames('');
 
 //select/search pokemon name
 echo(' <div class="d-table-cell mg0a txtcenter" style="height:1.5vw; width:28%;">' . PHP_EOL);
 echo(' <input type="hidden" id="xdropdownstateP1" name="xdropdownstateP1" value="">' . PHP_EOL);
 echo(' <div class="xdropdownP" style="width:18vw; border-radius:10px;">' . PHP_EOL);
 echo('  <button type="button" name="xdropdown1P1" onclick="xdropdownFunctionP(\'1\')" onkeydown="xdropdownEscCheckP(event);" class="xdropbtnP dropbox dropdown nwrap" style="width:18vw; border-radius:10px;">Search ' . ucfirstletters($filter_type) . ' Pok&eacute;mons &#8628;</button>' . PHP_EOL);
 echo('  <div id="xmyDropdownP1" class="xdropdownP-content" style="width:18vw; height:36vw; overflow-y:scroll; overflow-x:hidden;">' . PHP_EOL);
 echo('   <input type="text" name="xdropdown2P1" placeholder="Type and Press Enter to search..." id="xmyInputP1" onkeyup="xfilterFunctionP(\'1\')" onkeydown="xdropdownEscCheckP(event);">' . PHP_EOL);
 for ($i = 0; $i < sizeof($PokeNames); $i++) {
  echo('    <a href="#" onclick="pokesearch(\'' . $PokeNames[$i] . '\');">' . $PokeNames[$i] . '</a>' . PHP_EOL);
 }
 echo('  </div>' . PHP_EOL);
 echo(' </div>' . PHP_EOL);
 echo(' <script>' . PHP_EOL);
 echo(' var input = document.getElementById("xmyInputP1");' . PHP_EOL);
 echo(' input.addEventListener("keyup", function(event) {' . PHP_EOL);
 echo('  if (event.keyCode === 13) {' . PHP_EOL);
 echo('   event.preventDefault();' . PHP_EOL);
 echo('   document.getElementById("quick_name_search").value = document.getElementById("xmyInputP1").value;' . PHP_EOL);
 echo('   document.getElementById("offset").value = 0;' . PHP_EOL);
 echo('   document.getElementById("form_search").submit();' . PHP_EOL);
 echo('  }' . PHP_EOL);
 echo(' });' . PHP_EOL);
 echo(' function pokesearch(name) {' . PHP_EOL);
 echo('  event.preventDefault();' . PHP_EOL);
 echo('  document.getElementById("quick_name_search").value = name;' . PHP_EOL);
 echo('  document.getElementById("offset").value = 0;' . PHP_EOL);
 echo('  document.getElementById("form_search").submit();' . PHP_EOL);
 echo(' }' . PHP_EOL);
 echo(' </script>' . PHP_EOL);
 echo(' </div>' . PHP_EOL);
}

?>
