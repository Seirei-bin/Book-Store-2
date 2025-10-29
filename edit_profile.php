<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama   = trim($_POST['nama']);
    $email  = trim($_POST['email']);
    $no_hp  = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);

    $stmt = $conn->prepare("UPDATE users SET nama=?, email=?, no_hp=?, alamat=? WHERE id=?");
    $stmt->bind_param("ssssi", $nama, $email, $no_hp, $alamat, $user_id);

    if ($stmt->execute()) {
        // ✅ langsung kembali ke profile.php
        header("Location: profile.php?success=1");
        exit;
    } else {
        $message = "❌ Gagal update profil: " . $conn->error;
    }
}

$result = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Profil</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<div class="w-full max-w-md bg-white rounded-lg shadow-md p-8">
  <h2 class="text-2xl font-bold mb-6 text-center text-yellow-600">✏️ Edit Profil</h2>

  <?php if (!empty($message)): ?>
    <p class="mb-4 text-center text-red-600"><?php echo $message; ?></p>
  <?php endif; ?>

  <form method="POST">
    <div class="mb-4">
      <label class="block text-gray-700">Nama</label>
      <input type="text" name="nama" value="<?php echo $user['nama']; ?>" required class="w-full border px-3 py-2 rounded">
    </div>
    <div class="mb-4">
      <label class="block text-gray-700">Email</label>
      <input type="email" name="email" value="<?php echo $user['email']; ?>" required class="w-full border px-3 py-2 rounded">
    </div>
    <div class="mb-4">
      <label class="block text-gray-700">No HP</label>
      <input type="text" name="no_hp" value="<?php echo $user['no_hp']; ?>" required class="w-full border px-3 py-2 rounded">
    </div>
    <div class="mb-4">
      <label class="block text-gray-700">Alamat</label>
      <textarea name="alamat" required class="w-full border px-3 py-2 rounded"><?php echo $user['alamat']; ?></textarea>
    </div>

    <div class="flex justify-between">
      <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg">
        💾 Simpan
      </button>
      <a href="profile.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
        ❌ Batal
      </a>
    </div>
  </form>
</div>

</body>
</html>
