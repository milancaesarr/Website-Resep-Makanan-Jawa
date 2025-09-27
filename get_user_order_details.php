<?php
session_start();

// Proteksi akses: hanya untuk user yang login
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once "db.php";

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID required']);
    exit();
}

$order_id = (int)$_GET['id'];

// Get order details with user check (pastikan order milik user yang login)
$sql = "SELECT o.*, u.full_name, u.email
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ? AND o.user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($order_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit();
}

$order = mysqli_fetch_assoc($order_result);

// Get order items with product details
$sql = "SELECT oi.*, p.name, p.image_url, p.brand
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$items_result = mysqli_stmt_get_result($stmt);

$order_number = "NJE" . str_pad($order['id'], 6, "0", STR_PAD_LEFT);

// Generate HTML
$html = '
<div style="background: #f4e4d0; padding: 20px; border-radius: 15px; margin-bottom: 20px;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
        <div>
            <strong style="color: #5b2620;">Nomor Pesanan:</strong><br>
            <span style="font-size: 1.2em; font-weight: bold; color: #af2d2d;">' . $order_number . '</span>
        </div>
        <div>
            <strong style="color: #5b2620;">Tanggal Pesanan:</strong><br>
            ' . date('d F Y, H:i', strtotime($order['created_at'])) . '
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
        <div>
            <strong style="color: #5b2620;">Status Pesanan:</strong><br>
            <span class="order-status status-' . $order['status'] . '" style="display: inline-block; margin-top: 5px; padding: 6px 12px; border-radius: 15px; font-weight: bold; color: white; background: ' . getStatusColor($order['status']) . ';">' . getStatusText($order['status']) . '</span>
        </div>
        <div>
            <strong style="color: #5b2620;">Status Pembayaran:</strong><br>
            <span class="payment-status payment-' . ($order['payment_status'] ?? 'menunggu') . '" style="display: inline-block; margin-top: 5px; padding: 6px 12px; border-radius: 15px; font-weight: bold; color: white; background: ' . getPaymentStatusColor($order['payment_status'] ?? 'menunggu') . ';">' . getPaymentStatusText($order['payment_status'] ?? 'menunggu') . '</span>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div>
            <strong style="color: #5b2620;">Metode Pembayaran:</strong><br>
            ' . strtoupper($order['payment_method'] ?? 'COD') . '
        </div>
        <div>
            <strong style="color: #5b2620;">Total Pembayaran:</strong><br>
            <span style="font-size: 1.3em; font-weight: bold; color: #af2d2d;">Rp' . number_format($order['total_amount'], 0, ',', '.') . '</span>
        </div>
    </div>
</div>

<div style="background: #ffffff; padding: 20px; border-radius: 15px; margin-bottom: 20px; border: 2px solid #feba71;">
    <h3 style="color: #5b2620; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
        <i data-feather="user"></i> Informasi Pengiriman
    </h3>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div>
            <strong style="color: #5b2620;">Nama Penerima:</strong><br>
            ' . htmlspecialchars($order['customer_name'] ?? $order['full_name']) . '
        </div>
        <div>
            <strong style="color: #5b2620;">Email:</strong><br>
            ' . htmlspecialchars($order['customer_email'] ?? $order['email']) . '
        </div>
    </div>';

if (!empty($order['phone'])) {
    $html .= '
    <div style="margin-top: 15px;">
        <strong style="color: #5b2620;">Nomor Telepon:</strong><br>
        ' . htmlspecialchars($order['phone']) . '
    </div>';
}

if (!empty($order['shipping_address'])) {
    $html .= '
    <div style="margin-top: 15px;">
        <strong style="color: #5b2620;">Alamat Pengiriman:</strong><br>
        ' . nl2br(htmlspecialchars($order['shipping_address'])) . '
    </div>';
}

if (!empty($order['notes'])) {
    $html .= '
    <div style="margin-top: 15px;">
        <strong style="color: #5b2620;">Catatan:</strong><br>
        ' . nl2br(htmlspecialchars($order['notes'])) . '
    </div>';
}

$html .= '</div>';

// Order items
$html .= '
<div style="background: #ffffff; padding: 20px; border-radius: 15px; border: 2px solid #feba71;">
    <h3 style="color: #5b2620; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i data-feather="shopping-cart"></i> Produk yang Dipesan
    </h3>';

