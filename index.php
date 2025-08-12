<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Obtener publicaciones con información del usuario
$stmt = $pdo->query("
    SELECT p.*, u.nombre, u.apellido, u.usuario 
    FROM publicaciones p 
    JOIN usuarios u ON p.usuario_id = u.id 
    ORDER BY p.fecha_creacion DESC
");
$publicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Reseñas</title>
    <?php include 'includes/styles.php'; ?>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <h1 class="page-title">🌟 Descubre Lugares Increíbles</h1>
        
        <?php if (!isLoggedIn()): ?>
            <div class="card text-center">
                <h3>¡Únete a nuestra comunidad!</h3>
                <p class="text-muted">Regístrate para compartir tus experiencias y descubrir nuevos lugares a través de las reseñas de otros usuarios.</p>
                <div class="mt-3">
                    <a href="registro.php" class="btn btn-primary">Registrarse</a>
                    <a href="login.php" class="btn btn-success">Iniciar Sesión</a>
                </div>
            </div>
            <div class="separador"></div>
        <?php endif; ?>
        
        <?php if (empty($publicaciones)): ?>
            <div class="empty-state">
                <div class="icon">📝</div>
                <h3>No hay reseñas aún</h3>
                <p>Sé el primero en compartir una experiencia increíble.</p>
                <?php if (isLoggedIn()): ?>
                    <a href="resena.php" class="btn btn-primary mt-2">Escribir primera reseña</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach ($publicaciones as $pub): ?>
                <div class="publicacion fade-in">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h3><?php echo htmlspecialchars($pub['nombre']); ?></h3>
                        <?php if ($pub['puntuacion']): ?>
                            <?php echo renderStars($pub['puntuacion']); ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="publicacion-meta">
                        <span class="publicacion-autor">
                            👤 Por: <?php echo htmlspecialchars($pub['nombre'] . ' ' . $pub['apellido']); ?>
                            (@<?php echo htmlspecialchars($pub['usuario']); ?>)
                        </span>
                        <span>📅 <?php echo date('d/m/Y H:i', strtotime($pub['fecha_creacion'])); ?></span>
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
        
        // Animación de aparición para las publicaciones
        document.addEventListener('DOMContentLoaded', function() {
            const publicaciones = document.querySelectorAll('.publicacion');
            publicaciones.forEach((pub, index) => {
                setTimeout(() => {
                    pub.style.opacity = '0';
                    pub.style.transform = 'translateY(30px)';
                    pub.style.transition = 'all 0.6s ease';
                    
                    setTimeout(() => {
                        pub.style.opacity = '1';
                        pub.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 150);
            });
        });
    </script>
</body>
</html>