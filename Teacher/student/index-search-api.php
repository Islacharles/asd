
<?php
include '../../Config/api-config.php';
include '../../Config/connection.php'; // openning connection

$search = $_GET['search'] ?? '';

// SQL query
$sql = "SELECT 
            s.ID as 'STUDENT_NUMBER', 
            s.FIRSTNAME, 
            s.LASTNAME, 
            s.GRADE_LEVEL, 
            s.SECTION, 
            g.FIRSTNAME as 'guardian_first_name', 
            g.LASTNAME as 'guardian_last_name', 
            g.CONTACT_NUMBER, 
            g.EMAIL,
            s.PICTURE
        FROM 
            students s
        INNER JOIN 
            student_guardian sg ON s.ID = sg.STUDENT_NUMBER
        INNER JOIN 
            guardian g ON sg.PARENT_ID = g.ID
        WHERE
            s.STATUS = b'1' AND
            CONCAT(s.FIRSTNAME, s.LASTNAME, s.MIDDLENAME, g.FIRSTNAME, g.LASTNAME, s.ID) LIKE ?";

// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind parameter (s for string) to the prepared statementht 
$searchTermWithWildcards = "%" . $search . "%";  // Adding '%' for LIKE operator
$stmt->bind_param("s", $searchTermWithWildcards);  // Bind the parameter as a string

// Execute the query
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Initialize an array to hold the response data
$response = [];

// Fetch all the rows and add them to the response array
while ($row = $result->fetch_assoc()) {
    $response[] = array(
        'student_number' => $row["STUDENT_NUMBER"],
        'student_name' => $row["FIRSTNAME"] . " " . $row["LASTNAME"],
        'grade_level' => $row["GRADE_LEVEL"],
        'section' => $row["SECTION"],
        'guardian_name' => $row["guardian_first_name"] . " " . $row["guardian_last_name"],
        'guardian_contact' => $row["CONTACT_NUMBER"],
        'guardian_email' => $row["EMAIL"],
        'picture' => $row["PICTURE"]
    );
}

// Respond with the JSON data
echo json_encode($response, JSON_PRETTY_PRINT);

// Close the connection
$stmt->close();
$conn->close();

?>