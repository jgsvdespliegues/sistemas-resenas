<?php
// includes/auth.php
// Funciones de autenticación y utilidades

// Verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

// Obtener datos del usuario actual
function getCurrentUser($pdo) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT id, nombre, apellido, usuario, email, email_verificado FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    return $stmt->fetch();
}

// Requiere que el usuario esté logueado
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

// Logout
function logout() {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

// Generar token seguro
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Función para hash de contraseña
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verificar contraseña
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Enviar email de verificación usando PHPMailer
function sendVerificationEmail($email, $nombre, $token) {
    // Intentar cargar PHPMailer
    $phpmailer_loaded = false;
    
    // Cargar con Composer si está disponible
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
        $phpmailer_loaded = true;
    } 
    // Si no, cargar manualmente
    elseif (file_exists(__DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php') && 
            file_exists(__DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php') && 
            file_exists(__DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php')) {
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
        $phpmailer_loaded = true;
    }
    
    if (!$phpmailer_loaded) {
        // Si PHPMailer no está disponible, guardar en log para desarrollo
        $verification_link = BASE_URL . "verificar_email.php?token=" . $token;
        $log_message = "\n" . date('Y-m-d H:i:s') . " - VERIFICACIÓN PENDIENTE\n";
        $log_message .= "========================================\n";
        $log_message .= "Email: $email\n";
        $log_message .= "Nombre: $nombre\n";
        $log_message .= "Link de verificación: $verification_link\n";
        $log_message .= "Token: $token\n";
        $log_message .= "NOTA: PHPMailer no está instalado. Usa este link para verificar manualmente.\n";
        $log_message .= "========================================\n";
        
        file_put_contents(__DIR__ . '/../email_verification_log.txt', $log_message, FILE_APPEND | LOCK_EX);
        
        // Retornar false para mostrar el mensaje de error apropiado
        return false;
    }
    
    // Usar nombres completos de clases en lugar de 'use'
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        
        // Configuración adicional para Gmail
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Configuración del remitente y destinatario
        $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);
        $mail->addAddress($email, $nombre);
        $mail->addReplyTo(MAIL_USERNAME, MAIL_FROM_NAME);

        // Configuración del contenido
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = '🌟 Verificación de cuenta - Sistema de Reseñas';
        
        $verification_link = BASE_URL . "verificar_email.php?token=" . $token;
        
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Verificación de cuenta</title>
            <style>
                body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background-color: white; }
                .header { background: linear-gradient(135deg, #2c3e50, #3498db); padding: 30px 20px; text-align: center; }
                .header h1 { color: white; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
                .content { padding: 40px 30px; }
                .content h2 { color: #2c3e50; margin-bottom: 20px; }
                .button { display: inline-block; background: linear-gradient(45deg, #3498db, #2980b9); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; transition: transform 0.3s ease; }
                .button:hover { transform: translateY(-2px); }
                .link-box { background: #ecf0f1; padding: 15px; border-radius: 8px; word-break: break-all; margin: 20px 0; border-left: 4px solid #3498db; }
                .footer { background: #34495e; color: #bdc3c7; text-align: center; padding: 20px; font-size: 14px; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 8px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🌟 Bienvenido al Sistema de Reseñas</h1>
                </div>
                <div class='content'>
                    <h2>¡Hola $nombre!</h2>
                    <p>¡Gracias por unirte a nuestra comunidad de reseñas! Estamos emocionados de tenerte con nosotros.</p>
                    
                    <p>Para completar tu registro y comenzar a compartir tus experiencias, necesitas verificar tu dirección de email haciendo clic en el siguiente botón:</p>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$verification_link' class='button'>✅ Verificar mi cuenta</a>
                    </div>
                    
                    <p>Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
                    <div class='link-box'>$verification_link</div>
                    
                    <div class='warning'>
                        <strong>⏰ Importante:</strong> Este enlace de verificación expirará en 24 horas por motivos de seguridad.
                    </div>
                    
                    <p>Una vez verificada tu cuenta, podrás:</p>
                    <ul>
                        <li>📝 Escribir reseñas detalladas</li>
                        <li>📸 Subir fotos de tus experiencias</li>
                        <li>⭐ Calificar lugares del 1 al 5</li>
                        <li>🗺️ Compartir ubicaciones</li>
                        <li>👥 Descubrir recomendaciones de otros usuarios</li>
                    </ul>
                </div>
                <div class='footer'>
                    <p>Si no te registraste en nuestro sitio, puedes ignorar este email de forma segura.</p>
                    <p>© " . date('Y') . " Sistema de Reseñas - Conectando experiencias</p>
                </div>
            </div>
        </body>
        </html>";

        // Versión texto plano como alternativa
        $mail->AltBody = "¡Hola $nombre!\n\nGracias por registrarte en nuestro Sistema de Reseñas.\n\nPara verificar tu cuenta, visita este enlace:\n$verification_link\n\nEste enlace expirará en 24 horas.\n\nSi no te registraste, puedes ignorar este email.\n\n¡Gracias!";

        $mail->send();
        return true;
        
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        // Log detallado del error para debugging
        $error_log = "\n" . date('Y-m-d H:i:s') . " - ERROR EMAIL\n";
        $error_log .= "========================\n";
        $error_log .= "Email destino: $email\n";
        $error_log .= "Error: {$mail->ErrorInfo}\n";
        $error_log .= "Excepción: " . $e->getMessage() . "\n";
        $error_log .= "========================\n";
        
        error_log($error_log, 3, __DIR__ . '/../email_errors.log');
        return false;
    } catch (Exception $e) {
        // Catch genérico para otros errores
        $error_log = "\n" . date('Y-m-d H:i:s') . " - ERROR GENERAL\n";
        $error_log .= "========================\n";
        $error_log .= "Email destino: $email\n";
        $error_log .= "Error: " . $e->getMessage() . "\n";
        $error_log .= "========================\n";
        
        error_log($error_log, 3, __DIR__ . '/../email_errors.log');
        return false;
    }
}

// Sanitizar entrada
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Validar email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Función para subir imagen a Cloudinary
function uploadToCloudinary($file) {
    $upload_url = "https://api.cloudinary.com/v1_1/" . CLOUDINARY_CLOUD_NAME . "/image/upload";
    
    $timestamp = time();
    $signature = sha1("timestamp=$timestamp" . CLOUDINARY_API_SECRET);
    
    $post_data = array(
        'file' => new CurlFile($file['tmp_name'], $file['type'], $file['name']),
        'timestamp' => $timestamp,
        'api_key' => CLOUDINARY_API_KEY,
        'signature' => $signature
    );
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $upload_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if (isset($result['secure_url'])) {
        return $result['secure_url'];
    }
    
    return null;
}

// Función para extraer ID de YouTube
function getYouTubeId($url) {
    if (empty($url)) return null;
    
    $patterns = [
        '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i',
        '/youtube\.com\/shorts\/([^"&?\/\s]{11})/i',
        '/youtube\.com\/watch\?v=([^"&?\/\s]{11})/i',
        '/youtu\.be\/([^"&?\/\s]{11})/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
    }
    
    return null;
}

// Función para mostrar estrellas
function renderStars($puntuacion) {
    $html = '<div class="stars-rating">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $puntuacion) {
            $html .= '<span class="star filled">★</span>';
        } else {
            $html .= '<span class="star">☆</span>';
        }
    }
    $html .= '</div>';
    return $html;
}
?>