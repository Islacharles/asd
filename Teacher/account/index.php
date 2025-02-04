<?php
session_start(); // Start the session
include '../../Config/connection.php'; 
include '../../Config/auth.php';

if (!isset($_SESSION['id'])) {
    die("Error: id not logged in. Please log in first.");
}

$id = $_SESSION['id'];

// Fetch id details
$sql = "SELECT ID, FIRSTNAME, MIDDLENAME, LASTNAME, CONTACT_NUMBER, ADDRESS, PICTURE, EMAIL, PASSWORD 
        FROM teachers 
        WHERE ID = ?";
$stmt = $conn->prepare($sql); // Use $sql instead of $query
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// Check if id exists
if ($result->num_rows === 0) {
    die("Error: No id found with the given ID.");
}

$person = $result->fetch_assoc(); // Fetch the data as an associative array

$_SESSION['header'] = 'My Account';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Account</title>
    <?php include '../shared/sidebar.php' ?>
    <link href="index.css" rel="stylesheet"/>
</head>
<style>
        /* Add styling for the submit button */
        #uploadPicButton {
            display: none;
            background-color: #4CAF50; /* Green */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        /* Animation for button pop-up */
        #uploadPicButton.pop-up {
            animation: popUpAnimation 0.3s ease-out forwards;
        }

        @keyframes popUpAnimation {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Hover effect for the button */
        #uploadPicButton:hover {
            background-color: #45a049; /* Darker green */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        }
    </style>
<body>
    <div class="main-content">
        <div class="Account">
            <div class="account-header">
                <form id="profilePicForm" method="POST" action="upload-profile-picture.php" enctype="multipart/form-data">
                <img id="profilePic" 
                         src="data:image/png;base64,<?= htmlspecialchars($person['PICTURE']); ?>" 
                         alt="Profile Picture" 
                         style="cursor: pointer; width: 150px; height: 150px; border-radius: 50%;" />
                    <!-- Hidden file input -->
                    <input type="file" id="profilePicInput" name="profile_picture" accept="image/*" style="display: none;" />
                    <!-- Save button -->
                    <button type="submit" id="uploadPicButton" style="display: none;">Save</button>
                </form>
                <h2 id="greeting" style="margin: 0; color: white;">Hi, <?= htmlspecialchars($person['FIRSTNAME'].' '.$person['LASTNAME']); ?>!</h2>
            </div>
            <br>
            <h3>Account Information</h3>
            <br>
            <div class="info">
                <label>Full Name</label>
                <div class="value" id="fullname"><?= htmlspecialchars($person['FIRSTNAME'] . ' ' . $person['MIDDLENAME'] . ' ' . $person['LASTNAME']); ?></div>
            </div>
            <div class="info">
                <label>Contact Number</label>
                <div class="value" id="contactNumber"><?= htmlspecialchars($person['CONTACT_NUMBER']); ?></div>
            </div>
            <div class="info">
                <label>Email Address</label>
                <div class="value" id="email"><?= htmlspecialchars($person['EMAIL']); ?></div>
            </div>
            <div class="info">
                <label>Address</label>
                <div class="value" id="address"><?= htmlspecialchars($person['ADDRESS']); ?></div>
            </div>
            <div class="button">
                <button class="change-password-btn" id="changePasswordBtn">Change Password</button>
            </div>
        </div>
    </div>
      <!-- Change Password Modal -->
      <div id="changePasswordModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Change Your Password</h3>
        </div>
        <div class="modal-body">
            <form id="changePasswordForm" method="POST" action="change-password-api.php">
                <div>
                    <label for="current_password">Current Password:</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div>
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div>
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="modal-footer">
                    <button type="submit">Submit</button>
                    <button type="button" id="closePasswordModal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Modal elements
    const changePasswordModal = document.getElementById('changePasswordModal');
    const changePasswordBtn = document.getElementById('changePasswordBtn');
    const closePasswordModal = document.getElementById('closePasswordModal');
    const changePasswordForm = document.getElementById('changePasswordForm');

    // Open modal
    changePasswordBtn.onclick = function() {
        changePasswordModal.style.display = "block";
    };

    // Close modal and clear form
    closePasswordModal.onclick = function() {
        clearForm();
        changePasswordModal.style.display = "none";
    };

    // Close modal on outside click and clear form
    window.onclick = function(event) {
        if (event.target == changePasswordModal) {
            clearForm();
            changePasswordModal.style.display = "none";
        }
    };

    // Clear form fields
    function clearForm() {
        changePasswordForm.reset();
    }

    // Handle form submission with AJAX
    changePasswordForm.onsubmit = function(event) {
        event.preventDefault(); // Prevent default form submission
        location.reload();
        const formData = new FormData(this);

        fetch('change-password-api.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message); // Show success notification
                clearForm(); // Clear form fields
                changePasswordModal.style.display = "none"; // Close modal
            } else {
                alert(data.message); // Show error notification
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An unexpected error occurred. Please try again.');
        });
    };
    document.addEventListener('DOMContentLoaded', function () {
            const profilePic = document.getElementById('profilePic');
            const profilePicInput = document.getElementById('profilePicInput');
            const uploadPicButton = document.getElementById('uploadPicButton');

            // When profile picture is clicked, trigger file input
            profilePic.onclick = function () {
                profilePicInput.click();
            };

            // When a file is selected, preview it and show the save button
            profilePicInput.onchange = function (event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        profilePic.src = e.target.result; // Set the preview image
                        uploadPicButton.classList.add('pop-up'); // Show the save button with pop-up effect
                        uploadPicButton.style.display = "inline-block"; // Show the save button
                    };
                    reader.readAsDataURL(file);
                    
                }
            };
        });
</script>

</body>
</html>