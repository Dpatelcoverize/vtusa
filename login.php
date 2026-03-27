<?php
//
// File: login.php
// Author: Charles Parry
// Date: 5/07/2022
//

// Clear the session if it exists
if (isset($_SESSION)) {
	$_SESSION = array();
	session_destroy();
}

// Initialize the session
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Already logged in?
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
	header("location: index.php");
	exit;
}

// DB connection
require_once "includes/dbConnect.php";
require_once "lib/dblib.php";

// Variables
$username = "";
$password = "";
$Acct_ID = "";
$username_err = "";
$password_err = "";
$Sls_Agnt_ID = "";

// Handle POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

	// Username validation
	if (empty(trim($_POST["username"]))) {
		$username_err = "Please enter username.";
	} else {
		$username = trim($_POST["username"]);
	}

	// Password validation
	if (empty(trim($_POST["password"]))) {
		$password_err = "Please enter your password.";
	} else {
		$password = trim($_POST["password"]);
	}

	// Multi-dealer selection case
	if (isset($_POST["dealerID"])) {
		$Acct_ID        = $_POST["dealerID"];
		$username_err   = "";
		$password_err   = "";
		$username       = $_SESSION["login_username"];
		$password       = $_SESSION["login_password"];
	}

	// If errors → back to login
	if ($password_err !== "" || $username_err !== "") {
		header("location: login.php");
	}

	// Credentials validation
	if (empty($username_err) && empty($password_err)) {

		// If no Acct_ID, check for multiple dealer associations
		if ($Acct_ID === "") {
			$stmt = mysqli_prepare(
				$link,
				"SELECT u.userID, u.username, u.password, u.mustResetPassword, u.Role_ID,
                        a.Acct_Nm, a.Acct_ID
                 FROM Users u
                 JOIN Usr_Loc ul ON u.userID = ul.Usr_ID
                 JOIN Acct a ON ul.Dlr_Acct_ID = a.Acct_ID
                 WHERE u.username = ? AND u.isDeleted = 'N' AND u.isActive = 'Y'
                 ORDER BY a.Acct_Nm"
			);

			mysqli_stmt_bind_param($stmt, "s", $username);
			if (mysqli_stmt_execute($stmt)) {
				mysqli_stmt_store_result($stmt);

				if (mysqli_stmt_num_rows($stmt) > 1) {
					$_SESSION['login_username'] = $username;
					$_SESSION['login_password'] = $password;
				}
			}
		}

		// Build SQL depending on Acct_ID selection
		if ($Acct_ID !== "") {
			$stmt = mysqli_prepare(
				$link,
				"SELECT u.userID, u.username, u.password, u.mustResetPassword, u.Role_ID,
                        u.Agent_ID, a.Acct_Nm, a.Acct_ID, a.Sls_Agnt_ID
                 FROM Users u
                 JOIN Usr_Loc ul ON u.userID = ul.Usr_ID
                 JOIN Acct a ON ul.Dlr_Acct_ID = a.Acct_ID
                 WHERE u.username = ? AND ul.Dlr_Acct_ID = ? AND u.isDeleted = 'N'
                 ORDER BY a.Acct_Nm"
			);
			mysqli_stmt_bind_param($stmt, "si", $username, $Acct_ID);
		} else {
			$stmt = mysqli_prepare(
				$link,
				"SELECT u.userID, u.username, u.password, u.mustResetPassword, u.Role_ID,
                        u.Agent_ID, a.Acct_Nm, a.Acct_ID, a.Sls_Agnt_ID
                 FROM Users u
                 JOIN Usr_Loc ul ON u.userID = ul.Usr_ID
                 JOIN Acct a ON ul.Dlr_Acct_ID = a.Acct_ID
                 WHERE u.username = ? AND u.isDeleted = 'N' AND u.isActive = 'Y'
                 ORDER BY a.Acct_Nm"
			);
			mysqli_stmt_bind_param($stmt, "s", $username);
		}

		// Execute auth query
		if (mysqli_stmt_execute($stmt)) {

			$result = mysqli_stmt_get_result($stmt);
			$row    = mysqli_fetch_assoc($result);

			if ($row) {

				// Extract fields
				$userID            = $row["userID"];
				$roleID            = $row["Role_ID"];
				$Acct_ID           = $row["Acct_ID"];
				$Agent_ID          = $row["Agent_ID"];
				$storedPassword    = $row["password"];
				$mustResetPassword = $row["mustResetPassword"];
				$Sls_Agnt_ID       = $row["Sls_Agnt_ID"];
				$userDeleted       = $row["isDeleted"];
				$login_err         = "";

				// Login record
				$login_status = (password_verify($password, $storedPassword) && $userDeleted === "N") ? 1 : 0;
				$ip = $_SERVER['REMOTE_ADDR'];

				$stmtLog = mysqli_prepare(
					$link,
					"INSERT INTO login_record (user_id, login_status, ip_address, datetime)
                     VALUES (?, ?, ?, UNIX_TIMESTAMP(Now()))"
				);
				mysqli_stmt_bind_param($stmtLog, "iis", $userID, $login_status, $ip);
				mysqli_stmt_execute($stmtLog);

				// Successful password?
				if (password_verify($password, $storedPassword)) {

					//
					// ROLE-BASED SESSION INITIALIZATION
					//
					// If Role_ID == 1, this is an admin.
					//  If Role_ID == 2, this is a dealer account.
					//  If Role_ID == 3, this is a Vital Trends Sales Agent Account.
					//  If Role_ID == 4, this is a Agency Sales Agent Account.
					//  If Role_ID == 5, this is a Agency Account.
					//  If Role_ID == 6, this is a Dealer Agent Account.
					if ($roleID == 1) {        // Admin
						$_SESSION["admin_loggedin"] = true;
						$_SESSION["admin_id"]       = $userID;
						$_SESSION["admin_username"] = $username;
						$_SESSION["role_ID"]        = $roleID;

						// Clear dealer vars
						$_SESSION["loggedin"]  = "";
						$_SESSION["id"]        = "";
						$_SESSION["userID"]    = "";
						$_SESSION["username"]  = "";
						$_SESSION["userType"]  = "";
						unset($_SESSION["agentID"]);
					} elseif ($roleID == 2) { // Dealer
						$_SESSION["loggedin"]  = true;
						$_SESSION["id"]        = $Acct_ID;
						$_SESSION["agentID"]   = $Agent_ID;
						$_SESSION["userID"]    = $userID;
						$_SESSION["username"]  = $username;
						$_SESSION["userType"]  = "dealer";
						$_SESSION["role_ID"]   = $roleID;
						$_SESSION["admin_id"]  = $Sls_Agnt_ID;
					} elseif (in_array($roleID, [3, 4, 5, 6])) { // Agents

						if ($roleID == 4 || $roleID == 5) {
							$_SESSION["id"]        = "";
							$_SESSION["admin_id"]  = $userID;
							$_SESSION["username"]  = "";
							$_SESSION["userType"]  = "Agent";
						} elseif ($roleID == 6) {
							$_SESSION["id"]        = $Acct_ID;
							$_SESSION["admin_id"]  = $Sls_Agnt_ID;
							$_SESSION["userType"]  = "dealer";
							$_SESSION["username"]  = $username;
						} else {
							$_SESSION["id"]        = $Acct_ID;
							$_SESSION["admin_id"]  = $Sls_Agnt_ID;
							$_SESSION["username"]  = $username;
							$_SESSION["userType"]  = "Agent";
						}

						$_SESSION["loggedin"]        = true;
						$_SESSION["agentID"]         = $Agent_ID;
						$_SESSION["userID"]          = $userID;
						$_SESSION["role_ID"]         = $roleID;
						$_SESSION["admin_loggedin"]  = false;
						$_SESSION["admin_username"]  = "";
					}

					//
					// SECURITY FLAGS
					//
					$_SESSION["security_01_all_dealers"]            = "N";
					$_SESSION["security_02_dealership_customers"]   = "N";
					$_SESSION["security_03_dealership_agreement_info"] = "N";
					$_SESSION["security_04_dealership_agents_customers"] = "N";
					$_SESSION["security_05_all_quotes"]             = "N";
					$_SESSION["security_06_dealers_quotes"]         = "N";
					$_SESSION["security_07_customers_quotes"]       = "N";
					$_SESSION["security_08_all_warranties"]         = "N";
					$_SESSION["security_09_dealers_warranties"]     = "N";
					$_SESSION["security_10_customers_warranties"]   = "N";

					// Assign security matrix
					if ($roleID == 1) {
						$_SESSION = array_merge($_SESSION, [
							"security_01_all_dealers" => "Y",
							"security_02_dealership_customers" => "Y",
							"security_03_dealership_agreement_info" => "Y",
							"security_04_dealership_agents_customers" => "Y",
							"security_05_all_quotes" => "Y",
							"security_06_dealers_quotes" => "Y",
							"security_07_customers_quotes" => "Y",
							"security_08_all_warranties" => "Y",
							"security_09_dealers_warranties" => "Y",
							"security_10_customers_warranties" => "Y",
						]);
					} elseif ($roleID == 2) {
						$_SESSION["security_02_dealership_customers"] = "Y";
						$_SESSION["security_03_dealership_agreement_info"] = "Y";
						$_SESSION["security_04_dealership_agents_customers"] = "Y";
						$_SESSION["security_06_dealers_quotes"] = "Y";
						$_SESSION["security_07_customers_quotes"] = "Y";
						$_SESSION["security_09_dealers_warranties"] = "Y";
						$_SESSION["security_10_customers_warranties"] = "Y";
					} elseif (in_array($roleID, [3, 4, 5])) {
						$_SESSION["security_02_dealership_customers"] = "Y";
						$_SESSION["security_03_dealership_agreement_info"] = "Y";
						$_SESSION["security_04_dealership_agents_customers"] = "Y";
						$_SESSION["security_05_all_quotes"] = "Y";
						$_SESSION["security_06_dealers_quotes"] = "Y";
						$_SESSION["security_07_customers_quotes"] = "Y";
						$_SESSION["security_08_all_warranties"] = "Y";
						$_SESSION["security_09_dealers_warranties"] = "Y";
						$_SESSION["security_10_customers_warranties"] = "Y";
					} elseif ($roleID == 6) {
						$_SESSION["security_02_dealership_customers"] = "Y";
						$_SESSION["security_07_customers_quotes"] = "Y";
					} else {
						header("location: login.php");
						exit;
					}

					//
					// PASSWORD RESET
					//
					if ($mustResetPassword === "Y") {
						$redirect = "resetPassword.php";
						if (isset($_GET["FromEmail"])) {
							$redirect .= "?FromEmail=true";
						}
						header("location: $redirect");
						exit;
					}

					//
					// SIGNATURE CHECK
					//
					if (!empty($_SESSION["agentID"])) {
						$sql = "SELECT w9_signature FROM Pers WHERE Pers_ID=" . $_SESSION["agentID"];
						$res = mysqli_query($link, $sql);
						$rowSig = mysqli_fetch_assoc($res);

						if (!$rowSig["w9_signature"]) {
							header("location: stand_alone_signature.php?agentID=" . base64_encode($_SESSION["agentID"]));
							exit;
						}
					}

					header("location: index.php");
					exit;
				} else {
					$login_err = "Invalid username or password.";
				}
			} else {
				$login_err = "User is InActive. Please contact support.";
			}

			//
			// LEGACY AUTH BLOCK (unchanged, but cleaned)
			//
			if (mysqli_stmt_num_rows($stmt) == 1) {

				$hashed_password = password_hash($password, PASSWORD_DEFAULT);
				mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);

				if (mysqli_stmt_fetch($stmt)) {

					if (password_verify($password, $hashed_password)) {

						if (session_status() === PHP_SESSION_NONE) {
							session_start();
						}

						$_SESSION["admin_loggedin"] = true;
						$_SESSION["admin_id"]       = $id;
						$_SESSION["admin_username"] = $username;

						if ($mustResetPassword == "Y") {
							$redirect = "resetPassword.php";
							if (isset($_GET["FromEmail"])) {
								$redirect .= "?FromEmail=true";
							}
							header("location: $redirect");
						} else {
							header("location: index.php");
						}
					} else {
						$login_err = "Invalid username or password.";
					}
				}
			}
		}
	}

	mysqli_close($link);
}
?>
<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Vital Trends Portal</title>
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
				Sign in to Vital Trends Portal
			</h3>

			<?php if (!empty($login_err)): ?>
				<div class="alert alert-danger left-icon-big alert-dismissible fade show">
					
					</button>
					<div class="media">
						<div class="alert-left-icon-big">
							<span><i class="mdi mdi-alert"></i></span>
						</div>
						<div class="media-body">
							<h5 class="mt-1 mb-2">Error Occured!</h5>
							<p class="mb-0"><?= $login_err ?></p>
						</div>
					</div>
				</div>
				
			<?php endif; ?>

			<form method="POST" action="">

				<div class="floating-group mt-3">
					<input type="text" name="username" class="floating-input" required>
					<label class="floating-label">Username</label>
				</div>

				<div class="floating-group mt-4">
					<input type="password" name="password" class="floating-input" required>
					<label class="floating-label">Password</label>
				</div>

				<div class="text-right mt-2">
					<a class="text-light small" href="forgot-password.php">Forgot Password?</a>
				</div>

				<button type="submit" class="btn-flux mt-4">
					<span>Sign Me In</span>
				</button>

			</form>

		</div>
	</div>

	<canvas id="liquidFlux"></canvas>

	
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