<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Requiere login
requireLogin();

$usuario_actual = getCurrentUser($pdo);
$errors = [];
$success = '';

// Obtener ID de la publicación
$id_publicacion = (int)($_GET['id'] ?? 0);

if ($id_publicacion <= 0) {
    header('Location: perfil.php');
    exit;
}

// Verificar que la publicación pertenece al usuario
$stmt = $pdo->prepare("SELECT * FROM publicaciones WHERE id = ? AND usuario_id = ?");
$stmt->execute([$id_publicacion, $usuario_actual['id']]);
$publicacion = $stmt->fetch();

if (!$publicacion) {
    header('Location: perfil.php');
    exit;
}

// Procesar formulario
if ($_POST) {
    $nombre = trim($_POST['nombre'] ?? '');
    $comentario = trim($_POST['comentario'] ?? '');
    $puntuacion = (int)($_POST['puntuacion'] ?? 0);
    $video_url = trim($_POST['video_url'] ?? '');
    $mapa_iframe = trim($_POST['mapa_iframe'] ?? '');
    
    // Validaciones
    if (empty($nombre)) {
        $errors[] = "El nombre/título es requerido";
    }
    
    if (empty($comentario)) {
        $errors[] = "El comentario es requerido";
    }
    
    if ($puntuacion < 1 || $puntuacion > 5) {
        $errors[] = "La puntuación debe estar entre 1 y 5 estrellas";
    }
    
    if (empty($errors)) {
        // Procesar imágenes (mantener las existentes si no se suben nuevas)
        $imagen1_url = $publicacion['imagen1_url'];
        $imagen2_url = $publicacion['imagen2_url'];
        $imagen3_url = $publicacion['imagen3_url'];
        
        if (!empty($_FILES['imagen1']['tmp_name'])) {
            $nueva_imagen1 = uploadToCloudinary($_FILES['imagen1']);
            if ($nueva_imagen1) {
                $imagen1_url = $nueva_imagen1;
            } else {
                $errors[] = "Error al subir la imagen 1";
            }
        }
        
        if (!empty($_FILES['imagen2']['tmp_name'])) {
            $nueva_imagen2 = uploadToCloudinary($_FILES['imagen2']);
            if ($nueva_imagen2) {
                $imagen2_url = $nueva_imagen2;
            } else {
                $errors[] = "Error al subir la imagen 2";
            }
        }
        
        if (!empty($_FILES['imagen3']['tmp_name'])) {
            $nueva_imagen3 = uploadToCloudinary($_FILES['imagen3']);
            if ($nueva_imagen3) {
                $imagen3_url = $nueva_imagen3;
            } else {
                $errors[] = "Error al subir la imagen 3";
            }
        }
        
        // Procesar eliminación de imágenes
        if (isset($_POST['eliminar_imagen1'])) {
            $imagen1_url = null;
        }
        if (isset($_POST['eliminar_imagen2'])) {
            $imagen2_url = null;
        }
        if (isset($_POST['eliminar_imagen3'])) {
            $imagen3_url = null;
        }
        
        if (empty($errors)) {
            // Actualizar en base de datos
            $stmt = $pdo->prepare("UPDATE publicaciones SET nombre = ?, comentario = ?, puntuacion = ?, imagen1_url = ?, imagen2_url = ?, imagen3_url = ?, video_url = ?, mapa_iframe = ?, fecha_modificacion = NOW() WHERE id = ? AND usuario_id = ?");
            
            if ($stmt->execute([$nombre, $comentario, $puntuacion, $imagen1_url, $imagen2_url, $imagen3_url, $video_url, $mapa_iframe, $id_publicacion, $usuario_actual['id']])) {
                $success = "¡Reseña actualizada exitosamente!";
                
                // Recargar datos actualizados
                $stmt = $pdo->prepare("SELECT * FROM publicaciones WHERE id = ? AND usuario_id = ?");
                $stmt->execute([$id_publicacion, $usuario_actual['id']]);
                $publicacion = $stmt->fetch();
            } else {
                $errors[] = "Error al actualizar la reseña";
            }
        }
    }
} else {
    // Cargar datos existentes en las variables
    $nombre = $publicacion['nombre'];
    $comentario = $publicacion['comentario'];
    $puntuacion = $publicacion['puntuacion'];
    $video_url = $publicacion['video_url'];
    $mapa_iframe = $publicacion['mapa_iframe'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Reseña - Sistema de Reseñas</title>
    <?php include 'includes/styles.php'; ?>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <h1 class="page-title">✏️ Editar Reseña</h1>
        
        <div class="form-container">
            <div class="text-center mb-3">
                <h2>Modificar: <?php echo htmlspecialchars($publicacion['nombre']); ?></h2>
                <p class="text-muted">
                    Publicado el: <?php echo date('d/m/Y H:i', strtotime($publicacion['fecha_creacion'])); ?>
                    <?php if ($publicacion['fecha_modificacion'] != $publicacion['fecha_creacion']): ?>
                        | Última edición: <?php echo date('d/m/Y H:i', strtotime($publicacion['fecha_modificacion'])); ?>
                    <?php endif; ?>
                </p>
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
                        <a href="index.php" class="btn btn-success btn-small">Ver en inicio</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" id="editarReseñaForm">
                <div class="form-group">
                    <label for="nombre">📍 Nombre del Lugar <span class="required">*</span></label>
                    <input type="text" 
                           id="nombre" 
                           name="nombre" 
                           value="<?php echo htmlspecialchars($nombre); ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="puntuacion">⭐ Puntuación <span class="required">*</span></label>
                    <div class="rating-input">
                        <span>Tu calificación:</span>
                        <div class="rating-stars" id="ratingStars">
                            <span class="star interactive" data-rating="1">☆</span>
                            <span class="star interactive" data-rating="2">☆</span>
                            <span class="star interactive" data-rating="3">☆</span>
                            <span class="star interactive" data-rating="4">☆</span>
                            <span class="star interactive" data-rating="5">☆</span>
                        </div>
                        <span id="ratingText">Selecciona una puntuación</span>
                    </div>
                    <input type="hidden" id="puntuacion" name="puntuacion" value="<?php echo $puntuacion; ?>" required>
                </div>
                
                <!-- Imágenes existentes -->
                <?php if ($publicacion['imagen1_url'] || $publicacion['imagen2_url'] || $publicacion['imagen3_url']): ?>
                    <div class="form-group">
                        <label>📸 Imágenes Actuales</label>
                        <div class="imagenes-container">
                            <?php if ($publicacion['imagen1_url']): ?>
                                <div class="imagen-item">
                                    <img src="<?php echo htmlspecialchars($publicacion['imagen1_url']); ?>" alt="Imagen 1">
                                    <div class="mt-1">
                                        <label>
                                            <input type="checkbox" name="eliminar_imagen1" value="1"> 🗑️ Eliminar
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($publicacion['imagen2_url']): ?>
                                <div class="imagen-item">
                                    <img src="<?php echo htmlspecialchars($publicacion['imagen2_url']); ?>" alt="Imagen 2">
                                    <div class="mt-1">
                                        <label>
                                            <input type="checkbox" name="eliminar_imagen2" value="1"> 🗑️ Eliminar
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($publicacion['imagen3_url']): ?>
                                <div class="imagen-item">
                                    <img src="<?php echo htmlspecialchars($publicacion['imagen3_url']); ?>" alt="Imagen 3">
                                    <div class="mt-1">
                                        <label>
                                            <input type="checkbox" name="eliminar_imagen3" value="1"> 🗑️ Eliminar
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Nuevas imágenes -->
                <div class="form-group">
                    <label for="imagen1">📸 Nueva Imagen 1 (reemplaza la actual)</label>
                    <input type="file" id="imagen1" name="imagen1" accept="image/*">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="imagen2">📸 Nueva Imagen 2</label>
                        <input type="file" id="imagen2" name="imagen2" accept="image/*">
                    </div>
                    
                    <div class="form-group">
                        <label for="imagen3">📸 Nueva Imagen 3</label>
                        <input type="file" id="imagen3" name="imagen3" accept="image/*">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="video_url">🎥 Video de YouTube</label>
                    <input type="text" 
                           id="video_url" 
                           name="video_url" 
                           value="<?php echo htmlspecialchars($video_url); ?>" 
                           placeholder="https://www.youtube.com/watch?v=...">
                </div>
                
                <div class="form-group">
                    <label for="mapa_iframe">🗺️ Ubicación en Google Maps</label>
                    <textarea id="mapa_iframe" 
                              name="mapa_iframe" 
                              placeholder="Código iframe de Google Maps..."><?php echo htmlspecialchars($mapa_iframe); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="comentario">💭 Tu Experiencia <span class="required">*</span></label>
                    <textarea id="comentario" 
                              name="comentario" 
                              required><?php echo htmlspecialchars($comentario); ?></textarea>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">💾 Guardar Cambios</button>
                    <a href="perfil.php" class="btn btn-warning">❌ Cancelar</a>
                </div>
            </form>
        </div>
        
        <!-- Vista previa de la reseña actual -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">👁️ Vista Previa Actual</h3>
            </div>
            
            <div class="publicacion">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h3><?php echo htmlspecialchars($publicacion['nombre']); ?></h3>
                    <?php if ($publicacion['puntuacion']): ?>
                        <?php echo renderStars($publicacion['puntuacion']); ?>
                    <?php endif; ?>
                </div>
                
                <div class="publicacion-meta">
                    <span class="publicacion-autor">
                        👤 Por: <?php echo htmlspecialchars($usuario_actual['nombre'] . ' ' . $usuario_actual['apellido']); ?>
                    </span>
                    <span>📅 <?php echo date('d/m/Y H:i', strtotime($publicacion['fecha_creacion'])); ?></span>
                </div>
                
                <?php if ($publicacion['imagen1_url'] || $publicacion['imagen2_url'] || $publicacion['imagen3_url']): ?>
                    <div class="imagenes-container">
                        <?php if ($publicacion['imagen1_url']): ?>
                            <div class="imagen-item">
                                <img src="<?php echo htmlspecialchars($publicacion['imagen1_url']); ?>" alt="Imagen 1">
                            </div>
                        <?php endif; ?>
                        <?php if ($publicacion['imagen2_url']): ?>
                            <div class="imagen-item">
                                <img src="<?php echo htmlspecialchars($publicacion['imagen2_url']); ?>" alt="Imagen 2">
                            </div>
                        <?php endif; ?>
                        <?php if ($publicacion['imagen3_url']): ?>
                            <div class="imagen-item">
                                <img src="<?php echo htmlspecialchars($publicacion['imagen3_url']); ?>" alt="Imagen 3">
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($publicacion['video_url']): ?>
                    <?php $youtube_id = getYouTubeId($publicacion['video_url']); ?>
                    <?php if ($youtube_id): ?>
                        <div class="video-container">
                            <iframe src="https://www.youtube.com/embed/<?php echo htmlspecialchars($youtube_id); ?>" 
                                    frameborder="0" allowfullscreen></iframe>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if ($publicacion['mapa_iframe']): ?>
                    <div class="mapa-container">
                        <?php echo $publicacion['mapa_iframe']; ?>
                    </div>
                <?php endif; ?>
                
                <div class="comentario">
                    <?php echo nl2br(htmlspecialchars($publicacion['comentario'])); ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.star.interactive');
            const ratingInput = document.getElementById('puntuacion');
            const ratingText = document.getElementById('ratingText');
            
            const ratingTexts = {
                1: "😞 Muy malo",
                2: "😐 Regular", 
                3: "🙂 Bueno",
                4: "😃 Muy bueno",
                5: "🤩 Excelente"
            };
            
            // Configurar rating inicial
            const initialRating = parseInt(ratingInput.value);
            if (initialRating > 0) {
                updateStarDisplay(initialRating);
            }
            
            stars.forEach(star => {
                star.addEventListener('mouseover', function() {
                    const rating = parseInt(this.dataset.rating);
                    highlightStars(rating);
                });
                
                star.addEventListener('mouseout', function() {
                    const currentRating = parseInt(ratingInput.value);
                    highlightStars(currentRating);
                });
                
                star.addEventListener('click', function() {
                    const rating = parseInt(this.dataset.rating);
                    ratingInput.value = rating;
                    updateStarDisplay(rating);
                    ratingText.textContent = ratingTexts[rating];
                });
            });
            
            function highlightStars(rating) {
                stars.forEach((star, index) => {
                    if (index < rating) {
                        star.textContent = '★';
                        star.style.color = '#f1c40f';
                    } else {
                        star.textContent = '☆';
                        star.style.color = '#95a5a6';
                    }
                });
            }
            
            function updateStarDisplay(rating) {
                highlightStars(rating);
                ratingText.textContent = ratingTexts[rating] || "Selecciona una puntuación";
            }
            
            // Validación del formulario
            const form = document.getElementById('editarReseñaForm');
            
            form.addEventListener('submit', function(e) {
                const rating = parseInt(ratingInput.value);
                const comentario = document.getElementById('comentario').value.trim();
                
                if (rating < 1 || rating > 5) {
                    e.preventDefault();
                    alert('Por favor selecciona una puntuación del 1 al 5');
                    return;
                }
                
                if (comentario.length < 20) {
                    e.preventDefault();
                    alert('El comentario debe tener al menos 20 caracteres');
                    return;
                }
            });
            
            // Confirmación para eliminar imágenes
            const eliminarCheckboxes = document.querySelectorAll('input[type="checkbox"][name^="eliminar_imagen"]');
            eliminarCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        if (!confirm('¿Estás seguro de que quieres eliminar esta imagen?')) {
                            this.checked = false;
                        }
                    }
                });
            });
            
            // Validación de archivos
            const fileInputs = document.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        if (file.size > 10 * 1024 * 1024) { // 10MB
                            alert('El archivo es demasiado grande. Máximo 10MB.');
                            this.value = '';
                            return;
                        }
                        
                        if (!file.type.startsWith('image/')) {
                            alert('Solo se permiten archivos de imagen.');
                            this.value = '';
                            return;
                        }
                    }
                });
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
    </script>
</body>
</html>