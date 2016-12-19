<?php
	if (!defined("IN_SYSTEM"))
	{
		header("Location: ../index.php");
		die();
	}

	echo $_SESSION["username"] . " is ingelogd! <a href=\"?p=logout\">Log out</a>";
?>