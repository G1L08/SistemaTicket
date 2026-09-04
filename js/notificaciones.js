function actualizarContadorNotificaciones() {
    fetch('api/notificaciones/contador.php')
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('notificacionContador');
            if (data.total > 0) {
                badge.style.display = 'inline';
                badge.textContent = data.total;
            } else {
                badge.style.display = 'none';
            }
        })
        .catch(e => console.error('Error al obtener contador:', e));
}

function cargarNotificaciones() {
    const lista = document.getElementById('listaNotificaciones');
    lista.innerHTML = '<div class="text-center text-muted py-3">Cargando...</div>';
    fetch('api/notificaciones/obtener.php')
        .then(r => {
            if (!r.ok) {
                throw new Error('Error HTTP: ' + r.status);
            }
            return r.json();
        })
        .then(data => {
            if (data.error) {
                lista.innerHTML = '<div class="text-danger text-center py-3">' + data.error + '</div>';
                return;
            }
            if (data.length === 0) {
                lista.innerHTML = '<div class="text-center text-muted py-3">No hay notificaciones</div>';
                return;
            }
            let html = '';
            data.forEach(not => {
                const fecha = new Date(not.Fecha_creacion).toLocaleString('es-MX');
                const clase = not.Leida ? 'text-muted' : 'fw-bold';
                const link = not.Id_ticket ? `<a href="detalle-ticket.html?id=${not.Id_ticket}" class="text-decoration-none text-reset">` : '';
                const cierre = not.Id_ticket ? `</a>` : '';
                html += `
                    <div class="dropdown-item border-bottom p-2 ${clase}" data-id="${not.Id_notificacion}" onclick="marcarLeida(${not.Id_notificacion})">
                        ${link}
                        <div>${not.Mensaje}</div>
                        <small class="text-muted">${fecha}</small>
                        ${cierre}
                    </div>
                `;
            });
            lista.innerHTML = html;
        })
        .catch(e => {
            console.error('Error al cargar notificaciones:', e);
            lista.innerHTML = '<div class="alert alert-danger">Error al cargar notificaciones</div>';
        });
}

function toggleNotificaciones() {
    const dropdown = document.getElementById('notificacionesDropdown');
    if (dropdown.style.display === 'block') {
        dropdown.style.display = 'none';
    } else {
        dropdown.style.display = 'block';
        cargarNotificaciones();
    }
}

function marcarLeida(id) {
    fetch('api/notificaciones/marcar_leida.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_notificacion: id })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            actualizarContadorNotificaciones();
            cargarNotificaciones();
        }
    })
    .catch(e => console.error(e));
}

function marcarTodasLeidas() {
    fetch('api/notificaciones/marcar_todas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            actualizarContadorNotificaciones();
            cargarNotificaciones();
        }
    })
    .catch(e => console.error(e));
}

document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('notificacionesDropdown');
    const button = document.querySelector('[onclick="toggleNotificaciones()"]');
    if (dropdown && button && !dropdown.contains(event.target) && !button.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});

setInterval(actualizarContadorNotificaciones, 30000);
document.addEventListener('DOMContentLoaded', actualizarContadorNotificaciones);