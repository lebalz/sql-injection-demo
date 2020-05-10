<?php
function connectdb($database = "inject_demodb")
{
  if ($database == '') {
    $database = NULL;
  }
  $host = "ssh.balthasarhofer.ch";
  $port = 21;
  $username = "sql_injection";
  $password = "VeHUutCp7Z9SQYTHP4I55oCzz6ohaT5R";
  $db = mysqli_connect($host, $username, $password, $database, $port);
  if (!$db) {
    echo("Connection failed: " . mysqli_connect_error());
  }
  return $db;
}
?>