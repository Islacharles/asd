<?php
include '../../Config/api-config.php';
include '../../Config/connection.php'; // openning connection

// Get the search term (student name) and date from GET request
$search = $_GET['search'] ?? '';
$date = $_GET['date'] ?? ''; // Date in format YYYY-MM-DD

// SQL query to search attendance based on student name and created date
$sql = "
    SELECT 
        a.ID, 
        s.ID as student_number,
        s.FIRSTNAME, 
        s.LASTNAME, 
        a.CREATED_DT, 
        a.TIME_IN, 
        a.TIME_OUT,
        s.GRADE_LEVEL,
        s.SECTION,
        ap.FIRSTNAME as authorize_firstname,
        ap.LASTNAME as authorize_lastname
    FROM 
        student_attendance a
    INNER JOIN 
        students s ON a.STUDENT_ID = s.ID
    LEFT JOIN
    (SELECT ID,FIRSTNAME,LASTNAME FROM authorize_person UNION SELECT ID,FIRSTNAME,LASTNAME FROM guardian) ap ON ap.ID = a.AUTHORIZE_ID
    WHERE 
        (s.FIRSTNAME LIKE ? OR s.LASTNAME LIKE ?)
    AND 
        a.CREATED_DT = ?
";

// Prepare the statement
$stmt = $conn->prepare($sql);

// Add wildcards for LIKE operator
$searchTermWithWildcards = "%" . $search . "%";  // Adding '%' for LIKE operator

// Bind parameters to the prepared statement
$stmt->bind_param("sss", $searchTermWithWildcards, $searchTermWithWildcards, $date);

// Execute the query
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Initialize an array to hold the response data
$response = [];

// Fetch all the rows and add them to the response array
while ($row = $result->fetch_assoc()) {
    $response[] = array(
        'attendance_id' => $row["ID"],
        'student_name' => $row["FIRSTNAME"] . " " . $row["LASTNAME"],
        'created_date' => $row["CREATED_DT"],
        'grade_level' => $row["GRADE_LEVEL"],
        'student_number' => $row["student_number"],
        'section' => $row["SECTION"],
        'time_in' => $row["TIME_IN"] ,
        'time_out' => $row["TIME_OUT"],
        'authorize_fullname' => $row['authorize_firstname'] . ' ' . $row['authorize_lastname'] 
    );
}

// Respond with the JSON data
echo json_encode($response, JSON_PRETTY_PRINT);

// Close the connection
$stmt->close();
$conn->close();
?>
