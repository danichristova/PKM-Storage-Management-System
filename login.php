<?php
include "config.php";

$error = "";
$message = "";

// Cek apakah user baru saja logout otomatis
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    $message = "Sesi login Anda sudah berakhir.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();

  if ($user && password_verify($password, $user['password'])) {
    // Amankan session
    session_regenerate_id(true);
    $_SESSION['admin'] = true;
    $_SESSION['admin_user'] = $user['username'];
    $_SESSION['role'] = $user['role']; // admin / superadmin

    // log login
    $now = date('Y-m-d H:i:s'); // waktu realtime dari PHP
    $stmtLog = $conn->prepare("INSERT INTO admin_logs (admin_username, action, details, created_at) 
                           VALUES (?, ?, ?, ?)");
    $action = "Login";
    $details = "Berhasil login sebagai {$user['role']}";
    $stmtLog->bind_param("ssss", $user['username'], $action, $details, $now);
    $stmtLog->execute();
    $stmtLog->close();

    header("Location: index.php");
    exit();
  } else {
    $error = "Username atau Password salah!";
    $message = "";
  }
}
?>



<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            background: #f5f6fa;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .login-card {
            max-width: 400px;
            width: 100%;
            padding: 30px;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .login-card h3 {
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
        }

        .toggle-password {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <h3>Login Admin</h3>

        <!-- Alert untuk error / message -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <div class="alert alert-warning"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required autofocus>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" required>
                    <span class="input-group-text bg-white">
                        <i class="bi bi-eye" id="togglePassword" style="cursor:pointer;"></i>
                    </span>
                </div>
            </div>


            <button type="submit" class="btn btn-primary w-100">Login</button>

        </form>
        <p class="text-center mt-3">
            <a href="index.php" class="text-dark">Kembali</a>
        </p>
    </div>

    <script>
        const togglePassword = document.getElementById("togglePassword");
        const passwordInput = document.getElementById("password");

        togglePassword.addEventListener("click", function () {
            const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
            passwordInput.setAttribute("type", type);

            // toggle icon
            this.classList.toggle("bi-eye");
            this.classList.toggle("bi-eye-slash");
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>