<?php require 'config.php'; $title = 'Check availability | Sunrise Cove Villa';
$in = $_GET['check_in'] ?? ''; $out = $_GET['check_out'] ?? ''; $g = max(1, (int)($_GET['guests'] ?? 1));
$rooms = null; $err = null; $n = 0;
if ($in || $out) {
    [$n, $err] = parse_dates($in, $out);
    if (!$err) {
        $st = $pdo->prepare("SELECT * FROM rooms WHERE room_status='Active' AND max_guests>=? AND room_id NOT IN
            (SELECT room_id FROM bookings WHERE booking_status='Confirmed' AND check_in < ? AND check_out > ?) ORDER BY price_per_night");
        $st->execute([$g, $out, $in]); $rooms = $st->fetchAll();
    }
}
include 'includes/header.php'; ?>
<h1 class="mb-3">Check room availability</h1>
<form method="get" class="row g-2 mb-4 filter-bar js-validate">
  <div class="js-errors col-12"></div>
  <div class="col-md-3"><label class="form-label" for="check_in">Check-in</label><input type="date" id="check_in" name="check_in" class="form-control" required data-label="Check-in" value="<?= e($in) ?>"></div>
  <div class="col-md-3"><label class="form-label" for="check_out">Check-out</label><input type="date" id="check_out" name="check_out" class="form-control" required data-label="Check-out" value="<?= e($out) ?>"></div>
  <div class="col-md-3"><label class="form-label" for="guests">Guests</label><input type="number" id="guests" name="guests" min="1" max="10" class="form-control" required value="<?= $g ?>"></div>
  <div class="col-md-3 d-flex align-items-end"><button class="btn btn-sea w-100">Search rooms</button></div>
</form>
<?php if ($err) echo "<div class='alert alert-danger'>".e($err)."</div>"; ?>
<?php if ($rooms !== null): ?>
  <p><?= count($rooms) ?> room(s) available for <?= $n ?> night(s):</p>
  <div class="row g-4"><?php foreach ($rooms as $r) echo room_card($r, http_build_query(['check_in'=>$in,'check_out'=>$out,'guests'=>$g])); ?></div>
  <?php if (!$rooms) echo "<div class='alert alert-info'>No rooms are free for those dates. Try different dates or fewer guests.</div>"; ?>
<?php endif; include 'includes/footer.php'; ?>
