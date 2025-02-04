
<?php 
    include '../../Config/auth.php';
    include '../../Config/jslib.php';
    function getIsActiveClass($cls){  
        if (str_contains($_SERVER['REQUEST_URI'], $cls)) {
            return "active";
        } else {
            return "";
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
        // Process the image upload
        $image = $_FILES['profile_image'];
        $imageData = base64_encode(file_get_contents($image['tmp_name'])); // Get base64 of the uploaded image

        // Save the image to the database (You should implement this in your database update logic)
        // For example, assume the updated image is saved in $newImageData
        $newImageData = $imageData; // Replace with your actual database logic to fetch the updated picture

        // Update session with the new image
        $_SESSION['picture'] = $newImageData;

        // Redirect to avoid resubmitting the form on page refresh
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
?>
<link href="../shared/sidebar.css" rel="stylesheet" />
<!-- <div class="sidebar">
    <img src="../../Admin/logo/logo/logo.png" alt="Logo">
    <a href="../attendance" class="<?=getIsActiveClass("attendance")?>">
        <i class="fas fa-calendar-check"></i> Attendance
    </a>
    <a href="../qr" class="<?=getIsActiveClass("qr")?>">
        <i class="fas fa-calendar-check"></i> QR Scan
    </a>
    <a href="../student" class="<?=getIsActiveClass("student")?>">
        <i class="fas fa-user-graduate"></i> Student Records
    </a>
    <a href="../parent" class="<?=getIsActiveClass("parent")?>">
        <i class="fas fa-users"></i> Guardian Records
    </a>
    <a href="../events" class="<?=getIsActiveClass("events")?>">
        <i class="fas fa-calendar-alt"></i> Events
    </a>
    <a href="../account" class="<?=getIsActiveClass("account")?>">
        <i class="fas fa-calendar-alt"></i> My Account
    </a>
    <div class="bottom-links">
    <a href="javascript:void(0);" onclick="confirmLogout();">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
    </div>

    <script>
        function confirmLogout() {
            // Show confirmation dialog
            if (confirm("Are you sure you want to log out?")) {
                // Redirect to logout.php if confirmed
                window.location.href = "../../logout.php";
            }
        }
    </script>
    </div>
</div>

<div class="main-header">
    <h2 class="fw-bold"><?=($_SESSION['header'] ?? '')?></h2>
    <div class="profile">
        <img alt="User profile picture" height="40" src="data:image/png;base64,<?=$_SESSION['picture']?>" width="40"/>
        <div class="profile-text">
            <p><?=$_SESSION['fullname']?></p>
            <small><?=$_SESSION['role']?></small>
        </div>
    </div>
</div> -->

<div class="sidebar" id="sidebar">
    <img src="../../Admin/logo/logo/logo.png" alt="Logo"> 
    <a href="../attendance" class="<?=getIsActiveClass("attendance")?>">
        <i class="fas fa-calendar-check"></i> Attendance
    </a>
    <a href="../qr" class="<?=getIsActiveClass("qr")?>">
        <i class="fas fa-calendar-check"></i> QR Scan
    </a>
    <a href="../student" class="<?=getIsActiveClass("student")?>">
        <i class="fas fa-user-graduate"></i> Student Records
    </a>
    <a href="../parent" class="<?=getIsActiveClass("parent")?>">
        <i class="fas fa-users"></i>Guardian Records
    </a>
    <a href="../events" class="<?=getIsActiveClass("events")?>">
        <i class="fas fa-calendar-alt"></i> Events
    </a>
    <a href="../account" class="<?=getIsActiveClass("account")?>">
        <i class="fas fa-calendar-alt"></i> My Account
    </a>
    <div class="bottom-links">
    <a href="javascript:void(0);" onclick="confirmLogout();">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
    </div>
</div>

<div class="main-body-container" id="main-body-container">
    <div class="main-header">
        <h2 class="fw-bold"><small id="btnHumberger">☰</small> &nbsp; <?=($_SESSION['header'] ?? '')?></h2>
        <div class="profile">
            <div class="btn-notif-wrapper" style="display: none;">
                <small id="notif-count">0</small>
                <i class="fas fa-envelope btnNotif" id="btnNotif"></i>
            </div>
            <img alt="User profile picture" height="40" src="data:image/png;base64,<?=$_SESSION['picture']?>" width="40"/>
            <div class="profile-text">
                <p><?=$_SESSION['fullname']?></p>
                <small><?=$_SESSION['role']?></small>
            </div>
        </div>
    </div>
<script>

function confirmLogout() {
    // Show confirmation dialog
    if (confirm("Are you sure you want to log out?")) {
        // Redirect to logout.php if confirmed
        window.location.href = "../../logout.php";
    }
}
   
$(document).ready(function(){
    if ($(window).width() < 575){
        $('#sidebar').addClass("sidebar-hide");
        $('#main-body-container').addClass("main-body-container-min");
    }
});

$("#btnNotif").on('click', function(){ 
    if($("#notification-body").css("display") !== "none"){
        $("#notification-body").hide();
    }else{
        $("#notification-body").show();
    }
})
 

$("#btnHumberger").on('click', function(){
    if($("#sidebar").hasClass("sidebar-hide")){ 
        $('#sidebar').removeClass("sidebar-hide");
        $('#main-body-container').removeClass("main-body-container-min");
    }else{ 
        $('#sidebar').addClass("sidebar-hide");
        $('#main-body-container').addClass("main-body-container-min");
    }
})
</script>