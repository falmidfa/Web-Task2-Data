<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../config.php';

$name = trim($_POST['name'] ?? '');
$age  = trim($_POST['age'] ?? '');

if ($name === '' || $age === '' || !ctype_digit($age)) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid input']);
  exit;
}

try {
  $stmt = $pdo->prepare("INSERT INTO users (name, age) VALUES (:name, :age)");
  $stmt->execute([':name' => $name, ':age' => (int)$age]);
  echo json_encode(['id' => (int)$pdo->lastInsertId(), 'name' => $name, 'age' => (int)$age]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
