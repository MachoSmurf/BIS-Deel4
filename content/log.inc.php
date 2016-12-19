<?
	if (!defined("IN_SYSTEM"))
	{
		header("Location: ../index.php");
		die();
	}	

	if($_SESSION["level"] == 2)
	{
		$logStmt	=	$dbConn->prepare("SELECT l.ID, u.username AS owner, us.username AS subject, pr.name AS item, pro.name ,e.voornaam, e.achternaam, l.licence_id, l.action, l.time, l.parameter1, l.parameter2
	FROM log l 
		LEFT JOIN user u ON l.owner_id = u.user_id
		LEFT JOIN user us ON l.user_id = us.user_id        
        LEFT JOIN employee e ON l.employee_id = e.ID
        LEFT JOIN stock s ON l.stock_id = s.ID
        LEFT JOIN product pr ON s.product_id = pr.ID
        LEFT JOIN product pro ON l.product_id = pro.ID
        	ORDER BY l.time DESC LIMIT 0, 30");
		$logStmt	->	execute();
		$logStmt	->	bind_result($logID, $owner, $subjectUser, $stockItemName, $productName, $empFirstname, $empLastname, $licenceID, $action, $time, $param1, $param2);

		?>

		<table class="tbl_standard">
			<tr>
				<th>Tijd</th>
				<th>Gebruiker</th>
				<th>Actie</th>
			</tr>
			<?
				while($logStmt->fetch())
				{
					?>
						<tr>
							<td><? echo $time ?></td>
							<td><? echo $owner; ?></td>
							<td>
								<?
								switch($action)
								{
									//User events
									case 1:
										echo $owner . " heeft de gebruiker " . $subjectUser . " aangemaakt."; 
									break;

									case 2:
										echo $owner . " heeft de gebruiker " . $subjectUser . " bewerkt."; 
									break;

									case 3:
										echo $owner . " heeft de gebruiker " . $subjectUser . " administrator rechten gegeven."; 
									break;

									case 4:
										echo $owner . " heeft de gebruiker " . $subjectUser . " (in)actief gemaakt."; 
									break;	

									case 5:
										echo $owner . " is ingelogd."; 
									break;	

									case 6:
										echo $owner . " heeft een mislukte inlogpoging gedaan."; 
									break;	

									//employee events
									case 21:
										echo $owner . " heeft de medewerker " . $empFirstname ." " . $empLastname . " aangemaakt."; 
									break;

									case 22:
										echo $owner . " heeft de medewerker " . $empFirstname ." " . $empLastname . " bewerkt."; 
									break;

									case 23:
										echo $owner . " heeft de status van medewerker " . $empFirstname ." " . $empLastname . " bewerkt."; 
									break;

									//product events

									case 41:
										echo $owner . " heeft het product " . $productName . " toegevoegd aan de lijst met beschikbare producten."; 
									break;

									case 42:
										echo $owner . " heeft het product " . $productName . " bewerkt."; 
									break;

									//stock events

									case 51:
										echo $owner . " heeft " . $param1 . " nieuwe " . $stockItemName . " toegevoegd aan de voorraad."; 
									break;

									case 52:
										$statusText = "<span style=\"font-weight: bold;\">";
										switch ($param1)
										{
											case 1:
												$statusText .= "Beschikbaar";
											break;

											case 2:
												$statusText .=  "Uitgegeven";
											break;

											case 3:
												$statusText .=  "Defect";
											break;

											case 4:
												$statusText .=  "In reparatie";
											break;

											case 5:
												$statusText .=  "Afgeschreven";
											break;
											
											default:
												$statusText .=  "Onbekend";
											break;
										}
										$statusText .= "</span>";
										echo $owner . " heeft de status van het item " . $stockItemName . " aangepast naar: " . $statusText; 
									break;

									default:
										echo "Log entry unknown";
									break;
								}
								?>
							</td>
						</tr>
					<?
				}
			?>
		</table>

		<?

	}
	else
	{
		echo "Not authorised!";
	}
?>