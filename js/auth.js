const API_URL = 'http://localhost/ticket-system/api/';

document.addEventListener('DOMContentLoaded', function() {
    if (sessionStorage.getItem('usuario')) {
        window.location.href = 'dashboard.html';
    }
    
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const noEmpleado = document.getElementById('noEmpleado').value;
        const password = document.getElementById('password').value;
        
        const btn = document.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Cargando...';
        btn.disabled = true;
        
        try {
            const response = await fetch(API_URL + 'auth/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ no_empleado: noEmpleado, contraseña: password })
            });
            
            const data = await response.json();
            
            if (data.error === 'usuario_inactivo') {
                mostrarError('Su cuenta esta inactiva. Por favor, comuniquese con el departamento de TI.');
                btn.innerHTML = originalText;
                btn.disabled = false;
                return;
            }
            
            if (data.success) {
                sessionStorage.setItem('usuario', JSON.stringify(data.usuario));
                window.location.href = 'dashboard.html';
            } else {
                mostrarError(data.error || 'Credenciales invalidas');
            }
        } catch (error) {
            mostrarError('Error de conexion al servidor');
            console.error(error);
        }
        
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});

function mostrarError(mensaje) {
    const errorDiv = document.getElementById('loginError');
    errorDiv.textContent = mensaje;
    errorDiv.classList.remove('d-none');
    setTimeout(() => errorDiv.classList.add('d-none'), 5000);
}