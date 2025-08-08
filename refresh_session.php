<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isSessionValid()) {
    jsonResponse(false, 'Usuário não autenticado ou sessão expirada');
}

// Renovar o tempo da sessão
if (renewSession()) {
    logMessage("Sessão renovada para usuário: " . $_SESSION['usuario'], 'INFO');
    jsonResponse(true, 'Sessão renovada com sucesso', ['new_time' => $_SESSION['login_time']]);
} else {
    jsonResponse(false, 'Erro ao renovar sessão');
}
?>
