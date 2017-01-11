<?php
	if (!defined("IN_SYSTEM"))
	{
		header("Location: ../index.php");
		die();
	}

	$stmt	=	$dbConn->prepare("SELECT e.voornaam, e.achternaam, e.ID FROM employee e WHERE status = 1");
	$stmt	->	execute();
	$stmt	->	bind_result($voornaam, $achternaam, $employee_id);

	?>
	Selecteer een medewerker om een overzicht van de toegewezen producten en licenties te bekijken
	<table class="tbl_standard" style="width: 25%;">
		<tr>
			<th>Medewerker</th>			
		</tr>
		<?
			while ($stmt	->	fetch())
				{
					?>
					<tr>
						<td><a href="?p=overview&employee_id=<? echo $employee_id; ?>"><? echo $voornaam . " " . $achternaam;?></a></td>
					</tr>
					<?
				}
		?>
	</table>
	<?

?>