<?php
if (PHP_SAPI !== 'cli') {
  http_response_code(404);
  exit;
}

// Run inside the devcontainer. These checks drop and recreate its demo database.
require_once(__DIR__ . '/../lib/connectdb.php');
$base_url = $argv[1] ?? 'http://127.0.0.1:8000';

function check($condition, $message)
{
  if (!$condition) throw new RuntimeException($message);
}

function request($path, $data = NULL, $status = 200)
{
  global $base_url;
  static $cookie = '';
  $context = stream_context_create(array('http' => array(
    'method' => $data === NULL ? 'GET' : 'POST',
    'header' => "Content-Type: application/x-www-form-urlencoded\r\nCookie: $cookie\r\n",
    'content' => $data === NULL ? '' : http_build_query($data),
    'follow_location' => 0,
    'ignore_errors' => true,
    'timeout' => 10
  )));
  $body = file_get_contents($base_url . $path, false, $context);
  $headers = http_get_last_response_headers();
  check($body !== false, "Request failed: $path");
  check(str_contains($headers[0], " $status "), "Unexpected HTTP status for $path: " . $headers[0]);
  check(!preg_match('/Warning:|Deprecated:|Fatal error:|Uncaught|<b>(Warning|Deprecated|Fatal error)<\/b>/', $body), "PHP error in $path: $body");
  foreach ($headers as $header) {
    if (preg_match('/^Set-Cookie: (PHPSESSID=[^;]+)/i', $header, $matches)) {
      $cookie = $matches[1];
    }
  }
  if ($status == 302) {
    check(in_array('Location: ../index.php', $headers), "Missing redirect for $path");
    check(trim($body) === '', "Output before redirect in $path");
  }
  return $body;
}

function filter($blend)
{
  return request('/index.php?' . http_build_query(array('blend' => $blend)));
}

function login($name, $password)
{
  request('/lib/login.php', array('username' => $name, 'password' => $password), 302);
}

function recreate()
{
  request('/lib/recreate_and_seed_db.php', array(), 302);
}

check(database_name() == 'inject_demodb', 'Run this test against the devcontainer database.');
$db = connectdb();
$result = mysqli_query($db, 'SELECT DATABASE()');
check(mysqli_fetch_row($result)[0] == 'inject_demodb', 'Default database was not selected.');
mysqli_close($db);
$db = connectdb('');
$result = mysqli_query($db, 'SELECT DATABASE()');
check(mysqli_fetch_row($result)[0] === NULL, 'An empty database argument should select no database.');
mysqli_close($db);
$db = connectdb('information_schema');
$result = mysqli_query($db, 'SELECT DATABASE()');
check(mysqli_fetch_row($result)[0] == 'information_schema', 'Explicit database argument was ignored.');
try {
  mysqli_select_db($db, 'mysql');
  throw new RuntimeException('The demo user should not have access to the mysql database.');
} catch (mysqli_sql_exception $e) {
  check($e->getCode() == 1044, 'Expected a database permission error.');
}
mysqli_close($db);

$original_url = getenv('DATABASE_URL');
putenv('DATABASE_URL=mysql://sql%5Finjection:foo%62ar@mysql/inject%5Fdemodb');
$db = connectdb();
$result = mysqli_query($db, 'SELECT DATABASE()');
check(mysqli_fetch_row($result)[0] == 'inject_demodb', 'DATABASE_URL without a port or with encoded credentials failed.');
mysqli_close($db);
putenv($original_url === false ? 'DATABASE_URL' : 'DATABASE_URL=' . $original_url);
echo "Database configuration and permissions passed.\n";

$body = request('/index.php');
check(!str_contains($body, 'ERROR:'), 'Fresh database was not seeded.');
$rows = substr_count($body, '<tr>');
check($rows > 100, 'Expected the seeded coffee products.');
check(substr_count(filter('Good-morning Symphony'), '<tr>') == 2, 'Product filtering failed.');
check(substr_count(filter("not-a-blend' OR 1=1 -- "), '<tr>') == $rows, 'Filter injection no longer works.');
$body = filter("' OR 1=1;--");
check(substr_count($body, '<tr>') == $rows, 'The semicolon injection should show every product.');
check(!str_contains($body, 'ERROR:'), 'Trailing SQL must not hide a successful first result.');
check(str_contains(filter("'"), 'ERROR:'), 'Malformed SQL should show an error.');

login('test', 'wrong-password');
check(str_contains(request('/index.php'), 'Login Failed.'), 'Invalid login was accepted.');
login('test', 'test');
$body = request('/index.php');
check(str_contains($body, '>test</span>'), 'Consumer login failed.');
check(!str_contains($body, 'Löschen'), 'Consumer should not see admin controls.');
check(str_contains($body, 'button name="id"'), 'Cart form uses the wrong field name.');
request('/lib/add_to_cart.php', array('id' => 1), 302);
check(str_contains(request('/index.php'), '1🛒'), 'Cart item was not added.');
request('/lib/logout.php', array(), 302);
check(!str_contains(request('/index.php'), 'Logout'), 'Logout failed.');

login('admin', 'admin');
check(str_contains(request('/index.php'), 'Löschen'), 'Admin controls are missing.');
request('/lib/delete_item.php', array('id' => 1), 302);
check(substr_count(request('/index.php'), '<tr>') == $rows - 1, 'Admin deletion failed.');
recreate();
check(substr_count(request('/index.php'), '<tr>') == $rows, 'Recreate did not restore the coffee data.');
check(!str_contains(request('/index.php'), 'Logout'), 'Recreate did not clear the session.');
echo "Filtering, login, cart, deletion and reset passed.\n";

login("' OR role='admin' -- ", '');
check(str_contains(request('/index.php'), '>admin</span>'), 'Login injection no longer works.');
filter("not-a-blend'; UPDATE coffee SET blend_name='Migration test' WHERE id=1; SELECT 1; -- ");
check(substr_count(filter('Migration test'), '<tr>') == 2, 'Later injected statements were not executed.');
$body = filter("' OR 1=1; SELECT * FROM missing_table; -- ");
check(substr_count($body, '<tr>') == $rows, 'A later statement error should preserve the first result.');
check(!str_contains($body, 'ERROR:'), 'A later statement error should not replace the first result.');
request('/lib/logout.php', array(), 302);
check(str_contains(request('/lib/login.php', array('username' => "'", 'password' => ''), 500), 'ERROR:'), 'Malformed login SQL should show an error.');
check(str_contains(request('/lib/delete_item.php', array('id' => 'invalid SQL'), 500), 'ERROR:'), 'Malformed delete SQL should show an error.');
check(str_contains(request('/lib/delete_item.php', array('id' => '0; SELECT * FROM missing_table'), 500), 'ERROR:'), 'A later error without a first SELECT result should still be displayed.');

login('admin', 'admin');
filter("not-a-blend'; DROP TABLE coffee; DROP TABLE users; -- ");
check(str_contains(request('/index.php'), 'ERROR:'), 'Missing tables should show an error without crashing.');
recreate();
check(substr_count(request('/index.php'), '<tr>') == $rows, 'Recreate failed after dropping both tables.');
login('admin', 'admin');
filter("not-a-blend'; DROP DATABASE inject_demodb; -- ");
check(str_contains(request('/index.php'), 'ERROR:'), 'Missing database should show an error without crashing.');
recreate();
check(substr_count(request('/index.php'), '<tr>') == $rows, 'Recreate failed after DROP DATABASE.');
login('test', 'test');
request('/lib/logout.php', array(), 302);
echo "SQL injection examples and recovery passed.\n";
