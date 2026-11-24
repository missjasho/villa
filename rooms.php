<?php require 'config.php'; $title = 'Rooms | Sunrise Cove Villa';
$type = $_GET['type'] ?? ''; $max = (int)($_GET['max_price'] ?? 0); $fac = trim($_GET['facility'] ?? '');
$sql = "SELECT * FROM rooms WHERE room_status='Active'"; $p = [];
if ($type !== '') { $sql .= " AND room_type=?"; $p[] = $type; }
if ($max > 0) { $sql .= " AND price_per_night<=?"; $p[] = $max; }
if ($fac !== '') { $sql .= " AND facilities LIKE ?"; $p[] = "%$fac%"; }
$st = $pdo->prepare($sql." ORDER BY price_per_night"); $st->execute($p); $rooms = $st->fetchAll();
include 'includes/header.php'; ?>
<h1 class="mb-3">Rooms and accommodation</h1>
<form class="row g-2 mb-4 filter-bar" method="get">
  <div class="col-md-3"><select name="type" class="form-select"><option value="">All room types</option>
    <?php foreach (['Standard','Deluxe','Family','Suite'] as $t) echo "<option ".($type===$t?'selected':'').">$t</option>"; ?></select></div>
  <div class="col-md-3"><input type="number" name="max_price" min="0" class="form-control" placeholder="Max price (LKR)" value="<?= $max ?: '' ?>"></div>
  <div class="col-md-3"><input name="facility" class="form-control" placeholder="Facility, e.g. Pool" value="<?= e($fac) ?>"></div>
  <div class="col-md-3"><button class="btn btn-sea w-100">Filter rooms</button></div>
</form>
<div class="row g-4"><?php foreach ($rooms as $r) echo room_card($r); ?>
<?php if (!$rooms) echo "<p>No rooms match your filters. Try a higher price or a different type.</p>"; ?></div>
<?php include 'includes/footer.php'; ?>
