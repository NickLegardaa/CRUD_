<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

if (empty($_SESSION['user_id'])) {
  http_response_code(401);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['success' => false, 'message' => 'Inicia sesión para continuar.'], JSON_UNESCAPED_UNICODE);
  exit;
}
