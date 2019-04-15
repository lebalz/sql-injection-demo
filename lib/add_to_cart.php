<?php  
  session_start();
  if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;    
  }
  $id = $_POST['id'];
  if (!isset($_SESSION['cart_items'])) {
    $_SESSION['cart_items'] = array();
  }
  array_push($_SESSION['cart_items'], $id);
  header('Location: ../index.php');
 ?>