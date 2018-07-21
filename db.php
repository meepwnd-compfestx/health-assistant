<?php
try{
//Set DSN data source name
  $db = array('host'=>$_ENV'DB_HOST', 'dbname'=>$_ENV'DB_NAME', 'user'=>$_ENV'DB_USERNAME', 'password'=>$_ENV'DB_PASSWORD', 'port'=>5432);
  $host = $db['host'];
  $port = $db['port'];
  $dbname = $db['dbname'];
  $user = $db['user'];
  $password = $db['password'];

  $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password;";
//create a pdo instance
  $pdo = new PDO($dsn, $user, $password);
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_OBJ);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
  echo 'Connection failed: ' . $e->getMessage();
}
?>
