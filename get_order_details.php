<?php
session_start();

// Proteksi akses: hanya untuk admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
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

// Get order details with user info
$sql = "SELECT o.*, u.full_name, u.email
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $order_id);
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

// Generate HTML with enhanced styling
$html = '
<div style="background: linear-gradient(45deg, #f4e4d0, #feba71); padding: 25px; border-radius: 20px; margin-bottom: 25px; border: 3px solid #816040;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #5b2620; margin: 0; font-size: 2em; text-shadow: 1px 1px 3px rgba(0,0,0,0.1);">
            📋 Informasi Pesanan
        </h2>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 20px;">
        <div style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
            <strong style="color: #5b2620; font-size: 1.1em;">📊 Nomor Pesanan:</strong><br>
            <span style="font-size: 1.4em; font-weight: bold; color: #af2d2d; margin-top: 5px; display: block;">' . $order_number . '</span>
        </div>
        <div style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
            <strong style="color: #5b2620; font-size: 1.1em;">📅 Tanggal Pesanan:</strong><br>
            <span style="font-size: 1.2em; font-weight: 600; color: #816040; margin-top: 5px; display: block;">' . date('d F Y, H:i', strtotime($order['created_at'])) . '</span>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 20px;">
        <div style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
            <strong style="color: #5b2620; font-size: 1.1em;">🔄 Status Pesanan:</strong><br>
            <span class="order-status status-' . $order['status'] . '" style="display: inline-block; margin-top: 8px; padding: 10px 18px; border-radius: 25px; font-weight: bold; font-size: 1em;">' . ucfirst($order['status']) . '</span>
        </div>
        <div style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
            <strong style="color: #5b2620; font-size: 1.1em;">💳 Status Pembayaran:</strong><br>
            <span class="payment-status payment-' . ($order['payment_status'] ?? 'menunggu') . '" style="display: inline-block; margin-top: 8px; padding: 8px 16px; border-radius: 20px; font-weight: bold; font-size: 1em;">' . ucfirst($order['payment_status'] ?? 'Menunggu') . '</span>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
        <div style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
            <strong style="color: #5b2620; font-size: 1.1em;">💰 Metode Pembayaran:</strong><br>
            <span style="font-size: 1.2em; font-weight: 600; color: #816040; margin-top: 5px; display: block;">' . strtoupper($order['payment_method'] ?? 'COD') . '</span>
        </div>
        <div style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
            <strong style="color: #5b2620; font-size: 1.1em;">💵 Total Pembayaran:</strong><br>
            <span style="font-size: 1.6em; font-weight: bold; color: #af2d2d; margin-top: 5px; display: block; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);">Rp' . number_format($order['total_amount'], 0, ',', '.') . '</span>
        </div>
    </div>
</div>

<div style="background: white; padding: 25px; border-radius: 20px; margin-bottom: 25px; border: 3px solid #feba71; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
    <h3 style="color: #5b2620; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; font-size: 1.6em; border-bottom: 2px solid #f4e4d0; padding-bottom: 15px;">
        <i data-feather="user"></i> 👤 Informasi Customer
    </h3>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
        <div style="background: #f8f9fa; padding: 15px; border-radius: 12px; border-left: 4px solid #816040;">
            <strong style="color: #5b2620;">📝 Nama Lengkap:</strong><br>
            <span style="font-size: 1.1em; color: #333; font-weight: 500;">' . htmlspecialchars($order['customer_name'] ?? $order['full_name']) . '</span>
        </div>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 12px; border-left: 4px solid #816040;">
            <strong style="color: #5b2620;">📧 Email:</strong><br>
            <span style="font-size: 1.1em; color: #333; font-weight: 500;">' . htmlspecialchars($order['customer_email'] ?? $order['email']) . '</span>
        </div>
    </div>';

if (!empty($order['phone'])) {
    $html .= '
    <div style="background: #f8f9fa; padding: 15px; border-radius: 12px; border-left: 4px solid #816040; margin-bottom: 15px;">
        <strong style="color: #5b2620;">📱 Nomor Telepon:</strong><br>
        <span style="font-size: 1.1em; color: #333; font-weight: 500;">' . htmlspecialchars($order['phone']) . '</span>
    </div>';
}

if (!empty($order['shipping_address'])) {
    $html .= '
    <div style="background: #f8f9fa; padding: 15px; border-radius: 12px; border-left: 4px solid #816040; margin-bottom: 15px;">
        <strong style="color: #5b2620;">🏠 Alamat Pengiriman:</strong><br>
        <span style="font-size: 1.1em; color: #333; font-weight: 500; line-height: 1.5;">' . nl2br(htmlspecialchars($order['shipping_address'])) . '</span>
    </div>';
}

