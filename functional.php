<?php

function emoticonBuilder($code){
  $bin = hex2bin(str_repeat('0', 8 - strlen($code)) . $code);
  $emoticon =  mb_convert_encoding($bin, 'UTF-8', 'UTF-32BE');
  return $emoticon;
}


function executeQuery($pdo, $query){
  try{
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt;
  } catch (PDOException $e) {
    echo $e->getMessage();
  }
}
?>
