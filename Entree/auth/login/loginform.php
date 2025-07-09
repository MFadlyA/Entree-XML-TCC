<?php
session_start();
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Masuk | Entree</title>
	<link rel="icon" href="\Entree\assets\img\icon_favicon.png" type="image/x-icon">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="/Entree/assets/css/loginstyle.css">
	<link href="https://fonts.googleapis.com/css?family=Poppins:600&display=swap" rel="stylesheet">
	<script src="https://kit.fontawesome.com/77a99d5f4f.js" crossorigin="anonymous"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
	<img class="wave" src="/Entree/assets/img/wave.png">
	<div class="center-container">
    <div class="login-content p-4 border rounded shadow">
        <form action="/Entree/config/login.php" method="post">
            <!-- <img src="/Entree\assets\img\image1.png"> -->
			<img class="mb-5"  src="/entree\assets\img\logo_entree_login.png" alt="Logo Entree">
            <div class="input-div one">
                <div class="i">
                    <i class="fas fa-user"></i>
                </div>
                <div class="div">
                    <h5>Nama Pengguna</h5>
                    <input type="text" class="input username" name="username" oninvalid="this.setCustomValidity('Kolom ini tidak boleh kosong')" oninput="this.setCustomValidity('')">
                </div>
            </div>
            <div class="input-div pass">
                <div class="i"> 
                    <i class="fas fa-lock"></i>
                </div>
                <div class="div">
                    <h5>Kata Sandi</h5>
                    <input type="password" class="input password" name="password" required oninvalid="this.setCustomValidity('Kolom ini tidak boleh kosong')" oninput="this.setCustomValidity('')">
                </div>
            </div>
            <input type="submit" class="btnLogin" name="login" value="Masuk">
            <a href="/Entree"></a>
        </form>
</div>

    </div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

	<!-- Pop-Up Notifikasi -->
	<div id="errorPopup" class="popup hidden">
		<p id="errorMessage"></p>
		<button onclick="closePopup()">Tutup</button>
	</div>

    <script type="text/javascript" src="/Entree/assets/js/login.js"></script>
    <script>
        // Menangani pesan error dari sesi PHP
        const errorMessage = "<?php echo $error_message; ?>";
        if (errorMessage) {
            document.getElementById('errorMessage').textContent = errorMessage;
            document.getElementById('errorPopup').classList.remove('hidden');
        }

        function closePopup() {
            document.getElementById('errorPopup').classList.add('hidden');
        }
    </script>
</body>
</html>