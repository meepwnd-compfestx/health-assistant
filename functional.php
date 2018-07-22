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

function generateDay($currDay){
  $strDay = "";

  if ($currDay == 1) {
    // code...
    return "'SENIN'";
  } else if ($currDay == 2) {
    // code...
    return "'SELASA'";
  } else if ($currDay == 3) {
    // code...
    return "'RABU'";
  } else if ($currDay == 4) {
    // code...
    return "'KAMIS'";
  } else if ($currDay == 5) {
    // code...
    return "'JUMAT'";
  } else if ($currDay == 6) {
    // code...
    return "'SABTU'";
  } else if ($currDay == 7) {
    // code...
    return "'MINGGU'";
  }
}
?>
