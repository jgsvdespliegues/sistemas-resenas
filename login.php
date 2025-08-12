<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Si ya está logueado, redirigir
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$success = '';

if ($_POST) {
    $email_or_username = sanitize($_POST['email_or_username'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect = $_GET['redirect'] ?? 'index.php';
    
    if (empty($email_or_username)) {
        $errors[] = "El email o usuario es requerido";
    }
    
    if (empty($password)) {
        $errors[] = "La contraseña es requerida";
    }
    
    if (empty($errors)) {
        // Buscar usuario por email o nombre de usuario
        $stmt = $pdo->prepare("SELECT id, nombre, apellido, usuario, email, password, email_verificado FROM usuarios WHERE email = ? OR usuario = ?");
        $stmt->execute([$email_or_username, $email_or_username]);
        $usuario = $stmt->fetch();
        
        if ($usuario && verifyPassword($password, $usuario['password'])) {
            if (!$usuario['email_verificado']) {
                $errors[] = "Tu cuenta no ha sido verificada. Por favor, revisa tu email y haz clic en el enlace de verificación.";
            } else {
                // Login exitoso
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_email'] = $usuario['email'];
                
                header('Location: ' . $redirect);
                exit;
            }
        } else {
            $errors[] = "Credenciales incorrectas";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Reseñas</title>
    <?php include 'includes/styles.php'; ?>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <h1 class="page-title">🔐 Iniciar Sesión</h1>
        
        <div class="form-container">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="email_or_username">Email o Usuario <span class="required">*</span></label>
                    <input type="text" 
                           id="email_or_username" 
                           name="email_or_username" 
                           value="<?php echo htmlspecialchars($email_or_username ?? ''); ?>" 
                           placeholder="tu@email.com o tu_usuario"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña <span class="required">*</span></label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Tu contraseña"
                           required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">Iniciar Sesión</button>
            </form>
            
            <div class="text-center mt-3">
                <p class="text-muted">¿No tienes una cuenta?</p>
                <a href="registro.php" class="btn btn-success">Registrarse aquí</a>
            </div>
            
            <div class="text-center mt-2">
                <a href="recuperar_password.php" class="text-muted" style="text-decoration: none;">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>
        </div>
        
        <div class="card text-center">
            <h3>¿Por qué registrarse?</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
                <div>
                    <div style="font-size: 2em; margin-bottom: 10px;">✍️</div>
                    <h4>Comparte tus experiencias</h4>
                    <p class="text-muted">Escribe reseñas de lugares que has visitado</p>
                </div>
                <div>
                    <div style="font-size: 2em; margin-bottom: 10px;">📸</div>
                    <h4>Sube fotos y videos</h4>
                    <p class="text-muted">Muestra tus experiencias con imágenes</p>
                </div>
                <div>
                    <div style="font-size: 2em; margin-bottom: 10px;">🗺️</div>
                    <h4>Agrega ubicaciones</h4>
                    <p class="text-muted">Ayuda a otros a encontrar estos lugares</p>
                </div>
                <div>
                    <div style="font-size: 2em; margin-bottom: 10px;">⭐</div>
                    <h4>Puntúa lugares</h4>
                    <p class="text-muted">Dale una calificación del 1 al 5</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Animación de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const formContainer = document.querySelector('.form-container');
            formContainer.style.opacity = '0';
            formContainer.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                formContainer.style.transition = 'all 0.6s ease';
                formContainer.style.opacity = '1';
                formContainer.style.transform = 'translateY(0)';
            }, 200);
        });
        
        // Validación en tiempo real
        const emailInput = document.getElementById('email_or_username');
        const passwordInput = document.getElementById('password');
        
        emailInput.addEventListener('input', function() {
            if (this.value.length > 0) {
                this.style.borderColor = '#27ae60';
            } else {
                this.style.borderColor = '#5a6c7d';
            }
        });
        
        passwordInput.addEventListener('input', function() {
            if (this.value.length >= 6) {
                this.style.borderColor = '#27ae60';
            } else if (this.value.length > 0) {
                this.style.borderColor = '#f39c12';
            } else {
                this.style.borderColor = '#5a6c7d';
            }
        });
    </script>
</body>
</html>