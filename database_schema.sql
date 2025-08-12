-- =============================================
-- SCRIPT COMPLETO DE BASE DE DATOS ACTUALIZADO
-- Sistema de Publicaciones con Usuarios
-- Compatible con MariaDB/MySQL
-- =============================================

-- Eliminar base de datos si existe (para empezar desde cero)
DROP DATABASE IF EXISTS publicaciones_db;

-- Crear base de datos
CREATE DATABASE publicaciones_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Usar la base de datos
USE publicaciones_db;

-- Crear tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL COMMENT 'Nombre del usuario',
    apellido VARCHAR(100) NOT NULL COMMENT 'Apellido del usuario',
    usuario VARCHAR(50) NOT NULL UNIQUE COMMENT 'Nombre de usuario único',
    email VARCHAR(255) NOT NULL UNIQUE COMMENT 'Correo electrónico único',
    password VARCHAR(255) NOT NULL COMMENT 'Contraseña hasheada',
    email_verificado BOOLEAN DEFAULT FALSE COMMENT 'Si el email fue verificado',
    token_verificacion VARCHAR(255) NULL COMMENT 'Token para verificar email',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de registro',
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha de última modificación',
    
    INDEX idx_usuario (usuario),
    INDEX idx_email (email),
    INDEX idx_token_verificacion (token_verificacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de usuarios del sistema';

-- Crear tabla de publicaciones (actualizada con usuario_id)
CREATE TABLE publicaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL COMMENT 'ID del usuario que creó la publicación',
    nombre VARCHAR(255) NOT NULL COMMENT 'Título de la publicación',
    comentario TEXT NOT NULL COMMENT 'Comentario o descripción',
    puntuacion TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Puntuación de 1 a 5 estrellas',
    imagen1_url TEXT NULL COMMENT 'URL de la primera imagen en Cloudinary',
    imagen2_url TEXT NULL COMMENT 'URL de la segunda imagen en Cloudinary',
    imagen3_url TEXT NULL COMMENT 'URL de la tercera imagen en Cloudinary',
    video_url VARCHAR(500) NULL COMMENT 'URL completa del video de YouTube',
    mapa_iframe TEXT NULL COMMENT 'Código iframe completo de Google Maps',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación automática',
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha de última modificación',
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_fecha_creacion (fecha_creacion),
    INDEX idx_nombre (nombre),
    INDEX idx_puntuacion (puntuacion),
    
    -- Constraint para validar puntuación entre 1 y 5
    CONSTRAINT chk_puntuacion CHECK (puntuacion >= 1 AND puntuacion <= 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla principal para almacenar las publicaciones del sistema';

-- Crear tabla de sesiones (para manejo de sesiones seguras)
CREATE TABLE sesiones (
    id VARCHAR(128) PRIMARY KEY,
    usuario_id INT NOT NULL,
    datos TEXT NOT NULL,
    ultima_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_ultima_actividad (ultima_actividad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla para manejo de sesiones de usuario';

-- Insertar usuario de ejemplo (contraseña: password123)
INSERT INTO usuarios (nombre, apellido, usuario, email, password, email_verificado) VALUES 
('Juan', 'Pérez', 'juanperez', 'juan@ejemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);

-- Insertar publicación de ejemplo
INSERT INTO publicaciones (usuario_id, nombre, comentario, puntuacion, video_url) VALUES 
(1, 'Restaurante La Esquina', 'Excelente lugar para cenar con familia. La comida es deliciosa y el servicio muy atento.', 5, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');

-- Mostrar estructura de las tablas
DESCRIBE usuarios;
DESCRIBE publicaciones;
DESCRIBE sesiones;

-- Mostrar datos de ejemplo
SELECT u.nombre, u.apellido, u.usuario, u.email, u.email_verificado, u.fecha_creacion 
FROM usuarios u ORDER BY u.fecha_creacion DESC;

SELECT p.id, u.nombre, u.apellido, p.nombre as titulo, p.puntuacion, p.fecha_creacion 
FROM publicaciones p 
JOIN usuarios u ON p.usuario_id = u.id 
ORDER BY p.fecha_creacion DESC;