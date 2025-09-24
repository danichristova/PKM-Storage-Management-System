<?php
include "config.php";

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  // Tentukan role berdasarkan kombinasi username/password
  $role = null;
  if ($username === "pkm2025" && $password === "pkm2025") {
    $role = "admin";
  } elseif ($username === "admin" && $password === "admin") {
    $role = "admin"; // contoh tambahan
  } elseif ($username === "superadmin" && $password === "superadmin") {
    $role = "superadmin";
  }

  if ($role !== null) {
    // Amankan session
    session_regenerate_id(true);
    $_SESSION['admin'] = true;
    $_SESSION['admin_user'] = $username;
    $_SESSION['role'] = $role; // <- simpan role di session

    // catat log login (menggunakan koneksi mysqli $conn)
    $stmt = $conn->prepare("INSERT INTO admin_logs (admin_username, action, details) VALUES (?, ?, ?)");
    $action = "Login";
    $details = "Berhasil login sebagai $role";
    $stmt->bind_param("sss", $username, $action, $details);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit();
  } else {
    $error = "Username atau Password salah!";
  }
}
?>



<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Login Admin</title>
  <link rel="stylesheet" href="assets/bootstrap.min.css">
  <style>
    body {

      margin: 0;

      padding: 0;

      font-family: 'Poppins', sans-serif;

      height: 100vh;

      display: flex;

      justify-content: center;

      align-items: center;

      background: url('https://images.pexels.com/photos/1525041/pexels-photo-1525041.jpeg?cs=srgb&dl=pexels-francesco-ungaro-1525041.jpg&fm=jpg?blur=5') no-repeat center center/cover;

    }

    .login-container {

      background: rgba(255, 255, 255, 0.15);

      backdrop-filter: blur(12px);

      border-radius: 20px;

      padding: 40px;

      width: 350px;

      text-align: center;

      color: white;

      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);

      animation: fadeIn 1.2s ease-in-out;

    }

    .login-container h2 {

      margin-bottom: 20px;

      font-size: 28px;

    }

    .login-container input {

      width: 100%;

      padding: 12px;

      margin: 12px 0;

      border: none;

      border-radius: 10px;

      outline: none;

      font-size: 16px;

    }

    .login-container input[type="text"],

    .login-container input[type="password"] {

      max-width: 90%;

      background: rgba(255, 255, 255, 0.8);

    }

    .login-container button {

      width: 100%;

      padding: 12px;

      border: none;

      border-radius: 10px;

      background: linear-gradient(135deg, #667eea, #764ba2);

      color: white;

      font-size: 18px;

      cursor: pointer;

      margin-top: 15px;

      transition: 0.3s;

    }

    .login-container button:hover {

      background: linear-gradient(135deg, #5563c1, #5c3c8a);

    }

    .extra {

      margin-top: 15px;

      font-size: 14px;

    }

    .extra a {

      color: #fff;

      text-decoration: none;

      font-weight: bold;

    }

    @keyframes fadeIn {

      from {
        opacity: 0;
        transform: translateY(-30px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }

    }

    @media (max-width: 576px) {
      .login-container {
        max-width: 95%;
        /* hampir penuh layar */
        padding: 25px 20px;
        /* tetap ada jarak biar rapi */
      }

      .login-container h2 {
        font-size: 24px;
        /* judul tetap besar di HP */
      }

      .login-container input,
      .login-container button {
        font-size: 16px;
        /* tombol/input nyaman ditekan */
      }
    }
  </style>

</head>

<body>

  <div class="login-container">

    <h2>Admin PKM</h2>

    <form method="post">
      <?php if ($error)
        echo "<div class='alert alert-danger'>$error</div>"; ?>

      <input type="text" name="username" placeholder="Username" class="form-control" required>

      <input type="password" name="password" placeholder="Password" class="form-control" required>

      <button type="submit">Login</button>

    </form>

    <div class="extra">

      <a href="index.php">Kembali</a>

    </div>

  </div>



</body>

</html>