<?php

function provider_api_post(array $provider, array $payload): array
{
    $apiUrl = rtrim((string)($provider['api_url'] ?? ''), '/');
    $apiKey = (string)($provider['api_key'] ?? '');

    if ($apiUrl === '' || $apiKey === '') {
        return ['success' => false, 'error' => 'Proveedor sin API URL o API key configurada.'];
    }

    $payload['key'] = $apiKey;

    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($payload),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
    ]);

    $raw = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false || $curlError !== '') {
        return ['success' => false, 'error' => $curlError ?: 'Error CURL', 'http_code' => $httpCode, 'raw' => $raw];
    }

    $json = json_decode($raw, true);
    if (!is_array($json)) {
        return ['success' => false, 'error' => 'Respuesta inválida del proveedor.', 'http_code' => $httpCode, 'raw' => $raw];
    }

    if (isset($json['error'])) {
        return ['success' => false, 'error' => (string)$json['error'], 'http_code' => $httpCode, 'raw' => $raw, 'json' => $json];
    }

    return ['success' => true, 'http_code' => $httpCode, 'raw' => $raw, 'json' => $json];
}

function send_order_to_provider(Database $db, int $orderId): array
{
    $order = db_one($db, 'SELECT o.*, s.provider_id, s.provider_service_id FROM orders o LEFT JOIN services s ON s.id=o.service_id WHERE o.id=?', [$orderId]);

    if (!$order || empty($order['provider_id']) || empty($order['provider_service_id'])) {
        $db->query('UPDATE orders SET provider_response=? WHERE id=?', ['Sin proveedor configurado', $orderId]);
        return ['success' => false, 'error' => 'Pedido sin proveedor configurado.'];
    }

    $provider = db_one($db, 'SELECT * FROM providers WHERE id=? AND active=1', [(int)$order['provider_id']]);
    if (!$provider) {
        $db->query('UPDATE orders SET provider_response=? WHERE id=?', ['Proveedor inactivo o no encontrado', $orderId]);
        return ['success' => false, 'error' => 'Proveedor inactivo o no encontrado.'];
    }

    $response = provider_api_post($provider, [
        'action' => 'add',
        'service' => (int)$order['provider_service_id'],
        'link' => (string)$order['link'],
        'quantity' => (int)$order['quantity'],
    ]);

    $providerOrderId = $response['json']['order'] ?? null;
    $status = ($response['success'] && $providerOrderId) ? 'processing' : 'failed';

    $db->query('UPDATE orders SET status=?, provider_order_id=?, provider_response=? WHERE id=?', [
        $status,
        $providerOrderId,
        $response['raw'] ?? json_encode($response),
        $orderId
    ]);

    return $response;
}
