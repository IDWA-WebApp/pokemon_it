<?php

namespace TestPokePHP;

class TestPokeApi {
 
 //the API url address for the calls
 public function __construct() {
  $this->apiUrl = 'https://pokeapi.co/api/v2/';
 }
 
 //from the API documention, the call for list of results (with offset and limit)
 public function resourceList($endpoint, $limit = null, $offset = null) {
  $url = $this->apiUrl.$endpoint.'/?limit='.$limit.'&offset='.$offset;
  return $this->SendCurlRequest($url);
 }
 
 //from the API documention, the call for details 'ability' (id or name of ability)
 public function ability($pokeid) {
  $url = $this->apiUrl.'ability/'.$pokeid;
  return $this->SendCurlRequest($url);
 }
 
 //from the API documention, the call for details 'pokemon' (id or name of pokemon)
 public function pokemon($pokeid) {
  $url = $this->apiUrl.'pokemon/'.$pokeid;
  return $this->SendCurlRequest($url);
 }
 
 //from the API documention, the call for details 'pokemon-species' (id or name of pokemon-species)
 public function pokemonspecies($pokeid) {
  $url = $this->apiUrl.'pokemon-species/'.$pokeid;
  return $this->SendCurlRequest($url);
 }
 
 //from the API documention, the call for details 'stat' (id or name of stat)
 public function pokemonstat($pokeid) {
  $url = $this->apiUrl.'stat/'.$pokeid;
  return $this->SendCurlRequest($url);
 }
 
 //from the API documention, the call for details 'type' (id or name of type)
 public function pokemontype($pokeid) {
  $url = $this->apiUrl.'type/'.$pokeid;
  return $this->SendCurlRequest($url);
 }
 
 //curl the Pokemon API 
 public function SendCurlRequest($url) {
  $curl = curl_init();
  $timeout = 20;
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
  $data = curl_exec($curl);
  $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);
  if ($http_code != 200) {
   return json_encode(['httpcode'=>$http_code, 'message'=>$data]);
  }
  return $data;
 }
}

?>
