<?php
include 'config.php';
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $nama     = trim($_POST['nama']);
    $no_hp    = trim($_POST['no_hp']);
    $alamat   = trim($_POST['alamat']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // cek email atau username sudah ada
    $check = $conn->prepare("SELECT * FROM users WHERE username=? OR email=?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $message = "❌ Username atau Email sudah terdaftar!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, nama, no_hp, alamat, password, role) 
                                VALUES (?, ?, ?, ?, ?, ?, 'user')");
        $stmt->bind_param("ssssss", $username, $email, $nama, $no_hp, $alamat, $password);

        if ($stmt->execute()) {
            header("Location: login.php?success=1");
            exit;
        } else {
            $message = "❌ Gagal registrasi: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Register - Estrella Pustaka</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

  <!-- Card Register -->
  <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md animate-fadeIn">
    
    <!-- Heading -->
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-extrabold text-gray-800">Buat Akun Baru</h1>
        <p class="text-sm text-gray-500 mt-1">Estrella Pustaka</p>
    </div>

    <?php if ($message): ?>
        <p class="mb-4 text-center text-red-500 font-medium"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-gray-700 font-medium">Username</label>
        <input type="text" name="username" required 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
      </div>
      <div>
        <label class="block text-gray-700 font-medium">Email</label>
        <input type="email" name="email" required 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
      </div>
      <div>
        <label class="block text-gray-700 font-medium">Nama</label>
        <input type="text" name="nama" required 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
      </div>
      <div>
        <label class="block text-gray-700 font-medium">No HP</label>
        <input type="text" name="no_hp" required 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
      </div>
      <div>
        <label class="block text-gray-700 font-medium">Alamat</label>
        <textarea name="alamat" required 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"></textarea>
      </div>
      <div>
        <label class="block text-gray-700 font-medium">Password</label>
        <input type="password" name="password" required 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
      </div>

      <button type="submit" 
              class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold shadow-md transition transform hover:scale-105">
        🚀 Daftar
      </button>
    </form>

    <p class="text-center mt-4 text-gray-600">
      Sudah punya akun? 
      <a href="login.php" class="text-blue-600 hover:underline font-medium">Login</a>
    </p>
  </div>

  <style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeIn {
        animation: fadeIn 0.8s ease-out forwards;
    }
  </style>

</body>
</html>
