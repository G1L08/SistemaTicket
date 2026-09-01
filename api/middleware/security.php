<?php
function sanitizarEntrada($data) {
    if (is_array($data)) {
        return array_map('sanitizarEntrada', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function validarDatos($data, $reglas) {
    foreach ($reglas as $campo => $regla) {
        if (!isset($data[$campo])) {
            return false;
        }
        
        switch ($regla) {
            case 'int':
                if (!is_numeric($data[$campo])) return false;
                break;
            case 'email':
                if (!filter_var($data[$campo], FILTER_VALIDATE_EMAIL)) return false;
                break;
            case 'string':
                if (!is_string($data[$campo])) return false;
                break;
            case 'fecha':
                if (!strtotime($data[$campo])) return false;
                break;
        }
    }
    return true;
}

function ejecutarConsultaSegura($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch(PDOException $e) {
        error_log("SQL Error: " . $e->getMessage());
        throw new Exception("Error en la consulta");
    }
}
?>