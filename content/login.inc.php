<?php

if (!defined("IN_SYSTEM"))
	{
		header("Location: ../index.php");
		die();
	}

?>

<!DOCTYPE html>
<html>
<head>
	<title><? echo $title; ?></title>
	<link rel="stylesheet" type="text/css" href="./stylesheet.css">
</head>
<body>
<?
	if (isset($showlogin[1]))
	{
		echo "wrong credentials";
	}
 ?>
<div class="loginWrapper">
	<div id="loginBox">
		<div id="loginText"><img src="./img/logo2.gif" width="184" height="66"/></div>
		<div id="LoginInput">
			<form action="" method="post">
				<span class="loginBoxText">Username:</span><span class="loginBoxInput"><input type="text" name="username"></span><br>
				<span class="loginBoxText">Password:</span><span class="loginBoxInput"><input type="password" name="password"></span>
				<div class="loginButton">
					<input type="submit" value="Login" name="submit">
				</div>
			</form>
		</div>
	</div>
</div>

</body>
</html>