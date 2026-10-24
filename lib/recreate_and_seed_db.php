<?php
  session_start();
  require_once('connectdb.php');
  $db = NULL;
  try {
    $database = database_name();
    $db = connectdb('');
    $quoted_database = '`' . str_replace('`', '``', $database) . '`';
    mysqli_query($db, "CREATE DATABASE IF NOT EXISTS $quoted_database;");
    mysqli_select_db($db, $database);

    // coffee table
    mysqli_query($db, 'DROP TABLE IF EXISTS coffee;');
    mysqli_query($db, file_get_contents(__DIR__ . '/../sql/create_db.sql'));
    mysqli_query($db, file_get_contents(__DIR__ . '/../sql/coffee_data.sql'));

    // user table
    run_query($db, file_get_contents(__DIR__ . '/../sql/users.sql'));
  } catch (Throwable $e) {
    http_response_code(500);
    echo ('ERROR: ' . $e->getMessage());
    return;
  } finally {
    if ($db) mysqli_close($db);
  }
  $_SESSION = array();
  header('Location: ../index.php');
?>
