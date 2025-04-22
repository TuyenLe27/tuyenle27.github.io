<?php
require_once 'includes/config.php';

// Khởi tạo các tham số lọc từ GET (dùng cho fallback)
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$brand_id = isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : 0;
$price_min = isset($_GET['price_min']) && $_GET['price_min'] !== '' ? (float)$_GET['price_min'] : 0;
$price_max = isset($_GET['price_max']) && $_GET['price_max'] !== '' ? (float)$_GET['price_max'] : 999999;

// Lấy danh sách sản phẩm (fallback nếu JavaScript bị tắt)
$query = "SELECT p.*, pi.image_url 
          FROM Products p 
          LEFT JOIN Product_Images pi ON p.id = pi.product_id 
          WHERE p.deleted = 0 AND pi.is_primary = 1";
if ($search) {
    $query .= " AND p.name LIKE '%$search%'";
}
if ($category_id) {
    $query .= " AND p.category_id = $category_id";
}
if ($brand_id) {
    $query .= " AND p.brand_id = $brand_id";
}
if ($price_min || $price_max != 999999) {
    $query .= " AND (p.price - p.discount) BETWEEN $price_min AND $price_max";
}
$result = mysqli_query($conn, $query);
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

// Lấy danh sách danh mục
$categories_query = "SELECT * FROM Categories WHERE deleted = 0";
$categories_result = mysqli_query($conn, $categories_query);
$categories = [];
while ($row = mysqli_fetch_assoc($categories_result)) {
    $categories[] = $row;
}

// Lấy danh sách thương hiệu
$brands_query = "SELECT * FROM Brands WHERE deleted = 0";
$brands_result = mysqli_query($conn, $brands_query);
$brands = [];
while ($row = mysqli_fetch_assoc($brands_result)) {
    $brands[] = $row;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản Phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mx-auto my-5">
        <h1 class="text-3xl font-bold text-center mb-5">Sản Phẩm</h1>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="border p-4 rounded">
                <h2 class="text-xl font-semibold mb-4">Bộ Lọc</h2>
                <form id="filter-form">
                    <div class="mb-3">
                        <label for="search" class="form-label">Tìm kiếm</label>
                        <input type="text" name="search" id="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Tìm theo tên sản phẩm">
                    </div>
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Danh Mục</label>
                        <select name="category_id" id="category_id" class="form-control">
                            <option value="0">Tất cả</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="brand_id" class="form-label">Thương Hiệu</label>
                        <select name="brand_id" id="brand_id" class="form-control">
                            <option value="0">Tất cả</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand['id']; ?>" <?php echo $brand_id == $brand['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($brand['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="price_min" class="form-label">Giá Tối Thiểu</label>
                        <input type="number" name="price_min" id="price_min" class="form-control" value="<?php echo $price_min != 0 ? $price_min : ''; ?>" min="0" placeholder="0">
                    </div>
                    <div class="mb-3">
                        <label for="price_max" class="form-label">Giá Tối Đa</label>
                        <input type="number" name="price_max" id="price_max" class="form-control" value="<?php echo $price_max != 999999 ? $price_max : ''; ?>" min="0" placeholder="Không giới hạn">
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded w-full">Lọc</button>
                </form>
            </div>
            <div class="md:col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php if (empty($products)): ?>
                        <p class="text-center col-span-3">Không tìm thấy sản phẩm nào!</p>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                        <div class="card p-4 border rounded shadow relative">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-48 object-cover mb-2">
                            <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($product['name']); ?></h2>
                            <p class="text-gray-600"><?php echo number_format($product['price'] - $product['discount'], 2); ?> VNĐ</p>
                            <?php if ($product['discount'] > 0): ?>
                                <p class="text-red-500 line-through"><?php echo number_format($product['price'], 2); ?> VNĐ</p>
                            <?php endif; ?>
                            <div class="flex space-x-2 mt-2">
                                <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="bg-gray-500 text-white px-2 py-1 rounded text-sm">Xem Chi Tiết</a>
                                <button class="bg-blue-500 text-white px-2 py-1 rounded text-sm buy-now" data-id="<?php echo $product['id']; ?>">Mua Ngay</button>
                                <button class="add-to-cart text-blue-500" data-id="<?php echo $product['id']; ?>"><i class="fas fa-cart-plus"></i></button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
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
            // Log dữ liệu form khi submit để kiểm tra
            $('#filter-form').submit(function() {
                console.log('Form data:', $(this).serializeArray());
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