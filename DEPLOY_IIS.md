# SistemaTicket - Despliegue en IIS

## Stack detectado
- Frontend: HTML/CSS/JavaScript
- Backend: PHP
- Base de datos: MariaDB/MySQL mediante PDO (`pdo_mysql`)
- Dump incluido: `base-de-datos/ticket_system_dar.sql`

## Ajuste incluido para IIS
- `js/auth.js`: se cambió la API hardcodeada `http://localhost/ticket-system/api/` por `api/`.
- `web.config`: documento predeterminado, encabezados básicos, límite de solicitud y bloqueo HTTP de `base-de-datos`, `api/config` y `uploads`.

## Requisitos del servidor
1. IIS con CGI/FastCGI habilitado.
2. PHP x64 NTS configurado en IIS.
3. Extensiones PHP: `pdo_mysql` y `fileinfo`.
4. MariaDB o MySQL.
5. Permiso de lectura/ejecución para `IIS_IUSRS` sobre el proyecto y `Modify` sobre `uploads`.

## php.ini recomendado
```ini
extension=pdo_mysql
extension=fileinfo
upload_max_filesize=10M
post_max_size=12M
max_execution_time=60
date.timezone=America/Mexico_City
cgi.force_redirect=0
```

## Base de datos
Crear `ticket_system_dar`, importar `base-de-datos/ticket_system_dar.sql`, crear un usuario exclusivo para la app y ajustar `api/config/database.php`.

No usar en producción `root` sin contraseña.

## Prueba rápida
1. Abrir `/SistemaTicket/` y validar que muestre `login.html`.
2. Abrir `/SistemaTicket/api/categorias/listar.php`; debe regresar JSON.
3. Probar inicio de sesión.
4. Crear ticket y cargar un PDF/PNG/JPG de menos de 5 MB.
