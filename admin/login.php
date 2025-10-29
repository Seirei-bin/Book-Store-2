<?php
session_start();
include '../config.php';

// Kalau sudah login langsung ke dashboard
if (isset($_SESSION['admin'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = md5($_POST['password']); // sesuaikan dengan hash yg dipakai

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username=? AND password=?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($admin) {
        $_SESSION['admin'] = $admin['username'];
        header("Location: dashboard.php"); // ⬅️ langsung ke dashboard
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Admin Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  <div class="bg-white shadow-lg rounded-lg p-8 w-96">
    <h1 class="text-2xl font-bold text-center text-blue-600 mb-6">🔑 Login Admin</h1>

    <?php if ($error): ?>
      <p class="text-red-500 text-center mb-4"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <input type="text" name="username" placeholder="Username"
        class="w-full border px-4 py-2 rounded focus:ring-2 focus:ring-blue-400" required>
      
      <input type="password" name="password" placeholder="Password"
        class="w-full border px-4 py-2 rounded focus:ring-2 focus:ring-blue-400" required>

      <button type="submit"
        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold">
        Login
      </button>
    </form>
  </div>
</body>
</html>
