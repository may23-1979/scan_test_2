<?php
// vulnerable.php
$id = $_GET['id'];                      // ユーザ入力
$sql = "SELECT * FROM users WHERE id = $id";  // ★ SQLi
$db  = new PDO('sqlite::memory:');
$db->query($sql);
