<?php
// Atur header response
header('Content-Type: application/json');

// Ambil data dari body JSON
$data = json_decode(file_get_contents("php://input"), true);

// Validasi input
if (
  empty($data['game']) || empty($data['gameId']) || empty($data['nominal']) ||
  empty($data['payment']) || empty($data['harga']) || empty($data['sku'])
) {
  echo json_encode(["success" => false, "message" => "Data tidak lengkap"]);
  exit;
}

// === Konfigurasi ===
$tripayApiKey = "DEV-DdUT1UqK57IPn6HZWrArzgC07TUWcKAsK9wWrT3i";
$tripayPrivateKey = "OPN4E-yjwqJ-ZJT2Y-exYac-6rfZf";
$tripayMerchantCode = "T39413";

$digiflazzUsername = "Habied";
$digiflazzApiKey = "uZHIt1yNOaZRZpUdc2YogTuICRFTL2VK7MlAnVanqFiuRHDznn7Ucn4PoDlRoz82";

// Generate ref id unik
$ref_id = uniqid();

// === Step 1: Panggil Digiflazz untuk topup ===
$sign = md5($digiflazzUsername . $digiflazzApiKey . $ref_id);
$payload_digiflazz = [
  "username" => $digiflazzUsername,
  "buyer_sku_code" => $data['sku'],
  "customer_no" => $data['gameId'],
  "ref_id" => $ref_id,
  "sign" => $sign
];

$ch = curl_init("https://api.digiflazz.com/v1/transaction");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => json_encode(["cmd" => "prepaid", "data" => $payload_digiflazz]),
  CURLOPT_HTTPHEADER => ['Content-Type: application/json']
]);

$digiflazzResult = curl_exec($ch);
curl_close($ch);
$digiflazzResponse = json_decode($digiflazzResult, true);

if (empty($digiflazzResponse['data']['status']) || $digiflazzResponse['data']['status'] !== 'Pending') {
  echo json_encode(["success" => false, "message" => "Top up gagal: " . $digiflazzResponse['data']['message']]);
  exit;
}

// === Step 2: Buat pembayaran di Tripay ===
$signature = hash_hmac('sha256', $tripayMerchantCode . $ref_id . $data['harga'], $tripayPrivateKey);
$tripayPayload = [
  'method' => $data['payment'],
  'merchant_ref' => $ref_id,
  'amount' => (int) $data['harga'],
  'customer_name' => $data['gameId'],
  'order_items' => [[
    'sku' => $data['sku'],
    'name' => $data['nominal'] . " - " . strtoupper($data['game']),
    'price' => (int) $data['harga'],
    'quantity' => 1
  ]],
  'signature' => $signature
];

$ch = curl_init("https://tripay.co.id/api-sandbox/transaction/create");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => http_build_query($tripayPayload),
  CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $tripayApiKey]
]);

$tripayResult = curl_exec($ch);
curl_close($ch);
$tripayResponse = json_decode($tripayResult, true);

if (isset($tripayResponse['success']) && $tripayResponse['success'] && isset($tripayResponse['data']['checkout_url'])) {
  echo json_encode([
    "success" => true,
    "redirect_url" => $tripayResponse['data']['checkout_url']
  ]);
} else {
  echo json_encode([
    "success" => false,
    "message" => "Gagal membuat pembayaran Tripay"
  ]);
}
ini_set('display_errors' , 1);
ini_set('display_startup_errors' , 1);
error_reporting(E_ALL);
?>


