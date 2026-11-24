<?php require 'config.php'; $title = 'Log in | Sunrise Cove Villa'; $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $s = $pdo->prepare("SELECT * FROM users WHERE email=?"); $s->execute([trim($_POST['email'] ?? '')]); $u = $s->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['user_id'=>$u['user_id'],'name'=>$u['name'],'email'=>$u['email'],'contact'=>$u['contact_number'],'role'=>$u['role']];
        header('Location: '.($u['role'] === 'admin' ? 'admin/index.php' : 'my_bookings.php')); exit;
    }
    $err = 'Incorrect email or password.';
}
include 'includes/header.php'; ?>
<div class="row justify-content-center"><div class="col-md-6 col-lg-4">
<h1 class="mb-3">Log in</h1>
<?php if ($err) echo "<div class='alert alert-danger'>".e($err)."</div>"; ?>
<form method="post" class="js-validate" novalidate><div class="js-errors"></div><?= csrf_field() ?>
  <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required data-label="Email"></div>
  <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required data-label="Password"></div>
  <button class="btn btn-sea w-100">Log in</button>
  <p class="small mt-3">New here? <a href="register.php">Create an account</a></p>
</form></div></div>
<?php include 'includes/footer.php'; ?>
