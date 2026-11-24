<?php require '../config.php'; require_login(); require_admin(); $base = '../'; $title = 'Manage rooms';
$types = ['Standard','Deluxe','Family','Suite']; $errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf(); $act = $_POST['action'] ?? ''; $id = (int)($_POST['room_id'] ?? 0);
    if ($act === 'save') {
        $d = ['room_name'=>trim($_POST['room_name'] ?? ''), 'room_type'=>$_POST['room_type'] ?? '', 'description'=>trim($_POST['description'] ?? ''),
              'facilities'=>trim($_POST['facilities'] ?? ''), 'price'=>(float)($_POST['price_per_night'] ?? 0), 'max'=>(int)($_POST['max_guests'] ?? 0),
              'status'=>($_POST['room_status'] ?? '') === 'Inactive' ? 'Inactive' : 'Active', 'image'=>trim($_POST['image'] ?? '')];
        if ($d['room_name'] === '') $errors[] = 'Room name is required.';
        if (!in_array($d['room_type'], $types, true)) $errors[] = 'Choose a valid room type.';
        if ($d['price'] <= 0) $errors[] = 'Price must be greater than zero.';
        if ($d['max'] < 1 || $d['max'] > 10) $errors[] = 'Max guests must be between 1 and 10.';
        if (!$errors) {
            $p = [$d['room_name'],$d['room_type'],$d['description'],$d['facilities'],$d['price'],$d['max'],$d['status'],basename($d['image'])];
            if ($id) { $p[] = $id; $pdo->prepare("UPDATE rooms SET room_name=?,room_type=?,description=?,facilities=?,price_per_night=?,max_guests=?,room_status=?,image=? WHERE room_id=?")->execute($p); flash('success', 'Room updated.'); }
            else { $pdo->prepare("INSERT INTO rooms (room_name,room_type,description,facilities,price_per_night,max_guests,room_status,image) VALUES (?,?,?,?,?,?,?,?)")->execute($p); flash('success', 'Room added.'); }
            header('Location: rooms.php'); exit;
        }
    } elseif ($act === 'delete') {
        $c = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE room_id=?"); $c->execute([$id]);
        if ($c->fetchColumn() > 0) { $pdo->prepare("UPDATE rooms SET room_status='Inactive' WHERE room_id=?")->execute([$id]); flash('warning', 'Room has booking history, so it was deactivated instead of deleted.'); }
        else { $pdo->prepare("DELETE FROM rooms WHERE room_id=?")->execute([$id]); flash('success', 'Room deleted.'); }
        header('Location: rooms.php'); exit;
    }
}
$edit = null;
if (isset($_GET['edit'])) { $s = $pdo->prepare("SELECT * FROM rooms WHERE room_id=?"); $s->execute([(int)$_GET['edit']]); $edit = $s->fetch(); }
$rooms = $pdo->query("SELECT * FROM rooms ORDER BY room_id")->fetchAll();
$f = $edit ?: ['room_id'=>0,'room_name'=>'','room_type'=>'Standard','description'=>'','facilities'=>'','price_per_night'=>'','max_guests'=>2,'room_status'=>'Active','image'=>''];
include '../includes/header.php'; ?>
<h1 class="mb-3">Manage rooms</h1>
<?php foreach ($errors as $m) echo "<div class='alert alert-danger py-2'>".e($m)."</div>"; ?>
<div class="row g-4"><div class="col-lg-8"><div class="table-responsive"><table class="table align-middle table-sm">
<thead><tr><th>Room</th><th>Type</th><th>Price</th><th>Guests</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($rooms as $r): ?><tr><td><?= e($r['room_name']) ?></td><td><?= e($r['room_type']) ?></td><td><?= number_format($r['price_per_night']) ?></td><td><?= (int)$r['max_guests'] ?></td>
<td><span class="badge bg-<?= $r['room_status']==='Active'?'success':'secondary' ?>"><?= e($r['room_status']) ?></span></td>
<td class="d-flex gap-1"><a class="btn btn-outline-sea btn-sm" href="?edit=<?= $r['room_id'] ?>">Edit</a>
<form method="post" onsubmit="return confirm('Delete this room? Rooms with bookings are deactivated instead.')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="room_id" value="<?= $r['room_id'] ?>"><button class="btn btn-outline-danger btn-sm">Delete</button></form></td></tr>
<?php endforeach; ?></tbody></table></div></div>
<div class="col-lg-4"><div class="summary"><h2 class="h5"><?= $edit ? 'Edit room' : 'Add a room' ?></h2>
<form method="post" class="js-validate" novalidate><div class="js-errors"></div><?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="room_id" value="<?= (int)$f['room_id'] ?>">
<div class="mb-2"><label class="form-label">Name</label><input name="room_name" class="form-control" required data-label="Room name" value="<?= e($f['room_name']) ?>"></div>
<div class="mb-2"><label class="form-label">Type</label><select name="room_type" class="form-select"><?php foreach ($types as $t) echo "<option ".($f['room_type']===$t?'selected':'').">$t</option>"; ?></select></div>
<div class="mb-2"><label class="form-label">Description</label><textarea name="description" rows="2" class="form-control"><?= e($f['description']) ?></textarea></div>
<div class="mb-2"><label class="form-label">Facilities (comma separated)</label><input name="facilities" class="form-control" value="<?= e($f['facilities']) ?>"></div>
<div class="row g-2 mb-2"><div class="col"><label class="form-label">Price / night</label><input type="number" step="100" min="1" name="price_per_night" class="form-control" required data-label="Price" value="<?= e($f['price_per_night']) ?>"></div>
<div class="col"><label class="form-label">Max guests</label><input type="number" min="1" max="10" name="max_guests" class="form-control" required data-label="Max guests" value="<?= (int)$f['max_guests'] ?>"></div></div>
<div class="mb-2"><label class="form-label">Image file (in assets/img)</label><input name="image" class="form-control" value="<?= e($f['image']) ?>"></div>
<div class="mb-3"><label class="form-label">Status</label><select name="room_status" class="form-select"><option <?= $f['room_status']==='Active'?'selected':'' ?>>Active</option><option <?= $f['room_status']==='Inactive'?'selected':'' ?>>Inactive</option></select></div>
<button class="btn btn-sea w-100">Save room</button><?php if ($edit) echo "<a href='rooms.php' class='btn btn-link w-100'>Cancel edit</a>"; ?></form></div></div></div>
<?php include '../includes/footer.php'; ?>
