<?php
session_start(); // Start the session
include '../../Config/connection.php';
$_SESSION['header'] = 'Student Attendance';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="index.css" rel="stylesheet" />
    <title>Student Attendance Table</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Include DataTables CSS -->
    <style>
        .table-container {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include '../shared/sidebar.php' ?>
    <div class="container-main">
        <div class="main-wrapper-body card-container">
            <!-- Container -->
            <div class="container"> 

                <!-- Filters -->
                <div class="row mb-4 align-items-center">
                    <div class="col-md-6">
                        <!-- Date filter -->
                        <input type="date" class="form-control" id="filterDate" placeholder="Filter by Date">
                    </div>
                
                </div>
                
                <!-- Table -->
                <div class="table-container">
                    <table class="table table-striped table-bordered" id="attendanceTable">
                        
                        <thead class="table-dark">
                            <tr>
                                <th>Student #</th>
                                <th>Full Name</th>
                                <th>Grade Level</th>
                                <th>Section</th>
                                <th>Date</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Authorize Person</th>
                            </tr>
                        </thead>
                        <tbody id="body-table">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Initialize DataTable
            var table = $('#attendanceTable').DataTable({
            dom: '<"row"<"col-md-6"l><"col-md-6 text-end"Bf>>rtip',  // This defines the layout for DataTable buttons
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'] // Adding buttons for export and print
            });

            // Set default date to today
            var today = new Date();
            var yyyy = today.getFullYear();
            var mm = today.getMonth() + 1; // Months are zero-based
            var dd = today.getDate();

            if (mm < 10) mm = '0' + mm;
            if (dd < 10) dd = '0' + dd;

            var formattedDate = yyyy + '-' + mm + '-' + dd;
            $('#filterDate').val(formattedDate);

            fetchData();

            // Trigger data fetching on filter changes
            $("#filterDate, #searchStudent").on('input', function () {
                fetchData();
            });

            // Export report button event
            $('#exportReport').click(function () {
                exportReport();
            });

            function fetchData() {
                var search = $('#searchStudent').val();
                var date = $('#filterDate').val();

                $.ajax({
                    url: 'search-attendance-api.php', // API URL
                    method: 'GET',
                    data: {
                        search: search,
                        date: date
                    },
                    success: function (response) {
                        table.clear();
                        response?.forEach(function (item) {
                            table.row.add([
                                item.student_number,
                                item.student_name,
                                item.grade_level,
                                item.section,
                                item.created_date,
                                item.time_in,
                                item.time_out,
                                item.authorize_fullname
                            ]);
                        });
                        table.draw();
                    },
                    error: function () {
                        alert('An error occurred while fetching the data.');
                    }
                });
            }

            function exportReport() {
                var date = $('#filterDate').val();

                $.ajax({
                    url: 'export-attendance-api.php', // API for exporting report
                    method: 'POST',
                    data: { date: date },
                    xhrFields: {
                        responseType: 'blob' // Ensure the response is a binary blob (for Excel files)
                    },
                    success: function (response) {
                        var blob = response; // The server will send the Excel file as a blob
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = `Attendance_Report_${date}.xlsx`; // Set the file extension to .xlsx
                        link.click();
                    },
                    error: function () {
                        alert('An error occurred while exporting the report.');
                    }
                });
            }

        });
    </script>
</body>
</html>
