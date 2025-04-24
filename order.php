<?php
require 'config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

// Validasi basic data
if (
    !isset($data['game'], $data['gameId'], $data['nominal'],
    $data['payment'], $data['harga'], $data['sku'])
) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Data tidak lengkap"]);
    exit;
}

include 'tripay.php';

// Buat transaksi ke Tripay
$response = createTripayTransaction($data);

if (isset($response['success']) && $response['success']) {
    echo json_encode([
        "success" => true,
        "redirect_url" => $response['data']['checkout_url']
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => $response['message'] ?? 'Terjadi kesalahan'
    ]);
}