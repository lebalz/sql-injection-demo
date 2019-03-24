<?php
function show_table($blend_name)
{
  ?>
  <table class="table border">
    <tr>
      <th>ID</th>
      <th>BlendName</th>
      <th>Origin</th>
      <th>Variety</th>
      <th>Notes</th>
      <th>Intensifier</th>
      <th>Price</th>
    </tr>
    <?php
      require_once('connectdb.php');
      // vulnerability to sql injection
      $query = "SELECT * FROM coffee WHERE blend_name LIKE '%$blend_name%';";
    ?>
    <br>
    <div class="card">
      <div class="card-body">
        Query: <code>
          <?php
            echo($query);
          ?>
        </code>
      </div>
    </div>
    <br>
    <h3>Kaffee</h3>
    <?php
      $db = connectdb();
      // mysqli_multi_query is required. With mysqli_query only the first
      // query statement is performed to prevent sql injection...
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
        echo('ERROR: ' . mysqli_error($db));
      }
      mysqli_close($db);
    ?>
  </table>
  <?php
}
?>
