<?php require 'config.php'; $title = 'Register | Sunrise Cove Villa';
$v = ['name'=>'','email'=>'','contact'=>'']; $errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    foreach ($v as $k => $_) $v[$k] = trim($_POST[$k] ?? '');
    $pw = $_POST['password'] ?? ''; $cf = $_POST['confirm'] ?? '';
    if (strlen($v['name']) < 2) $errors[] = 'Please enter your full name.';
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (!preg_match('/^\+?[0-9 \-]{7,15}$/', $v['contact'])) $errors[] = 'Please enter a valid contact number.';
    if (strlen($pw) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($pw !== $cf) $errors[] = 'Passwords do not match.';
    if (!$errors) {
        $s = $pdo->prepare("SELECT 1 FROM users WHERE email=?"); $s->execute([$v['email']]);
        if ($s->fetch()) $errors[] = 'An account with this email already exists. Try logging in.';
        else {
            $pdo->prepare("INSERT INTO users (name,email,contact_number,password,role) VALUES (?,?,?,?, 'guest')")
                ->execute([$v['name'], $v['email'], $v['contact'], password_hash($pw, PASSWORD_DEFAULT)]);
            flash('success', 'Account created. You can now log in.'); header('Location: login.php'); exit;
        }
    }
}
include 'includes/header.php'; ?>
<div class="row justify-content-center"><div class="col-md-6 col-lg-5">
<h1 class="mb-3">Create your account</h1>
<?php foreach ($errors as $m) echo "<div class='alert alert-danger py-2'>".e($m)."</div>"; ?>
<form method="post" class="js-validate" data-register novalidate><div class="js-errors"></div><?= csrf_field() ?>
  <div class="mb-3"><label class="form-label">Full name</label><input name="name" class="form-control" required data-label="Name" value="<?= e($v['name']) ?>"></div>
  <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required data-label="Email" value="<?= e($v['email']) ?>"></div>
  <div class="mb-3"><label class="form-label">Contact number</label><input name="contact" class="form-control" required data-label="Contact number" value="<?= e($v['contact']) ?>"></div>
  <div class="mb-3"><label class="form-label">Password (8+ characters)</label><input type="password" name="password" class="form-control" required data-label="Password"></div>
  <div class="mb-3"><label class="form-label">Confirm password</label><input type="password" name="confirm" class="form-control" required data-label="Confirm password"></div>
  <button class="btn btn-sea w-100">Create account</button>
</form></div></div>
<?php include 'includes/footer.php'; ?>
