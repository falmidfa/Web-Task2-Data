<?php
// api/toggle.php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../config.php';

// Optional: show PHP errors while testing (remove later)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Make sure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
  exit;
}

// Get JSON data from fetch body
$payload = json_decode(file_get_contents('php://input'), true);
$id = isset($payload['id']) ? (int)$payload['id'] : 0;

// Validate ID
if ($id <= 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid ID']);
  exit;
}

try {
  $pdo->beginTransaction();

  // Lock the row for update
  $stmt = $pdo->prepare("SELECT status FROM users WHERE id = :id FOR UPDATE");
  $stmt->execute([':id' => $id]);
  $user = $stmt->fetch();

  if (!$user) {
    throw new RuntimeException('User not found');
  }

  // Toggle the value (if 1 → 0, if 0 → 1)
  $newStatus = $user['status'] ? 0 : 1;

  // Update in DB
  $update = $pdo->prepare("UPDATE users SET status = :status WHERE id = :id");
  $update->execute([':status' => $newStatus, ':id' => $id]);

  $pdo->commit();

  // Return JSON response
  echo json_encode(['id' => $id, 'status' => $newStatus]);
  exit;

} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
  exit;
}
