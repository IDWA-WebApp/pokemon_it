<?php


//returns results based on the endpoint and id
function getResults($call_func, $call_id) {
 global $pokeapi;
 $response = $pokeapi->$call_func($call_id);
 return $response;
}


//returns results from url (e.g. https://pokeapi.co/api/v2/pokemon/35/)
function getRequest($url) {
 global $pokeapi;
 $response = $pokeapi->SendCurlRequest($url);
 return $response;
}


//returns a list of results with offset and limit
function getResourceList($call_func, $call_endpoint, $call_limit, $call_offset) {
 global $pokeapi;
 $response = $pokeapi->$call_func($call_endpoint, $call_limit, $call_offset);
 return $response;
}


//calculate the stats percentages
function calc_percentage($val) {
 $aa = intval($val / 51) + 1;
 if ($aa > 4) $aa = 4;
 return $aa;
}


//get requests and checks
function request_get($request, $type, $empty, $init) {
 $value = $init;
 if (isset($_GET[$request])) $value = $_GET[$request];
 if ($type=='intval') $value = intval($value);
 if ($type=='string') {
  if (($empty=='no') and ($value=='')) $value = $init;
 }
 return $value;
}


//return text odd/even for a given number
function oddeventext($value) {
 $oddeven = 'odd';
 if ($value % 2 == 0) $oddeven = 'even'; 
 return $oddeven;
}


//makes upper case the first letter of each word before space, dash, parenthesis, comma
function ucfirstletters($string) {
 $string = ucwords($string);
 $string = ucwords($string, "-");
 $string = ucwords($string, "(");
 $string = ucwords($string, ",");
 return $string;
}


//main function to get the pokemon data with sorting and pagination per 20
function getThePokemons(&$PokeCount, &$PokeStats, &$PokeData, $quick_name_search, $filter_sort, $sqlfilter_sort, $filter_type) {
 
 global $sqloffset, $sqllimit, $LastTableEntry, $sql_stat_fields, $conn_poke, $cur_results;
 
 $PokeCount = 0;
 $PokeStats = array();
 $PokeData = array();
 
 //to add options for offset/limit from the user
 if (($sqloffset + $sqllimit) > $LastTableEntry) $sqloffset = $LastTableEntry - $sqllimit;
 if (($sqllimit<10) or ($sqllimit>50)) $sqllimit = 20;
 
 $sqlfilter_type = '';
 if ($filter_type!='') $sqlfilter_type = ' AND (types1 = \'' . $filter_type . '\' OR types2 = \'' . $filter_type . '\')';
 
 //create sql select command
 $sqlA = '';
 if ($quick_name_search!='') {
  $sqlA .= ' AND name LIKE \'%' . $quick_name_search . '%\'';
  $sqlfilter_sort = 'INSTR(name, \'' . $quick_name_search . '\')';
  if ($filter_sort!='') $sqlfilter_sort = $sqlfilter_sort . ', ' . $filter_sort;
 }
 if ($filter_sort=='favorites desc') {
  $sqlfilter_type .= ' AND id IN (SELECT pokemon_id FROM tbl_Pokemon_fav WHERE pokemon_user = \'demo\')';
 }
 $sqlA .= $sqlfilter_type . ' ORDER BY ' . $sqlfilter_sort . ', name ';
 
 //count total results
 $sqlB = 'SELECT COUNT(id) AS totresults FROM tbl_Pokemon WHERE 1=1' . $sqlA . ';';
 $result = $conn_poke->query($sqlB);
 if ($result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $cur_results = $row['totresults'];
 }
 
 //results with offset/limit
 $sqlB = 'SELECT * FROM tbl_Pokemon WHERE 1=1' . $sqlA . ' LIMIT ' . intval($sqllimit) . ' OFFSET ' . $sqloffset . ';';
 $result = $conn_poke->query($sqlB);
 if ($result->num_rows > 0) {
  $finfo = $result->fetch_fields();
  $fieldcount = mysqli_num_fields($result);
  while($row = $result->fetch_assoc()) {
   $PokeCount = $PokeCount + 1;
   if ($PokeCount > intval($sqllimit)) break;
   $istats = 0;
   foreach ($finfo as $fld) {
    $PokeData[$PokeCount][$fld->name] = $row[$fld->name];
    if (isset($sql_stat_fields[$fld->name])) {
     $istats = $istats + 1;
     $PokeStats[$istats] = $fld->name;
    }
   }
  }
 }
}


