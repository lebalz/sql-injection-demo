<?php
function showTable($blend)
{
  ?>
  <table class="table border">
    <tr><th>id</th><th>Blend</th><th>Origin</th><th>Variety</th><th>Notes</th><th>Intensifier</th><th>Price</th></tr>
    <?php
    require_once('connectdb.php');
    $db = connectdb();
    $query = "SELECT * FROM coffee WHERE blend_name LIKE '%". $blend . "%';";
    ?>
    <h6>Query:</h6>
    <code>
      <?php
        echo($query);
      ?>
    </code>
    <br>
    <br>
    <h3>Kaffee</h3>
    <?php
    $result = mysqli_multi_query($db, $query);
    if ($result) {
      $result = mysqli_use_result($db);
    }
    if ($result) {
      while ($coffee = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        echo ('<tr>');
        foreach ($coffee as $attr) {
          echo ('<td>' . $attr . '</td>');
        }
        echo ('</tr>');
      }
    } else {
      echo('ERROR: not working!');
    }
    mysqli_close($db);
    ?>
  </table>

<?php

}
?>
