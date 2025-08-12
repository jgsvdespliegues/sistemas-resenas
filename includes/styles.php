<?php
// includes/styles.php
// Estilos CSS comunes para todas las páginas
?>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    min-height: 100vh;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
    min-height: 100vh;
    color: #ecf0f1;
    line-height: 1.6;
    display: flex;
    flex-direction: column;
}

/* Hacer que el contenido principal ocupe el espacio disponible */
.container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    flex: 1; /* Esto hace que el contenido principal use todo el espacio disponible */
}

.page-title {
    text-align: center;
    margin-bottom: 30px;
    color: #ecf0f1;
    font-size: 2.5em;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.form-container {
    background: rgba(52, 73, 94, 0.9);
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    margin-bottom: 40px;
    backdrop-filter: blur(10px);
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #bdc3c7;
}

input[type="text"],
input[type="email"],
input[type="password"],
input[type="file"],
input[type="number"],
select,
textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #5a6c7d;
    border-radius: 8px;
    background: rgba(44, 62, 80, 0.8);
    color: #ecf0f1;
    font-size: 16px;
    transition: all 0.3s ease;
}

input:focus,
select:focus,
textarea:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 10px rgba(52, 152, 219, 0.3);
}

textarea {
    resize: vertical;
    min-height: 120px;
}

.required {
    color: #e74c3c;
}

.btn {
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-primary {
    background: linear-gradient(45deg, #3498db, #2980b9);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(45deg, #2980b9, #21618c);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
}

.btn-success {
    background: linear-gradient(45deg, #27ae60, #229954);
    color: white;
}

.btn-success:hover {
    background: linear-gradient(45deg, #229954, #1e8449);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
}

.btn-danger {
    background: linear-gradient(45deg, #e74c3c, #c0392b);
    color: white;
}

.btn-danger:hover {
    background: linear-gradient(45deg, #c0392b, #a93226);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
}

.btn-warning {
    background: linear-gradient(45deg, #f39c12, #e67e22);
    color: white;
}

.btn-warning:hover {
    background: linear-gradient(45deg, #e67e22, #d35400);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(243, 156, 18, 0.3);
}

.btn-full {
    width: 100%;
}

.btn-small {
    padding: 8px 16px;
    font-size: 14px;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: 500;
}

.alert-error {
    background: rgba(231, 76, 60, 0.9);
    color: white;
    border-left: 4px solid #c0392b;
}

.alert-success {
    background: rgba(39, 174, 96, 0.9);
    color: white;
    border-left: 4px solid #27ae60;
}

.alert-info {
    background: rgba(52, 152, 219, 0.9);
    color: white;
    border-left: 4px solid #3498db;
}

.alert-warning {
    background: rgba(243, 156, 18, 0.9);
    color: white;
    border-left: 4px solid #f39c12;
}

.publicacion {
    background: rgba(52, 73, 94, 0.9);
    padding: 25px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    backdrop-filter: blur(10px);
}

.publicacion h3 {
    color: #3498db;
    margin-bottom: 15px;
    font-size: 1.5em;
}

.publicacion-meta {
    color: #95a5a6;
    font-size: 0.9em;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.publicacion-autor {
    font-weight: 600;
    color: #bdc3c7;
}

.imagenes-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin: 20px 0;
}

.imagen-item img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 8px;
    transition: transform 0.3s ease;
    cursor: pointer;
}

.imagen-item img:hover {
    transform: scale(1.05);
}

.video-container {
    margin: 20px 0;
}

.video-container iframe {
    width: 100%;
    height: 315px;
    border-radius: 8px;
}

.mapa-container {
    margin: 20px 0;
}

.mapa-container iframe {
    width: 100%;
    height: 300px;
    border-radius: 8px;
}

.comentario {
    background: rgba(44, 62, 80, 0.7);
    padding: 15px;
    border-radius: 8px;
    margin-top: 15px;
    border-left: 4px solid #3498db;
}

.separador {
    height: 2px;
    background: linear-gradient(90deg, transparent, #3498db, transparent);
    margin: 30px 0;
}

.stars-rating {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    margin: 10px 0;
}

.star {
    font-size: 1.5em;
    color: #95a5a6;
    transition: color 0.3s ease;
}

.star.filled {
    color: #f1c40f;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
}

.star.interactive {
    cursor: pointer;
}

.star.interactive:hover {
    color: #f39c12;
    transform: scale(1.1);
}

.actions-container {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    flex-wrap: wrap;
}

.text-center {
    text-align: center;
}

.text-muted {
    color: #95a5a6;
}

.mt-1 { margin-top: 0.5rem; }
.mt-2 { margin-top: 1rem; }
.mt-3 { margin-top: 1.5rem; }
.mb-1 { margin-bottom: 0.5rem; }
.mb-2 { margin-bottom: 1rem; }
.mb-3 { margin-bottom: 1.5rem; }

.d-flex {
    display: flex;
}

.align-items-center {
    align-items: center;
}

.justify-content-between {
    justify-content: space-between;
}

.gap-2 {
    gap: 1rem;
}

.card {
    background: rgba(52, 73, 94, 0.9);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
    backdrop-filter: blur(10px);
    margin-bottom: 20px;
}

.card-header {
    border-bottom: 2px solid rgba(52, 152, 219, 0.3);
    padding-bottom: 15px;
    margin-bottom: 20px;
}

.card-title {
    color: #3498db;
    font-size: 1.3em;
    font-weight: 600;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #95a5a6;
}

.empty-state .icon {
    font-size: 4em;
    margin-bottom: 20px;
    opacity: 0.5;
}

.loading {
    text-align: center;
    padding: 40px;
    color: #95a5a6;
}

.loading .spinner {
    display: inline-block;
    width: 40px;
    height: 40px;
    border: 4px solid rgba(52, 152, 219, 0.3);
    border-radius: 50%;
    border-top-color: #3498db;
    animation: spin 1s ease-in-out infinite;
    margin-bottom: 20px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.modal {
    display: none;
    position: fixed;
    z-index: 2000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.8);
    backdrop-filter: blur(5px);
}

.modal-content {
    background: rgba(52, 73, 94, 0.95);
    margin: 5% auto;
    padding: 30px;
    border-radius: 15px;
    width: 90%;
    max-width: 600px;
    position: relative;
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from { transform: translateY(-50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.modal-close {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 24px;
    cursor: pointer;
    color: #95a5a6;
}

.modal-close:hover {
    color: #ecf0f1;
}

/* Estilos para elementos de formulario específicos */
.rating-input {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 15px 0;
}

.rating-stars {
    display: flex;
    gap: 5px;
}

.rating-stars .star {
    font-size: 2em;
    cursor: pointer;
    transition: all 0.2s ease;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        padding: 10px;
    }
    
    .page-title {
        font-size: 2em;
    }
    
    .form-container {
        padding: 20px;
    }
    
    .imagenes-container {
        grid-template-columns: 1fr;
    }
    
    .actions-container {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 10px;
    }
    
    .publicacion-meta {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}

/* Estilos específicos para el footer */
body {
    /* Asegurar que el footer siempre esté al bottom */
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

main, .container {
    flex: 1;
}

/* Ajustar el último separador antes del footer */
.separador:last-of-type {
    margin-bottom: 20px;
}
</style>