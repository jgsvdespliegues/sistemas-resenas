<?php
// includes/navbar.php
// Componente de navegación común

$current_page = basename($_SERVER['PHP_SELF'], '.php');
$usuario_actual = getCurrentUser($pdo);
?>

<nav class="navbar">
    <div class="navbar-container">
        <div class="navbar-brand">
            <a href="index.php">🌟 Reseñas</a>
        </div>
        
        <div class="navbar-menu" id="navbarMenu">
            <div class="navbar-nav">
                <a href="index.php" class="navbar-item <?php echo $current_page == 'index' ? 'active' : ''; ?>">
                    <i class="icon">🏠</i> Inicio
                </a>
                
                <?php if (isLoggedIn()): ?>
                    <a href="perfil.php" class="navbar-item <?php echo $current_page == 'perfil' ? 'active' : ''; ?>">
                        <i class="icon">👤</i> Perfil
                    </a>
                    <a href="resena.php" class="navbar-item <?php echo $current_page == 'resena' ? 'active' : ''; ?>">
                        <i class="icon">✍️</i> Reseña aquí
                    </a>
                    <div class="navbar-dropdown">
                        <a href="#" class="navbar-item navbar-dropdown-trigger">
                            <i class="icon">👋</i> Hola, <?php echo htmlspecialchars($usuario_actual['nombre']); ?>
                            <span class="dropdown-arrow">▼</span>
                        </a>
                        <div class="navbar-dropdown-menu">
                            <a href="perfil.php" class="navbar-dropdown-item">Mi Perfil</a>
                            <a href="logout.php" class="navbar-dropdown-item">Cerrar Sesión</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="navbar-item <?php echo in_array($current_page, ['login', 'registro']) ? 'active' : ''; ?>">
                        <i class="icon">🔐</i> Ingresar
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="navbar-burger" id="navbarBurger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</nav>

<style>
.navbar {
    background: rgba(52, 73, 94, 0.95);
    backdrop-filter: blur(10px);
    border-bottom: 2px solid #3498db;
    padding: 0;
    margin-bottom: 20px;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.navbar-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20px;
    height: 70px;
}

.navbar-brand a {
    color: #3498db;
    font-size: 1.8em;
    font-weight: bold;
    text-decoration: none;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.navbar-menu {
    display: flex;
    align-items: center;
}

.navbar-nav {
    display: flex;
    align-items: center;
    gap: 10px;
}

.navbar-item {
    color: #ecf0f1;
    text-decoration: none;
    padding: 12px 20px;
    border-radius: 8px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
}

.navbar-item:hover {
    background: rgba(52, 152, 219, 0.2);
    color: #3498db;
    transform: translateY(-2px);
}

.navbar-item.active {
    background: rgba(52, 152, 219, 0.3);
    color: #3498db;
    border: 1px solid rgba(52, 152, 219, 0.5);
}

.icon {
    font-size: 1.1em;
}

.navbar-dropdown {
    position: relative;
}

.navbar-dropdown-trigger {
    cursor: pointer;
}

.dropdown-arrow {
    font-size: 0.8em;
    transition: transform 0.3s ease;
}

.navbar-dropdown:hover .dropdown-arrow {
    transform: rotate(180deg);
}

.navbar-dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: rgba(52, 73, 94, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(52, 152, 219, 0.3);
    border-radius: 8px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    min-width: 180px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    margin-top: 10px;
}

.navbar-dropdown:hover .navbar-dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.navbar-dropdown-item {
    display: block;
    padding: 12px 20px;
    color: #ecf0f1;
    text-decoration: none;
    transition: all 0.3s ease;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.navbar-dropdown-item:last-child {
    border-bottom: none;
}

.navbar-dropdown-item:hover {
    background: rgba(52, 152, 219, 0.2);
    color: #3498db;
}

.navbar-burger {
    display: none;
    flex-direction: column;
    cursor: pointer;
    width: 30px;
    height: 30px;
    justify-content: space-between;
}

.navbar-burger span {
    width: 100%;
    height: 3px;
    background: #ecf0f1;
    border-radius: 2px;
    transition: all 0.3s ease;
}

@media (max-width: 768px) {
    .navbar-menu {
        position: fixed;
        top: 70px;
        left: 0;
        width: 100%;
        background: rgba(52, 73, 94, 0.98);
        backdrop-filter: blur(15px);
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }
    
    .navbar-menu.active {
        transform: translateX(0);
    }
    
    .navbar-nav {
        flex-direction: column;
        padding: 20px;
        gap: 0;
    }
    
    .navbar-item {
        width: 100%;
        border-radius: 0;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        justify-content: flex-start;
    }
    
    .navbar-burger {
        display: flex;
    }
    
    .navbar-burger.active span:nth-child(1) {
        transform: rotate(45deg) translate(8px, 8px);
    }
    
    .navbar-burger.active span:nth-child(2) {
        opacity: 0;
    }
    
    .navbar-burger.active span:nth-child(3) {
        transform: rotate(-45deg) translate(8px, -8px);
    }
    
    .navbar-dropdown-menu {
        position: static;
        opacity: 1;
        visibility: visible;
        transform: none;
        box-shadow: none;
        border: none;
        background: rgba(44, 62, 80, 0.5);
        margin: 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const burger = document.getElementById('navbarBurger');
    const menu = document.getElementById('navbarMenu');
    
    burger.addEventListener('click', function() {
        burger.classList.toggle('active');
        menu.classList.toggle('active');
    });
    
    // Cerrar menú al hacer click en un enlace (móvil)
    const navItems = document.querySelectorAll('.navbar-item');
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                burger.classList.remove('active');
                menu.classList.remove('active');
            }
        });
    });
});
</script>