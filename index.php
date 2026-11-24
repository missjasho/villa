<?php require 'config.php'; $title = 'Sunrise Cove Villa, Mirissa';
$featured = $pdo->query("SELECT * FROM rooms WHERE room_status='Active' ORDER BY price_per_night DESC LIMIT 3")->fetchAll();
include 'includes/header.php'; ?>
<section class="hero mb-5"><div>
  <h1>Wake up to the Indian Ocean in Mirissa</h1>
  <p class="lead">A small family-run villa 200 metres from the beach, with whale-watching departures, surf breaks and fresh seafood nearby.</p>
  <a href="rooms.php" class="btn btn-sun btn-lg">Book now</a>
  <a href="search.php" class="btn btn-outline-light btn-lg">Check availability</a>
</div></section>
<section class="row g-4 mb-5">
  <?php foreach ([['Beachfront location','A five-minute walk to Mirissa Beach and Coconut Tree Hill.'],['Pool and garden','Saltwater pool, hammocks and a shaded breakfast terrace.'],['Local breakfast','Hoppers, fruit and Ceylon tea included with every room.'],['Easy transfers','Airport and Galle train station pick-ups on request.']] as $f): ?>
  <div class="col-md-6 col-lg-3"><div class="feature"><h2 class="h6"><?= e($f[0]) ?></h2><p class="small mb-0"><?= e($f[1]) ?></p></div></div>
  <?php endforeach; ?>
</section>
<h2 class="mb-3">Our rooms</h2>
<div class="row g-4 mb-5"><?php foreach ($featured as $r) echo room_card($r); ?></div>
<h2 class="mb-3">Find us</h2>
<div class="ratio ratio-21x9 mb-4"><iframe title="Map of Mirissa" loading="lazy" src="https://www.openstreetmap.org/export/embed.html?bbox=80.42%2C5.93%2C80.48%2C5.96&amp;layer=mapnik"></iframe></div>
<?php include 'includes/footer.php'; ?>
