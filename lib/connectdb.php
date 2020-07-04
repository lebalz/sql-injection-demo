<?php
function connectdb($database = "inject_demodb")
{
  if ($database == '') {
    $database = NULL;
  }
  $host = isset($_ENV['SQL_INJECTION_DB_HOST']) ? $_ENV['SQL_INJECTION_DB_HOST'] : 'localhost';
  $port = isset($_ENV['SQL_INJECTION_DB_PORT']) ? $_ENV['SQL_INJECTION_DB_PORT'] : 3306;
  $username = isset($_ENV['SQL_INJECTION_DB_USERNAME']) ? $_ENV['SQL_INJECTION_DB_USERNAME'] : 'sql_injection';
  $password = isset($_ENV['SQL_INJECTION_DB_PASSWORD']) ? $_ENV['SQL_INJECTION_DB_PASSWORD'] : 'foobar';
  $db = mysqli_connect($host, $username, $password, $database, $port);
  if (!$db) {
    echo("Connection failed: " . mysqli_connect_error());
  }
  return $db;
}
?>