<?php

date_default_timezone_set('Asia/Manila');
include '../../Config/api-config.php';
include '../../Config/connection.php'; // Open the database connection
include '../../Config/notification-api.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

// Collect POST data
$attendanceId = $_POST['attendance_id'] ?? null;
$authorizeId = $_POST['authorize_id'] ?? null;
$parentId = $_POST['parent_id'] ?? null;
$studentId = $_POST['student_id'] ?? null;
$studentName = $_POST['student_name'] ?? null;
$authorizeName = $_POST['authorize_name'] ?? null;

// Validate required fields
if (empty($attendanceId) || empty($authorizeId)) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'attendance_id and authorize_id are required.']);
    exit();
}

// Get the current date and time
$currentDateTime = date('Y-m-d H:i:s');

// Prepare the SQL query to update the AUTHORIZE_ID and set the TIME_OUT
$sql = "UPDATE student_attendance 
        SET AUTHORIZE_ID = ?, TIME_OUT = ? 
        WHERE ID = ?";

// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind parameters to the prepared statement
$stmt->bind_param("ssi", $authorizeId, $currentDateTime, $attendanceId);

// Execute the statement
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        
        $title = 'Attendance';
        $redirection = '../student/view.php?id=' . $studentId;
        $description = $studentName . ' has been Time out on school with - ' . $authorizeName;
        
        // Insert into notifications table
        $sqlNotif = "INSERT INTO notifications (USER_ID, TITLE, REDIRECTION, DESCRIPTION, STUDENT_ID) VALUES (?, ?, ?, ?, ?)";
        $stmtNotif = $conn->prepare($sqlNotif);
        
        // Bind parameters and execute
        $stmtNotif->bind_param("isssi", $parentId, $title, $redirection, $description, $studentId);
        $stmtNotif->execute();
        
        // Close the statement and connection
        $stmtNotif->close();

        echo json_encode(['status' => 'success', 'message' => 'Authorize personel successfully in attendance record.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No record found with the given authorize person.']);
    }
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Failed to update the AUTHORIZE_ID and TIME_OUT.']);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
