<?php
require 'config.php';

$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Verifikasi signature dari Tripay
$callbackSignature = $_SERVER['HTTP_X_CALLBACK_SIGNATURE'] ?? '';
$computedSignature = hash_hmac('sha256', $json, TRIPAY_PRIVATE_KEY);

if ($callbackSignature !== $computedSignature) {
    http_response_code(403);
    exit('Invalid Signature');
}

// Ambil data penting
$merchant_ref = $data['merchant_ref'] ?? '';
$status = $data['status'] ?? '';

if ($status === 'PAID') {
  include 'digiflazz.php';

  $sku = $data['order_items'][0]['sku'] ?? '';
  $id = explode('-', $merchant_ref)[1] ?? '';

  if ($sku && $id) {
    topupGame($sku, $id);
  }
}

// Respons ke Tripay
http_response_code(200);
echo json_encode(['success' => true]);