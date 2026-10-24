<?php

function setup()
{
  if (session_status() == PHP_SESSION_NONE) {
    session_start();
  }
  if (!isset($_SESSION['user_id'])) return false;
  require_once('connectdb.php');
  return true;
}

function current_user()
{
  if (!setup()) return false;
  $user_id = $_SESSION['user_id'];
  // don't do it like that in productive systems
  // session variables can be changed and thus sql
  // injection is possible here aswell!
  $query = "SELECT * from users WHERE id=$user_id";
  $db = NULL;
  try {
    $db = connectdb();
    $result = run_query($db, $query);
    if ($result) {
      $user = mysqli_fetch_array($result, MYSQLI_ASSOC);
      mysqli_free_result($result);
      if ($user) return $user;
    }
  } catch (Throwable $e) {
    $_SESSION['sql_errors'][] = $e->getMessage();
  } finally {
    if ($db) mysqli_close($db);
  }
  unset($_SESSION['user_id']);
  return false;
}

function logged_in()
{
  return current_user() !== false;
}

function is_admin()
{
  $user = current_user();
  return $user && $user['role'] == 'admin';
}
