<?php
session_start();

// Log do logout
if (isset($_SESSION['usuario'])) {
    $username = $_SESSION['usuario'];
    error_log("Logout realizado para usuário: $username - IP: " . $_SERVER['REMOTE_ADDR']);
}

// Destruir a sessão
session_destroy();

// Se for uma requisição AJAX, retornar JSON
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Logout realizado com sucesso']);
    exit;
}

// Se for uma requisição normal, mostrar página de logout
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - LDAP</title>
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

        .logout-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logout-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: slideOut 1s ease-in;
        }

        @keyframes slideOut {
            0% { transform: translateX(0); opacity: 1; }
            100% { transform: translateX(-100px); opacity: 0; }
        }

        .logout-header h2 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .logout-header p {
            color: #666;
            margin-bottom: 2rem;
        }

        .redirect-info {
            background: #e3f2fd;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border-left: 4px solid #2196f3;
        }

        .countdown {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1976d2;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-icon">👋</div>
        
        <div class="logout-header">
            <h2>Logout Realizado</h2>
            <p>Você foi desconectado com sucesso</p>
        </div>

        <div class="redirect-info">
            <p>Você será redirecionado para a página de login em:</p>
            <div class="countdown" id="countdown">5</div>
        </div>

        <a href="index.php" class="btn">🔐 Fazer Login Novamente</a>
    </div>

    <script>
        let countdown = 5;
        const countdownElement = document.getElementById('countdown');

        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = 'index.php';
            }
        }, 1000);

        // Redirecionar imediatamente se clicar no botão
        document.querySelector('.btn').addEventListener('click', () => {
            clearInterval(timer);
        });
    </script>
</body>
</html>
