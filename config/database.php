<?php
// config/database.php
// Configuración de base de datos

$host = 'localhost';
$dbname = 'publicaciones_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Configuración de Cloudinary
define('CLOUDINARY_CLOUD_NAME', 'dyfw7jukd');
define('CLOUDINARY_API_KEY', '318147298777683');
define('CLOUDINARY_API_SECRET', 'CMbDjWIY4TDZ71g2_dvlBGVmI0k');

// Configuración de correo (para verificación de email)
define('MAIL_HOST', 'smtp.gmail.com'); // Cambiar por tu servidor SMTP
define('MAIL_USERNAME', 'jgsvdespliegues@gmail.com'); // Cambiar por tu email
define('MAIL_PASSWORD', 'qtvy rgfn cmky fxzt'); // Cambiar por tu password de aplicación
define('MAIL_PORT', 587);
define('MAIL_FROM_EMAIL', 'noreply@sistemareviews.com');
define('MAIL_FROM_NAME', 'Sistema de Reseñas');

// URL base del sitio
define('BASE_URL', 'http://localhost/sistema-resenas/'); // Cambiar por tu URL

// Configuración de sesiones
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 si usas HTTPS
ini_set('session.use_strict_mode', 1);
session_start();
?>