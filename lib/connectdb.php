<?php
function connectdb($database = NULL)
{
  if (getenv('DATABASE_URL')) {
    //  http://username:password@hostname:9090/path
    $db_props = parse_url(getenv('DATABASE_URL'));
    $host = $db_props['host'];
    $port = $db_props['port'];
    $username = $db_props['user'];
    $password = $db_props['pass'];
    $database = $database ? $database : substr($db_props['path'], 1); // remove the slash from '/path'
  } else {
    if ($database == '') {
      $database = NULL;
    } else {
      $database = "inject_demodb";
    }
    $host = getenv('SQL_INJECTION_DB_HOST') ? getenv('SQL_INJECTION_DB_HOST') : 'localhost';
    $port = getenv('SQL_INJECTION_DB_PORT') ? getenv('SQL_INJECTION_DB_PORT') : 3306;
    $username = getenv('SQL_INJECTION_DB_USERNAME') ? getenv('SQL_INJECTION_DB_USERNAME') : 'sql_injection';
    $password = getenv('SQL_INJECTION_DB_PASSWORD') ? getenv('SQL_INJECTION_DB_PASSWORD') : 'foobar';
  }

  try {
    $db = mysqli_connect($host, $username, $password, $database, $port);
    if (!$db) {
      echo ("Connection failed: " . mysqli_connect_error());
    }
    return $db;
  } catch (Error $e) {
    echo ("Connection failed: " . $e);
  } catch (Exception $e) {
    echo ("Connection failed: " . $e);
  }
}
