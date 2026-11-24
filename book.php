<?php require 'config.php'; require_login(); $title = 'Book a room | Sunrise Cove Villa';
$room_id = (int)($_GET['room_id'] ?? $_POST['room_id'] ?? 0);
$s = $pdo->prepare("SELECT * FROM rooms WHERE room_id=? AND room_status='Active'"); $s->execute([$room_id]); $room = $s->fetch();
if (!$room) { flash('danger', 'That room is not available for booking.'); header('Location: rooms.php'); exit; }
$u = $_SESSION['user'];
$v = ['check_in'=>$_GET['check_in'] ?? '', 'check_out'=>$_GET['check_out'] ?? '', 'guests'=>$_GET['guests'] ?? 1,
      'name'=>$u['name'], 'email'=>$u['email'], 'contact'=>$u['contact'], 'notes'=>''];
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    foreach ($v as $k => $_) $v[$k] = trim($_POST[$k] ?? '');
    if (strlen($v['name']) < 2) $errors[] = 'Please enter the guest name.';
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email.';
    if (!preg_match('/^\+?[0-9 \-]{7,15}$/', $v['contact'])) $errors[] = 'Please enter a valid contact number.';
    $g = (int)$v['guests'];
    if ($g < 1 || $g > $room['max_guests']) $errors[] = "This room sleeps 1 to {$room['max_guests']} guests.";
    [$n, $derr] = parse_dates($v['check_in'], $v['check_out']);
    if ($derr) $errors[] = $derr;
    if (!$errors) {
        $pdo->beginTransaction();
        $pdo->prepare("SELECT room_id FROM rooms WHERE room_id=? FOR UPDATE")->execute([$room_id]);   // lock to avoid races
        if (!is_available($pdo, $room_id, $v['check_in'], $v['check_out'])) {
            $pdo->rollBack(); $errors[] = 'Sorry, this room is already booked for those dates.';
        } else {
            $total = total_cost($room['price_per_night'], $v['check_in'], $v['check_out']);    // price always computed on the server
            $pdo->prepare("INSERT INTO bookings (user_id,room_id,guest_name,guest_email,guest_contact,guests,notes,check_in,check_out,total_amount) VALUES (?,?,?,?,?,?,?,?,?,?)")
                ->execute([$u['user_id'], $room_id, $v['name'], $v['email'], $v['contact'], $g, $v['notes'], $v['check_in'], $v['check_out'], $total]);
            $pdo->commit();
            flash('success', "Booking request sent for $n night(s). Total LKR ".number_format($total).". Status: Pending.");
            header('Location: my_bookings.php'); exit;
        }
    }
}
include 'includes/header.php'; ?>
<h1 class="mb-1">Book: <?= e($room['room_name']) ?></h1>
<p class="text-muted">LKR <?= number_format($room['price_per_night']) ?> per night &middot; sleeps up to <?= (int)$room['max_guests'] ?> &middot; Dec to Mar nightly rate is 20% higher (peak season)</p>
<?php foreach ($errors as $m) echo "<div class='alert alert-danger py-2'>".e($m)."</div>"; ?>
<div class="row g-4"><div class="col-lg-8">
<form method="post" id="bookForm" class="js-validate" novalidate data-room="<?= $room_id ?>" data-price="<?= (float)$room['price_per_night'] ?>" data-max="<?= (int)$room['max_guests'] ?>">
  <div class="js-errors"></div><?= csrf_field() ?><input type="hidden" name="room_id" value="<?= $room_id ?>">
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label">Guest name</label><input name="name" class="form-control" required data-label="Name" value="<?= e($v['name']) ?>"></div>
    <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required data-label="Email" value="<?= e($v['email']) ?>"></div>
    <div class="col-md-6"><label class="form-label">Contact number</label><input name="contact" class="form-control" required data-label="Contact number" value="<?= e($v['contact']) ?>"></div>
    <div class="col-md-6"><label class="form-label" for="guests">Guests</label><input type="number" id="guests" name="guests" min="1" max="<?= (int)$room['max_guests'] ?>" class="form-control" required value="<?= e($v['guests']) ?>"></div>
    <div class="col-md-6"><label class="form-label" for="check_in">Check-in</label><input type="date" id="check_in" name="check_in" class="form-control" required data-label="Check-in" value="<?= e($v['check_in']) ?>"></div>
    <div class="col-md-6"><label class="form-label" for="check_out">Check-out</label><input type="date" id="check_out" name="check_out" class="form-control" required data-label="Check-out" value="<?= e($v['check_out']) ?>"></div>
    <div class="col-12"><label class="form-label">Special requests (optional)</label><textarea name="notes" rows="2" maxlength="300" class="form-control"><?= e($v['notes']) ?></textarea></div>
  </div>
  <button class="btn btn-sun btn-lg mt-3">Send booking request</button>
</form></div>
<div class="col-lg-4"><div class="summary" aria-live="polite"><h2 class="h5">Your stay</h2>
  <p id="nights" class="mb-1">Choose your dates</p><p id="cost" class="price mb-1"></p><p id="avail" class="small mb-0"></p></div></div></div>
<?php include 'includes/footer.php'; ?>
