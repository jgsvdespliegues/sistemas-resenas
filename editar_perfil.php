<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Requiere login
requireLogin();

$usuario_actual = getCurrentUser($pdo);
$errors = [];
$success = '';

// Procesar formulario
if ($_POST) {
    $nombre = sanitize($_POST['nombre'] ?? '');
    $apellido = sanitize($_POST['apellido'] ?? '');
    $usuario = sanitize($_POST['usuario'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password_actual = $_POST['password_actual'] ?? '';
    $nueva_password = $_POST['nueva_password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';
    
    // Validaciones básicas
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
    
    // Verificar si el usuario o email ya existen (excepto el actual)
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE (usuario = ? OR email = ?) AND id != ?");
        $stmt->execute([$usuario, $email, $usuario_actual['id']]);
        if ($stmt->fetch()) {
            $errors[] = "El nombre de usuario o email ya están registrados por otro usuario";
        }
    }
    
    // Validaciones de contraseña (solo si se quiere cambiar)
    $cambiar_password = false;
    if (!empty($nueva_password) || !empty($confirmar_password) || !empty($password_actual)) {
        $cambiar_password = true;
        
        if (empty($password_actual)) {
            $errors[] = "Debes ingresar tu contraseña actual para cambiarla";
        } else {
            // Verificar contraseña actual
            $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario_actual['id']]);
            $user_data = $stmt->fetch();
            
            if (!verifyPassword($password_actual, $user_data['password'])) {
                $errors[] = "La contraseña actual es incorrecta";
            }
        }
        
        if (empty($nueva_password)) {
            $errors[] = "Debes ingresar una nueva contraseña";
        } elseif (strlen($nueva_password) < 6) {
            $errors[] = "La nueva contraseña debe tener al menos 6 caracteres";
        }
        
        if ($nueva_password !== $confirmar_password) {
            $errors[] = "Las contraseñas nuevas no coinciden";
        }
    }
    
    if (empty($errors)) {
        try {
            if ($cambiar_password) {
                // Actualizar con nueva contraseña
                $password_hash = hashPassword($nueva_password);
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, usuario = ?, email = ?, password = ?, fecha_modificacion = NOW() WHERE id = ?");
                $resultado = $stmt->execute([$nombre, $apellido, $usuario, $email, $password_hash, $usuario_actual['id']]);
            } else {
                // Actualizar sin cambiar contraseña
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, usuario = ?, email = ?, fecha_modificacion = NOW() WHERE id = ?");
                $resultado = $stmt->execute([$nombre, $apellido, $usuario, $email, $usuario_actual['id']]);
            }
            
            if ($resultado) {
                $success = "¡Perfil actualizado exitosamente!";
                // Actualizar datos en sesión
                $_SESSION['usuario_nombre'] = $nombre;
                $_SESSION['usuario_email'] = $email;
                // Recargar datos del usuario
                $usuario_actual = getCurrentUser($pdo);
            } else {
                $errors[] = "Error al actualizar el perfil";
            }
        } catch (PDOException $e) {
            $errors[] = "Error en la base de datos: " . $e->getMessage();
        }
    }
} else {
    // Cargar datos actuales del usuario
    $nombre = $usuario_actual['nombre'];
    $apellido = $usuario_actual['apellido'];
    $usuario = $usuario_actual['usuario'];
    $email = $usuario_actual['email'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Sistema de Reseñas</title>
    <?php include 'includes/styles.php'; ?>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <h1 class="page-title">⚙️ Editar Mi Perfil</h1>
        
        <div class="form-container">
            <div class="text-center mb-3">
                <div style="font-size: 4em; margin-bottom: 15px;">👤</div>
                <h2>Actualizar Información Personal</h2>
                <p class="text-muted">Modifica tus datos personales y configuración de cuenta</p>
            </div>
            
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
                    <div class="mt-2">
                        <a href="perfil.php" class="btn btn-primary btn-small">Volver a mi perfil</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="editarPerfilForm">
                <div class="card-header">
                    <h3 class="card-title">📝 Información Personal</h3>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre <span class="required">*</span></label>
                        <input type="text" 
                               id="nombre" 
                               name="nombre" 
                               value="<?php echo htmlspecialchars($nombre); ?>" 
                               placeholder="Tu nombre"
                               required>
                        <div class="form-feedback" id="nombre-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="apellido">Apellido <span class="required">*</span></label>
                        <input type="text" 
                               id="apellido" 
                               name="apellido" 
                               value="<?php echo htmlspecialchars($apellido); ?>" 
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
                           value="<?php echo htmlspecialchars($usuario); ?>" 
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
                           value="<?php echo htmlspecialchars($email); ?>" 
                           placeholder="tu@email.com"
                           required>
                    <div class="form-feedback" id="email-feedback"></div>
                </div>
                
                <!-- Sección de cambio de contraseña -->
                <div class="separador"></div>
                
                <div class="card-header">
                    <h3 class="card-title">🔐 Cambiar Contraseña</h3>
                    <p class="text-muted">Deja en blanco si no quieres cambiar tu contraseña</p>
                </div>
                
                <div class="form-group">
                    <label for="password_actual">Contraseña Actual</label>
                    <input type="password" 
                           id="password_actual" 
                           name="password_actual" 
                           placeholder="Tu contraseña actual">
                    <small class="text-muted">Requerida solo si quieres cambiar tu contraseña</small>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nueva_password">Nueva Contraseña</label>
                        <input type="password" 
                               id="nueva_password" 
                               name="nueva_password" 
                               placeholder="Nueva contraseña (mínimo 6 caracteres)">
                        <div class="form-feedback" id="nueva-password-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmar_password">Confirmar Nueva Contraseña</label>
                        <input type="password" 
                               id="confirmar_password" 
                               name="confirmar_password" 
                               placeholder="Repite la nueva contraseña">
                        <div class="form-feedback" id="confirmar-password-feedback"></div>
                    </div>
                </div>
                
                <div class="separador"></div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success" id="submitBtn">💾 Guardar Cambios</button>
                    <a href="perfil.php" class="btn btn-warning">❌ Cancelar</a>
                    <button type="button" class="btn btn-danger" id="eliminarCuentaBtn">🗑️ Eliminar Cuenta</button>
                </div>
            </form>
        </div>
        
        <!-- Información adicional -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">📊 Información de la Cuenta</h3>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div class="text-center">
                    <div style="font-size: 2em; margin-bottom: 10px;">📅</div>
                    <h4>Miembro desde</h4>
                    <p class="text-muted"><?php echo date('d/m/Y', strtotime($usuario_actual['fecha_creacion'] ?? 'now')); ?></p>
                </div>
                <div class="text-center">
                    <div style="font-size: 2em; margin-bottom: 10px;">✅</div>
                    <h4>Estado de la cuenta</h4>
                    <p class="text-muted">
                        <?php echo $usuario_actual['email_verificado'] ? 'Verificada' : 'Pendiente de verificación'; ?>
                    </p>
                </div>
                <div class="text-center">
                    <div style="font-size: 2em; margin-bottom: 10px;">🔒</div>
                    <h4>Seguridad</h4>
                    <p class="text-muted">Contraseña protegida con encriptación</p>
                </div>
                <div class="text-center">
                    <div style="font-size: 2em; margin-bottom: 10px;">🌟</div>
                    <h4>Tipo de cuenta</h4>
                    <p class="text-muted">Usuario estándar</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de confirmación para eliminar cuenta -->
    <div id="eliminarCuentaModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="cerrarModalEliminar()">&times;</span>
            <h2 style="color: #e74c3c; text-align: center;">⚠️ Eliminar Cuenta</h2>
            <p>Esta acción <strong>no se puede deshacer</strong>. Se eliminarán:</p>
            <ul>
                <li>Tu cuenta de usuario</li>
                <li>Todas tus reseñas</li>
                <li>Tus fotos y contenido</li>
                <li>Tu historial completo</li>
            </ul>
            <p><strong>¿Estás absolutamente seguro?</strong></p>
            <div class="mt-3 text-center">
                <button onclick="cerrarModalEliminar()" class="btn btn-success">Cancelar</button>
                <button onclick="confirmarEliminacion()" class="btn btn-danger">Sí, eliminar mi cuenta</button>
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
            const form = document.getElementById('editarPerfilForm');
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
                
                // Validar campos básicos
                ['nombre', 'apellido', 'usuario', 'email'].forEach(fieldName => {
                    const field = document.getElementById(fieldName);
                    if (!validateField(field)) {
                        isValid = false;
                    }
                });
                
                // Validar contraseñas si se están cambiando
                const nuevaPassword = document.getElementById('nueva_password');
                const confirmarPassword = document.getElementById('confirmar_password');
                const passwordActual = document.getElementById('password_actual');
                
                if (nuevaPassword.value || confirmarPassword.value || passwordActual.value) {
                    if (!passwordActual.value) {
                        alert('Debes ingresar tu contraseña actual para cambiarla');
                        isValid = false;
                    }
                    if (!nuevaPassword.value) {
                        alert('Debes ingresar una nueva contraseña');
                        isValid = false;
                    }
                    if (nuevaPassword.value !== confirmarPassword.value) {
                        alert('Las contraseñas nuevas no coinciden');
                        isValid = false;
                    }
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
            
            function validateField(input) {
                const value = input.value.trim();
                const name = input.name;
                const feedback = document.getElementById(name + '-feedback') || 
                                document.getElementById(name.replace('_', '-') + '-feedback');
                
                if (!feedback) return true;
                
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
                        
                    case 'nueva_password':
                        if (value.length > 0 && value.length < 6) {
                            isValid = false;
                            message = 'Mínimo 6 caracteres';
                        } else if (value.length > 0) {
                            message = '✓ Válido';
                            updatePasswordStrength(value);
                        }
                        break;
                        
                    case 'confirmar_password':
                        const nuevaPasswordInput = document.getElementById('nueva_password');
                        if (value.length > 0 && value !== nuevaPasswordInput.value) {
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
                const feedback = document.getElementById('nueva-password-feedback');
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
            
            // Modal de eliminar cuenta
            document.getElementById('eliminarCuentaBtn').addEventListener('click', function() {
                document.getElementById('eliminarCuentaModal').style.display = 'block';
            });
            
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
        
        function cerrarModalEliminar() {
            document.getElementById('eliminarCuentaModal').style.display = 'none';
        }
        
        function confirmarEliminacion() {
            if (confirm('ÚLTIMA ADVERTENCIA: Esta acción eliminará permanentemente tu cuenta y todos tus datos. ¿Continuar?')) {
                window.location.href = 'eliminar_cuenta.php';
            }
        }
        
        // Cerrar modal al hacer click fuera
        window.onclick = function(event) {
            const modal = document.getElementById('eliminarCuentaModal');
            if (event.target == modal) {
                cerrarModalEliminar();
            }
        }
    </script>
</body>
</html>