//check for new pokemons from the API and add the in the local db (the function is set to run only for a new user session)
function testForNewPokemons() {
 if (isset($_SESSION['pokeupdate'])) return;
 $_SESSION['pokeupdate'] = 1;
 //connect to db
 global $servername_poke, $username_poke, $password_poke, $dbname_poke, $LastTableEntry, $conn_poke;
 
 //count total pokemons in local db
 $TotPokemons = 0;
 $sqlA = 'SELECT COUNT(id) AS TotPokemons FROM tbl_Pokemon';
 $result = $conn_poke->query($sqlA);
 if ($result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $TotPokemons = $row['TotPokemons'];
 }
 $LastTableEntry = $TotPokemons;
 $_SESSION['LastTableEntry'] = $LastTableEntry;
 
 $PokeCount = 0;
 $poke_data = array();
 
 //get total number of pokemons from API
 $objList = json_decode(getResourceList('resourceList', 'pokemon', '1', '0'), true);
 $totPoke = intval($objList['count']);
 if ($TotPokemons==$totPoke) { //exit if there is no difference in numbers
  $_SESSION['pokeupdatemessage'] = 'Pokemon Database is up to date! ' . $LastTableEntry . ' Pokemons in Database, ' . $totPoke . ' Pokemons in API Server.';
  return;
 }
 
 //call for next 100 results after the last total count (if none the call/function ends)
 $objList = json_decode(getResourceList('resourceList', 'pokemon', '100', $TotPokemons), true);
 
 //call to find the details of each pokemon
 foreach($objList['results'] as $res) {
  $objPoke = json_decode(getRequest($res['url']), true);
  $PokeCount = $PokeCount + 1;
  $poke_data[$PokeCount] = $objPoke;
 }
 
 //insert the new pokemons (the id filed is the primary key and no duplicates are submitted)
 $PokeCountOK = 0;
 for ($i = 1; $i <= $PokeCount; $i++) {
  $sqlB = 'INSERT INTO tbl_Pokemon (id, name, height, weight, abilities, types1, types2, speed, specialdefense, specialattack, defense, attack, hp, genera, img) VALUES (';
  $sqlB = $sqlB . $poke_data[$i]['id'] . ',';
  $sqlB = $sqlB . '\'' . $poke_data[$i]['name'] . '\',';
  $sqlB = $sqlB . $poke_data[$i]['height'] . ',';
  $sqlB = $sqlB . $poke_data[$i]['weight'] . ',';
  $abilities = '';
  foreach($poke_data[$i]['abilities'] as $res) {
   if ($res['is_hidden']!='1') $abilities .= $res['ability']['name'] . ', ';
  }
  foreach($poke_data[$i]['abilities'] as $res) {
   if ($res['is_hidden']=='1') $abilities .= '(' . $res['ability']['name'] . ')' . ', ';
  }
  if (substr($abilities, -2)==', ') $abilities = substr($abilities, 0, strlen($abilities) - 2);
  $sqlB = $sqlB . '\'' . $abilities . '\',';
  $sqlB = $sqlB . '\'' . $poke_data[$i]['types'][1]['type']['name'] . '\',';
  $sqlB = $sqlB . '\'' . $poke_data[$i]['types'][0]['type']['name'] . '\',';
  foreach(array_reverse($poke_data[$i]['stats']) as $res) {
   $sqlB = $sqlB . $res['base_stat'] . ',';
  }
  foreach($objSpecies['genera'] as $res) {
   if ($res['language']['name']=='en') {
    $sqlB = $sqlB . '\'' . $res['genus'] . '\',';
    break;
   }
  }
  $sqlB = $sqlB . '\'' . $poke_data[$i]['sprites']['front_default'] . '\'';
  $sqlB = $sqlB . ')';
  if ($conn_poke->query($sqlB) === TRUE) $PokeCountOK = $PokeCountOK + 1;
 }
 $LastTableEntry = $LastTableEntry + $PokeCountOK;
 //keeps a referense in a session for message
 if ($PokeCountOK > 0) {
  $_SESSION['pokeupdatemessage'] = 'Pokemon Database has been updated! ' . $PokeCountOK . ' new Pokemons have been added.';
  echo("1");
 } else {
  $_SESSION['pokeupdatemessage'] = 'Pokemon Database is up to date! ' . $LastTableEntry . ' Pokemons in Database, ' . $totPoke . ' Pokemons in API Server.';
  echo("2");
 }
 
}


