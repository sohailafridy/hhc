<?php
// POST api/doctor-review.php
// Body (JSON ya form-data): doctor_id, name, email, stars (1-5), comment
require_once __DIR__ . '/_common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);

// Naya review foran show ho (1) ya admin approval ke baad (0)
const REVIEW_DEFAULT_STATUS = 1;

$in = request_body();
$doctorId = (int)($in['doctor_id'] ?? 0);
$name     = trim($in['name'] ?? '');
$email    = trim($in['email'] ?? '');
$stars    = (int)($in['stars'] ?? 0);
$comment  = trim($in['comment'] ?? '');

// ---- validation (limits feedback table ke column sizes se match karti hain) ----
if ($doctorId <= 0)                                   json_error('doctor_id is required');
if ($name === '' || mb_strlen($name) > 30)            json_error('Name is required (max 30 characters)');
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 30)
                                                      json_error('Valid email is required (max 30 characters)');
if ($stars < 1 || $stars > 5)                         json_error('Rating must be between 1 and 5');
if ($comment === '' || mb_strlen($comment) > 100)     json_error('Review is required (max 100 characters)');

// ---- doctor exists? ----
$d = db_one($conn,
    "SELECT d.user_id, d.entity_id FROM doctors d
     JOIN users u ON u.user_id = d.user_id
     WHERE d.doctor_id = ? AND u.status = 1 AND d.approve = 1",
    'i', [$doctorId]);
if (!$d) json_error('Doctor not found', 404);

$userId   = (int)$d['user_id'];
$entityId = (int)$d['entity_id'];

// ---- duplicate: same email ek doctor ko dobara review na de ----
$dup = db_one($conn,
    "SELECT feedback_id FROM feedback WHERE user_id = ? AND commenter_gmail = ? LIMIT 1",
    'is', [$userId, $email]);
if ($dup) json_error('You have already reviewed this doctor', 409);

// ---- insert ----
$status = REVIEW_DEFAULT_STATUS;
$st = $conn->prepare(
    "INSERT INTO feedback (entity_id, user_id, comment, email, commenter_name, commenter_gmail, stars, status, created_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
if (!$st) json_error('Query error', 500);
$st->bind_param('iissssii', $entityId, $userId, $comment, $email, $name, $email, $stars, $status);

if (!$st->execute()) json_error('Could not save review', 500);
$st->close();

json_out([
    'success' => true,
    'message' => $status === 1 ? 'Thank you! Your review has been submitted.'
                               : 'Thank you! Your review will appear after approval.',
], 201);