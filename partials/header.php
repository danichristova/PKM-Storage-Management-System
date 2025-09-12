<?php require_once __DIR__ . '/../db.php'; ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PKM Storage Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?php echo h(BASE_URL . 'assets/styles.css'); ?>" rel="stylesheet">
  <link rel="icon" type="image/x-icon" href="/partials/pkm_logo.png">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom">
  <div class="container">
    <a class="navbar-brand" href="<?php echo h(BASE_URL . 'index.php'); ?>">PKM Storage</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?php echo h(BASE_URL . 'add_item.php'); ?>">Input Barang</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo h(BASE_URL . 'borrow.php'); ?>">Pinjam</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo h(BASE_URL . 'return.php'); ?>">Kembalikan</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo h(BASE_URL . 'take.php'); ?>">Ambil</a></li>
        <?php if (isset($_SESSION['admin'])): ?> <li class="nav-item"><a class="nav-link" href="<?php echo h(BASE_URL . 'logs.php'); ?>">Log</a></li> <?php endif; ?>
        <?php if (isset($_SESSION['admin'])): ?> <li class="nav-item"><a class="nav-link" href="<?php echo h(BASE_URL . 'settings.php'); ?>">Settings</a></li> <?php endif; ?>
      </ul>
    </div>
    <div class="d-flex">
    <?php if (isset($_SESSION['admin'])): ?>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout Admin</a>
    <?php else: ?>
        <a href="login.php" class="btn btn-outline-primary btn-sm">Login Admin</a>
    <?php endif; ?>
</div>

  </div>
</nav>
<div class="container py-4">
  <?php if ($msg = flash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php echo h($msg); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
  <?php if ($msg = flash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php echo h($msg); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
