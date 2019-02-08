<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css"
    integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
</head>

<body>
  <h1>Onlineshop Kaffee</h1>
  <form action="index.php" method="get">
    Blend: <input type="text" name="blend"><br>
    <input type="submit">
  </form>
  <?php
      require_once('showTable.php');
      showTable(array_key_exists('blend', $_GET) ? $_GET['blend'] : '');
    ?>
  </table>
</body>

</html>