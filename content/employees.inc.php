<?php
	if (!defined("IN_SYSTEM"))
	{
		header("Location: ../index.php");
		die();
	}

	if (isset($_POST["submit"]))
	{
		//user wants to add or remove software
		if ($_POST["submit"] == "Medewerker Toevoegen")
		{
			?>
			<div class="inputContainer">
				<form action="?p=employees" method="post">
				<div class="inputLine">
					<div class="inputLeft">Voornaam: </div>
					<div class="inputRight"><input type="text" name="voornaam"></div>
				</div>
				<div class="inputLine">
					<div class="inputLeft">Achternaam: </div>
					<div class="inputRight"><input type="text" name="achternaam"></div>
				</div>
				<div class="inputLine">
					<div class="inputLeft">Email: </div>
					<div class="inputRight"><input type="text" name="email"></div>
				</div>
				<div class="inputLine">
					<div class="inputLeft"><input type="submit" name="submit" value="Voeg Toe"></div>
				</div>
				</form>
			</div>
			<?php
		}
		if ($_POST["submit"] == "Voeg Toe")
		{
			if ((isset($_POST["voornaam"]) != "") && (isset($_POST["achternaam"]) != "") && (isset($_POST["achternaam"]) != ""))
			{
				
				//add to database
				$result = addEmployee($_POST["voornaam"], $_POST["achternaam"], $_POST["email"]);
				switch ($result){
					case 1:
						?>
						Medewerker succesvol toegevoegd.
						<?
						break;
					case 2:
						echo $dbConn -> error;
						break;
					case 3:
						echo "Deze medewerker bestaat al!";
						break;
				}
			}
			else
			{
				echo "Niet alle vereiste velden zijn ingevuld. Ga terug en probeer het nog eens.";
			}
		}
	}
	else
	{
		?>
			
		<form action="?p=employees" method="post">
			<input type="submit" value="Medewerker Toevoegen" name="submit">
		</form>
			
		<?php
	
		//haal huidige Lijst met medewerkers op op
		$query = $dbConn->prepare("SELECT ID, voornaam, achternaam, email FROM employee WHERE status > 0 ORDER BY ID ASC");
		$query -> execute();
		$query -> bind_result($emp_id, $voornaam, $achternaam, $email);

		echo "<table>";
		?>
		<table class="tbl_standard">
			<tr>
				<th>ID</th>
				<th>Voornaam</th>
				<th>Achternaam</th>
				<th>Email</th>
				<th></th>
			</tr>
		<?

		while($query -> fetch())
		{
			?>
			<tr>
				<td><a href="?p=overview&employee_id=<? echo $emp_id; ?>"><? echo $emp_id; ?></a></td>
				<td><? echo $voornaam; ?></td>
				<td><? echo $achternaam; ?></td>
				<td><? echo $email; ?></td>
				<td><!--Edit--></td>
			</tr>
			<?
		}

		echo "</table>";
		
	}
?>