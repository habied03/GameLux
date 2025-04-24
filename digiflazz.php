<?php
function topupGame($sku, $id) {
  $ref_id = 'DFZ' . time();
  $signature = md5(DIGIFLAZZ_USERNAME . $ref_id . DIGIFLAZZ_API_KEY);

  $payload = [
    "username" => DIGIFLAZZ_USERNAME,
    "buyer_sku_code" => $sku,
    "customer_no" => $id,
    "ref_id" => $ref_id,
    "sign" => $signature
  ];

  $curl = curl_init();

  curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.digiflazz.com/v1/transaction",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json']
  ]);

  $response = curl_exec($curl);
  curl_close($curl);

  return json_decode($response, true);
}