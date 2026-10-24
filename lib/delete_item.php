<?php
  require_once('connectdb.php');
  if (isset($_POST['id'])) {
    $id = $_POST['id'];
    // vulnerable...
    $query = "DELETE FROM coffee WHERE id=$id";
    $db = NULL;
    try {
      $db = connectdb();
      $result = run_query($db, $query);
      if ($result) mysqli_free_result($result);
    } catch (Throwable $e) {
      http_response_code(500);
      require_once(__DIR__ . '/show_sql_error.php');
      show_sql_error($e->getMessage());
      return;
    } finally {
      if ($db) mysqli_close($db);
    }
  }
  header('Location: ../index.php');
?>
