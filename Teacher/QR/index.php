<?php
session_start(); // Start the session
include '../../Config/connection.php';
$_SESSION['header'] = 'QR Scan Attendance';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="index.css" rel="stylesheet"/> <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode/minified/html5-qrcode.min.js"></script>

    <title>Teacher Details</title>
</head>
<body>
    <?php include '../shared/sidebar.php' ?>
    <div class="container-main">
        <div class="main-wrapper-body">  
            <div class="row">
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card card-container">
                        <div id="reader" style="width: 100%; height: 500px;"></div>
                    </div>
                </div>
                <div class="col-lg-8 col-md-6 col-sm-12" id="student-content">
                <div class="card card-container">
    <div class="row">
        <div class="col-4">
            <img src="" alt="" class="user-img" id="imgUser">
        </div>
        <div class="col-8">
            <div class="form-group">
                <label for="txtFirstName">Full Name</label>
                <input type="text" class="form-control" id="txtfullname" name="frmGroup" disabled value="" aria-describedby="txtName" placeholder="Enter First Name">
            </div>
            <div class="form-group">
                <label for="txtGrade">Grade</label>
                <input type="text" class="form-control" id="txtGrade" name="frmGroup" disabled value="" aria-describedby="txtName" placeholder="Enter First Name">
            </div>
            <div class="form-group">
                <label for="txtSection">Section</label>
                <input type="text" class="form-control" id="txtSection" name="frmGroup" disabled value="" aria-describedby="txtName" placeholder="Enter First Name">
            </div>
            <div class="form-group">
                <label for="txtBirthDate">Date</label>
                <input type="date" class="form-control" id="txtBirthDate" name="frmGroup" disabled value="" aria-describedby="txtName" placeholder="Enter First Name">
            </div>
            <br/>
            <h1 class="text-success"><b id="txtTimeAction"></b></h1>
            <br>
            <h4>Select Authorized Person</h4>
            <hr>
            <div class="row" id="authorizePersons">
                <!-- Select authorized persons will be appended here -->
            </div>
            <div class="form-group">
            <button type="button" id="cancelButton" class="btn btn-danger">Cancel</button>
            </div>
        </div> 
    </div>
</div>

                </div>
            </div>
        </div>
    </div>
    <script>
    // Initialize the QR scanner
    let qrCodeScanner;
    let qrStatus = true;
    let lastScannedCode = null;
    let scanCooldown = 30000; // 30 seconds delay for scanned QR codes
    let selectedAuthorizePersonId = null; // Track selected authorized person
    let selectedAttendanceId = null; // Track selected attendance

    $(document).ready(function () {
        reinitializeQRCode();
        $("#student-content").hide();
        $("#backButton").hide(); // Initially hide the back button
    });

    // On successful QR code scan
    function onScanSuccess(decodedText, decodedResult) {
        if (qrStatus && decodedText !== lastScannedCode) {
            qrStatus = false;
            lastScannedCode = decodedText; // Store last scanned QR code
            $('#result').text(decodedText);
            console.log('QR Code scanned: ', decodedText);
            onQRScanAttendance(decodedText);

            setTimeout(() => {
                qrStatus = true;
                lastScannedCode = null; // Reset scanned code after cooldown
            }, scanCooldown);
        }
    }

    function reinitializeQRCode() {
        qrCodeScanner = new Html5QrcodeScanner(
            "reader",
            { fps: 5, qrbox: 300 },
            false
        );
        qrCodeScanner.render(onScanSuccess, onScanFailure);
    }

    function onScanFailure(error) {
        // Handle QR scan error if needed
    }

    function onQRScanAttendance(qrtext) {
    $.ajax({
        url: 'qr-attendance-api.php',
        type: 'POST',
        data: { qrcode_value: qrtext },
        success: function (response) {
            console.log(response);

            if (response.status === "cooldown") {
                alert(response.message); // Show cooldown message
                return;
            }

            // Fill student details
            $("#txtfullname").val(response.student_firstname + ' ' + response.student_lastname);
            $("#txtGrade").val(response.GRADE_LEVEL);
            $("#txtSection").val(response.SECTION);
            $("#txtBirthDate").val(response.CurrentDateNow);
            $("#imgUser").attr("src", `data:image/png;base64,${response.PICTURE}`);

            // Display attendance information
            if (response.action === "TIME IN") {
                const timeIn = new Date(response.attendance_time).toLocaleString();
                $("#txtTimeAction").html(`<span style="color: green; font-size: 18px;"><strong>Time In`);
                $("#cancelButton").hide(); // Hide cancel button for Time In
            } else if (response.action === "TIME OUT") {
                const timeOut = new Date(response.attendance_time).toLocaleString();
                $("#txtTimeAction").html(`<span style="color: red; font-size: 18px;"><strong>Time Out`);
                $("#cancelButton").show(); // Show cancel button for Time Out
            } else {
                $("#txtTimeAction").html("No attendance record for today.");
                $("#cancelButton").hide(); // Hide cancel button if no record
            }

            // If the student is clocking out, show authorized persons to select
            if (response.action === "TIME OUT") {
                $("#student-content").find('h4').show();
                $("#authorizePersons").html(""); // Clear previous list
                response?.authorizePersons?.forEach(obj => {
                    $("#authorizePersons").append(`
                        <div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="card-body">
                                <img src="data:image/png;base64,${obj.PICTURE}" alt=""/>
                                <div class="details">
                                    <p>${obj.first_name} ${obj.last_name}</p>
                                    <small>${obj.role}</small>
                                </div>
                                <div class="action">
                                    <button name="selectPerson" authName='${obj.first_name} ${obj.last_name}' 
                                            studentName='${response.student_firstname} ${response.student_lastname}' 
                                            studentId='${response.student_id}' parentId='${response.PARENT_ID}' 
                                            ident="${obj.person_id}" attendance-ident="${response?.attendance?.ID}" 
                                            class="btn btn-primary">
                                        <i class="fas fa-check" style="display: none;"></i> Select
                                    </button>
                                </div>
                            </div>
                        </div>
                    `);
                });
            }

            $("#student-content").show();
            $("#backButton").show();
        },
        error: function (xhr, status, error) {
            console.log(error);
        }
    });
}

