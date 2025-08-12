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
        // Procesar imágenes
        $imagen1_url = null;
        $imagen2_url = null;
        $imagen3_url = null;
        
        if (!empty($_FILES['imagen1']['tmp_name'])) {
            $imagen1_url = uploadToCloudinary($_FILES['imagen1']);
            if (!$imagen1_url) {
                $errors[] = "Error al subir la imagen 1";
            }
        }
        
        if (!empty($_FILES['imagen2']['tmp_name'])) {
            $imagen2_url = uploadToCloudinary($_FILES['imagen2']);
            if (!$imagen2_url) {
                $errors[] = "Error al subir la imagen 2";
            }
        }
        
        if (!empty($_FILES['imagen3']['tmp_name'])) {
            $imagen3_url = uploadToCloudinary($_FILES['imagen3']);
            if (!$imagen3_url) {
                $errors[] = "Error al subir la imagen 3";
            }
        }
        
        if (empty($errors)) {
            // Guardar en base de datos
            $stmt = $pdo->prepare("INSERT INTO publicaciones (usuario_id, nombre, comentario, puntuacion, imagen1_url, imagen2_url, imagen3_url, video_url, mapa_iframe, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            
            if ($stmt->execute([$usuario_actual['id'], $nombre, $comentario, $puntuacion, $imagen1_url, $imagen2_url, $imagen3_url, $video_url, $mapa_iframe])) {
                $success = "¡Reseña publicada exitosamente!";
                // Limpiar variables para resetear el formulario
                $nombre = $comentario = $video_url = $mapa_iframe = '';
                $puntuacion = 0;
            } else {
                $errors[] = "Error al guardar la reseña";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escribir Reseña - Sistema de Reseñas</title>
    <?php include 'includes/styles.php'; ?>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <h1 class="page-title">✍️ Comparte tu Experiencia</h1>
        
        <div class="form-container">
            <div class="text-center mb-3">
                <h2>Nueva Reseña</h2>
                <p class="text-muted">Ayuda a otros usuarios compartiendo tu experiencia en este lugar</p>
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
                        <a href="index.php" class="btn btn-primary btn-small">Ver en inicio</a>
                        <a href="perfil.php" class="btn btn-success btn-small">Ver en mi perfil</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" id="reseñaForm">
                <div class="form-group">
                    <label for="nombre">📍 Nombre del Lugar <span class="required">*</span></label>
                    <input type="text" 
                           id="nombre" 
                           name="nombre" 
                           value="<?php echo htmlspecialchars($nombre ?? ''); ?>" 
                           placeholder="Ej: Restaurante La Esquina, Hotel Plaza, Museo Nacional..."
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
                    <input type="hidden" id="puntuacion" name="puntuacion" value="<?php echo $puntuacion ?? 0; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="imagen1">📸 Imagen 1 (opcional)</label>
                    <input type="file" id="imagen1" name="imagen1" accept="image/*">
                    <small class="text-muted">Formatos: JPG, PNG, GIF. Máximo 10MB</small>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="imagen2">📸 Imagen 2 (opcional)</label>
                        <input type="file" id="imagen2" name="imagen2" accept="image/*">
                    </div>
                    
                    <div class="form-group">
                        <label for="imagen3">📸 Imagen 3 (opcional)</label>
                        <input type="file" id="imagen3" name="imagen3" accept="image/*">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="video_url">🎥 Video de YouTube (opcional)</label>
                    <input type="text" 
                           id="video_url" 
                           name="video_url" 
                           value="<?php echo htmlspecialchars($video_url ?? ''); ?>" 
                           placeholder="https://www.youtube.com/watch?v=...">
                    <small class="text-muted">Pega aquí el enlace completo del video de YouTube</small>
                </div>
                
                <div class="form-group">
                    <label for="mapa_iframe">🗺️ Ubicación en Google Maps (opcional)</label>
                    <textarea id="mapa_iframe" 
                              name="mapa_iframe" 
                              placeholder="Pega aquí el código iframe completo de Google Maps..."><?php echo htmlspecialchars($mapa_iframe ?? ''); ?></textarea>
                    <small class="text-muted">
                        Ve a Google Maps → Busca el lugar → Compartir → Insertar mapa → Copia el código HTML
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="comentario">💭 Tu Experiencia <span class="required">*</span></label>
                    <textarea id="comentario" 
                              name="comentario" 
                              placeholder="Cuéntanos sobre tu experiencia: ¿Qué te gustó? ¿Qué recomendarías? ¿Volverías?"
                              required><?php echo htmlspecialchars($comentario ?? ''); ?></textarea>
                    <small class="text-muted">Mínimo 20 caracteres. Sé específico y útil para otros usuarios.</small>
                </div>
                
                <button type="submit" class="btn btn-success btn-full" id="submitBtn">
                    🚀 Publicar Reseña
                </button>
            </form>
        </div>
        
        <div class="card">
            <h3 class="text-center">💡 Consejos para una buena reseña</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
                <div>
                    <h4>📝 Sé específico</h4>
                    <p class="text-muted">Menciona detalles como la calidad del servicio, precios, ambiente, etc.</p>
                </div>
                <div>
                    <h4>📸 Agrega fotos</h4>
                    <p class="text-muted">Las imágenes ayudan mucho a otros usuarios a conocer el lugar.</p>
                </div>
                <div>
                    <h4>🎯 Sé honesto</h4>
                    <p class="text-muted">Comparte tanto lo positivo como lo que se puede mejorar.</p>
                </div>
                <div>
                    <h4>🗺️ Incluye ubicación</h4>
                    <p class="text-muted">El mapa ayuda a otros usuarios a encontrar fácilmente el lugar.</p>
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
            
            // Configurar rating inicial si existe
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
            const form = document.getElementById('reseñaForm');
            const submitBtn = document.getElementById('submitBtn');
            
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
                
                // Deshabilitar botón para evitar doble envío
                submitBtn.disabled = true;
                submitBtn.textContent = '⏳ Publicando...';
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
            
            // Contador de caracteres para el comentario
            const comentarioTextarea = document.getElementById('comentario');
            const comentarioGroup = comentarioTextarea.closest('.form-group');
            
            const charCounter = document.createElement('div');
            charCounter.className = 'text-muted';
            charCounter.style.textAlign = 'right';
            charCounter.style.fontSize = '14px';
            charCounter.style.marginTop = '5px';
            
            comentarioGroup.appendChild(charCounter);
            
            function updateCharCounter() {
                const length = comentarioTextarea.value.length;
                charCounter.textContent = `${length} caracteres`;
                
                if (length < 20) {
                    charCounter.style.color = '#e74c3c';
                } else if (length > 500) {
                    charCounter.style.color = '#f39c12';
                } else {
                    charCounter.style.color = '#27ae60';
                }
            }
            
            comentarioTextarea.addEventListener('input', updateCharCounter);
            updateCharCounter();
            
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