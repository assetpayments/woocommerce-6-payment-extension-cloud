<?php
// Set appropriate headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Content-Type: text/html; charset=utf-8');

// Read JSON input and sanitize input data
$jsonData = file_get_contents('php://input');
$json = json_decode($jsonData, true);
$url = 'https://tabooclothes.com.ua/?wc-api=WC_Gateway_Assetpayments';

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HEADER, true);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
$result = curl_exec($curl);
$http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

echo 'Callback Resend Status: ' . $http;

  // if ($http != 200){
  //   $timestamp = date('d/m/Y H:i');
  //   $dataWithTimestamp = 'data: ' . $timestamp . '/  Transaction: ' . $json['Payment']['TransactionId'] . '/  OrderN: ' . $order_number . '/  Http: ' . $http . '/  Response: ' . $result . "\n";
  //   $file = '__json.json';
  //   file_put_contents($file, $dataWithTimestamp . PHP_EOL, FILE_APPEND);
  // }



?>
