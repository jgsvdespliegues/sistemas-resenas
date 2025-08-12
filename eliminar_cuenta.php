<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Requiere login
requireLogin();

$usuario_actual = getCurrentUser($pdo);
$errors = [];
$success = '';
$confirmacion_requerida = true;

// Procesar confirmación de eliminación
if ($_POST) {
    $password_confirmacion = $_POST['password_confirmacion'] ?? '';
    $confirmacion_texto = sanitize($_POST['confirmacion_texto'] ?? '');
    $acepta_eliminacion = isset($_POST['acepta_eliminacion']);
    
    // Validaciones
    if (empty($password_confirmacion)) {
        $errors[] = "Debes ingresar tu contraseña para confirmar";
    } else {
        // Verificar contraseña
        $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_actual['id']]);
        $user_data = $stmt->fetch();
        
        if (!verifyPassword($password_confirmacion, $user_data['password'])) {
            $errors[] = "La contraseña es incorrecta";
        }
    }
    
    if ($confirmacion_texto !== 'ELIMINAR') {
        $errors[] = "Debes escribir exactamente 'ELIMINAR' para confirmar";
    }
    
    if (!$acepta_eliminacion) {
        $errors[] = "Debes aceptar que comprendes las consecuencias";
    }
    
    if (empty($errors)) {
        try {
            // Iniciar transacción para eliminar todo de forma segura
            $pdo->beginTransaction();
            
            // Obtener información del usuario antes de eliminar
            $nombre_usuario = $usuario_actual['nombre'] . ' ' . $usuario_actual['apellido'];
            $email_usuario = $usuario_actual['email'];
            $total_publicaciones = 0;
            
            // Contar publicaciones
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM publicaciones WHERE usuario_id = ?");
            $stmt->execute([$usuario_actual['id']]);
            $result = $stmt->fetch();
            $total_publicaciones = $result['total'];
            
            // Eliminar publicaciones (la FK con CASCADE eliminará automáticamente)
            // Pero podemos hacer una eliminación manual para más control
            $stmt = $pdo->prepare("DELETE FROM publicaciones WHERE usuario_id = ?");
            $stmt->execute([$usuario_actual['id']]);
            
            // Eliminar sesiones del usuario
            $stmt = $pdo->prepare("DELETE FROM sesiones WHERE usuario_id = ?");
            $stmt->execute([$usuario_actual['id']]);
            
            // Eliminar el usuario
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario_actual['id']]);
            
            // Confirmar transacción
            $pdo->commit();
            
            // Log de eliminación (opcional, para auditoria)
            $log_message = date('Y-m-d H:i:s') . " - CUENTA ELIMINADA\n";
            $log_message .= "Usuario: $nombre_usuario\n";
            $log_message .= "Email: $email_usuario\n";
            $log_message .= "Publicaciones eliminadas: $total_publicaciones\n";
            $log_message .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Desconocida') . "\n";
            $log_message .= "========================================\n\n";
            
            file_put_contents('logs/cuentas_eliminadas.log', $log_message, FILE_APPEND | LOCK_EX);
            
            // Cerrar sesión
            session_unset();
            session_destroy();
            
            $success = true;
            $confirmacion_requerida = false;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Error al eliminar la cuenta: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Cuenta - Sistema de Reseñas</title>
    <?php include 'includes/styles.php'; ?>
