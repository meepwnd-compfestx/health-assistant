<?php
try{
//Set DSN data source name
  $db = array('host'=>getenv('DB_HOST'), 'dbname'=>getenv('DB_NAME'), 'user'=>getenv('DB_USERNAME'), 'password'=>getenv('DB_PASSWORD'), 'port'=>"5432");
  $dsn = "pgsql:host=" . $db['host'] . ";port=" . $db['port'] .";dbname=" . $db['dbname'] . ";user=" . $db['user'] . ";password=" . $db['password'] . ";";
//create a pdo instance
  $pdo = new PDO($dsn, $db['user'], $db['password']);
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_OBJ);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

}
catch (PDOException $e) {
  echo 'Connection failed: ' . $e->getMessage();
}

function executeQuery($pdo, $query){
  try{
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt;
  } catch (PDOException $e) {
    echo $e->getMessage();
  }
?>
