<?php require 'config.php'; require_login(); $title = 'My bookings | Sunrise Cove Villa'; $uid = $_SESSION['user']['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    // user_id in WHERE clause stops guests cancelling (or seeing) anyone else's bookings
    $s = $pdo->prepare("UPDATE bookings SET booking_status='Cancelled' WHERE booking_id=? AND user_id=? AND booking_status='Pending'");
    $s->execute([(int)($_POST['booking_id'] ?? 0), $uid]);
    flash($s->rowCount() ? 'success' : 'danger', $s->rowCount() ? 'Booking cancelled.' : 'Only your own pending bookings can be cancelled.');
    header('Location: my_bookings.php'); exit;
}
$s = $pdo->prepare("SELECT b.*, r.room_name FROM bookings b JOIN rooms r ON r.room_id=b.room_id WHERE b.user_id=? ORDER BY b.booking_date DESC");
$s->execute([$uid]); $rows = $s->fetchAll();
$cls = ['Pending'=>'warning text-dark','Confirmed'=>'success','Cancelled'=>'secondary'];
include 'includes/header.php'; ?>
<h1 class="mb-3">My bookings</h1>
<?php if (!$rows): ?><div class="alert alert-info">You have no bookings yet. <a href="rooms.php">Browse rooms</a> to make one.</div><?php else: ?>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Guests</th><th>Total (LKR)</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $b): ?><tr>
  <td><?= $b['booking_id'] ?></td><td><?= e($b['room_name']) ?></td><td><?= e($b['check_in']) ?></td><td><?= e($b['check_out']) ?></td>
  <td><?= (int)$b['guests'] ?></td><td><?= number_format($b['total_amount']) ?></td>
  <td><span class="badge bg-<?= $cls[$b['booking_status']] ?>"><?= e($b['booking_status']) ?></span></td>
  <td><?php if ($b['booking_status'] === 'Pending'): ?>
    <form method="post" onsubmit="return confirm('Cancel this booking request?')"><?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= $b['booking_id'] ?>"><button class="btn btn-outline-danger btn-sm">Cancel</button></form>
  <?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
<?php include 'includes/footer.php'; ?>
