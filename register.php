<?php
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Kiểm tra email đã tồn tại chưa
    $query = "SELECT * FROM Users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $error = "Email đã tồn tại!";
    } else {
        // Mã hóa mật khẩu
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        if (!$hashed_password) {
            $error = "Lỗi khi mã hóa mật khẩu!";
        } else {
            // Thêm người dùng mới vào cơ sở dữ liệu
            // role_id = 2 tương ứng với vai trò 'user' trong bảng Roles
            $query = "INSERT INTO Users (email, password, role_id) 
                      VALUES ('$email', '$hashed_password', 2)";
            if (mysqli_query($conn, $query)) {
                $success = "Đăng ký thành công! Vui lòng đăng nhập.";
            } else {
                $error = "Lỗi khi đăng ký: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mx-auto my-5">
        <h1 class="text-3xl font-bold text-center mb-5">Đăng Ký Tài Khoản</h1>
        <div class="max-w-md mx-auto">
            <?php if (isset($error)) echo "<p class='text-red-500 text-center mb-4'>$error</p>"; ?>
            <?php if (isset($success)) echo "<p class='text-green-500 text-center mb-4'>$success</p>"; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mật Khẩu</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded w-full">Đăng Ký</button>
            </form>
            <p class="text-center mt-3">Đã có tài khoản? <a href="login.php" class="text-blue-500">Đăng nhập</a></p>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>