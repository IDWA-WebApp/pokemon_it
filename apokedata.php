<?php

//database connection information
$servername_poke = 'server';
$username_poke = 'username';
$password_poke = 'password';
$dbname_poke = 'db_name';

$cur_results = 0;
$LastTableEntry = 0; if (isset($_SESSION['LastTableEntry'])) $LastTableEntry = $_SESSION['LastTableEntry'];
$sql_stat_fields = array('speed'          =>'Speed',
                         'specialdefense' =>'Special Defense',
                         'specialattack'  =>'Special Attack',
                         'defense'        =>'Defense',
                         'attack'         =>'Attack',
                         'hp'             =>'Hp');

?>
