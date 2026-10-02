<?php
function rateLimit($ip, $limite = 100, $tiempo = 3600) {
    global $pdo;
    $pdo->prepare("DELETE FROM RateLimit WHERE timestamp < DATE_SUB(NOW(), INTERVAL ? SECOND)")
        ->execute([$tiempo]);
    $stmt = $pdo->prepare("SELECT COUNT(*) as total 
                           FROM RateLimit 
                           WHERE ip = ? AND timestamp > DATE_SUB(NOW(), INTERVAL ? SECOND)");
    $stmt->execute([$ip, $tiempo]);
    $total = $stmt->fetch()['total'];
    
    if ($total >= $limite) {
        http_response_code(429);
        echo json_encode(['error' => 'Demasiadas peticiones. Intente más tarde.']);
        exit;
    }
    $pdo->prepare("INSERT INTO RateLimit (ip, timestamp) VALUES (?, NOW())")
        ->execute([$ip]);
}

?>