<?php
session_start();
include '../../Config/connection.php';

if (!isset($_SESSION['id'])) {
    die(json_encode(['status' => 'error', 'message' => 'User not logged in.']));
}

$id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];

    // Validate the file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die(json_encode(['status' => 'error', 'message' => 'File upload error.']));
    }

    // Check file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        die(json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.']));
    }

    // Convert image to Base64
    $imageData = file_get_contents($file['tmp_name']);
    $base64Image = base64_encode($imageData);

    // Update database
    $sql = "UPDATE teachers SET PICTURE = ? WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $base64Image, $id);

    if ($stmt->execute()) {
        // Update session picture
        $_SESSION['picture'] = $base64Image;  // Update the session variable with the new profile picture

        echo json_encode(['status' => 'success', 'message' => 'Profile picture updated successfully.']);
        header('Location: index.php'); // Redirect to the desired page after updating
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update profile picture.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>
