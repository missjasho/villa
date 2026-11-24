<?php require_once __DIR__.'/../config.php'; $base = $base ?? ''; $u = $_SESSION['user'] ?? null; ?>
<!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Sunrise Cove Villa, Mirissa') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
<link href="<?= $base ?>assets/css/style.css" rel="stylesheet">
</head><body>
<nav class="navbar navbar-expand-lg navbar-dark site-nav"><div class="container">
  <a class="navbar-brand" href="<?= $base ?>index.php">Sunrise Cove Villa</a>
  <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
  <div class="collapse navbar-collapse" id="nav">
    <ul class="navbar-nav me-auto">
      <li class="nav-item"><a class="nav-link" href="<?= $base ?>index.php">Home</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= $base ?>rooms.php">Rooms</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= $base ?>search.php">Check availability</a></li>
      <?php if ($u): ?><li class="nav-item"><a class="nav-link" href="<?= $base ?>my_bookings.php">My bookings</a></li><?php endif; ?>
      <?php if (($u['role'] ?? '') === 'admin'): ?><li class="nav-item"><a class="nav-link" href="<?= $base ?>admin/index.php">Admin</a></li><?php endif; ?>
    </ul>
    <div class="d-flex gap-2 align-items-center">
      <?php if ($u): ?><span class="text-white-50 small">Hi, <?= e($u['name']) ?></span>
        <a class="btn btn-outline-light btn-sm" href="<?= $base ?>logout.php">Log out</a>
      <?php else: ?><a class="btn btn-outline-light btn-sm" href="<?= $base ?>login.php">Log in</a>
        <a class="btn btn-outline-light btn-sm" href="<?= $base ?>register.php">Register</a><?php endif; ?>
      <a class="btn btn-sun btn-sm" href="<?= $base ?>rooms.php">Book now</a>
    </div>
  </div></div></nav>
<main class="container py-4"><?= flash() ?>
