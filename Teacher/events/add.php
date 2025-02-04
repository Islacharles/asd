<?php
include '../../Config/connection.php';

$data = json_decode(file_get_contents("php://input"), true);
$title = $data['title'];
$image = $data['image'];

$sql = "INSERT INTO events (TITLE, IMAGE) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $title, $image);

if ($stmt->execute()) {
    echo json_encode(["message" => "Blog added successfully!"]);
} else {
    echo json_encode(["message" => "Failed to add blog."]);
}
?>
