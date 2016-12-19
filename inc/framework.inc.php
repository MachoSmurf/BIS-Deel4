<?
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
<div class="wrapper">

	<div class="navigation">
		<div class="logoBox">
			<img id="logo" src="./img/logo2.gif" />
		</div>
		<div class="navBody">
			<ul>
				<li><a href="?p=home" <?php if ($activePage == "home") {echo "class=\"active\"";} ?> >Home</a></li>
				<li><a href="?p=systeem" <?php if ($activePage == "systeem") {echo "class=\"active\"";} ?> >Systeem Registratie</a></li>
				<li><a href="?p=licentie" <?php if ($activePage == "licentie") {echo "class=\"active\"";} ?> >Licentie Beheer</a></li>
				<li><a href="?p=stock" <?php if ($activePage == "stock") {echo "class=\"active\"";} ?> >Voorraad Beheer</a></li>
				<li><a href="?p=employees" <?php if ($activePage == "employees") {echo "class=\"active\"";} ?> >Medewerkers Beheer</a></li>
				<li><a href="?p=users" <?php if ($activePage == "users") {echo "class=\"active\"";} ?> >Gebruikers Beheer</a></li>
				<?
				if ($_SESSION["level"] == 2)
				{
				?>
				<li><a href="?p=log" <?php if ($activePage == "log") {echo "class=\"active\"";} ?> >Logboek</a></li>
				<?
				}
				?>
			</ul>
		</div>
		<div class="navFooter">
			<div id="footerSettingsButton">
				<a href="?p=settings"><img src="./img/settings.png" width="16" height="16" alt="settings"></a>
			</div>
			<div id="footerLogoutButton">
				<a href="?p=logout"><img src="./img/power.png" width="16" height="16" alt="settings"></a>
			</div>
		</div>
	</div>

	<div class="content">

		<div class="contentNav">
			
		</div>

		<div class="pageContent">			
		<!--Content starts here-->