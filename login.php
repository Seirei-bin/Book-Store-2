<?php
session_start();
include 'config.php'; // koneksi DB

// Kalau sudah login, redirect ke index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Cari user di DB
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();

        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Simpan session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];

            header("Location: index.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Estrella Pustaka</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <!-- Card Login -->
    <div class="bg-white p-8 rounded-2xl shadow-md w-full max-w-md animate-fadeIn">
        
        <!-- Heading -->
        <div class="mb-6 text-center">
            <div class="flex justify-center mb-2">
     

<img src="images/estrella_pustaka.png" alt="Logo Estrella Pustaka" class="w-20 h-20 mx-auto rounded-full object-cover">




            </div>
            <h1 class="text-2xl font-extrabold text-gray-800">
                Selamat Datang
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                di Estrella Pustaka
            </p>
        </div>

        <h2 class="text-lg font-semibold text-center text-gray-700 mb-6">🔑 Login</h2>

        <?php if($error): ?>
            <p class="text-red-500 text-center font-medium mb-4"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-gray-700 font-medium">Username</label>
                <input type="text" name="username" required
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-medium">Password</label>
                <input type="password" name="password" required
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <button type="submit" 
                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold shadow transition">
                Login
            </button>
        </form>

        <p class="text-center text-sm text-gray-600 mt-4">
            Belum punya akun? 
            <a href="register.php" class="text-blue-600 hover:underline font-medium">Daftar</a>
        </p>
    </div>

    <!-- Animasi simple -->
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
