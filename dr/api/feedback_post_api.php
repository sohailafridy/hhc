<?php
include "../config.php";
header("Content-Type: application/json");

$entity_id = $_POST['entity_id'];
$comment = $_POST['comment'];
$email = $_POST['email'];
$commenter_name = $_POST['commenter_name'];
$commenter_gmail = $_POST['commenter_gmail'];
$stars = $_POST['stars'];
$status = 1;

$sql = "INSERT INTO feedback
(entity_id, comment, email, commenter_name, commenter_gmail, stars, status, created_at, updated_at)
VALUES
('$entity_id', '$comment', '$email', '$commenter_name', '$commenter_gmail', '$stars', '$status', NOW(), NOW())";

if(mysqli_query($con, $sql)){
    echo json_encode([
        "status" => true,
        "message" => "Feedback submitted successfully"
    ]);
}else{
    echo json_encode([
        "status" => false,
        "message" => mysqli_error($con)
    ]);
}
?>