//show message
function ShowMessages() {
 $message = '';
 if ((isset($_SESSION['pokeupdatemessage'])) and ($_SESSION['pokeupdatemessage']!='')) $message = $_SESSION['pokeupdatemessage'];
 //if there is a message, it appears at the right bottom corner and fades out after 5 seconds
 $ishidden = 'v-hidden'; if ($message!='') $ishidden = '';
 echo('<table id="div_MyMessage" class="' . $ishidden . ' div_message shddop50 tblcenter brd-rad10">' . PHP_EOL);
 echo(' <tr>' . PHP_EOL);
 echo('  <td class="vatop">' . PHP_EOL);
 echo('   <div class="mpointer float-r" onclick="document.getElementById(\'div_MyMessage\').style.visibility=\'hidden\'; document.getElementById(\'div_MyMessage\').style.opacity=\'0\';">x&nbsp;</div>' . PHP_EOL);
 echo('  </td>' . PHP_EOL);
 echo(' </tr>' . PHP_EOL);
 echo(' <tr>' . PHP_EOL);
 echo('  <td class="txtcenter vatop h5vw pd1vw"><div id="MyMessage_Text">' . $_SESSION['pokeupdatemessage'] . '</div><br><br></td>' . PHP_EOL);
 echo(' </tr>' . PHP_EOL);
 echo('</table>' . PHP_EOL);
 unset($_SESSION['pokeupdatemessage']);
}


//search for the favorites
function getPokeFav(&$PokeFav) {
 global $conn_poke;
 $sqlA = 'SELECT pokemon_id FROM tbl_Pokemon_fav WHERE pokemon_user = \'demo\'';
 $result = $conn_poke->query($sqlA);
 if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
   $PokeFav[$row['pokemon_id']] = 1;
  }
 }
}


//sanitize post/get data for dirrect use in an sql command
function sanitize_variable($value) {
 global $iBlackList, $BlackList;
 for ($i = 1; $i <= $iBlackList; $i++) {
  if (strpos($value, $BlackList[$i]) !== false) $i = str_replace($BlackList[$i], '', $value);
 }
 return $value;
}


//returns an array list with pokemon names
function getPokemonNames($categ) {
 global $conn_poke, $filter_sort, $filter_type;
 $PokeNamesCount = 0;
 $PokeNames = array();
 $sqlA = 'SELECT name FROM tbl_Pokemon WHERE 1=1';
 if ($filter_type!='') $sqlA .= ' AND types1=\'' . $filter_type . '\' OR types2 = \'' . $filter_type . '\'';
 if ($filter_sort=='favorites desc') $sqlA .= ' AND id IN (SELECT pokemon_id FROM tbl_Pokemon_fav WHERE pokemon_user = \'demo\')';
 $sqlA .= ' ORDER BY name;';
 $result = $conn_poke->query($sqlA);
 if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
   $PokeNames[$PokeNamesCount] = $row['name'];
   $PokeNamesCount++;
  }
 }
 return $PokeNames;
} 


//returns an array list with pokemon types
function getPokemonTypes($categ) {
 global $conn_poke;
 $PokeTypesCount = 0;
 $PokeTypes = array();
 $sqlA = '(SELECT types2 FROM tbl_Pokemon GROUP BY types2 ORDER BY types2) UNION (SELECT types1 FROM tbl_Pokemon GROUP BY types1 ORDER BY types1);';
 $result = $conn_poke->query($sqlA);
 if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
   if ($row['types2']!='') {
    $PokeTypes[$PokeTypesCount] = $row['types2'];
    $PokeTypesCount++;
   }
  }
 }
 return $PokeTypes;
} 


//check the filter for not valid values
function check_string_filter($string) {
 global $sql_sort_filter;
 $test = $string;
 foreach ($sql_sort_filter as $key=>$value) {
  $test = str_replace($value, '', $test);
 }
 if ($test!='') $string = ' weight desc';
 return $string;
}

?>
