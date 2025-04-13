<?php
$config = include('vnpay_config.php');

$vnp_HashSecret = $config['vnp_HashSecret'];

$inputData = array();
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

$vnp_SecureHash = $_GET['vnp_SecureHash'];
unset($inputData['vnp_SecureHash']);
ksort($inputData);
$hashData = "";
foreach ($inputData as $key => $value) {
    $hashData .= $key . "=" . $value . "&";
}
$hashData = rtrim($hashData, "&");

$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

if ($secureHash === $vnp_SecureHash) {
    if ($_GET['vnp_ResponseCode'] == '00') {
        echo "Payment successful!";
    } else {
        echo "Payment failed: " . $_GET['vnp_ResponseCode'];
    }
} else {
    echo "Invalid signature!";
}
?>