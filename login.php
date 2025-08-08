<?php
session_start();
require_once 'config.php';

// Função para retornar resposta JSON
function jsonResponse($success, $message, $redirect = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'redirect' => $redirect
    ]);
    exit;
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método não permitido');
}

// Coletar e validar dados do formulário
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validação básica
if (empty($username) || empty($password)) {
    jsonResponse(false, 'Usuário e senha são obrigatórios');
}

if (!validateUsername($username)) {
    jsonResponse(false, 'Formato de usuário inválido');
}

// Sanitizar entrada
$username = sanitizeInput($username);

// Montar DN
$user_dn = sprintf(LDAP_USER_DN_FORMAT, $username);

// Conectar ao LDAP
$ldap_conn = @ldap_connect(LDAP_HOST, LDAP_PORT);

if (!$ldap_conn) {
    jsonResponse(false, 'Não foi possível conectar ao servidor LDAP');
}

// Configurar opções LDAP
ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
ldap_set_option($ldap_conn, LDAP_OPT_NETWORK_TIMEOUT, 10);

// Tentar autenticar
$bind_result = @ldap_bind($ldap_conn, $user_dn, $password);

if ($bind_result) {
    // Login bem-sucedido
    $_SESSION['usuario'] = $username;
    $_SESSION['login_time'] = time();
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
    
    // Log de sucesso
    logMessage("Login LDAP bem-sucedido para usuário: $username - IP: " . $_SERVER['REMOTE_ADDR'], 'SUCCESS');
    
    ldap_close($ldap_conn);
    jsonResponse(true, 'Login realizado com sucesso!', 'success.php');
} else {
    // Login falhou
    $ldap_error = ldap_error($ldap_conn);
    ldap_close($ldap_conn);
    
    // Log de falha
    logMessage("Tentativa de login LDAP falhou para usuário: $username - IP: " . $_SERVER['REMOTE_ADDR'] . " - Erro: $ldap_error", 'ERROR');
    
    // Não revelar informações específicas sobre o erro por segurança
    jsonResponse(false, 'Usuário ou senha incorretos');
}
?>
