<?php
require_once 'includes/config.php';

$product_id = mysqli_real_escape_string($conn, $_GET['id']);
$query = "SELECT p.*, pi.image_url 
          FROM Products p 
          LEFT JOIN Product_Images pi ON p.id = pi.product_id 
          WHERE p.id = '$product_id' AND p.deleted = 0 AND pi.is_primary = 1";
$result = mysqli_query($conn, $query);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    header('Location: error.php');
    exit;
}

$query = "SELECT f.*, u.name 
          FROM Feedbacks f 
          JOIN Users u ON f.user_id = u.id 
          WHERE f.product_id = '$product_id' AND f.status = 'approved'";
$result = mysqli_query($conn, $query);
$feedbacks = [];
while ($row = mysqli_fetch_assoc($result)) {
    $feedbacks[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && isset($_POST['submit_feedback'])) {
    $user_id = $_SESSION['user_id'];
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $rating = (int)$_POST['rating'];

    if ($rating >= 1 && $rating <= 5) {
        $query = "INSERT INTO Feedbacks (user_id, product_id, message, rating, status) 
                  VALUES ('$user_id', '$product_id', '$message', '$rating', 'pending')";
        if (mysqli_query($conn, $query)) {
            $success = "Đánh giá của bạn đã được gửi và đang chờ duyệt!";
        } else {
            $error = "Lỗi khi gửi đánh giá: " . mysqli_error($conn);
        }
    } else {
        $error = "Vui lòng chọn số sao từ 1 đến 5!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mx-auto my-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-96 object-cover rounded">
            </div>
            <div>
                <h1 class="text-3xl font-bold mb-4"><?php echo htmlspecialchars($product['name']); ?></h1>
                <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($product['description']); ?></p>
                <p class="text-2xl font-semibold mb-2"><?php echo number_format($product['price'] - $product['discount'], 2); ?> VNĐ</p>
                <?php if ($product['discount'] > 0): ?>
                    <p class="text-red-500 line-through mb-2"><?php echo number_format($product['price'], 2); ?> VNĐ</p>
                <?php endif; ?>
                <p class="mb-2"><strong>Công suất:</strong> <?php echo $product['wattage']; ?>W</p>
                <p class="mb-2"><strong>Loại ánh sáng:</strong> <?php echo $product['light_type']; ?></p>
                <p class="mb-2"><strong>Tồn kho:</strong> <?php echo $product['stock']; ?> sản phẩm</p>
                <div class="flex items-center space-x-4 mb-4">
                    <label for="quantity" class="font-semibold">Số lượng:</label>
                    <input type="number" id="quantity" name="quantity" class="w-16 form-control" value="1" min="1" max="<?php echo $product['stock']; ?>">
                </div>
                <div class="flex space-x-4">
                    <button class="bg-blue-500 text-white px-4 py-2 rounded add-to-cart" data-id="<?php echo $product['id']; ?>">Thêm vào giỏ</button>
                    <button class="bg-green-500 text-white px-4 py-2 rounded buy-now" data-id="<?php echo $product['id']; ?>">Mua Ngay</button>
                </div>
            </div>
        </div>
        <div class="mt-8">
            <h2 class="text-2xl font-semibold mb-4">Đánh Giá Sản Phẩm</h2>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (isset($success)) echo "<p class='text-green-500 mb-4'>$success</p>"; ?>
                <?php if (isset($error)) echo "<p class='text-red-500 mb-4'>$error</p>"; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="rating" class="form-label">Số Sao (1-5)</label>
                        <input type="number" name="rating" class="form-control" min="1" max="5" required>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Bình Luận</label>
                        <textarea name="message" class="form-control" rows="4" required></textarea>
                    </div>
                    <button type="submit" name="submit_feedback" class="bg-blue-500 text-white px-4 py-2 rounded">Gửi Đánh Giá</button>
                </form>
            <?php else: ?>
                <p>Vui lòng <a href="login.php" class="text-blue-500">đăng nhập</a> để gửi đánh giá!</p>
            <?php endif; ?>
            <div class="mt-6">
                <?php if (empty($feedbacks)): ?>
                    <p>Chưa có đánh giá nào cho sản phẩm này.</p>
                <?php else: ?>
                    <?php foreach ($feedbacks as $feedback): ?>
                    <div class="border-b py-4">
                        <p><strong><?php echo htmlspecialchars($feedback['name']); ?></strong> - <?php echo $feedback['rating']; ?> sao</p>
                        <p><?php echo htmlspecialchars($feedback['message']); ?></p>
                        <p class="text-gray-500 text-sm"><?php echo $feedback['created_at']; ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <a href="cart.php" class="fixed right-4 top-1/2 transform -translate-y-1/2 bg-blue-500 text-white p-4 rounded-full shadow-lg">
        <i class="fas fa-shopping-cart"></i>
        <span id="cart-count" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full px-2 py-1 text-xs">0</span>
    </a>
    <?php include 'includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        $(document).ready(function() {
            updateCartCount();

            // Xử lý Mua Ngay từ trang chi tiết
            $('.buy-now').click(function() {
                let productId = $(this).data('id');
                let quantity = $('#quantity').val();
                window.location.href = 'checkout.php?buy_now=' + productId + '&quantity=' + quantity;
            });
        });

        function updateCartCount() {
            $.ajax({
                url: 'api/get_cart_count.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#cart-count').text(response.count);
                }
            });
        }
    </script>
</body>
</html>