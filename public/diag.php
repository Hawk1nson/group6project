<?php
// Diagnostic script to verify that paths and includes are set up correctly

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function ok($label, $bool)
{
    echo ($bool ? "✅ " : "❌ ") . $label . "<br>";
}

$root = realpath(__DIR__ . '/..');     // /Applications/XAMPP/xamppfiles/htdocs/cdms
$pub  = __DIR__;                       // …/cdms/public
$lib  = $root . '/lib';
$cfg  = $root . '/config';

echo "<h3>Paths</h3>";
echo "root: $root<br>public: $pub<br>lib: $lib<br>config: $cfg<br><hr>";

echo "<h3>File existence</h3>";
ok("../lib/helpers.php", file_exists($lib . "/helpers.php"));
ok("../lib/auth.php",    file_exists($lib . "/auth.php"));
ok("../lib/db.php",      file_exists($lib . "/db.php"));
ok("../config/config.php", file_exists($cfg . "/config.php"));

echo "<hr><h3>Requiring files</h3>";
try {
    require_once $lib . "/helpers.php";
    echo "✅ helpers loaded<br>";
} catch (Throwable $e) {
    echo "❌ helpers error: " . $e->getMessage() . "<br>";
}
try {
    require_once $lib . "/auth.php";
    echo "✅ auth loaded<br>";
} catch (Throwable $e) {
    echo "❌ auth error: " . $e->getMessage() . "<br>";
}
try {
    require_once $lib . "/db.php";
    echo "✅ db loaded<br>";
} catch (Throwable $e) {
    echo "❌ db error: " . $e->getMessage() . "<br>";
}

echo "<hr><h3>DB smoke test</h3>";
try {
    require_once $lib . "/db.php";
    $pdo = DB::conn();
    $n = (int)$pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn();
    echo "✅ DB OK, employees: $n<br>";
} catch (Throwable $e) {
    echo "❌ DB error: " . $e->getMessage();
}
?>

<?php require_once __DIR__ . '/bootstrap.php'; ?>