<?php
// Core config, DB connection (PDO) and shared helper functions.
session_start();
const DB_HOST = 'localhost', DB_NAME = 'villa_booking', DB_USER = 'root', DB_PASS = '';
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $ex) { die('Database connection failed. Check config.php and that db_structure.sql is imported.'); }

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function csrf_field() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return '<input type="hidden" name="csrf" value="'.$_SESSION['csrf'].'">';
}
function check_csrf() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(400); die('Invalid request token.'); }
}
function flash($type = null, $msg = null) {            // set: flash('success','..')  show: echo flash();
    if ($type) { $_SESSION['flash'] = [$type, $msg]; return; }
    if (isset($_SESSION['flash'])) { [$t, $m] = $_SESSION['flash']; unset($_SESSION['flash']);
        return "<div class='alert alert-$t'>".e($m)."</div>"; }
    return '';
}
function require_login() { if (empty($_SESSION['user'])) { flash('warning', 'Please log in to continue.'); header('Location: login.php'); exit; } }
function require_admin() { if (($_SESSION['user']['role'] ?? '') !== 'admin') { http_response_code(403); die('Access denied: administrators only.'); } }

// Returns [nights, error]. Server-side date validation.
function parse_dates($in, $out) {
    $d1 = DateTime::createFromFormat('Y-m-d', $in); $d2 = DateTime::createFromFormat('Y-m-d', $out);
    if (!$d1 || $d1->format('Y-m-d') !== $in || !$d2 || $d2->format('Y-m-d') !== $out) return [0, 'Please select valid dates.'];
    if ($d1 < new DateTime('today')) return [0, 'Check-in date cannot be in the past.'];
    if ($d2 <= $d1) return [0, 'Check-out must be after check-in.'];
    return [$d1->diff($d2)->days, null];
}
// Seasonal pricing: +20% per night for Dec-Mar (peak season). Mirrored in assets/js/app.js.
function total_cost($price, $in, $out) {
    $t = 0; $d = new DateTime($in); $end = new DateTime($out);
    while ($d < $end) { $t += $price * (in_array((int)$d->format('n'), [12,1,2,3]) ? 1.2 : 1.0); $d->modify('+1 day'); }
    return round($t, 2);
}
// A room is unavailable only if a CONFIRMED booking overlaps the requested range.
function is_available(PDO $pdo, $room_id, $in, $out) {
    $s = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE room_id=? AND booking_status='Confirmed' AND check_in < ? AND check_out > ?");
    $s->execute([$room_id, $out, $in]);
    return $s->fetchColumn() == 0;
}
function room_card(array $r, $qs = '') {
    $img = ($r['image'] && file_exists(__DIR__.'/assets/img/'.$r['image']))
        ? "<img src='assets/img/".e($r['image'])."' class='card-img-top room-img' alt='".e($r['room_name'])."'>"
        : "<div class='room-ph ph-".e($r['room_type'])."'><span>".e($r['room_type'])."</span></div>";
    $fac = ''; foreach (array_filter(array_map('trim', explode(',', (string)$r['facilities']))) as $f) $fac .= "<span class='badge fac'>".e($f)."</span> ";
    $id = (int)$r['room_id'];
    $extra = $qs ? '&'.$qs : '';
    return "<div class='col-md-6 col-lg-4'><div class='card room-card h-100'>$img<div class='card-body d-flex flex-column'>
      <h3 class='h5'>".e($r['room_name'])."</h3>
      <p class='small text-muted mb-1'>Sleeps up to ".(int)$r['max_guests']." guests</p>
      <p class='price mb-2'>LKR ".number_format($r['price_per_night'])." <small>/ night</small></p>
      <div class='mb-2'>$fac</div>
      <div class='collapse' id='room$id'><p class='small'>".e($r['description'])."</p></div>
      <div class='mt-auto d-flex gap-2'>
        <button class='btn btn-outline-sea btn-sm' data-bs-toggle='collapse' data-bs-target='#room$id'>View details</button>
        <a class='btn btn-sun btn-sm' href='book.php?room_id=$id$extra'>Book this room</a></div>
    </div></div></div>";
}
