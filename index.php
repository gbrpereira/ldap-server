<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login LDAP</title>
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

        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h2 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #666;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group input.error {
            border-color: #e74c3c;
        }

        .error-message {
            color: #e74c3c;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: none;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .loading {
            display: none;
            text-align: center;
            margin-top: 1rem;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 1rem;
            display: none;
        }

        .alert-error {
            background-color: #fee;
            border: 1px solid #fcc;
            color: #c33;
        }

        .alert-success {
            background-color: #efe;
            border: 1px solid #cfc;
            color: #3c3;
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle input {
            padding-right: 40px;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2>🔐 Login LDAP</h2>
            <p>Faça login com suas credenciais do domínio</p>
        </div>

        <div id="alert" class="alert"></div>

        <form id="loginForm" action="login.php" method="post">
            <div class="form-group">
                <label for="username">Usuário</label>
                <input type="text" id="username" name="username" required>
                <div class="error-message" id="username-error"></div>
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <div class="password-toggle">
                    <input type="password" id="password" name="password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword()">👁️</button>
                </div>
                <div class="error-message" id="password-error"></div>
            </div>

            <button type="submit" class="submit-btn" id="submitBtn">
                <span id="btnText">Entrar</span>
            </button>
        </form>

        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p>Autenticando...</p>
        </div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const loading = document.getElementById('loading');
        const alert = document.getElementById('alert');

        // Validação em tempo real
        const username = document.getElementById('username');
        const password = document.getElementById('password');
        const usernameError = document.getElementById('username-error');
        const passwordError = document.getElementById('password-error');

        username.addEventListener('input', () => validateUsername());
        password.addEventListener('input', () => validatePassword());

        function validateUsername() {
            const value = username.value.trim();
            if (value.length < 3) {
                showError(username, usernameError, 'Usuário deve ter pelo menos 3 caracteres');
                return false;
            } else {
                clearError(username, usernameError);
                return true;
            }
        }

        function validatePassword() {
            const value = password.value;
            if (value.length < 1) {
                showError(password, passwordError, 'Senha é obrigatória');
                return false;
            } else {
                clearError(password, passwordError);
                return true;
            }
        }

        function showError(input, errorElement, message) {
            input.classList.add('error');
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }

        function clearError(input, errorElement) {
            input.classList.remove('error');
            errorElement.style.display = 'none';
        }

        function showAlert(message, type) {
            alert.textContent = message;
            alert.className = `alert alert-${type}`;
            alert.style.display = 'block';
        }

        function hideAlert() {
            alert.style.display = 'none';
        }

        function setLoading(loading) {
            if (loading) {
                submitBtn.disabled = true;
                btnText.textContent = 'Autenticando...';
                this.loading.style.display = 'block';
            } else {
                submitBtn.disabled = false;
                btnText.textContent = 'Entrar';
                this.loading.style.display = 'none';
            }
        }

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.toggle-password');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = '👁️';
            }
        }

        // Prevenir múltiplos submits
        let isSubmitting = false;

        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (isSubmitting) return;

            // Validação
            const isUsernameValid = validateUsername();
            const isPasswordValid = validatePassword();

            if (!isUsernameValid || !isPasswordValid) {
                showAlert('Por favor, corrija os erros no formulário.', 'error');
                return;
            }

            isSubmitting = true;
            setLoading(true);
            hideAlert();

            try {
                const formData = new FormData(form);
                const response = await fetch('login.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showAlert(result.message, 'success');
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1000);
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                console.error('Erro:', error);
                showAlert('Erro de conexão. Verifique sua internet e tente novamente.', 'error');
            } finally {
                isSubmitting = false;
                setLoading(false);
            }
        });

        // Foco automático no primeiro campo
        username.focus();
    </script>
</body>
</html>
