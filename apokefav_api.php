<?php

if (!isset($_SESSION)) session_start(); //start a session

require('apokedata.php'); //db connection information

$poke_id = 0; if (isset($_POST['poke_id'])) $poke_id = intval($_POST['poke_id']);
$action = ''; if (isset($_POST['action'])) $action = $_POST['action'];
$myhash = ''; if (isset($_POST['hash'])) $myhash = $_POST['hash'];

if (($poke_id==0) or (!is_numeric($poke_id))) exit('incorrect id');
if (!in_array($action, array('add','remove'), true )) exit('incorrect action');
$myhashstring = strval($poke_id) . session_id() . 'myaslt'; //as 'myaslt' you may use any custom word or random number in session for greater hash security
if (($myhash=='') or ($myhash!=hash('sha256', $myhashstring))) exit('incorrect hash');

//connect to the local db
$conn_poke = new mysqli($servername_poke, $username_poke, $password_poke, $dbname_poke);
if ($conn_poke->connect_error) { die('DB Connection failed. Please try again in a while.1'); }
$conn_poke->set_charset('utf8');

if ($action=='add') {
 $status = 'started add';
 $sqlB = 'INSERT INTO tbl_Pokemon_fav (pokemon_user, pokemon_id, pokemon_fav) ';
 $sqlB = $sqlB . ' SELECT * FROM (SELECT \'demo\' as col1, ' . $poke_id . ' as col2, 1 as col3) AS tmp ';
 $sqlB = $sqlB . ' WHERE NOT EXISTS (SELECT pokemon_user, pokemon_id FROM tbl_Pokemon_Fav WHERE pokemon_user = \'demo\' AND pokemon_id = ' . $poke_id . ')';
 if ($conn_poke->query($sqlB) === TRUE) $status = 'OK';
}

if ($action=='remove') {
 $status = 'started remove';
 $sqlB = 'DELETE FROM tbl_Pokemon_fav WHERE pokemon_user = \'demo\' AND pokemon_id = ' . $poke_id;
 if ($conn_poke->query($sqlB) === TRUE) $status = 'OK';
 $sqlB = 'SELECT pokemon_user, pokemon_id FROM tbl_Pokemon_Fav WHERE pokemon_user = \'demo\' AND pokemon_id = ' . $poke_id;
 $result = $conn_poke->query($sqlB);
 if ($result->num_rows > 0) $status = 'removal not made';
}

$conn_poke->close();
exit($status);

?>
