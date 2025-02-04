<?php
include '../../Config/connection.php';

$sql = "SELECT * FROM events WHERE `STATUS` = b'1'";
$result = $conn->query($sql);

$blogs = [];
while ($row = $result->fetch_assoc()) {
    $blogs[] = [
        'id' => $row['ID'],
        'title' => $row['TITLE'],
        'image' => $row['IMAGE'],
        'description' => $row['DESCRIPTION']
    ];
}

echo json_encode($blogs);
?>
