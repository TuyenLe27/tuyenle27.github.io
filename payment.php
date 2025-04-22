<?php
require_once 'includes/config.php';

// Kiểm tra order_id từ URL
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($order_id <= 0) {
    header('Location: error.php');
    exit;
}

// Lấy thông tin đơn hàng từ bảng Orders
$query = "SELECT * FROM Orders WHERE id = '$order_id'";
$result = mysqli_query($conn, $query);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    header('Location: error.php');
    exit;
}

// Lấy danh sách sản phẩm trong đơn hàng từ bảng Order_Details
$query = "SELECT od.*, p.name, pi.image_url 
          FROM Order_Details od 
          JOIN Products p ON od.product_id = p.id 
          JOIN Product_Images pi ON p.id = pi.product_id 
          WHERE od.order_id = '$order_id' AND pi.is_primary = 1";
$result = mysqli_query($conn, $query);
$order_details = [];
while ($row = mysqli_fetch_assoc($result)) {
    $order_details[] = $row;
}

if (empty($order_details)) {
    header('Location: error.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Tin Thanh Toán</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mx-auto my-5">
        <h1 class="text-3xl font-bold text-center mb-5">Thông Tin Thanh Toán</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="text-2xl font-semibold mb-4">Thông Tin Đơn Hàng</h2>
                <p><strong>Mã Đơn Hàng:</strong> #<?php echo $order['id']; ?></p>
                <p><strong>Họ Tên:</strong> <?php echo htmlspecialchars($order['fullname']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                <p><strong>Số Điện Thoại:</strong> <?php echo htmlspecialchars($order['phone_number']); ?></p>
                <p><strong>Địa Chỉ:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                <p><strong>Ghi Chú:</strong> <?php echo htmlspecialchars($order['note']); ?></p>
                <p><strong>Tổng Tiền:</strong> <?php echo number_format($order['total_money'], 2); ?> VNĐ</p>
            </div>
            <div>
                <h2 class="text-2xl font-semibold mb-4">Danh Sách Sản Phẩm</h2>
                <?php foreach ($order_details as $item): ?>
                <div class="flex items-center mb-4">
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="h-16 w-16 object-cover mr-4">
                    <div>
                        <p class="font-semibold"><?php echo htmlspecialchars($item['name']); ?></p>
                        <p>Số lượng: <?php echo $item['quantity']; ?></p>
                        <p>Giá: <?php echo number_format($item['price'], 2); ?> VNĐ</p>
                        <p>Tổng: <?php echo number_format($item['total_money'], 2); ?> VNĐ</p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="mt-6">
            <h2 class="text-2xl font-semibold mb-4">Thông Tin Chuyển Khoản</h2>
            <p><strong>Ngân Hàng:</strong> Vietcombank</p>
            <p><strong>Số Tài Khoản:</strong> 1234567890</p>
            <p><strong>Chủ Tài Khoản:</strong> Công Ty XYZ</p>
            <p><strong>Nội Dung Chuyển Khoản:</strong> Thanh toán đơn hàng #<?php echo $order['id']; ?></p>
            <p class="text-red-500 mt-2">Vui lòng chuyển khoản số tiền <strong><?php echo number_format($order['total_money'], 2); ?> VNĐ</strong> để hoàn tất đơn hàng.</p>
        </div>
        <div class="mt-6 text-center">
            <a href="success.php?order_id=<?php echo $order['id']; ?>" class="bg-green-500 text-white px-4 py-2 rounded">Xác Nhận Thanh Toán</a>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>