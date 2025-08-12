<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Requiere login
requireLogin();

$usuario_actual = getCurrentUser($pdo);
$success = '';
$errors = [];

// Procesar eliminación de reseña
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id_publicacion = (int)$_GET['eliminar'];
    
    // Verificar que la publicación pertenece al usuario
    $stmt = $pdo->prepare("SELECT id FROM publicaciones WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id_publicacion, $usuario_actual['id']]);
    
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("DELETE FROM publicaciones WHERE id = ? AND usuario_id = ?");
        if ($stmt->execute([$id_publicacion, $usuario_actual['id']])) {
            $success = "Reseña eliminada exitosamente";
        } else {
            $errors[] = "Error al eliminar la reseña";
        }
    } else {
        $errors[] = "No tienes permisos para eliminar esta reseña";
    }
}

// Obtener publicaciones del usuario
$stmt = $pdo->prepare("SELECT * FROM publicaciones WHERE usuario_id = ? ORDER BY fecha_creacion DESC");
$stmt->execute([$usuario_actual['id']]);
$mis_publicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular estadísticas
$total_resenas = count($mis_publicaciones);
$promedio_puntuacion = 0;
if ($total_resenas > 0) {
    $suma_puntuaciones = array_sum(array_column($mis_publicaciones, 'puntuacion'));
    $promedio_puntuacion = $suma_puntuaciones / $total_resenas;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Sistema de Reseñas</title>
    <?php include 'includes/styles.php'; ?>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <h1 class="page-title">👤 Hola, <?php echo htmlspecialchars($usuario_actual['nombre']); ?>!</h1>
        
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
        
        <!-- Información del usuario -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">📊 Tu Perfil</h2>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div class="text-center">
                    <div style="font-size: 3em; margin-bottom: 10px;">👋</div>
                    <h4><?php echo htmlspecialchars($usuario_actual['nombre'] . ' ' . $usuario_actual['apellido']); ?></h4>
                    <p class="text-muted">@<?php echo htmlspecialchars($usuario_actual['usuario']); ?></p>
                    <p class="text-muted"><?php echo htmlspecialchars($usuario_actual['email']); ?></p>
                </div>
                
                <div class="text-center">
                    <div style="font-size: 3em; margin-bottom: 10px;">📝</div>
                    <h4><?php echo $total_resenas; ?></h4>
                    <p class="text-muted">Reseñas escritas</p>
                </div>
                
                <div class="text-center">
                    <div style="font-size: 3em; margin-bottom: 10px;">⭐</div>
                    <h4><?php echo number_format($promedio_puntuacion, 1); ?></h4>
                    <p class="text-muted">Puntuación promedio</p>
                </div>
                
                <div class="text-center">
                    <div style="font-size: 3em; margin-bottom: 10px;">📅</div>
                    <h4><?php echo date('d/m/Y', strtotime($usuario_actual['fecha_creacion'] ?? 'now')); ?></h4>
                    <p class="text-muted">Miembro desde</p>
                </div>
            </div>
            
            <div class="mt-3 text-center">
                <a href="resena.php" class="btn btn-primary">✍️ Escribir Nueva Reseña</a>
                <a href="editar_perfil.php" class="btn btn-warning">⚙️ Editar Perfil</a>
            </div>
        </div>
        
        <!-- Mis reseñas -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="card-title">📋 Mis Reseñas</h2>
                <span class="text-muted"><?php echo $total_resenas; ?> reseña<?php echo $total_resenas != 1 ? 's' : ''; ?></span>
            </div>
            
            <?php if (empty($mis_publicaciones)): ?>
                <div class="empty-state">
                    <div class="icon">✍️</div>
                    <h3>No has escrito ninguna reseña aún</h3>
                    <p>¡Comparte tu primera experiencia y ayuda a otros usuarios!</p>
                    <a href="resena.php" class="btn btn-primary mt-2">Escribir mi primera reseña</a>
                </div>
            <?php else: ?>
                <?php foreach ($mis_publicaciones as $pub): ?>
                    <div class="publicacion">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h3><?php echo htmlspecialchars($pub['nombre']); ?></h3>
                            <div class="actions-container">
                                <a href="editar_resena.php?id=<?php echo $pub['id']; ?>" class="btn btn-warning btn-small">✏️ Editar</a>
                                <a href="?eliminar=<?php echo $pub['id']; ?>" 
                                   class="btn btn-danger btn-small"
                                   onclick="return confirm('¿Estás seguro de que quieres eliminar esta reseña?')">🗑️ Eliminar</a>
                            </div>
                        </div>
                        
                        <div class="publicacion-meta">
                            <?php if ($pub['puntuacion']): ?>
                                <?php echo renderStars($pub['puntuacion']); ?>
                            <?php endif; ?>
                            <span>📅 <?php echo date('d/m/Y H:i', strtotime($pub['fecha_creacion'])); ?></span>
                            <?php if ($pub['fecha_modificacion'] != $pub['fecha_creacion']): ?>
                                <span class="text-muted">(Editado: <?php echo date('d/m/Y H:i', strtotime($pub['fecha_modificacion'])); ?>)</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($pub['imagen1_url'] || $pub['imagen2_url'] || $pub['imagen3_url']): ?>
                            <div class="imagenes-container">
                                <?php if ($pub['imagen1_url']): ?>
                                    <div class="imagen-item">
                                        <img src="<?php echo htmlspecialchars($pub['imagen1_url']); ?>" 
                                             alt="Imagen 1" 
                                             onclick="openImageModal(this.src)">
                                    </div>
                                <?php endif; ?>
                                <?php if ($pub['imagen2_url']): ?>
                                    <div class="imagen-item">
                                        <img src="<?php echo htmlspecialchars($pub['imagen2_url']); ?>" 
                                             alt="Imagen 2" 
                                             onclick="openImageModal(this.src)">
                                    </div>
                                <?php endif; ?>
                                <?php if ($pub['imagen3_url']): ?>
                                    <div class="imagen-item">
                                        <img src="<?php echo htmlspecialchars($pub['imagen3_url']); ?>" 
                                             alt="Imagen 3" 
                                             onclick="openImageModal(this.src)">
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($pub['video_url']): ?>
                            <?php $youtube_id = getYouTubeId($pub['video_url']); ?>
                            <?php if ($youtube_id): ?>
                                <div class="video-container">
                                    <iframe src="https://www.youtube.com/embed/<?php echo htmlspecialchars($youtube_id); ?>" 
                                            frameborder="0" allowfullscreen title="Video de la reseña"></iframe>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if ($pub['mapa_iframe']): ?>
                            <div class="mapa-container">
                                <?php echo $pub['mapa_iframe']; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="comentario">
                            <?php echo nl2br(htmlspecialchars($pub['comentario'])); ?>
                        </div>
                    </div>
                    
                    <div class="separador"></div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal para ver imágenes -->
    <div id="imageModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeImageModal()">&times;</span>
            <img id="modalImage" src="" alt="Imagen ampliada" style="width: 100%; border-radius: 8px;">
        </div>
    </div>
    
    <script>
        function openImageModal(src) {
            document.getElementById('imageModal').style.display = 'block';
            document.getElementById('modalImage').src = src;
        }
        
        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }
        
        // Cerrar modal al hacer click fuera de la imagen
        window.onclick = function(event) {
            const modal = document.getElementById('imageModal');
            if (event.target == modal) {
                closeImageModal();
            }
        }
        
        // Animación de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card, .publicacion');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 150);
            });
        });
    </script>
</body>
</html>