<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/routes/web.php';

$database = new Database();
$db = $database->getConnection();

$route = $_GET['route'] ?? 'books';
dispatch($route, $db);