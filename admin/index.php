<?php require '../config.php'; require_login(); require_admin(); $base = '../'; $title = 'Admin dashboard';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf(); $id = (int)($_POST['booking_id'] ?? 0); $act = $_POST['action'] ?? '';
    $pdo->beginTransaction();
    $s = $pdo->prepare("SELECT * FROM bookings WHERE booking_id=? FOR UPDATE"); $s->execute([$id]); $b = $s->fetch();
    if (!$b) { $pdo->rollBack(); flash('danger', 'Booking not found.'); }
    elseif ($act === 'confirm') {
        $pdo->prepare("SELECT room_id FROM rooms WHERE room_id=? FOR UPDATE")->execute([$b['room_id']]);
        if ($b['booking_status'] !== 'Pending') { $pdo->rollBack(); flash('danger', 'Only pending bookings can be confirmed.'); }
        elseif (!is_available($pdo, $b['room_id'], $b['check_in'], $b['check_out'])) { $pdo->rollBack(); flash('danger', 'Cannot confirm: it overlaps another confirmed booking for this room.'); }
        else { $pdo->prepare("UPDATE bookings SET booking_status='Confirmed' WHERE booking_id=?")->execute([$id]); $pdo->commit(); flash('success', "Booking #$id confirmed."); }
    } elseif ($act === 'cancel') {
        $pdo->prepare("UPDATE bookings SET booking_status='Cancelled' WHERE booking_id=?")->execute([$id]); $pdo->commit(); flash('success', "Booking #$id cancelled.");
    } else $pdo->rollBack();
    header('Location: index.php'); exit;
}
$stat = $pdo->query("SELECT COUNT(*) total, SUM(booking_status='Pending') pending, SUM(booking_status='Confirmed') confirmed,
    COALESCE(SUM(CASE WHEN booking_status='Confirmed' THEN total_amount END),0) revenue FROM bookings")->fetch();
$rows = $pdo->query("SELECT b.*, r.room_name, u.name AS user_name FROM bookings b JOIN rooms r ON r.room_id=b.room_id JOIN users u ON u.user_id=b.user_id ORDER BY FIELD(b.booking_status,'Pending','Confirmed','Cancelled'), b.check_in")->fetchAll();
$cls = ['Pending'=>'warning text-dark','Confirmed'=>'success','Cancelled'=>'secondary'];
include '../includes/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="mb-0">Admin dashboard</h1><a class="btn btn-sea" href="rooms.php">Manage rooms</a></div>
<div class="row g-3 mb-4">
<?php foreach ([['Total bookings',$stat['total']],['Pending',$stat['pending']],['Confirmed',$stat['confirmed']],['Confirmed revenue (LKR)',number_format($stat['revenue'])]] as $c): ?>
<div class="col-6 col-lg-3"><div class="feature"><div class="small"><?= e($c[0]) ?></div><div class="h3 mb-0"><?= e($c[1] ?? 0) ?></div></div></div><?php endforeach; ?></div>
<div class="table-responsive"><table class="table align-middle table-sm"><thead><tr><th>#</th><th>Guest</th><th>Room</th><th>Dates</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($rows as $b): ?><tr>
 <td><?= $b['booking_id'] ?></td><td><?= e($b['guest_name']) ?><br><small class="text-muted"><?= e($b['guest_contact']) ?></small></td><td><?= e($b['room_name']) ?></td>
 <td><?= e($b['check_in']) ?> to <?= e($b['check_out']) ?></td><td><?= number_format($b['total_amount']) ?></td>
 <td><span class="badge bg-<?= $cls[$b['booking_status']] ?>"><?= e($b['booking_status']) ?></span></td>
 <td><form method="post" class="d-flex gap-1"><?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= $b['booking_id'] ?>">
  <?php if ($b['booking_status'] === 'Pending'): ?><button name="action" value="confirm" class="btn btn-success btn-sm">Confirm</button><?php endif; ?>
  <?php if ($b['booking_status'] !== 'Cancelled'): ?><button name="action" value="cancel" class="btn btn-outline-danger btn-sm" onclick="return confirm('Cancel booking #<?= $b['booking_id'] ?>?')">Cancel</button><?php endif; ?></form></td></tr>
<?php endforeach; ?></tbody></table></div>
<?php include '../includes/footer.php'; ?>
