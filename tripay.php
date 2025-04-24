<?php
function createTripayTransaction($data) {
  $merchant_ref = 'INV' . time();
  $signature = hash_hmac('sha256', TRIPAY_MERCHANT_CODE . $merchant_ref . $data['harga'], TRIPAY_PRIVATE_KEY);

  $payload = [
    'method'         => $data['payment'],
    'merchant_ref'   => $merchant_ref,
    'amount'         => (int) $data['harga'],
    'customer_name'  => $data['gameId'],
    'customer_email' => $data['gameId'] . '@example.com',
    'order_items'    => [[
      'sku'      => $data['sku'],
      'name'     => $data['game'] . " - " . $data['nominal'],
      'price'    => (int) $data['harga'],
      'quantity' => 1,
    ]],
    'callback_url'   => 'https://habied03.github.io/GameLux//callback.php',
    'return_url'     => 'https://habied03.github.io/GameLux/success.html',
    'signature'      => $signature,
  ];

  $curl = curl_init();

  curl_setopt_array($curl, [
    CURLOPT_FRESH_CONNECT  => true,
    CURLOPT_URL            => "https://tripay.co.id/api-sandbox/transaction/create",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER         => false,
    CURLOPT_HTTPHEADER     => [
      "Authorization: Bearer " . TRIPAY_API_KEY
    ],
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($payload)
  ]);

  $response = curl_exec($curl);

  if ($response === false) {
    return ['success' => false, 'message' => 'CURL Error: ' . curl_error($curl)];
  }

  curl_close($curl);
  return json_decode($response, true);
}
