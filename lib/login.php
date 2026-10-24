<?php
  session_start();
  require_once('helpers.php');

  if (logged_in()) {
    header('Location: ../index.php');
    exit;
  }

  $name = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';
  // Intentionally vulnerable to SQL injection.
  $query = "SELECT * FROM users WHERE name='$name' AND password='$password';";

  require_once('connectdb.php');
  $db = NULL;
  try {
    $db = connectdb();
    $result = run_query($db, $query);
    if ($result) {
      $user = mysqli_fetch_array($result, MYSQLI_ASSOC);
      mysqli_free_result($result);
      if (isset($user['id'])) {
        $_SESSION['user_id'] = $user['id'];
      }
    }
  } catch (Throwable $e) {
    http_response_code(500);
    echo ('ERROR: ' . $e->getMessage());
    return;
  } finally {
    if ($db) mysqli_close($db);
  }

  if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = true;
  }
  header('Location: ../index.php');
?>
