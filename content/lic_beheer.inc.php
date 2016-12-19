<?php
	if (!defined("IN_SYSTEM"))
	{
		header("Location: ../index.php");
		die();
	}

	if (isset($_POST["submit"]))
	{
		//user wants to add or remove software
		if ($_POST["submit"] == "Software Toevoegen")
		{
			?>
			<form action="?p=licentie" method="post">
				Software Naam: <input type="text" name="name"><br>
				Software Versie: <input type="text" name="version"><br>
				<input type="submit" name="submit" value="Voeg Toe">
			</form>
			<?php
		}
		if ($_POST["submit"] == "Voeg Toe")
		{
			if ((isset($_POST["name"]) != "") && (isset($_POST["version"]) != ""))
			{
				//add to database
				$q = $dbConn->prepare("INSERT INTO `software` (naam, versie) VALUES (?, ?)");
				$q	->	bind_param("ss", $_POST["name"], $_POST["version"]);

				if ($q	->	execute())
				{
					?>
					Software Succesvol toegevoegd. 
					<?php
				}
			}
		}
	}
	else
	{

	?>
			
		<!--<form action="?p=licentie" method="post">
			<input type="submit" value="Software Toevoegen" name="submit">
		</form>-->
			
			
		<div class="infoWarning">Licentiebeheer wordt in de volgende versie geimplementeerd</div>

		<?php
		/*
		//haal huidige softwarelijst op
		$query = $dbConn->prepare("SELECT software_id, naam, versie FROM software");
		$query -> execute();
		$query -> bind_result($sID, $naam, $versie);

		echo "<table>";

		while($query -> fetch())
		{
			echo "<tr>";
				echo "<td>" . $sID . "  -  " . $naam . "  -  " . $versie . "</td>";
			echo "</tr>";
		}

		echo "</table>";*/
		
	}
	?>