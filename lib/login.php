
  <?php
  session_start();
  
  if (isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;    
  }
  
  $name = $_POST['username'];
  $password = $_POST['password'];
  $query = "SELECT * FROM users WHERE name='$name' AND password='$password';";
  
  require_once('connectdb.php');
  $db = connectdb();
  $result = mysqli_multi_query($db, $query);
  if ($result) {
    $result = mysqli_use_result($db);
  }
  if ($result) {
    $user = mysqli_fetch_array($result, MYSQLI_ASSOC);
    if ($user['name']) {
      $_SESSION['user'] = $user['name'];
    }
  }
  if (!isset($_SESSION['user'])) {
    $_SESSION['login_error'] = true;
  }
  mysqli_close($db);


  header('Location: ../index.php');
  ?>