<?php // AJAX endpoint: returns JSON availability for a room and date range.
require 'config.php'; header('Content-Type: application/json');
$room = (int)($_GET['room_id'] ?? 0); $in = $_GET['check_in'] ?? ''; $out = $_GET['check_out'] ?? '';
[$n, $err] = parse_dates($in, $out);
if ($err) { echo json_encode(['ok' => false, 'message' => $err]); exit; }
$ok = is_available($pdo, $room, $in, $out);
echo json_encode(['ok' => $ok, 'message' => $ok ? 'Room is available for these dates.' : 'Sorry, this room is already booked for these dates.']);
