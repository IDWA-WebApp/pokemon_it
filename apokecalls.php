<?php


//επιστρέφει αποτελέσματα βάση κλήσης με endpoint και id
function getResults($call_func, $call_id) {
 global $pokeapi;
 $response = $pokeapi->$call_func($call_id);
 return $response;
}


//επιστρέφει αποτελέσματα από url (π.χ. https://pokeapi.co/api/v2/pokemon/35/)
function getRequest($url) {
 global $pokeapi;
 $response = $pokeapi->SendCurlRequest($url);
 return $response;
}


//επιστρέφει λίστα αποτελεσμάτων με offset και limit
function getResourceList($call_func, $call_endpoint, $call_limit, $call_offset) {
 global $pokeapi;
 $response = $pokeapi->$call_func($call_endpoint, $call_limit, $call_offset);
 return $response;
}


//υπολογισμό ποσοστών
function calc_percentage($val) {
 $aa = intval($val / 51) + 1;
 if ($aa > 4) $aa = 4;
 return $aa;
}


//get requests και έλεγχος
function request_get($request, $type, $empty, $init) {
 $value = $init;
 if (isset($_GET[$request])) $value = $_GET[$request];
 if ($type=='intval') $value = intval($value);
 if ($type=='string') {
  if (($empty=='no') and ($value=='')) $value = $init;
 }
 return $value;
}


//επιστρέφει κείμενο odd/even
function oddeventext($value) {
 $oddeven = 'odd';
 if ($value % 2 == 0) $oddeven = 'even'; 
 return $oddeven;
}


//κεφαλαίο το πρώτο γράμμα κάθε λέξης μετά από κενό, παύλα, παρένθεση, κόμμα
function ucfirstletters($string) {
 $string = ucwords($string);
 $string = ucwords($string, "-");
 $string = ucwords($string, "(");
 $string = ucwords($string, ",");
 return $string;
}


//κύρια κλήση (τελευταία) για λήψη δεδομένων από βάση με ταξινόμηση ως προς το βάρος (descending) με pagination ανά 20 και αναζήτησ φίλτρων
function getThePokemons(&$PokeCount, &$PokeStats, &$PokeData) {
 
 global $sqloffset, $sqllimit, $LastTableEntry, $sql_stat_fields, $conn_poke, $cur_results;
 
 $PokeCount = 0;
 $PokeStats = array();
 $PokeData = array();
 
 //για δυνατότητα προσθήκης επιλογής offset/limit από το χρήστη
 if (($sqloffset + $sqllimit) > $LastTableEntry) $sqloffset = $LastTableEntry - $sqllimit;
 if (($sqllimit<10) or ($sqllimit>50)) $sqllimit = 20;
 //εντολή αναζήτησης όλων στη βάση
 $sqlA = '';
 $sqlA .= ' ORDER BY weight desc, name ';
 
 //αριθμός αποτελεσμάτων
 $sqlB = 'SELECT COUNT(id) AS totresults FROM tbl_Pokemon WHERE 1=1' . $sqlA . ';';
 $result = $conn_poke->query($sqlB);
 if ($result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $cur_results = $row['totresults'];
 }
 
 //αποτελέσματα με offset/limit
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


//έλεγχος για νέα pokemon και καταχώρηση στη db (η εντολή δεν τρέχει ξανά για το session του χρήστη)
function testForNewPokemons() {
 if (isset($_SESSION['pokeupdate'])) return;
 $_SESSION['pokeupdate'] = 1;
 //σύνδεση με τη db
 global $servername_poke, $username_poke, $password_poke, $dbname_poke, $LastTableEntry, $conn_poke;
 
 //εύρεση συνόλου pokemon στη βάση
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
 
 //εύρεση συνολικού αριθμού pokemon στο api
 $objList = json_decode(getResourceList('resourceList', 'pokemon', '1', '0'), true);
 $totPoke = intval($objList['count']);
 if ($TotPokemons==$totPoke) { //έξοδος αν δεν υπάρχει διαφορά στα σύνολα
  $_SESSION['pokeupdatemessage'] = 'Pokemon Database is up to date! ' . $LastTableEntry . ' Pokemons in Database, ' . $totPoke . ' Pokemons in API Server.';
  return;
 }
 
 //κλήση για τα 100 επόμενα pokemon στο apo (εάν δεν υπάρχουν η κλήση τερματίζει)
 $objList = json_decode(getResourceList('resourceList', 'pokemon', '100', $TotPokemons), true);
 
 //κλήση για εύρεση των λεπτομερειών των pokemon από τη λίστα
 foreach($objList['results'] as $res) {
  $objPoke = json_decode(getRequest($res['url']), true);
  $PokeCount = $PokeCount + 1;
  $poke_data[$PokeCount] = $objPoke;
 }
 
 //εισαγωγή των νέων pokemon (το id είναι κλειδί οπότε δεν καταχωρούντε διπλοεγγραφές)
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
 //κρατάει αναφορά σε session για εμφνάνιση μηνύματος
 if ($PokeCountOK > 0) {
  $_SESSION['pokeupdatemessage'] = 'Pokemon Database has been updated! ' . $PokeCountOK . ' new Pokemons have been added.';
  echo("1");
 } else {
  $_SESSION['pokeupdatemessage'] = 'Pokemon Database is up to date! ' . $LastTableEntry . ' Pokemons in Database, ' . $totPoke . ' Pokemons in API Server.';
  echo("2");
 }
 
}


//εμφάνιση μηνύματος
function ShowMessages() {
 $message = '';
 if ((isset($_SESSION['pokeupdatemessage'])) and ($_SESSION['pokeupdatemessage']!='')) $message = $_SESSION['pokeupdatemessage'];
 //εάν υπάρχει κάποιο μήνυμα, εμφανίζεται κάτω δεξιά και μετά από 5 δευτερόλεπτα κλείνει μόνο του
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

?>
