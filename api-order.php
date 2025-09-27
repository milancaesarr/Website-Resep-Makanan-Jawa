<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$conn = new mysqli('localhost','root','','nusajava_eats');
if($conn->connect_error) die(json_encode(['success'=>false, 'error'=>'DB gagal']));

$post = json_decode(file_get_contents('php://input'), true);

// Validasi
$fields = ['firstName','lastName','email','phone','alamat','cart'];
foreach($fields as $f) if(empty($post[$f])) die(json_encode(['success'=>false,'error'=>'Data tidak lengkap']));
$cart = $post['cart'];
if(!is_array($cart) || !count($cart)) die(json_encode(['success'=>false,'error'=>'Cart kosong']));

// Generate order number
$orderNumber = 'NJ'.date('ymdHis').rand(10,99);
$total = 0; foreach($cart as $it) $total += intval($it['harga'])*intval($it['qty']);

// Simpan order -- pakai kolom baru
$stmt = $conn->prepare("INSERT INTO orders(order_number,first_name,last_name,email,phone,metode,alamat,catatan,total) VALUES (?,?,?,?,?,?,?,?,?)");
$stmt->bind_param(
    'ssssssssi',
    $orderNumber,
    $post['firstName'],
    $post['lastName'],
    $post['email'],
    $post['phone'],
    $post['metode'],
    $post['alamat'],
    $post['catatan'],
    $total
);
$stmt->execute();
$orderId = $stmt->insert_id;
$stmt->close();

// Simpan produk
foreach($cart as $prod) {
    $stmt = $conn->prepare("INSERT INTO order_items(order_id,product_id,product_name,price,qty) VALUES (?,?,?,?,?)");
    $stmt->bind_param('issii', $orderId, $prod['id'], $prod['nama'], $prod['harga'], $prod['qty']);
    $stmt->execute();
    $stmt->close();
}

echo json_encode([
    'success' => true,
    'orderNumber' => $orderNumber,
    'amount' => $total,
    'products' => $cart,
    'instructions' => 'Transfer ke BCA 464-864-8888 a/n PT. Benteng Persada Multindo',
    'date' => date('Y-m-d H:i:s')
]);
$conn->close();
?>
