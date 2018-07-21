<?php
require __DIR__ . '/vendor/autoload.php';
use \Dotenv\Dotenv;

$dotenv = new Dotenv(__DIR__);
$dotenv->load();
try{
//Set DSN data source name
  $db = array('host'=>getenv('DB_HOST'), 'dbname'=>getenv('DB_NAME'), 'user'=>getenv('DB_USERNAME'), 'password'=>getenv('DB_PASSWORD'), 'port'=>5432);
  $host = $db['host'];
  $port = $db['port'];
  $dbname = $db['dbname'];
  $user = $db['user'];
  $password = $db['password'];

  $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password;sslmode=require;";
//create a pdo instance
  $pdo = new PDO($dsn, $db['user'], $db['password']);
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_OBJ);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  echo "Connected ! Grats bro.";
}
catch (PDOException $e) {
  echo 'Connection failed: ' . $e->getMessage();
}
?>
