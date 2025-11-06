<?php
$password = '123456';

$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h1>Generador de Hash</h1>";
echo "<pre>" . htmlspecialchars($hash) . "</pre>";
?>