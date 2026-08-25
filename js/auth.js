const API_URL = 'http://localhost/ticket-system/api/';

document.addEventListener('DOMContentLoaded', function() {
    if (sessionStorage.getItem('usuario')) {
        window.location.href = 'dashboard.html';
    }
    
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const noEmpleado = document.getElementById('noEmpleado').value;
        const password = document.getElementById('password').value;
        
        try {
            const response = await fetch(API_URL + 'auth/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ no_empleado: noEmpleado, contraseña: password })
            });
            
            const data = await response.json();
            
            if (data.success) {
                sessionStorage.setItem('usuario', JSON.stringify(data.usuario));
                window.location.href = 'dashboard.html';
            } else {
                mostrarError(data.error || 'Credenciales inválidas');
            }
        } catch (error) {
            mostrarError('Error de conexión al servidor');
            console.error(error);
        }
    });
});

function mostrarError(mensaje) {
    const errorDiv = document.getElementById('loginError');
    errorDiv.textContent = mensaje;
    errorDiv.classList.remove('d-none');
    setTimeout(() => errorDiv.classList.add('d-none'), 5000);
}