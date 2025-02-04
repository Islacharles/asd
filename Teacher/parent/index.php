<?php
session_start(); // Start the session
include '../../Config/connection.php';
include '../../Config/auth.php';
$_SESSION['header'] = 'Guardian';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardian Records</title>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="dataTables.min.css">
    <script src="dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .table-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <?php include '../shared/sidebar.php'; ?>
    <link href="index.css" rel="stylesheet" />
    <div class="container-main">
        <div class="main-wrapper-body card-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Guardian Records</h4>
                <!-- <button class="btn btn-success" id="downloadParentsExcel">
                    <i class="bi bi-file-earmark-excel"></i> Generate Parents Records
                </button> -->
                <script>
                        $('#downloadParentsExcel').on('click', function () {
                            window.location.href = 'generateParentsExcel.php';
                        });
                </script>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle" id="GuardianTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Picture</th>
                            <th>Full Name</th>
                            <th>Contact Number</th>
                            <th>Students</th>
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
            const table = $('#GuardianTable').DataTable({
                dom: '<"row"<"col-md-6"l><"col-md-6 text-end"Bf>>rtip',  // This defines the layout for DataTable buttons
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                ajax: {
                    url: 'index-search-api.php',
                    type: 'GET',
                    dataSrc: ''
                },
                columns: [
                    {
                        data: 'PICTURE',
                        render: function (data) {
                            return `<img src="data:image/png;base64,${data}" class="table-img" alt="N/A">`;
                        }
                    },
                    { data: 'FullName' },
                    { data: 'CONTACT_NUMBER' },
                    { data: 'TOTAL_STUDENTS' },
                    { data: 'EMAIL' },
                    {
                        data: 'PARENT_NUMBER',
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

            // Delete Parent Handler
            $('#GuardianTable').on('click', '.btnDelete', function () {
                const parentId = $(this).data('id');
                if (confirm('Are you sure you want to delete this parent?')) {
                    $.ajax({
                        url: 'delete-api.php',
                        type: 'POST',
                        data: { parent_id: parentId },
                        success: function (response) {
                            alert(response?.message || 'Parent deleted successfully.');
                            table.ajax.reload(); // Reload the DataTable
                        },
                        error: function () {
                            alert('An error occurred while deleting the parent.');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
