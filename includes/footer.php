<?php
// includes/footer.php
// Componente de footer para todas las páginas
?>

<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-content">
            <div class="footer-info">
                <p class="footer-text">
                    Sitio web creado por 
                    <strong>Juan Gabriel Soto Valenzuela</strong>
                </p>
                <div class="footer-social">
                    <a href="https://www.linkedin.com/in/juan-gabriel-soto-valenzuela/" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="linkedin-link"
                       title="Perfil de LinkedIn de Juan Gabriel Soto Valenzuela">
                        <svg class="linkedin-icon" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                        LinkedIn
                    </a>
                </div>
            </div>
            
            <div class="footer-divider"></div>
            
            <div class="footer-bottom">
                <p class="footer-copyright">
                    © <?php echo date('Y'); ?> Sistema de Reseñas. 
                    Desarrollado con 💙 usando PHP y MySQL.
                </p>
                <div class="footer-tech">
                    <span class="tech-badge">PHP</span>
                    <span class="tech-badge">MySQL</span>
                    <span class="tech-badge">JavaScript</span>
                    <span class="tech-badge">CSS3</span>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
.site-footer {
    background: linear-gradient(135deg, #1a252f 0%, #2c3e50 100%);
    color: #ecf0f1;
    padding: 40px 20px 20px;
    margin-top: 60px;
    border-top: 3px solid #3498db;
    position: relative;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
}

.site-footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, #3498db, transparent);
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
}

.footer-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.footer-info {
    margin-bottom: 30px;
}

.footer-text {
    font-size: 18px;
    margin-bottom: 20px;
    color: #bdc3c7;
    font-weight: 400;
}

.footer-text strong {
    color: #3498db;
    font-weight: 600;
    font-size: 19px;
}

.footer-social {
    display: flex;
    justify-content: center;
    gap: 15px;
}

.linkedin-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: linear-gradient(45deg, #0077b5, #005885);
    color: white;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    font-size: 16px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 119, 181, 0.3);
}

.linkedin-link:hover {
    background: linear-gradient(45deg, #005885, #004066);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 119, 181, 0.4);
}

.linkedin-icon {
    width: 20px;
    height: 20px;
    transition: transform 0.3s ease;
}

.linkedin-link:hover .linkedin-icon {
    transform: scale(1.1);
}

.footer-divider {
    width: 100%;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(52, 152, 219, 0.3), transparent);
    margin: 20px 0;
}

.footer-bottom {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
}

.footer-copyright {
    color: #95a5a6;
    font-size: 14px;
    margin: 0;
}

.footer-tech {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: center;
}

.tech-badge {
    background: rgba(52, 152, 219, 0.2);
    color: #3498db;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    border: 1px solid rgba(52, 152, 219, 0.3);
    transition: all 0.3s ease;
}

.tech-badge:hover {
    background: rgba(52, 152, 219, 0.3);
    transform: translateY(-1px);
}

/* Responsive */
@media (max-width: 768px) {
    .site-footer {
        padding: 30px 15px 15px;
        margin-top: 40px;
    }
    
    .footer-text {
        font-size: 16px;
    }
    
    .footer-text strong {
        font-size: 17px;
    }
    
    .linkedin-link {
        padding: 10px 20px;
        font-size: 15px;
    }
    
    .footer-bottom {
        gap: 12px;
    }
    
    .footer-copyright {
        font-size: 13px;
        text-align: center;
    }
    
    .tech-badge {
        font-size: 11px;
        padding: 3px 10px;
    }
}

@media (max-width: 480px) {
    .footer-text {
        font-size: 15px;
    }
    
    .linkedin-link {
        padding: 8px 16px;
        font-size: 14px;
    }
    
    .linkedin-icon {
        width: 18px;
        height: 18px;
    }
}

/* Animación de entrada */
.site-footer {
    opacity: 0;
    transform: translateY(30px);
    animation: fadeInUp 0.6s ease 0.3s forwards;
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
