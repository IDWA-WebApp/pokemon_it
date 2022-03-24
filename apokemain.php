<?php

if (!isset($_SESSION)) session_start(); //������ session ��� ������ ����������� ��� ����

//��������� ������
require('apokeclass.php'); //class ��� curl ��� functions �� endpoint
require('apokedata.php'); //�������� �������� �� mariadb ��� ��������/����������
require('apokecalls.php'); //������ �� ��� ����������� ��� ���������

//�� class ��� curl
use TestPokePHP\TestPokeApi;
$pokeapi = new TestPokeApi();

//������� ��� ������� �� ��� ����
$conn_poke = new mysqli($servername_poke, $username_poke, $password_poke, $dbname_poke);
if ($conn_poke->connect_error) { die('DB Connection failed. Please try again in a while.1'); }
$conn_poke->set_charset('utf8');


//������� ��� ��� pokemon ��� ���������� ��� db (� ������ ��� ������ ���� ��� �� session ��� ������)
testForNewPokemons();


//requests
$sqloffset = request_get('offset', 'intval', 'no', '0');
$sqllimit = request_get('limit', 'intval', 'no', '20');


//����� ��������� ��� ������� ������ pokemon, �������� ��� ���� (body)
function ListOfPokemonsFromDB() {
 global $sql_stat_fields, $sqloffset, $sqllimit;
 
 //���� ��������� ��� ���� �� ���������� �� ���� �� ����� (descending) �� pagination ��� 20 ��� �������� �������
 getThePokemons($PokeCount, $PokeStats, $PokeData);
 
 //������� ������ pokemon
 for ($i = 1; $i <= sizeof($PokeData); $i++) {
  //������� ��� �����-������� ����� ��� ���������� ��������
  echo('<div class="main_table main_table_' . oddeventext($i) . '">' . PHP_EOL);
  
  echo(' <div class="sub_table_left">' . PHP_EOL);
  
  //������� ��� ������� id pokemon ��� ��������� ��� ����� 4����� ���� �� ����� ������ ��� ���������� ��� ��������������
  $fnt = 'style="margin-top:1%;"';
  if ($PokeData[$i]['id'] > '999') $fnt = 'style="letter-spacing:-5px; font-size:7.5vw; margin-top:3%;"';
  echo('  <div class="poke_id" ' . $fnt . '>#' . $PokeData[$i]['id'] . '</div>' . PHP_EOL);
  
  //���� �� div ������ ��������
  echo('  <div class="poke_circle left0"><div class="poke_circle_text">' . number_format(intval($PokeData[$i]['height'])/10, 1) . 'm</div></div>' . PHP_EOL);
  //����� �� div ������ �����
  echo('  <div class="poke_circle right0"><div class="poke_circle_text">' . number_format(intval($PokeData[$i]['weight'])/10, 1) . 'Kg</div></div>' . PHP_EOL);
  
  //��������, ����������, �����, �����
  echo('  <div class="poke_descs">' . PHP_EOL);
  echo('   <div><span class="poke_name">' . ucfirstletters($PokeData[$i]['name']) . '</div>' . PHP_EOL);
  echo('   <div><span class="poke_spec">' . $PokeData[$i]['genera'] . '</div>' . PHP_EOL);
  
  //���� ���� �������� ��� �� ����� �� css ���� ��������� ��� �����
  echo('   <div>');
  echo('    <span class="poke_type"><span class="poke_type_' . strtolower($PokeData[$i]['types2']) . '">' . strtoupper($PokeData[$i]['types2']) . '</span>');
  //������� ��� �������� ��� �������������� ����� ��� �� ���� ������������
  if (($PokeData[$i]['types2']!='') and ($PokeData[$i]['types1']!='')) echo('    <span style="color:#aaa;"> &bull; </span>');
  echo('    <span class="poke_type_' . strtolower($PokeData[$i]['types1']) . '">' . strtoupper($PokeData[$i]['types1']) . '</span>');
  echo('   </div>' . PHP_EOL);
  
  //�� abilities ���� �� ���������� ����� ����������� ��� ���� �� hidden
  echo('   <div><span class="poke_abil">' . ucfirstletters($PokeData[$i]['abilities']) . '</div>' . PHP_EOL);
  echo('  </div>' . PHP_EOL);
  
  //image ��� pokemon
  echo('  <div class="poke_img_cont" title="#' . $PokeData[$i]['id'] . '"><img src="' . $PokeData[$i]['img'] . '" class="poke_img"></div>' . PHP_EOL);
  
  echo(' </div>' . PHP_EOL);
  
  //����� �����������
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



//������� pagination
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
  echo(' <div class="d-table-cell mg0a txtcenter nwrap" style="height:1.5vw; width:50%;">' . PHP_EOL);
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
  echo(' <div class="btnnext d-table-cell mg0a txtcenter nwrap" style="height:1.5vw; width:50%;">' . PHP_EOL);
  echo('  <button title="Next Page" class="d-inblock buttprevnext mgb1vw mpointer pdpagi1 ' . $isvisible . '"' . $jscript1 . '><span class="nextone">&nbsp;&#8250;&nbsp;</span></button>&nbsp;' . PHP_EOL);
  echo('  <button title="Last Page" class="d-inblock buttprevnext mgb1vw mpointer pdpagi2 ' . $isvisible . '"' . $jscript2 . '>&#8250;&#8250;</button>' . PHP_EOL);
  echo(' </div>' . PHP_EOL);
 }
 if ($prevnext=='next-b') {
  echo(' <button title="Last Page" class="d-inblock buttprevnext mpointer float-r pdpagi2 ' . $isvisible . '" style="margin-top:1vw; margin-right:5vw;"' . $jscript2 . '>&#8250;&#8250;</button>' . PHP_EOL);
  echo(' <button title="Next Page" class="d-inblock buttprevnext mpointer float-r pdpagi1 ' . $isvisible . '" style="margin-top:1vw; margin-right:5vw;"' . $jscript1 . '>&nbsp;&#8250;&nbsp;</button>' . PHP_EOL);
 }
}


//������� ��������� � ������������� ���������� ��� reset
function paginationPageNumber() {
 global $LastTableEntry, $sqloffset, $sqllimit, $cur_results;
 if (($sqloffset + $sqllimit) > $LastTableEntry) $sqloffset = $LastTableEntry - $sqllimit;
 if (($sqllimit<0) or ($sqllimit>50)) $sqllimit = 20;
 echo(' <div class="pos-rel txtcenter mg0a" style="height:2vw; font-size:1vw; font-weight:600; color:#555;">' . ($LastTableEntry > 0 ? ('Showing ' . ($sqloffset + 1) . ' to ' . ($sqloffset + ($LastTableEntry > $sqllimit ? $sqllimit : $LastTableEntry)) . ' (from ' . $LastTableEntry . ')') : 'No Results') . '</div>' . PHP_EOL);
}

?>
