<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isSessionValid()) {
    session_destroy();
    jsonResponse(false, 'Sessão expirada ou inválida', ['valid' => false]);
}

// Sessão ainda válida
$user = getCurrentUser();
$remaining_time = SESSION_TIMEOUT - $user['session_duration'];

jsonResponse(true, 'Sessão válida', [
    'valid' => true,
    'remaining_time' => $remaining_time,
    'user' => $user
]);
?>