if (!empty($order['notes'])) {
    $html .= '
    <div style="background: #fff3cd; padding: 15px; border-radius: 12px; border-left: 4px solid #f39c12;">
        <strong style="color: #5b2620;">📝 Catatan Khusus:</strong><br>
        <span style="font-size: 1.1em; color: #856404; font-weight: 500; line-height: 1.5;">' . nl2br(htmlspecialchars($order['notes'])) . '</span>
    </div>';
}

$html .= '</div>';

// Order items with enhanced styling
$html .= '
<div style="background: white; padding: 25px; border-radius: 20px; border: 3px solid #feba71; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
    <h3 style="color: #5b2620; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; font-size: 1.6em; border-bottom: 2px solid #f4e4d0; padding-bottom: 15px;">
        <i data-feather="shopping-cart"></i> 🛒 Produk yang Dipesan
    </h3>';

$subtotal = 0;
$item_count = 0;
while ($item = mysqli_fetch_assoc($items_result)) {
    $item_total = $item['price'] * $item['quantity'];
    $subtotal += $item_total;
    $item_count++;

    $html .= '
    <div style="display: flex; align-items: center; gap: 20px; padding: 20px; margin-bottom: 15px; background: linear-gradient(45deg, #f8f9fa, #ffffff); border-radius: 15px; border: 2px solid #e9ecef; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: all 0.3s ease;">
        <div style="flex-shrink: 0;">
            <img src="' . ($item['image_url'] ?: 'https://via.placeholder.com/80x80/f4e4d0/5b2620?text=No+Image') . '"
                 alt="' . htmlspecialchars($item['name']) . '"
                 style="width: 80px; height: 80px; object-fit: cover; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); border: 2px solid #f4e4d0;">
        </div>
        <div style="flex: 1; min-width: 0;">
            <div style="font-weight: bold; color: #5b2620; margin-bottom: 8px; font-size: 1.2em; line-height: 1.3;">
                ' . htmlspecialchars($item['name']) . '
            </div>
            <div style="color: #816040; font-size: 0.95em; margin-bottom: 8px; font-style: italic;">
                🏷️ Brand: ' . htmlspecialchars($item['brand'] ?: 'Unknown') . '
            </div>
            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <div style="background: #e3f2fd; color: #1565c0; padding: 6px 12px; border-radius: 20px; font-size: 0.9em; font-weight: 600;">
                    💰 Rp' . number_format($item['price'], 0, ',', '.') . ' /item
                </div>
                <div style="background: #f3e5f5; color: #7b1fa2; padding: 6px 12px; border-radius: 20px; font-size: 0.9em; font-weight: 600;">
                    📦 Qty: ' . $item['quantity'] . '
                </div>
            </div>
        </div>
        <div style="text-align: right; flex-shrink: 0;">
            <div style="font-weight: bold; color: #af2d2d; font-size: 1.3em; background: #fff5f5; padding: 10px 15px; border-radius: 12px; border: 2px solid #ffcdd2;">
                Rp' . number_format($item_total, 0, ',', '.') . '
            </div>
        </div>
    </div>';
}

$shipping = 15000;
$total = $subtotal + $shipping;

$html .= '
    <div style="margin-top: 30px; padding: 25px; background: linear-gradient(45deg, #f4e4d0, #feba71); border-radius: 20px; border: 3px solid #816040;">
        <h4 style="color: #5b2620; margin-bottom: 20px; text-align: center; font-size: 1.4em;">📊 Ringkasan Pembayaran</h4>

        <div style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #e9ecef;">
                <span style="font-size: 1.1em; color: #333;">🛍️ Subtotal (' . $item_count . ' item):</span>
                <span style="font-weight: bold; font-size: 1.2em; color: #5b2620;">Rp' . number_format($subtotal, 0, ',', '.') . '</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #f4e4d0;">
                <span style="font-size: 1.1em; color: #333;">🚚 Ongkos Kirim:</span>
                <span style="font-weight: bold; font-size: 1.2em; color: #5b2620;">Rp' . number_format($shipping, 0, ',', '.') . '</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 1.4em; font-weight: bold; color: #af2d2d; background: linear-gradient(45deg, #fff5f5, #ffebee); padding: 15px; border-radius: 12px; border: 2px solid #ef5350;">
                <span>💵 TOTAL PEMBAYARAN:</span>
                <span style="text-shadow: 1px 1px 2px rgba(0,0,0,0.1);">Rp' . number_format($total, 0, ',', '.') . '</span>
            </div>
        </div>
    </div>
</div>';

echo json_encode(['success' => true, 'html' => $html]);
?>
