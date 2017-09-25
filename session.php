<?php

session_start();
if ($_SESSION['email']) {
	echo "Welcome!";	
} else {
	header("Location: index.php");
}

?>
