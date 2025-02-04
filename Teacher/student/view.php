<?php
session_start(); // Start the session
include '../../Config/connection.php';
require_once '../../library/phpqrcode/qrlib.php'; 
include '../../Config/qrcode.php';

if(!isset($_GET['id'])){
    header("Location: index.php");
}

$studentId = $_GET['id'] ;


// SQL Query to select all data using JOINs
$sql = "SELECT 
            s.ID AS student_number, 
            s.FIRSTNAME AS student_first_name, 
            s.LASTNAME AS student_last_name, 
            s.MIDDLENAME AS student_middle_name, 
            s.ADDRESS AS student_address, 
            s.BIRTHDATE AS student_birthdate, 
            s.PICTURE AS student_picture, 
            s.GRADE_LEVEL AS student_grade_level, 
            s.SECTION AS student_section,
            g.ID AS guardian_id,
            g.FIRSTNAME AS guardian_first_name, 
            g.LASTNAME AS guardian_last_name, 
            g.MIDDLENAME AS guardian_middle_name, 
            g.CONTACT_NUMBER AS guardian_contact, 
            g.ADDRESS AS guardian_address, 
            g.PICTURE AS guardian_picture, 
            g.EMAIL AS guardian_email 
        FROM students s
        INNER JOIN student_guardian sg ON s.ID = sg.STUDENT_NUMBER
        INNER JOIN guardian g ON sg.PARENT_ID = g.ID
        WHERE s.ID = ?
        LIMIT 1";



$stmt = $conn->prepare($sql);

// Bind parameter (s for string) to the prepared statement
$stmt->bind_param("s", $studentId);  // Bind the parameter as a string

// Execute the query
$stmt->execute();

// Get the result
$result = $stmt->get_result();



// Check if the query was successful and if there is at least one row
if ($result->num_rows > 0) {
    // Fetch the first (and only) row
    $firstRow = $result->fetch_assoc(); // Fetch the first row
 
} else {
    header("Location: index.php");
    // No data found
    echo json_encode(['message' => 'No students found']);
}

$_SESSION['header'] = 'Student Details';


$sqlqrcode = "SELECT * FROM student_qrcode WHERE STUDENT_NUMBER = ? LIMIT 1";

// Prepare the statement
$stmtqrcode = $conn->prepare($sqlqrcode);

// Bind parameter (i for integer)
$stmtqrcode->bind_param("i", $studentId);
// Execute the query
$stmtqrcode->execute();

// Get the result
$resultqrcode = $stmtqrcode->get_result();

