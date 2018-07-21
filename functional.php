<?php

function emoticonBuilder($code){
  $bin = hex2bin(str_repeat('0', 8 - strlen($code)) . $code);
  $emoticon =  mb_convert_encoding($bin, 'UTF-8', 'UTF-32BE');
  return $emoticon;
}


function executeQuery($conn, $query){
  $result = pg_query($query) or die('Query failed: ' . pg_last_error());
  $array = pg_fetch_all($result);
  return $array;
}
?>
