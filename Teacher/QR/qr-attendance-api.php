<?php

date_default_timezone_set('Asia/Manila');
include '../../Config/api-config.php';
include '../../Config/connection.php'; 

if (!isset($_POST['qrcode_value']) && !isset($_POST['authorize_id'])) {
    exit();
}

$qrcode_value = $_POST['qrcode_value'] ?? '';
$authorize_id = $_POST['authorize_id'] ?? null;
$attendance_id = $_POST['attendance_id'] ?? null;
$student_id = $_POST['student_id'] ?? null;

$conn->query("SET time_zone = '+08:00'");

if ($authorize_id && $attendance_id && $student_id) {
    // UPDATE TIME_OUT WHEN AUTHORIZED PERSON IS SELECTED
    $sqlUpdateTimeout = "UPDATE student_attendance 
                         SET TIME_OUT = NOW(), AUTHORIZED_PERSON_ID = ? 
                         WHERE ID = ? AND STUDENT_ID = ? AND TIME_OUT IS NULL";
    $stmtUpdateTimeout = $conn->prepare($sqlUpdateTimeout);
    $stmtUpdateTimeout->bind_param("sss", $authorize_id, $attendance_id, $student_id);

    if ($stmtUpdateTimeout->execute()) {
        echo json_encode(["status" => "success", "message" => "Time-out updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update time-out"]);
    }

    $stmtUpdateTimeout->close();
    $conn->close();
    exit();
}

// FETCH STUDENT INFO BASED ON QR CODE
$sql = "SELECT 
            s.ID AS student_id,
            s.FIRSTNAME AS student_firstname,
            s.LASTNAME AS student_lastname,
            s.GRADE_LEVEL,
            s.SECTION,
            s.PICTURE,
            sq.QR_CODE,
            CURRENT_DATE() as CurrentDateNow,
            sg.PARENT_ID
        FROM 
            students s
        INNER JOIN 
            student_qrcode sq ON s.ID = sq.STUDENT_NUMBER
        LEFT JOIN
            student_guardian sg ON sg.STUDENT_NUMBER = s.ID
        WHERE 
            sq.QR_CODE = ? 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $qrcode_value);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $students = $row;
    $studentId = $row['student_id'];

    // CHECK IF TIME_IN ALREADY EXISTS FOR TODAY
    $sqlCheckAttendance = "SELECT * FROM student_attendance 
                           WHERE STUDENT_ID = ? 
                           AND CREATED_DT = CURRENT_DATE() 
                           ORDER BY TIME_IN DESC 
                           LIMIT 1";
    $stmtCheckAttendance = $conn->prepare($sqlCheckAttendance);
    $stmtCheckAttendance->bind_param("s", $studentId);
    $stmtCheckAttendance->execute();
    $resultCheckAttendance = $stmtCheckAttendance->get_result();

    if ($resultCheckAttendance->num_rows > 0) {
        $rowAttendance = $resultCheckAttendance->fetch_assoc();
        $students['attendance'] = $rowAttendance;

        if (!is_null($rowAttendance['TIME_OUT'])) {
            // IF TIME_OUT EXISTS, PREVENT ANOTHER SCAN FOR TODAY
            echo json_encode(["status" => "error", "message" => "QR code already used today. Please try again tomorrow."]);
            exit();
        } else {
            // ALLOW ONLY TIME_OUT IF TIME_IN EXISTS AND TIME_OUT IS NULL
            $students['action'] = "TIME OUT";
        }
    } else {
        // INSERT TIME_IN IF NO RECORD EXISTS FOR TODAY
        $sqlInsert = "INSERT INTO student_attendance (STUDENT_ID, CREATED_DT, TIME_IN) VALUES (?, CURRENT_DATE(), NOW())";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param("s", $studentId);
        $stmtInsert->execute();
        $stmtInsert->close();
        $students['action'] = "TIME IN";
    }

    $stmtCheckAttendance->close();
}

// FETCH AUTHORIZED PERSONS
$sqlAuthorize = "SELECT 
        g.ID AS person_id,
        g.FIRSTNAME AS first_name,
        g.LASTNAME AS last_name,
        g.CONTACT_NUMBER AS contact_number,
        g.ADDRESS AS address,
        g.EMAIL AS email,
        'guardian' AS role,
        g.PICTURE
    FROM 
        guardian g
    INNER JOIN 
        student_guardian sg ON g.ID = sg.PARENT_ID
    WHERE 
        sg.STUDENT_NUMBER = ?
    UNION
    SELECT 
        ap.ID AS person_id,
        ap.FIRSTNAME AS first_name,
        ap.LASTNAME AS last_name,
        ap.CONTACT_NUMBER AS contact_number,
        ap.ADDRESS AS address,
        NULL AS email,
        'authorize_person' AS role,
        ap.PICTURE
    FROM 
        authorize_person ap
    INNER JOIN
        student_guardian sg ON ap.PARENT_ID = sg.PARENT_ID
    WHERE 
        sg.STUDENT_NUMBER = ?";

$stmtAuthorize = $conn->prepare($sqlAuthorize);
$stmtAuthorize->bind_param("ii", $studentId, $studentId);
$stmtAuthorize->execute();
$resultAuthorize = $stmtAuthorize->get_result();

while ($rowAuthorize = $resultAuthorize->fetch_assoc()) {
    $students['authorizePersons'][] = $rowAuthorize;
}

$stmt->close();
$stmtAuthorize->close();
$conn->close();

echo json_encode($students, JSON_PRETTY_PRINT);

?>