<?php
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: /Entree/login');
        exit;
    }
    
    // Cek apakah role pengguna sesuai
    if ($_SESSION['role'] !== 'Admin') {
        header('Location: /Entree/login');
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Kewirausahaan</title>
    <link rel="icon" href="\Entree\assets\img\icon_favicon.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/77a99d5f4f.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="/Entree/assets/css/header.css">
</head>

<body>
    <div class="wrapper">
        <div class="main_header">
            <h1 class="halaman"><?php echo $pageTitle ?? 'Halaman'; ?></h1>
            <a href="javascript:void(0)" class="notification d-none" id="notificationButton">
                <i class="fa-regular fa-bell"></i>
            </a>
        </div>

        <!-- Notification Popup -->
        <div class="notification-popup" id="notificationPopup">
            <div class="notification-content">
                <h5>New Notifications</h5>
                <ul>
                    <li>
                        <strong>Materi Kewirausahaan Baru:</strong> Materi tentang 'Marketing Strategy' telah diupload!
                        <p class="notif-time">12:30 PM</p>
                    </li>
                    <li>
                        <strong>Mentor Bisnis Kelompok:</strong> Anda telah ditugaskan ke kelompok 'Tech Startups' untuk mentoring bisnis.
                        <p class="notif-time">11:15 AM</p>
                    </li>
                    <li>
                        <strong>Peran:</strong> Peran anda telah dinaikan menjadi Dosen Pengampu
                        <p class="notif-time">10:00 AM</p>
                    </li>
                    <li>
                        <strong>Jadwal Konseling:</strong> Konseling bisnis dengan mentor akan dilaksanakan pada 10 Desember 2024.
                        <p class="notif-time">09:45 AM</p>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // JavaScript for popup notification with scroll effect
        const notificationButton = document.getElementById('notificationButton');
        const notificationPopup = document.getElementById('notificationPopup');
        const notificationContent = document.querySelector('.notification-content');
        
        // Show/hide popup when notification button is clicked
        notificationButton.addEventListener('click', function (event) {
            event.preventDefault(); // Prevent default action
            const isVisible = notificationPopup.style.display === 'block';
            notificationPopup.style.display = isVisible ? 'none' : 'block';
            
            if (!isVisible) {
                // Scroll down to the bottom when the popup is shown
                setTimeout(() => {
                    notificationContent.scrollTop = notificationContent.scrollHeight;
                }, 50); // small timeout for smooth scrolling
            } else {
                // Scroll up to the top when the popup is hidden
                setTimeout(() => {
                    notificationContent.scrollTop = 0;
                }, 50);
            }
        });
    </script>

    <style>
        /* Style for notification popup */
        .notification-popup {
            position: fixed;
            top: 10%;
            right: 20px;  /* Adjusted to be left side of the screen */
            display: none;
            width: 350px;
            max-height: 400px;
            background-color: #f8f9fa; /* Light background for modern look */
            border: 1px solid #ced4da;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            overflow-y: auto;
            transition: all 0.3s ease;
        }

        .notification-content {
            text-align: left;
        }

        .notification-content h5 {
            color: #495057;
            margin-bottom: 15px;
            font-size: 16px;
            font-weight: bold;
        }

        .notification-content ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            color: #343a40;
        }

        .notification-content li {
            margin-bottom: 15px;
            font-size: 14px;
            line-height: 1.5;
        }

        .notification-content li strong {
            color: #007bff; /* Blue color for message titles */
        }

        .notif-time {
            font-size: 12px;
            color: #6c757d;  /* Light gray color for the time */
            margin-top: 5px;
        }

        /* Scrollbar styling */
        .notification-content::-webkit-scrollbar {
            width: 6px;
        }

        .notification-content::-webkit-scrollbar-thumb {
            background-color: #007bff;
            border-radius: 10px;
        }

        .notification-content::-webkit-scrollbar-track {
            background-color: #f1f1f1;
        }
    </style>
</body>

</html>
