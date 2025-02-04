<?php
session_start();
include '../../Config/connection.php';
include '../../Config/encryption.php'; // Include encryption library

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in. Please log in first.']);
    exit;
}

$id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch current password
    $sql = "SELECT PASSWORD FROM teachers WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo json_encode(['status' => 'error', 'message' => 'User not found.']);
        exit;
    }

    // Decrypt the current password stored in the database
    $decryptedPassword = decrypt($user['PASSWORD']);
    if ($current_password !== $decryptedPassword) {
        echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect.']);
        exit;
    }

    // Check if new password matches confirmation
    if ($new_password !== $confirm_password) {
        echo json_encode(['status' => 'error', 'message' => 'New passwords do not match.']);
        exit;
    }

    // Encrypt the new password
    $encryptedPassword = encrypt($new_password);

    // Update the password in the database
    $update_sql = "UPDATE teachers SET PASSWORD = ? WHERE ID = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $encryptedPassword, $id);

    if ($update_stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Password updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Could not update the password.']);
    }
}
?>
