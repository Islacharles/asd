<?php
include '../../Config/connection.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'];
$title = $data['title'];
$image = $data['image'] ?? null;

if ($image) {
    $sql = "UPDATE events SET TITLE = ?, IMAGE = ? WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $title, $image, $id);
} else {
    $sql = "UPDATE events SET TITLE = ? WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $title, $id);
}

if ($stmt->execute()) {
    echo json_encode(["message" => "Blog updated successfully!"]);
} else {
    echo json_encode(["message" => "Failed to update blog."]);
}
?>