$(document).ready(function () {
    $("#cancelButton").hide(); // Hide initially

    // Handle cancel button click
    $("#cancelButton").click(function () {
        clearStudentDetails();
        $("#student-content").hide();
        $("#backButton").hide();
        $("#cancelButton").hide(); // Hide the cancel button after clicking
    });
});




    $(document).ready(function () {
        // Handle selection of an authorized person
        $(document).on('click', "button[name='selectPerson']", function () {
            const authId = $(this).attr('ident');
            const attendanceId = $(this).attr('attendance-ident');
            const parentId = $(this).attr('parentId');
            const studentId = $(this).attr('studentId');
            const studentName = $(this).attr('studentName');
            const authName = $(this).attr('authName');

            selectedAttendanceId = attendanceId;
            selectedAuthorizePersonId = authId;

            const confirmation = confirm(`Are you sure you want to select ${authName} as the authorized person for ${studentName}?`);

            if (confirmation) {
                $(this).html('<i class="fas fa-check"></i>'); // Show check sign
                $(this).prop('disabled', true); // Disable button
                $(this).toggleClass('selected');

                $.ajax({
                    url: 'select-person-api.php',
                    type: 'POST',
                    data: {
                        attendance_id: attendanceId,
                        authorize_id: authId,
                        parent_id: parentId,
                        student_id: studentId,
                        student_name: studentName,
                        authorize_name: authName
                    },
                    success: function (response) {
                        console.log(response);
                        if (response.status === 'success') {
                            alert(response.message);
                            $("#student-content").hide();
                            $("#backButton").hide(); // Hide back button
                            clearStudentDetails();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.log(error);
                    }
                });
            }
        });

        // Back button functionality
        $("#backButton").click(function () {
            $("#student-content").hide();
            $("#backButton").hide();
            clearStudentDetails();
        });
    });


    $(document).ready(function () {
    // Handle cancel button click
    $("#cancelButton").click(function () {
        // Clear all selections and reset form
        clearStudentDetails();

        // Optionally, you can also hide the student content and back button
        $("#student-content").hide();
        $("#backButton").hide();
    });
});

// This function already resets form fields, so it will be reused here.
function clearStudentDetails() {
    $("#txtfullname").val('');
    $("#txtGrade").val('');
    $("#txtSection").val('');
    $("#txtBirthDate").val('');
    $("#imgUser").attr("src", '');
    $("#txtTimeAction").html('');
    $("#authorizePersons").html('');
}


</script>

</body>
</html>
