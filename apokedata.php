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

//sanitize variables for use in sql
$iBlackList = 41;
$BlackList = array();
$BlackList[1] = '"';
$BlackList[2] = '|';
$BlackList[3] = '--';
$BlackList[4] = ';';
$BlackList[5] = '/*';
$BlackList[6] = '*/';
$BlackList[7] = '@@';
$BlackList[8] = 'CHAR ';
$BlackList[9] = 'NCHAR ';
$BlackList[10] = 'VARCHAR ';
$BlackList[11] = 'NVARCHAR ';
$BlackList[12] = 'ALTER ';
$BlackList[13] = 'BEGIN ';
$BlackList[14] = 'CAST ';
$BlackList[15] = 'CREATE ';
$BlackList[16] = 'CURSOR ';
$BlackList[17] = 'DECLARE ';
$BlackList[18] = 'DELETE ';
$BlackList[19] = 'DROP ';
$BlackList[20] = 'END ';
$BlackList[21] = 'EXEC ';
$BlackList[22] = 'EXECUTE ';
$BlackList[23] = 'FETCH ';
$BlackList[24] = 'INSERT ';
$BlackList[25] = 'KILL ';
$BlackList[26] = 'OPEN ';
$BlackList[27] = 'SELECT ';
$BlackList[28] = ' SYS ';
$BlackList[29] = 'SYSOBJECT ';
$BlackList[30] = 'SYSCOLUMNS ';
$BlackList[31] = 'TABLE ';
$BlackList[32] = 'UPDATE ';
$BlackList[33] = '`';
$BlackList[34] = '\'';
$BlackList[35] = '\\';
$BlackList[36] = '�';
$BlackList[37] = ' OR ';
$BlackList[38] = '<';
$BlackList[39] = '>';
$BlackList[40] = '�';
$BlackList[41] = '�';

?>