</head>
<body>
    <?php if (!$success): ?>
        <?php include 'includes/navbar.php'; ?>
    <?php endif; ?>
    
    <div class="container">
        <?php if ($success): ?>
            <!-- Página de confirmación de eliminación exitosa -->
            <div class="text-center" style="margin-top: 100px;">
                <div style="font-size: 6em; color: #27ae60; margin-bottom: 30px;">✅</div>
                <h1 style="color: #27ae60; margin-bottom: 30px;">Cuenta Eliminada Exitosamente</h1>
                
                <div class="card" style="max-width: 600px; margin: 0 auto;">
                    <p style="font-size: 18px; margin-bottom: 20px;">
                        Tu cuenta y todos los datos asociados han sido eliminados permanentemente de nuestro sistema.
                    </p>
                    
                    <div style="background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;">
                        <h3 style="color: #27ae60; margin-bottom: 15px;">✓ Lo que se ha eliminado:</h3>
                        <ul style="text-align: left; color: #2c3e50;">
                            <li>Tu cuenta de usuario</li>
                            <li>Todas tus reseñas y publicaciones</li>
                            <li>Tus fotos y contenido multimedia</li>
                            <li>Tu historial de actividad</li>
                            <li>Todas las sesiones activas</li>
                        </ul>
                    </div>
                    
                    <p style="color: #7f8c8d; font-style: italic; margin: 20px 0;">
                        Lamentamos verte partir. Si decides volver en el futuro, siempre serás bienvenido a crear una nueva cuenta.
                    </p>
                    
                    <div class="mt-3">
                        <a href="index.php" class="btn btn-primary">🏠 Volver al Inicio</a>
                        <a href="registro.php" class="btn btn-success">📝 Crear Nueva Cuenta</a>
                    </div>
                </div>
                
                <div style="margin-top: 40px; color: #95a5a6; font-size: 14px;">
                    <p>Gracias por haber sido parte de nuestra comunidad de reseñas.</p>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Formulario de confirmación de eliminación -->
            <h1 class="page-title" style="color: #e74c3c;">🗑️ Eliminar Cuenta</h1>
            
            <div class="form-container">
                <div class="text-center mb-3">
                    <div style="font-size: 4em; color: #e74c3c; margin-bottom: 15px;">⚠️</div>
                    <h2 style="color: #e74c3c;">ADVERTENCIA: Acción Irreversible</h2>
                    <p class="text-muted">Esta acción eliminará permanentemente tu cuenta y no se puede deshacer.</p>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Información de lo que se eliminará -->
                <div class="alert alert-warning">
                    <h3 style="color: #856404; margin-bottom: 15px;">📊 Resumen de tu cuenta:</h3>
                    <?php
                    // Obtener estadísticas del usuario
                    $stmt = $pdo->prepare("SELECT COUNT(*) as total_publicaciones FROM publicaciones WHERE usuario_id = ?");
                    $stmt->execute([$usuario_actual['id']]);
                    $stats = $stmt->fetch();
                    ?>
                    <ul style="color: #856404;">
                        <li><strong>Usuario:</strong> <?php echo htmlspecialchars($usuario_actual['nombre'] . ' ' . $usuario_actual['apellido']); ?></li>
                        <li><strong>Email:</strong> <?php echo htmlspecialchars($usuario_actual['email']); ?></li>
                        <li><strong>Reseñas publicadas:</strong> <?php echo $stats['total_publicaciones']; ?></li>
                        <li><strong>Miembro desde:</strong> <?php echo date('d/m/Y', strtotime($usuario_actual['fecha_creacion'] ?? 'now')); ?></li>
                    </ul>
                </div>
                
                <div class="alert alert-error">
                    <h3 style="color: #721c24; margin-bottom: 15px;">🚨 Se eliminará PERMANENTEMENTE:</h3>
                    <ul style="color: #721c24;">
                        <li>Tu cuenta de usuario y toda la información personal</li>
                        <li>Todas tus reseñas (<?php echo $stats['total_publicaciones']; ?> publicaciones)</li>
                        <li>Todas las fotos y videos que hayas subido</li>
                        <li>Tu historial completo de actividad</li>
                        <li>Todas las sesiones activas en todos los dispositivos</li>
                    </ul>
                    <p style="font-weight: bold; margin-top: 15px;">
                        ⚠️ Esta acción NO SE PUEDE DESHACER. Los datos no podrán ser recuperados.
                    </p>
                </div>
                
                <form method="POST" id="eliminarCuentaForm">
                    <div class="form-group">
                        <label for="password_confirmacion">Confirma tu contraseña <span class="required">*</span></label>
                        <input type="password" 
                               id="password_confirmacion" 
                               name="password_confirmacion" 
                               placeholder="Ingresa tu contraseña actual"
                               required>
                        <small class="text-muted">Necesitamos verificar que realmente eres tú.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmacion_texto">Escribe "ELIMINAR" para confirmar <span class="required">*</span></label>
                        <input type="text" 
                               id="confirmacion_texto" 
                               name="confirmacion_texto" 
                               placeholder="Escribe exactamente: ELIMINAR"
                               required>
                        <small class="text-muted">Esto confirma que entiendes las consecuencias.</small>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: flex-start; gap: 10px;">
                            <input type="checkbox" 
                                   id="acepta_eliminacion" 
                                   name="acepta_eliminacion" 
                                   required 
                                   style="margin-top: 5px;">
                            <span>
                                Comprendo que esta acción es <strong>irreversible</strong> y que 
                                <strong>todos mis datos serán eliminados permanentemente</strong>. 
                                También entiendo que no podré recuperar mi cuenta ni mis reseñas después de este proceso.
                            </span>
                        </label>
                    </div>
                    
                    <div class="separador"></div>
                    
                    <div class="d-flex gap-2">
                        <a href="editar_perfil.php" class="btn btn-success">❌ Cancelar</a>
                        <button type="submit" class="btn btn-danger" id="confirmarEliminacionBtn">
                            🗑️ SÍ, ELIMINAR MI CUENTA PERMANENTEMENTE
                        </button>
                    </div>
                </form>
                
                <div class="mt-3 text-center">
                    <p class="text-muted">
                        ¿Tienes problemas con tu cuenta? 
                        <a href="mailto:soporte@ejemplo.com" style="color: #3498db;">Contacta al soporte</a> 
                        antes de eliminarla.
                    </p>
                </div>
            </div>
            
            <!-- Información adicional -->
            <div class="card">
                <h3 class="text-center">🤔 ¿Estás seguro?</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
                    <div class="text-center">
                        <div style="font-size: 3em; margin-bottom: 15px;">⏸️</div>
                        <h4>Desactivar temporalmente</h4>
                        <p class="text-muted">Considera desactivar tu cuenta temporalmente en lugar de eliminarla.</p>
                    </div>
                    <div class="text-center">
                        <div style="font-size: 3em; margin-bottom: 15px;">📧</div>
                        <h4>Contactar soporte</h4>
                        <p class="text-muted">Nuestro equipo puede ayudarte a resolver cualquier problema.</p>
                    </div>
                    <div class="text-center">
                        <div style="font-size: 3em; margin-bottom: 15px;">💾</div>
                        <h4>Exportar datos</h4>
                        <p class="text-muted">Puedes solicitar una copia de tus datos antes de eliminar.</p>
                    </div>
                    <div class="text-center">
                        <div style="font-size: 3em; margin-bottom: 15px;">🔄</div>
                        <h4>Volver más tarde</h4>
                        <p class="text-muted">Siempre puedes crear una nueva cuenta cuando quieras volver.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        <?php if (!$success): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('eliminarCuentaForm');
            const submitBtn = document.getElementById('confirmarEliminacionBtn');
            
            form.addEventListener('submit', function(e) {
                const password = document.getElementById('password_confirmacion').value;
                const confirmacion = document.getElementById('confirmacion_texto').value;
                const acepta = document.getElementById('acepta_eliminacion').checked;
                
                if (!password) {
                    e.preventDefault();
                    alert('Debes ingresar tu contraseña');
                    return;
                }
                
                if (confirmacion !== 'ELIMINAR') {
                    e.preventDefault();
                    alert('Debes escribir exactamente "ELIMINAR" para confirmar');
                    return;
                }
                
                if (!acepta) {
                    e.preventDefault();
                    alert('Debes aceptar que comprendes las consecuencias');
                    return;
                }
                
                // Confirmación final
                const confirmacionFinal = confirm(
                    '🚨 ÚLTIMA ADVERTENCIA 🚨\n\n' +
                    'Estás a punto de eliminar PERMANENTEMENTE tu cuenta.\n\n' +
                    '• Se eliminarán TODOS tus datos\n' +
                    '• No se puede deshacer\n' +
                    '• No podrás recuperar nada\n\n' +
                    '¿Estás ABSOLUTAMENTE seguro?'
                );
                
                if (!confirmacionFinal) {
                    e.preventDefault();
                    return;
                }
                
                // Cambiar texto del botón
                submitBtn.disabled = true;
                submitBtn.innerHTML = '⏳ Eliminando cuenta...';
                submitBtn.style.background = '#6c757d';
            });
            
            // Validación en tiempo real del campo "ELIMINAR"
            const confirmacionTexto = document.getElementById('confirmacion_texto');
            confirmacionTexto.addEventListener('input', function() {
                if (this.value === 'ELIMINAR') {
                    this.style.borderColor = '#27ae60';
                    this.style.background = '#d4edda';
                } else if (this.value.length > 0) {
                    this.style.borderColor = '#e74c3c';
                    this.style.background = '#f8d7da';
                } else {
                    this.style.borderColor = '#5a6c7d';
                    this.style.background = 'rgba(44, 62, 80, 0.8)';
                }
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
        <?php endif; ?>
    </script>
</body>
</html>