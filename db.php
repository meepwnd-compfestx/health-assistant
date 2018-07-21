<?php
require __DIR__ . '/vendor/autoload.php';
use \Dotenv\Dotenv;
//load env
$dotenv = new Dotenv(__DIR__);
$dotenv->load();

$db = array('host'=>$_ENV['DB_HOST'], 'dbname'=>$_ENV['DB_NAME'], 'user'=>$_ENV['DB_USERNAME'], 'password'=>$_ENV['DB_PASSWORD'], 'port'=>5432);

$host = $db['host'];
$port = $db['port'];
$dbname = $db['dbname'];
$user = $db['user'];
$password = $db['password'];

$conn = pg_connect("host=".$host." port=".$port." dbname=".$dbname." user=".$user." password=".$password)
  or die('Could not connect: ' . pg_last_error());
?>
