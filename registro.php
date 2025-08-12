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
    $nombre = sanitize($_POST['nombre'] ?? '');
    $apellido = sanitize($_POST['apellido'] ?? '');
    $usuario = sanitize($_POST['usuario'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validaciones
    if (empty($nombre)) {
        $errors[] = "El nombre es requerido";
    } elseif (strlen($nombre) < 2) {
        $errors[] = "El nombre debe tener al menos 2 caracteres";
    }
    
    if (empty($apellido)) {
        $errors[] = "El apellido es requerido";
    } elseif (strlen($apellido) < 2) {
        $errors[] = "El apellido debe tener al menos 2 caracteres";
    }
    
    if (empty($usuario)) {
        $errors[] = "El nombre de usuario es requerido";
    } elseif (strlen($usuario) < 3) {
        $errors[] = "El nombre de usuario debe tener al menos 3 caracteres";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $usuario)) {
        $errors[] = "El nombre de usuario solo puede contener letras, números y guiones bajos";
    }
    
    if (empty($email)) {
        $errors[] = "El email es requerido";
    } elseif (!isValidEmail($email)) {
        $errors[] = "El email no es válido";
    }
    
    if (empty($password)) {
        $errors[] = "La contraseña es requerida";
    } elseif (strlen($password) < 6) {
        $errors[] = "La contraseña debe tener al menos 6 caracteres";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Las contraseñas no coinciden";
    }
    
    // Verificar si el usuario o email ya existen
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ? OR email = ?");
        $stmt->execute([$usuario, $email]);
        if ($stmt->fetch()) {
            $errors[] = "El nombre de usuario o email ya están registrados";
        }
    }
    
    if (empty($errors)) {
        try {
            // Generar token de verificación
            $token_verificacion = generateToken();
            $password_hash = hashPassword($password);
            
            // Insertar usuario
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, usuario, email, password, token_verificacion, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            
            if ($stmt->execute([$nombre, $apellido, $usuario, $email, $password_hash, $token_verificacion])) {
                // Enviar email de verificación
                if (sendVerificationEmail($email, $nombre, $token_verificacion)) {
                    $success = "¡Registro exitoso! Te hemos enviado un email de verificación a " . $email . ". Por favor, revisa tu bandeja de entrada y haz clic en el enlace para activar tu cuenta.";
                    // Limpiar variables
                    $nombre = $apellido = $usuario = $email = '';
                } else {
                    $errors[] = "Usuario registrado, pero no se pudo enviar el email de verificación. Contacta al administrador.";
                }
            } else {
                $errors[] = "Error al registrar el usuario";
            }
        } catch (PDOException $e) {
            $errors[] = "Error en la base de datos: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Sistema de Reseñas</title>
    <?php include 'includes/styles.php'; ?>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <h1 class="page-title">📝 Crear Cuenta</h1>
        
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
            
            <form method="POST" id="registroForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre <span class="required">*</span></label>
                        <input type="text" 
                               id="nombre" 
                               name="nombre" 
                               value="<?php echo htmlspecialchars($nombre ?? ''); ?>" 
                               placeholder="Tu nombre"
                               required>
                        <div class="form-feedback" id="nombre-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="apellido">Apellido <span class="required">*</span></label>
                        <input type="text" 
                               id="apellido" 
                               name="apellido" 
                               value="<?php echo htmlspecialchars($apellido ?? ''); ?>" 
                               placeholder="Tu apellido"
                               required>
                        <div class="form-feedback" id="apellido-feedback"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="usuario">Nombre de Usuario <span class="required">*</span></label>
                    <input type="text" 
                           id="usuario" 
                           name="usuario" 
                           value="<?php echo htmlspecialchars($usuario ?? ''); ?>" 
                           placeholder="usuario_ejemplo"
                           required>
                    <small class="text-muted">Solo letras, números y guiones bajos. Mínimo 3 caracteres.</small>
                    <div class="form-feedback" id="usuario-feedback"></div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                           placeholder="tu@email.com"
                           required>
                    <div class="form-feedback" id="email-feedback"></div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Contraseña <span class="required">*</span></label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               placeholder="Mínimo 6 caracteres"
                               required>
                        <div class="form-feedback" id="password-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña <span class="required">*</span></label>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               placeholder="Repite tu contraseña"
                               required>
                        <div class="form-feedback" id="confirm-password-feedback"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="terms" required style="margin-right: 10px;">
                        Acepto los <a href="#" style="color: #3498db;">términos y condiciones</a>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-success btn-full" id="submitBtn">Crear Cuenta</button>
            </form>
            
            <div class="text-center mt-3">
                <p class="text-muted">¿Ya tienes una cuenta?</p>
                <a href="login.php" class="btn btn-primary">Iniciar Sesión</a>
            </div>
        </div>
        
        <div class="card">
            <h3 class="text-center">🎯 Únete a nuestra comunidad</h3>
            <p class="text-center text-muted">Comparte tus experiencias y descubre lugares increíbles recomendados por otros usuarios.</p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px;">
                <div class="text-center">
                    <div style="font-size: 3em; margin-bottom: 15px;">📍</div>
                    <h4>Descubre lugares únicos</h4>
                    <p class="text-muted">Encuentra restaurantes, hoteles, atracciones y más lugares recomendados por la comunidad.</p>
                </div>
                <div class="text-center">
                    <div style="font-size: 3em; margin-bottom: 15px;">📝</div>
                    <h4>Comparte experiencias</h4>
                    <p class="text-muted">Escribe reseñas detalladas con fotos, videos y ubicaciones para ayudar a otros viajeros.</p>
                </div>
                <div class="text-center">
                    <div style="font-size: 3em; margin-bottom: 15px;">⭐</div>
                    <h4>Sistema de puntuación</h4>
                    <p class="text-muted">Califica lugares del 1 al 5 y ayuda a otros a tomar mejores decisiones.</p>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .form-feedback {
            font-size: 14px;
            margin-top: 5px;
            min-height: 20px;
        }
        
        .form-feedback.success {
            color: #27ae60;
        }
        
        .form-feedback.error {
            color: #e74c3c;
        }
        
        .input-valid {
            border-color: #27ae60 !important;
            box-shadow: 0 0 10px rgba(39, 174, 96, 0.3) !important;
        }
        
        .input-invalid {
            border-color: #e74c3c !important;
            box-shadow: 0 0 10px rgba(231, 76, 60, 0.3) !important;
        }
        
        .password-strength {
            height: 4px;
            background-color: #34495e;
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }
        
        .strength-weak {
            background-color: #e74c3c;
            width: 33%;
        }
        
        .strength-medium {
            background-color: #f39c12;
            width: 66%;
        }
        
        .strength-strong {
            background-color: #27ae60;
            width: 100%;
        }
    </style>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registroForm');
            const inputs = form.querySelectorAll('input[type="text"], input[type="email"], input[type="password"]');
            
            // Validación en tiempo real
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    validateField(this);
                });
                
                input.addEventListener('blur', function() {
                    validateField(this);
                });
            });
            
            // Validación del formulario al enviar
            form.addEventListener('submit', function(e) {
                let isValid = true;
                inputs.forEach(input => {
                    if (!validateField(input)) {
                        isValid = false;
                    }
                });
                
                if (!document.getElementById('terms').checked) {
                    alert('Debes aceptar los términos y condiciones');
                    isValid = false;
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
            
            function validateField(input) {
                const value = input.value.trim();
                const name = input.name;
                const feedback = document.getElementById(name + '-feedback');
                
                let isValid = true;
                let message = '';
                
                switch(name) {
                    case 'nombre':
                    case 'apellido':
                        if (value.length < 2) {
                            isValid = false;
                            message = 'Mínimo 2 caracteres';
                        } else {
                            message = '✓ Válido';
                        }
                        break;
                        
                    case 'usuario':
                        if (value.length < 3) {
                            isValid = false;
                            message = 'Mínimo 3 caracteres';
                        } else if (!/^[a-zA-Z0-9_]+$/.test(value)) {
                            isValid = false;
                            message = 'Solo letras, números y guiones bajos';
                        } else {
                            message = '✓ Válido';
                        }
                        break;
                        
                    case 'email':
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(value)) {
                            isValid = false;
                            message = 'Email no válido';
                        } else {
                            message = '✓ Válido';
                        }
                        break;
                        
                    case 'password':
                        if (value.length < 6) {
                            isValid = false;
                            message = 'Mínimo 6 caracteres';
                        } else {
                            message = '✓ Válido';
                            updatePasswordStrength(value);
                        }
                        // Validar confirmación si existe
                        const confirmInput = document.getElementById('confirm_password');
                        if (confirmInput.value) {
                            validateField(confirmInput);
                        }
                        break;
                        
                    case 'confirm_password':
                        const passwordInput = document.getElementById('password');
                        if (value !== passwordInput.value) {
                            isValid = false;
                            message = 'Las contraseñas no coinciden';
                        } else if (value.length > 0) {
                            message = '✓ Las contraseñas coinciden';
                        }
                        break;
                }
                
                // Aplicar estilos
                if (isValid && value.length > 0) {
                    input.classList.remove('input-invalid');
                    input.classList.add('input-valid');
                    feedback.className = 'form-feedback success';
                } else if (value.length > 0) {
                    input.classList.remove('input-valid');
                    input.classList.add('input-invalid');
                    feedback.className = 'form-feedback error';
                } else {
                    input.classList.remove('input-valid', 'input-invalid');
                    feedback.className = 'form-feedback';
                }
                
                feedback.textContent = message;
                return isValid;
            }
            
            function updatePasswordStrength(password) {
                const feedback = document.getElementById('password-feedback');
                let strengthBar = document.querySelector('.password-strength-bar');
                
                if (!strengthBar) {
                    const strengthContainer = document.createElement('div');
                    strengthContainer.className = 'password-strength';
                    strengthBar = document.createElement('div');
                    strengthBar.className = 'password-strength-bar';
                    strengthContainer.appendChild(strengthBar);
                    feedback.appendChild(strengthContainer);
                }
                
                let strength = 0;
                if (password.length >= 6) strength++;
                if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
                if (password.match(/[0-9]/)) strength++;
                if (password.match(/[^a-zA-Z0-9]/)) strength++;
                
                strengthBar.className = 'password-strength-bar';
                if (strength >= 3) {
                    strengthBar.classList.add('strength-strong');
                } else if (strength >= 2) {
                    strengthBar.classList.add('strength-medium');
                } else {
                    strengthBar.classList.add('strength-weak');
                }
            }
            
            // Animación de entrada
            const formContainer = document.querySelector('.form-container');
            formContainer.style.opacity = '0';
            formContainer.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                formContainer.style.transition = 'all 0.6s ease';
                formContainer.style.opacity = '1';
                formContainer.style.transform = 'translateY(0)';
            }, 200);
        });
    </script>
</body>
</html>