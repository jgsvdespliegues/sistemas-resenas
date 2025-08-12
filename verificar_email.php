<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

$success = '';
$error = '';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = 'Token de verificación no válido';
} else {
    // Buscar usuario con este token
    $stmt = $pdo->prepare("SELECT id, nombre, email FROM usuarios WHERE token_verificacion = ? AND email_verificado = FALSE");
    $stmt->execute([$token]);
    $usuario = $stmt->fetch();
    
    if ($usuario) {
        // Verificar email
        $stmt = $pdo->prepare("UPDATE usuarios SET email_verificado = TRUE, token_verificacion = NULL WHERE id = ?");
        if ($stmt->execute([$usuario['id']])) {
            $success = "¡Email verificado exitosamente! Ya puedes iniciar sesión con tu cuenta.";
        } else {
            $error = "Error al verificar el email. Inténtalo nuevamente.";
        }
    } else {
        $error = "Token de verificación no válido o ya utilizado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Email - Sistema de Reseñas</title>
    <?php include 'includes/styles.php'; ?>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <h1 class="page-title">📧 Verificación de Email</h1>
        
        <div class="card text-center">
            <?php if ($success): ?>
                <div style="font-size: 4em; color: #27ae60; margin-bottom: 20px;">✅</div>
                <h2 style="color: #27ae60; margin-bottom: 20px;">¡Verificación Exitosa!</h2>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <div class="mt-3">
                    <a href="login.php" class="btn btn-primary">Iniciar Sesión</a>
                    <a href="index.php" class="btn btn-success">Ir al Inicio</a>
                </div>
                
            <?php elseif ($error): ?>
                <div style="font-size: 4em; color: #e74c3c; margin-bottom: 20px;">❌</div>
                <h2 style="color: #e74c3c; margin-bottom: 20px;">Error de Verificación</h2>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <div class="mt-3">
                    <a href="registro.php" class="btn btn-primary">Registrarse Nuevamente</a>
                    <a href="login.php" class="btn btn-success">Iniciar Sesión</a>
                </div>
                
            <?php else: ?>
                <div style="font-size: 4em; color: #f39c12; margin-bottom: 20px;">⏳</div>
                <h2 style="color: #f39c12; margin-bottom: 20px;">Verificando...</h2>
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Procesando verificación...</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h3 class="text-center">💡 ¿Necesitas ayuda?</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
                <div class="text-center">
                    <div style="font-size: 2em; margin-bottom: 10px;">📬</div>
                    <h4>Revisa tu bandeja</h4>
                    <p class="text-muted">El email puede tardar unos minutos en llegar. Revisa también la carpeta de spam.</p>
                </div>
                <div class="text-center">
                    <div style="font-size: 2em; margin-bottom: 10px;">🔗</div>
                    <h4>Link único</h4>
                    <p class="text-muted">Cada enlace de verificación solo se puede usar una vez y expira en 24 horas.</p>
                </div>
                <div class="text-center">
                    <div style="font-size: 2em; margin-bottom: 10px;">❓</div>
                    <h4>¿Problemas?</h4>
                    <p class="text-muted">Si tienes problemas, puedes registrarte nuevamente o contactar al soporte.</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Animación de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });
    </script>
</body>
</html>