// Fetch the row
$qrResultRow = $resultqrcode->fetch_assoc();
$qrImageB64 = '';
// Check if a row was found
if ($qrResultRow) {
    // Access the data from the row
    $qr_code = $qrResultRow['QR_CODE'];
    $qrImageB64 = getQRCodeImage($qrResultRow['QR_CODE']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="create.css" rel="stylesheet"/>
    <title>Edit Student</title>
</head>
<body>
    <?php include '../shared/sidebar.php' ?>
    <style>
        
.main-header{
    width: calc(100vw - 256px) !important;
}

.main-wrapper-body {
    max-width: calc(100% - 24px) !important;
}

body{
    overflow-x: hidden;
}
    </style>
    <div class="container-main">
        <div class="main-wrapper-body card-container"> 
            <hr>
            <div class="row">
                <div class="col-2">
                    <img src="data:image/png;base64,<?=$firstRow['student_picture']?>" alt="" class="user-img" id="imgUser">
                    <?php if ($qrResultRow){ ?>
                        <img  src="data:image/png;base64,<?=$qrImageB64?>" class="user-img"  /> 
                    <?php 
                     }
                    ?>
                </div>
                <div class="col-10">
                    <div class="form-group">
                        <label for="txtFirstName">First Name</label>
                        <input type="text" class="form-control" id="txtFirstName" value="<?=$firstRow['student_first_name']?>" name="frmGroup" aria-describedby="txtName" placeholder="Enter First Name">
                        <small ident="FirstName" name="frmGroup" class="form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label for="txtMiddleName">Middle Name</label>
                        <input type="text" class="form-control" name="frmGroup" id="txtMiddleName" value="<?=$firstRow['student_middle_name']?>" aria-describedby="txtName" placeholder="Enter First Name">
                        <small ident="MiddleName" name="frmGroup" class="form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label for="txtLastName">Last Name</label>
                        <input type="text" class="form-control" id="txtLastName" name="frmGroup" value="<?=$firstRow['student_last_name']?>" aria-describedby="txtName" placeholder="Enter First Name">
                        <small  ident="LastName" name="frmGroup" class="form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label for="txtBirthDate">Date of Birth</label>
                        <input type="date" class="form-control" id="txtBirthDate" name="frmGroup" value="<?=$firstRow['student_birthdate']?>" aria-describedby="txtName" placeholder="Enter First Name">
                        <small  ident="BirthDate" name="frmGroup" class="form-text text-danger"></small>
                    </div>
                    <script>
                        $(document).ready(function(){
                            $("#txtGradeLevel").val("<?=$firstRow['student_grade_level']?>").change();
                        })
                    </script>
                    <div class="form-group">
                        <label for="txtGradeLevel">Grade Level</label>
                        <select name="frmGroup" id="txtGradeLevel" class="form-control">
                            <option value="NURSERY">NURSERY</option>
                            <option value="KINDERGARTEN">KINDERGARTEN</option>
                            <option value="GRADE 1">GRADE 1</option>
                            <option value="GRADE 2">GRADE 2</option>
                            <option value="GRADE 3">GRADE 3</option>
                            <option value="GRADE 4">GRADE 4</option>
                            <option value="GRADE 5">GRADE 5</option>
                            <option value="GRADE 6">GRADE 6</option>
                        </select>
                        <small ident="GradeLevel" name="frmGroup" class="form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label for="txtSection">Section</label>
                        <input type="text" class="form-control" id="txtSection" name="frmGroup" value="<?=$firstRow['student_section']?>" aria-describedby="txtName" placeholder="Enter First Name">
                        <small  ident="Section" name="frmGroup" class="form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label for="txtAddress">Address</label>
                        <textarea id="txtAddress" class="form-control" name="frmGroup" row="3"><?=$firstRow['student_address']?></textarea>
                        <small ident="Address" name="frmGroup" class="form-text text-danger"></small>
                    </div>
                    <br/>
                    <br>
                </div>
                <h3>Guardian Details</h3>
                <hr>
                <div class="col-2">
                    <img src="data:image/png;base64,<?=$firstRow['guardian_picture']?>" alt="" class="user-img" id="imgGuardianUser">
                </div>
                <div class="col-10">
                    <div class="form-group">
                        <label for="txtGuardianFirstName">First Name</label>
                        <input type="text" class="form-control" id="txtGuardianFirstName" name="frmGroup" value="<?=$firstRow['guardian_first_name']?>" aria-describedby="txtName" placeholder="Enter First Name">
                        <small ident="GuardianFirstName" name="frmGroup" class="form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label for="txtGuardianMiddleName">Middle Name</label>
                        <input type="text" class="form-control" name="frmGroup" id="txtGuardianMiddleName" value="<?=$firstRow['guardian_middle_name']?>" aria-describedby="txtName" placeholder="Enter First Name">
                        <small ident="GuardianMiddleName" name="frmGroup" class="form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label for="txtGuardianLastName">Last Name</label>
                        <input type="text" class="form-control" id="txtGuardianLastName" name="frmGroup" value="<?=$firstRow['guardian_last_name']?>" aria-describedby="txtName" placeholder="Enter First Name">
                        <small  ident="GuardianLastName" name="frmGroup" class="form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label for="txtGuardianContactNumber">Contact Number</label>
                        <input type="number" class="form-control" id="txtGuardianContactNumber" name="frmGroup" value="<?=$firstRow['guardian_contact']?>" aria-describedby="txtName" placeholder="Enter First Name">
                        <small  ident="GuardianContactNumber" name="frmGroup" class="form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label for="txtGuardianAddress">Address</label>
                        <textarea id="txtGuardianAddress" class="form-control" name="frmGroup" row="3"><?=$firstRow['guardian_address']?></textarea>
                        <small ident="GuardianAddress" name="frmGroup" class="form-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label for="txtGuardianEmail">Email</label>
                        <input type="Email" class="form-control" id="txtGuardianEmail" name="frmGroup" value="<?=$firstRow['guardian_email']?>" aria-describedby="txtName" placeholder="Enter First Name">
                        <small  ident="GuardianEmail" name="frmGroup" class="form-text text-danger"></small>
                    </div>
                    </div>
                    <div style="display:flex;justify-content: end;align-items:end;width:100%;">
                    <a class="btn btn-primary" type="button" href="index.php" id="btnCancel" style="margin-right:12px;width:160px;">Back</a>
                    </div>
                    <br>
                    <hr>
                </div>
            </div>
        </div>
    </div>
</body>
<script>
    $(document).ready(function(){
        $("input[name='frmGroup']").attr("disabled", true);
        $("select[name='frmGroup']").attr("disabled", true);
        $("textarea[name='frmGroup']").attr("disabled", true);
        $("small[name='frmGroup']").html("");
    })
</script>

</html>
<?php
// Close the connection
$conn->close();
 ?>