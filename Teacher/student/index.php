<?php
session_start(); // Start the session
include '../../Config/connection.php';
include '../../Config/auth.php';

$_SESSION['header'] = 'Students';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Records</title>
    <!-- DataTables CSS -->

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .table-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="index.css" rel="stylesheet" />
</head>
<body>
    <?php include '../shared/sidebar.php'; ?>
    <div class="container-main">
        <div class="main-wrapper-body card-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Student Records</h4>
                <form action="export-studentlist-api.php" method="POST" style="display: inline;">
                                <!-- <button type="submit" name="generate_excel" class="btn btn-success">Generate Student Records</button> -->
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle" id="studentTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Picture</th>
                            <th>Student Number</th>
                            <th>Full Name</th>
                            <th>Grade</th>
                            <th>Section</th>
                            <th>Parent Name</th>
                            <th>Contact Number</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data populated dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Initialize DataTable
            const table = $('#studentTable').DataTable({
                dom: '<"row"<"col-md-6"l><"col-md-6 text-end"Bf>>rtip',  // This defines the layout for DataTable buttons
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                ajax: {
                    url: 'index-search-api.php',
                    type: 'GET',
                    dataSrc: ''
                },
                columns: [
                    {
                        data: 'picture',
                        render: function (data) {
                            return `<img src="data:image/png;base64,${data}" class="table-img" alt="N/A">`;
                        }
                    },
                    { data: 'student_number' },
                    { data: 'student_name' },
                    { data: 'grade_level' },
                    { data: 'section' },
                    { data: 'guardian_name' },
                    { data: 'guardian_contact' },
                    { data: 'guardian_email' },
                    {
                        data: 'student_number',
                        render: function (data) {
                            return `
                                <a href="view.php?id=${data}" class="btn btn-dark btn-sm">View</a>
                                
                            `;
                        }
                    }
                ],
                initComplete: function () {
                    $('#searchField').on('keyup', function () {
                        table.search(this.value).draw();
                    });
                }
            });

            // Delete Student Handler
            $('#studentTable').on('click', '.btnDelete', function () {
                const studentId = $(this).data('id');
                if (confirm('Are you sure you want to delete this student?')) {
                    $.ajax({
                        url: 'delete-api.php',
                        type: 'POST',
                        data: { student_id: studentId },
                        success: function (response) {
                            alert(response?.message || 'Student deleted successfully.');
                            table.ajax.reload(); // Reload the DataTable
                        },
                        error: function () {
                            alert('An error occurred while deleting the student.');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
