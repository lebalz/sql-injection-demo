<?php
function connectdb()
{
  $host = "mdm2016.bbz.cloud";
  $port = 21;
  $database = "inject_demodb";
  $username = "sql_injection";
  $password = "VeHUutCp7Z9SQYTHP4I55oCzz6ohaT5R";
  $db = mysqli_connect($host, $username, $password, $database, $port);
  if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
  }
  return $db;
}
?>