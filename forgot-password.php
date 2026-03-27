<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/vendor/autoload.php';
// Connect to DB
require_once "includes/dbConnect.php";
require_once "lib/dblib.php";

// Email function
require_once "lib/emailHelper.php";

// Variables.
$username = "";
$error_msg = "";
$success_msg = "";

// Process form data when form is submitted.
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        //$username_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);

        $usersResult = selectUser($link,$username);

        if ($usersResult){
            $row = $usersResult->fetch_assoc();

			// Clean up some data
			if($row['firstName']==""){
				$tempFirstName = "Vital Trends";
			}else{
				$tempFirstName = $row['firstName'];
			}

			if($row['lastName']==""){
				$tempLastName = "User";
			}else{
				$tempLastName = $row['lastName'];
			}

			if($row['emailAddress']==""){
				$tempEmail = $username;
			}else{
				$tempEmail = $row['emailAddress'];
			}

			/*
			echo "here";
			print_r($row);
			die();
			*/

            $to = $tempEmail;
            $fullName=$tempFirstName." ".$tempLastName;
            $password = randomPassword();
            $hashPassword = password_hash($password, PASSWORD_DEFAULT);

            $mail = new PHPMailer(true);
            try {
                //$mail->SMTPDebug = 2;
                $subject = 'Vitaltrendsusa | Forgot Password';
                $body    = '<b>Hello, "'.$fullName.'" <br> Your new password is : "'.$password.'"</b> ';

                $moreInfo['user_type']='';
                $moreInfo['mail_type']='Dealer Agreement';
                $moreInfo['email_purpose']='Forgot Password';	  

                $emailResult = sendEmail($to, $tempFirstName, $tempLastName, $subject, $body,$moreInfo);
                
                if($emailResult){
                   $success_msg = "Mail has been sent successfully! Please check your inbox to get new password!";
                    $stmt='';
                    if($row['emailAddress']==$username){
                        $stmt = mysqli_prepare($link, "UPDATE Users SET password=?, mustResetPassword='Y' WHERE emailAddress=?");
                    }
                    if($row['username']==$username){
                        $stmt = mysqli_prepare($link, "UPDATE Users SET password=?, mustResetPassword='Y' WHERE username=?");
                    }
                    mysqli_stmt_bind_param($stmt, "ss", $hashPassword,$username);
                    $result = mysqli_stmt_execute($stmt);
                }
            } catch (Exception $e) {
                $error_msg = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else{
           $error_msg="Enter valid Username / Email Address";
        }
    }
    // Close connection
    mysqli_close($link);
}

?>
<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Vital Trends Portal</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="./images/favicon.ico">
    <link href="./css/style.css" rel="stylesheet">
    <link href="./css/custom.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">
</head>

<body class="h-100 liquid-bg">

	<div class="auth-wrapper h-100 d-flex justify-content-center align-items-center">
		<div class="login-card glass animate-card">

			<div class="text-center mb-4">
				<img src="images/vt_logo.png" class="brand-logo animate-pop" />
			</div>

			<h3 class="text-center text-light fw-bold mb-4 animate-fade">
				Forgot your password?
			</h3>

			<p class="text-center text-white small mb-4">
				Enter your username or email and we’ll send you a reset password.
			</p>

			<?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger animate-fade">
                    <?= htmlspecialchars($error_msg) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success animate-fade">
                    <?= htmlspecialchars($success_msg) ?>
                </div>
            <?php endif; ?>

			<form method="POST" action="">

				<div class="floating-group mt-3">
					<input type="text" name="username" class="floating-input" required>
					<label class="floating-label">Username or Email</label>
				</div>

				<button type="submit" class="btn-flux mt-4">
					<span>Send Reset Password</span>
				</button>

				<div class="text-center mt-3">
					<a class="text-light small" href="login.php">
						← Back to Login
					</a>
				</div>

			</form>

		</div>
	</div>

	<canvas id="liquidFlux"></canvas>
            
    <!--**********************************
        Scripts
    ***********************************-->
    <!-- Required vendors -->
    <script src="./vendor/global/global.min.js"></script>
	<script src="./vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="./js/custom.min.js"></script>
    <script src="./js/deznav-init.js"></script>
    <script>

       
		const c = document.getElementById("liquidFlux");
		const ctx = c.getContext("2d");

		function resize() {
			c.width = window.innerWidth;
			c.height = window.innerHeight;
		}
		resize();
		window.onresize = resize;

		let blobs = [];
		for (let i = 0; i < 20; i++) {
			blobs.push({
				x: Math.random() * c.width,
				y: Math.random() * c.height,
				r: 80 + Math.random() * 120,
				dx: (Math.random() - .5) * 1.5,
				dy: (Math.random() - .5) * 1.5,
				color: "rgba(32,31,88,0.35)"
			});
		}

		function animate() {
			ctx.clearRect(0, 0, c.width, c.height);

			blobs.forEach(b => {
				ctx.beginPath();
				ctx.fillStyle = b.color;
				ctx.arc(b.x, b.y, b.r, 0, Math.PI * 2);
				ctx.fill();

				b.x += b.dx;
				b.y += b.dy;

				if (b.x < -200) b.x = c.width + 200;
				if (b.y < -200) b.y = c.height + 200;
				if (b.x > c.width + 200) b.x = -200;
				if (b.y > c.height + 200) b.y = -200;
			});

			requestAnimationFrame(animate);
		}
		animate();

	</script>    
</body>

</html>