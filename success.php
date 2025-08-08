<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

$username = htmlspecialchars($_SESSION['usuario']);
$login_time = isset($_SESSION['login_time']) ? $_SESSION['login_time'] : time();
$login_date = date('d/m/Y H:i:s', $login_time);
$ip_address = $_SESSION['ip_address'] ?? 'N/A';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo - LDAP</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .success-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: bounce 1s ease-in;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        .success-header h2 {
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }

        .success-header p {
            color: #666;
            margin-bottom: 2rem;
        }

        .user-info {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: left;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid #e9ecef;
        }

        .info-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .info-label {
            font-weight: 600;
            color: #495057;
        }

        .info-value {
            color: #6c757d;
        }

        .session-timer {
            background: #e3f2fd;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border-left: 4px solid #2196f3;
        }

        .timer-display {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1976d2;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .logout-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
            font-size: 0.9rem;
        }

        @media (max-width: 600px) {
            .success-container {
                margin: 1rem;
                padding: 1.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">✅</div>
        
        <div class="success-header">
            <h2>Bem-vindo, <?php echo $username; ?>!</h2>
            <p>Você foi autenticado com sucesso via LDAP</p>
        </div>

        <div class="user-info">
            <div class="info-item">
                <span class="info-label">Usuário:</span>
                <span class="info-value"><?php echo $username; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Data/Hora do Login:</span>
                <span class="info-value"><?php echo $login_date; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Endereço IP:</span>
                <span class="info-value"><?php echo $ip_address; ?></span>
            </div>
        </div>

        <div class="session-timer">
            <div>Tempo de sessão ativa:</div>
            <div class="timer-display" id="sessionTimer">00:00:00</div>
        </div>

        <div class="action-buttons">
            <button class="btn btn-primary" onclick="refreshSession()">🔄 Renovar Sessão</button>
            <a href="logout.php" class="btn btn-secondary">🚪 Sair</a>
        </div>

        <div class="logout-warning">
            ⚠️ Por segurança, sua sessão será encerrada automaticamente após 30 minutos de inatividade.
        </div>
    </div>

    <script>
        let sessionStartTime = <?php echo $login_time; ?> * 1000; // Converter para milissegundos
        let sessionTimer;

        function updateSessionTimer() {
            const now = Date.now();
            const elapsed = now - sessionStartTime;
            
            const hours = Math.floor(elapsed / (1000 * 60 * 60));
            const minutes = Math.floor((elapsed % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((elapsed % (1000 * 60)) / 1000);
            
            const timerDisplay = document.getElementById('sessionTimer');
            timerDisplay.textContent = 
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }

        function refreshSession() {
            fetch('refresh_session.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        sessionStartTime = Date.now();
                        showNotification('Sessão renovada com sucesso!', 'success');
                    } else {
                        showNotification('Erro ao renovar sessão', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showNotification('Erro de conexão', 'error');
                });
        }

        function showNotification(message, type) {
            // Criar notificação temporária
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem;
                border-radius: 5px;
                color: white;
                font-weight: 600;
                z-index: 1000;
                animation: slideIn 0.3s ease;
            `;
            
            if (type === 'success') {
                notification.style.background = '#28a745';
            } else {
                notification.style.background = '#dc3545';
            }
            
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Atualizar timer a cada segundo
        setInterval(updateSessionTimer, 1000);
        updateSessionTimer(); // Executar imediatamente

        // Verificar se a sessão ainda é válida a cada 5 minutos
        setInterval(() => {
            fetch('check_session.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.valid) {
                        alert('Sua sessão expirou. Você será redirecionado para o login.');
                        window.location.href = 'index.php';
                    }
                })
                .catch(error => {
                    console.error('Erro ao verificar sessão:', error);
                });
        }, 5 * 60 * 1000); // 5 minutos

        // Adicionar CSS para animação da notificação
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
