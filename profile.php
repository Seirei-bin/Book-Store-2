<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Profil Saya - Estrella Pustaka</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInUp {
      animation: fadeInUp 0.7s ease-out;
    }
  </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

<div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-md animate-fadeInUp">

  <!-- Foto Profil -->
  <div class="flex flex-col items-center">
    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['username']); ?>&background=0D8ABC&color=fff&size=100" 
         alt="Avatar" class="w-24 h-24 rounded-full border-4 border-blue-500 shadow-md">
    <h2 class="text-2xl font-bold mt-4 text-gray-800">
      <?php echo htmlspecialchars($user['nama']); ?>
    </h2>
    <p class="text-blue-500 font-medium">@<?php echo htmlspecialchars($user['username']); ?></p>
  </div>

  <!-- Informasi -->
  <div class="mt-6 space-y-3 text-gray-700">
    <p><i class="fa-solid fa-envelope mr-2 text-blue-500"></i> <?php echo $user['email']; ?></p>
    <p><i class="fa-solid fa-phone mr-2 text-green-500"></i> <?php echo $user['no_hp']; ?></p>
    <p><i class="fa-solid fa-location-dot mr-2 text-red-500"></i> <?php echo $user['alamat']; ?></p>
  </div>

  <!-- Tombol Aksi -->
  <div class="mt-8 flex flex-col gap-3">
    <a href="edit_profile.php" 
       class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-2 rounded-lg font-semibold text-center transition">
       ✏️ Edit Profil
    </a>
    <a href="orders_history.php" 
       class="w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg font-semibold text-center transition">
       📜 Lihat Riwayat Pemesanan
    </a>
    <a href="index.php" 
       class="w-full bg-gray-500 hover:bg-gray-600 text-white py-2 rounded-lg font-semibold text-center transition">
       ⬅️ Kembali ke Beranda
    </a>
  </div>
</div>

</body>
</html>
