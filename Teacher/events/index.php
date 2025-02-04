<?php
session_start(); // Start the session
include '../../Config/connection.php'; 
$_SESSION['header'] = 'School Event'
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Blog</title>
    <link href="indexs.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../shared/sidebar.php'; ?>
<div class="main-content">
    <div class="container ">
        <div id="eventCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner" id="blogContainer">
                <!-- Dynamic content will be inserted here -->
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#eventCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#eventCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
</div>




    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE_URL = ""; // Set your API base URL

        function fetchBlogs() {
    $.get(`${API_BASE_URL}get.php`, function (data) {
        const blogs = JSON.parse(data);
        let blogHTML = '';
        blogs.forEach((blog, index) => {
            blogHTML += `
                <div class="carousel-item ${index === 0 ? 'active' : ''}">
                    <img src="${blog.image}" alt="${blog.title}">
                    <div class="overlay">
                        <div class="title">${blog.title}</div>
                        <div class="description">${blog.description ? blog.description : 'No description available'}</div>
                    </div>
                </div>
            `;
        });
        $('#blogContainer').html(blogHTML);
    });
}


        fetchBlogs();
    </script>
</body>
</html>
