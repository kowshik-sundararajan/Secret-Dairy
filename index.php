<?php
session_start();
$error = "";

if (array_key_exists("logout", $_GET)) {
	session_unset($_SESSION);
	setcookie("id", "", time() - 60*60);
	$_COOKIE["id"] = "";
} else if ((array_key_exists("id", $_SESSION) AND $_SESSION['id']) OR (array_key_exists("id", $_COOKIE) AND $_COOKIE['id'])) {
	header("Location: logged-in-page.php");
}

if (array_key_exists("submit", $_POST)) {
	include("connection.php");


	if ($_POST['email'] == '') {
		$error .= "Email address is required<br>";
	}

	if ($_POST['password'] == '') {
		$error .= "Password is required<br>";
	}

	if ($error != "") {
		// echo "<p>There were error(s) in your form:</p>".$error;
	} else {
		
		if ($_POST['signup'] == '1') {
			// Check whether entered email address is already taken
			$query = "SELECT id FROM `diary` WHERE email = '".mysqli_real_escape_string($link, $_POST['email'])."' LIMIT 1";

			$result = mysqli_query($link, $query);
			if (mysqli_num_rows($result) > 0) {
				$error = "That email address is already taken.";
			} else {
				$query = "INSERT INTO `diary` (`email`, `password`) VALUES('".mysqli_real_escape_string($link, $_POST['email'])."', '".mysqli_real_escape_string($link, $_POST['password'])."')";

				if (!mysqli_query($link, $query)) {
					echo "<p>There was an error signing you up.</p>";
				} else {

					$query = "UPDATE `diary` SET password = '".md5(md5(mysqli_insert_id($link)).$_POST['password'])."' WHERE id = '".mysqli_insert_id($link)."' LIMIT 1";
					
					mysqli_query($link, $query);

					$_SESSION['id'] = mysqli_insert_id($link);

					if ($POST['stay-logged-in'] == 1) {
						setcookie("id", mysqli_insert_id($link), time() + 60*60*24);
					}
					
					header("Location: logged-in-page.php");
				}
			}
		} else {
			$query = "SELECT * FROM `diary` WHERE email = '".mysqli_real_escape_string($link, $_POST['email'])."'";
			$result = mysqli_query($link, $query);
			$row = mysqli_fetch_array($result);

			if (isset($row)) {
				$hashedPassword = md5(md5($row['id']).$_POST['password']);
				if ($hashedPassword == $row['password']) {
					$_SESSION['id'] = $row['id'];

					if ($_POST['stay-logged-in'] == 1) {
						setcookie("id", $row['id'], time() + 60*60*24);
					}

					header("Location: logged-in-page.php");
				} else {
					$error = "That email/password combination could not be found.";
				}
			} else {
				$error = "That email/password combination could not be found.";
			}
		}
	}
}
?>

<?php include 'header.php'; ?>
<!DOCTYPE html>

	<div class="container" id="home-page-container">
		<h1>Secret Diary</h1>
		<p><strong>Store your thoughts permanently and securely</strong></p>
		<div id="error"><?php if ($error != "") {
			echo '<div class="alert alert-danger" role="alert">'.$error.'
</div>';
			}?></div>

		<form method="POST" id="signup-form">
		<p>Interested? Sign up now!</p>
			<fieldset class="form-group">
				<input class="form-control" type="text" name="email" placeholder="Email address">
			</fieldset>
			
			<fieldset class="form-group">
				<input class="form-control" type="text" name="password" placeholder="Password">
			</fieldset>
			
			<div class="checkbox">
				<label>
					<input type="checkbox" name="stay-logged-in" value="1"> Stay logged in 
				</label>
			</div>
			
			<fieldset class="form-group">
				<input type="hidden" name="signup" value="1">
				<input class="btn btn-success" type="submit" name="submit" value="Sign up!">
			</fieldset>	
			<p><a class="toggle-forms">Log In</a></p>
		</form>

		<form method="POST" id="login-form">
		<p>Log in using your username and password.</p>
			<fieldset class="form-group">
				<input class="form-control" type="text" name="email" placeholder="Email address">
			</fieldset>
			
			<fieldset class="form-group">
				<input class="form-control" type="text" name="password" placeholder="Password">
			</fieldset>
			
			<div class="checkbox">
				<label>
					<input type="checkbox" name="stay-logged-in" value="0"> Stay logged in 
				</label>
			</div>
			
			<fieldset class="form-group">
				<input type="hidden" name="signup" value="0">
				<input class="btn btn-success" type="submit" name="submit" value="Login!">
			</fieldset>
			<p><a class="toggle-forms">Sign Up</a></p>
		</form>
	</div>
<?php include 'footer.php'; ?>

