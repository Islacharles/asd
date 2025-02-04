<?php
include '../../Config/api-config.php';
include '../../Config/connection.php';

$search = $_GET['search'] ?? '';

$query = "SELECT 
            g.ID as 'PARENT_NUMBER',
            CONCAT(FIRSTNAME, ' ', LASTNAME) AS FullName, 
            CONCAT(FIRSTNAME, LASTNAME) AS defaultPword,
            g.CONTACT_NUMBER, 
            g.EMAIL,
            g.PICTURE,
              (SELECT count(*) FROM student_guardian sg
          LEFT JOIN 
            students s on s.ID = sg.STUDENT_NUMBER
          WHERE s.STATUS = b'1' AND sg.PARENT_ID = g.ID) AS TOTAL_STUDENTS
          FROM 
            guardian as g
          WHERE 
            CONCAT(FIRSTNAME, ' ', LASTNAME) LIKE ?
            AND (SELECT count(*) FROM student_guardian sg
          LEFT JOIN 
            students s on s.ID = sg.STUDENT_NUMBER
          WHERE s.STATUS = b'1' AND sg.PARENT_ID = g.ID) <> '0'";

$stmt = $conn->prepare($query);
$searchParam = "%$search%";
$stmt->bind_param("s", $searchParam);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
$stmt->close();
$conn->close();