$subtotal = 0;
while ($item = mysqli_fetch_assoc($items_result)) {
    $item_total = $item['price'] * $item['quantity'];
    $subtotal += $item_total;

    $html .= '
    <div style="display: flex; align-items: center; gap: 15px; padding: 15px 0; border-bottom: 1px solid #f4e4d0;">
        <img src="' . ($item['image_url'] ?: 'https://via.placeholder.com/60x60/f4e4d0/5b2620?text=No+Image') . '"
             alt="' . htmlspecialchars($item['name']) . '"
             style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
        <div style="flex: 1;">
            <div style="font-weight: bold; color: #5b2620; margin-bottom: 5px;">
                ' . htmlspecialchars($item['name']) . '
            </div>
            <div style="color: #816040; font-size: 0.9em;">
                Brand: ' . htmlspecialchars($item['brand'] ?: 'Unknown') . '
            </div>
            <div style="color: #af2d2d; font-weight: bold;">
                ' . $item['quantity'] . ' x Rp' . number_format($item['price'], 0, ',', '.') . '
            </div>
        </div>
        <div style="font-weight: bold; color: #5b2620; font-size: 1.1em;">
            Rp' . number_format($item_total, 0, ',', '.') . '
        </div>
    </div>';
}

$shipping = 0;
$total = $subtotal + $shipping;

$html .= '
    <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #feba71;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
            <span>Subtotal:</span>
            <span style="font-weight: bold;">Rp' . number_format($subtotal, 0, ',', '.') . '</span>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 1.2em; font-weight: bold; color: #af2d2d; border-top: 2px solid #5b2620; padding-top: 15px;">
            <span>Total:</span>
            <span>Rp' . number_format($total, 0, ',', '.') . '</span>
        </div>
    </div>
</div>';

// Status tracking
$html .= '
<div style="background: #ffffff; padding: 20px; border-radius: 15px; border: 2px solid #feba71; margin-top: 20px;">
    <h3 style="color: #5b2620; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i data-feather="truck"></i> Status Pengiriman
    </h3>
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div style="text-align: center; flex: 1;">
            <div style="width: 40px; height: 40px; border-radius: 50%; margin: 0 auto 10px; background: ' . ($order['status'] == 'pending' || $order['status'] == 'confirmed' || $order['status'] == 'shipped' || $order['status'] == 'delivered' ? '#27ae60' : '#ddd') . '; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">1</div>
            <small style="color: #5b2620;">Pesanan Dikonfirmasi</small>
        </div>
        <div style="flex: 1; height: 2px; background: ' . ($order['status'] == 'confirmed' || $order['status'] == 'shipped' || $order['status'] == 'delivered' ? '#27ae60' : '#ddd') . ';"></div>
        <div style="text-align: center; flex: 1;">
            <div style="width: 40px; height: 40px; border-radius: 50%; margin: 0 auto 10px; background: ' . ($order['status'] == 'shipped' || $order['status'] == 'delivered' ? '#27ae60' : '#ddd') . '; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">2</div>
            <small style="color: #5b2620;">Sedang Dikirim</small>
        </div>
        <div style="flex: 1; height: 2px; background: ' . ($order['status'] == 'delivered' ? '#27ae60' : '#ddd') . ';"></div>
        <div style="text-align: center; flex: 1;">
            <div style="width: 40px; height: 40px; border-radius: 50%; margin: 0 auto 10px; background: ' . ($order['status'] == 'delivered' ? '#27ae60' : '#ddd') . '; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">3</div>
            <small style="color: #5b2620;">Pesanan Diterima</small>
        </div>
    </div>
</div>';

echo json_encode(['success' => true, 'html' => $html]);

function getStatusColor($status) {
    switch($status) {
        case 'pending': return '#f39c12';
        case 'confirmed': return '#3498db';
        case 'shipped': return '#a7774e';
        case 'delivered': return '#27ae60';
        case 'cancelled': return '#e74c3c';
        default: return '#f39c12';
    }
}

function getStatusText($status) {
    switch($status) {
        case 'pending': return 'Menunggu Konfirmasi';
        case 'confirmed': return 'Dikonfirmasi';
        case 'shipped': return 'Sedang Dikirim';
        case 'delivered': return 'Terkirim';
        case 'cancelled': return 'Dibatalkan';
        default: return 'Menunggu';
    }
}

function getPaymentStatusColor($status) {
    switch($status) {
        case 'menunggu': return '#ffeaa7';
        case 'lunas': return '#00b894';
        case 'gagal': return '#e74c3c';
        case 'dikembalikan': return '#6c5ce7';
        default: return '#ffeaa7';
    }
}

function getPaymentStatusText($status) {
    switch($status) {
        case 'menunggu': return 'Menunggu Pembayaran';
        case 'lunas': return 'Lunas';
        case 'gagal': return 'Gagal';
        case 'dikembalikan': return 'Dikembalikan';
        default: return 'Menunggu';
    }
}
?>
