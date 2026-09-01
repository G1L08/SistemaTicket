<?php
function validarArchivo($archivo) {
    $tipos_permitidos = [
        'image/jpeg', 'image/png', 'image/gif',
        'application/pdf', 'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    // Extensiones permitidas
    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
    $tamano_maximo = 5 * 1024 * 1024;
    
    // Verificar tipo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $tipo = finfo_file($finfo, $archivo['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($tipo, $tipos_permitidos)) {
        return ['error' => 'Tipo de archivo no permitido'];
    }
    
    // Verificar extensión
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $extensiones_permitidas)) {
        return ['error' => 'Extensión no permitida'];
    }
    
    // Verificar tamaño
    if ($archivo['size'] > $tamano_maximo) {
        return ['error' => 'El archivo excede el tamaño máximo (5MB)'];
    }
    
    // Generar nombre seguro
    $nombre_seguro = uniqid() . '.' . $extension;
    $ruta = '../uploads/' . $nombre_seguro;
    
    return [
        'success' => true,
        'nombre' => $nombre_seguro,
        'ruta' => $ruta,
        'tipo' => $tipo,
        'tamano' => $archivo['size']
    ];
}

// Ejemplo de uso
if ($_FILES['archivo']) {
    $validacion = validarArchivo($_FILES['archivo']);
    
    if ($validacion['success']) {
        move_uploaded_file($_FILES['archivo']['tmp_name'], $validacion['ruta']);
        // Guardar en base de datos
    } else {
        echo json_encode($validacion);
    }
}
?>