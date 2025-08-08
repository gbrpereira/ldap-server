<?php
/**
 * Configurações do Sistema LDAP
 * 
 * Este arquivo centraliza todas as configurações relacionadas ao LDAP
 * e outras configurações do sistema.
 */

// Configurações do servidor LDAP
define('LDAP_HOST', 'ldap://192.168.18.23');
define('LDAP_PORT', 389);
define('LDAP_BASE_DN', 'dc=domain,dc=local');
define('LDAP_USER_DN_FORMAT', 'uid=%s,ou=usuarios,' . LDAP_BASE_DN);

// Configurações de sessão
define('SESSION_TIMEOUT', 30 * 60); // 30 minutos em segundos
define('SESSION_CHECK_INTERVAL', 5 * 60); // 5 minutos em segundos

// Configurações de segurança
define('MAX_LOGIN_ATTEMPTS', 5); // Máximo de tentativas de login
define('LOGIN_LOCKOUT_TIME', 15 * 60); // 15 minutos de bloqueio

// Configurações de log
define('LOG_ENABLED', true);
define('LOG_FILE', 'ldap_auth.log');

// Configurações de debug (desabilitar em produção)
define('DEBUG_MODE', false);

/**
 * Função para log personalizado
 */
function logMessage($message, $type = 'INFO') {
    if (!LOG_ENABLED) return;
    
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$type] $message" . PHP_EOL;
    
    if (LOG_FILE) {
        error_log($logEntry, 3, LOG_FILE);
    } else {
        error_log($logEntry);
    }
}

/**
 * Função para sanitizar entrada
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Função para validar formato de usuário
 */
function validateUsername($username) {
    return strlen($username) >= 3 && preg_match('/^[a-zA-Z0-9._-]+$/', $username);
}

/**
 * Função para retornar resposta JSON
 */
function jsonResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => time()
    ]);
    exit;
}

/**
 * Função para verificar se a sessão é válida
 */
function isSessionValid() {
    if (!isset($_SESSION['usuario']) || !isset($_SESSION['login_time'])) {
        return false;
    }
    
    $current_time = time();
    $login_time = $_SESSION['login_time'];
    
    return ($current_time - $login_time) <= SESSION_TIMEOUT;
}

/**
 * Função para renovar sessão
 */
function renewSession() {
    if (isset($_SESSION['usuario'])) {
        $_SESSION['login_time'] = time();
        return true;
    }
    return false;
}

/**
 * Função para obter informações do usuário logado
 */
function getCurrentUser() {
    if (!isSessionValid()) {
        return null;
    }
    
    return [
        'username' => $_SESSION['usuario'],
        'login_time' => $_SESSION['login_time'],
        'ip_address' => $_SESSION['ip_address'] ?? 'N/A',
        'session_duration' => time() - $_SESSION['login_time']
    ];
}
?>
