<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function database_name()
{
  if (getenv('DATABASE_URL')) {
    $db_props = parse_url(getenv('DATABASE_URL'));
    if (!$db_props || empty($db_props['host']) || empty($db_props['path']) || $db_props['path'] == '/') {
      throw new InvalidArgumentException('DATABASE_URL must include a host and database name.');
    }
    return rawurldecode(substr($db_props['path'], 1));
  }
  return 'inject_demodb';
}

function connectdb($database = NULL)
{
  $default_database = database_name();
  if (getenv('DATABASE_URL')) {
    $db_props = parse_url(getenv('DATABASE_URL'));
    $host = $db_props['host'];
    $port = (int) ($db_props['port'] ?? 3306);
    $username = rawurldecode($db_props['user'] ?? '');
    $password = rawurldecode($db_props['pass'] ?? '');
  } else {
    $host = getenv('SQL_INJECTION_DB_HOST') ? getenv('SQL_INJECTION_DB_HOST') : 'localhost';
    $port = (int) (getenv('SQL_INJECTION_DB_PORT') ? getenv('SQL_INJECTION_DB_PORT') : 3306);
    $username = getenv('SQL_INJECTION_DB_USERNAME') ? getenv('SQL_INJECTION_DB_USERNAME') : 'sql_injection';
    $password = getenv('SQL_INJECTION_DB_PASSWORD') ? getenv('SQL_INJECTION_DB_PASSWORD') : 'foobar';
  }

  // An empty string connects without selecting a database, so Recreate also works after DROP DATABASE.
  $database = $database === NULL ? $default_database : $database;
  return mysqli_connect($host, $username, $password, $database === '' ? NULL : $database, $port);
}

function run_query($db, $query)
{
  // Keep multi-statement queries for the injection examples and return the first result for display.
  mysqli_multi_query($db, $query);
  $result = mysqli_store_result($db);
  while (mysqli_more_results($db)) {
    try {
      mysqli_next_result($db);
    } catch (mysqli_sql_exception $e) {
      // Display the first SELECT even if a later injected statement fails.
      if ($result) return $result;
      throw $e;
    }
    $next_result = mysqli_store_result($db);
    if ($next_result) {
      mysqli_free_result($next_result);
    }
  }
  return $result;
}
