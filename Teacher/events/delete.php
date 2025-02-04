<?php
include '../../Config/connection.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'];

$sql = "DELETE FROM events WHERE ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["message" => "Blog deleted successfully!"]);
} else {
    echo json_encode(["message" => "Failed to delete blog."]);
}
?>
