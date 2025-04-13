<?php
// Thiết lập múi giờ Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

$config = include('vnpay_config.php');

$vnp_TmnCode = $config['vnp_TmnCode'];
$vnp_HashSecret = $config['vnp_HashSecret'];
$vnp_Url = $config['vnp_Url'];
$vnp_ReturnUrl = $config['vnp_ReturnUrl'];
$vnp_SecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
// Tạo mã giao dịch duy nhất
$vnp_TxnRef = time(); // Sử dụng timestamp hiện tại

// Kiểm tra mã giao dịch trong cơ sở dữ liệu
include '../config.php'; // Include file kết nối cơ sở dữ liệu
$query = new Database(); // Khởi tạo đối tượng Database
$result = $query->select('transactions', 'id', "WHERE txn_ref = '$vnp_TxnRef'");
if (!empty($result)) {
    $vnp_TxnRef = uniqid(); // Tạo mã giao dịch mới nếu bị trùng
}
$data = array(
    'txn_ref' => $vnp_TxnRef,
    'amount' => $vnp_Amount / 100, // Chuyển về đơn vị VNĐ
    'status' => 'pending'
);
$query->insert('transactions', $data);

$vnp_OrderInfo = "Payment for order #" . $vnp_TxnRef;
$vnp_Amount = $_POST['amount'] * 100; // Số tiền (VNPay yêu cầu nhân 100)
$vnp_Locale = 'vn'; // Ngôn ngữ (vn hoặc en)
$vnp_BankCode = ''; // Mã ngân hàng (nếu có)
$vnp_IpAddr = $_SERVER['REMOTE_ADDR']; // Địa chỉ IP của khách hàng
$data = array(
    'txn_ref' => $vnp_TxnRef,
    'amount' => $vnp_Amount / 100, // Chuyển về đơn vị VNĐ
    'status' => 'pending'
);


$inputData = array(
    "vnp_Version" => "2.1.0",
    "vnp_TmnCode" => $vnp_TmnCode,
    "vnp_Amount" => $vnp_Amount,
    "vnp_Command" => "pay",
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_CurrCode" => "VND",
    "vnp_IpAddr" => $vnp_IpAddr,
    "vnp_Locale" => $vnp_Locale,
    "vnp_OrderInfo" => $vnp_OrderInfo,
    "vnp_OrderType" => "billpayment",
    "vnp_ReturnUrl" => $vnp_ReturnUrl,
    "vnp_TxnRef" => $vnp_TxnRef,
);

ksort($inputData);

// Tạo chuỗi hashdata
$hashdata = "";
foreach ($inputData as $key => $value) {
    $hashdata .= $key . "=" . $value . "&";
}
$hashdata = rtrim($hashdata, "&"); // Loại bỏ ký tự `&` cuối cùng

// Tạo chữ ký
$vnp_SecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

// Tạo URL thanh toán
$query = http_build_query($inputData);
$vnp_Url .= "?" . $query . "&vnp_SecureHash=" . $vnp_SecureHash;



// Chuyển hướng đến cổng thanh toán VNPay
header('Location:' . $vnp_Url);
exit();
?>