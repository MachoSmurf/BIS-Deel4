<?
	if (!defined("IN_SYSTEM"))
	{
		header("Location: ../index.php");
		die();
	}	

	if($_SESSION["level"] == 2)
	{
		/*$logStmt	=	$dbConn->prepare("SELECT l.ID, u.username AS owner, us.username AS subject, pr.name AS item, pro.name ,e.voornaam, e.achternaam, l.licence_id, l.action, l.time, l.parameter1, l.parameter2
	FROM log l 
		LEFT JOIN user u ON l.owner_id = u.user_id
		LEFT JOIN user us ON l.user_id = us.user_id        
        LEFT JOIN employee e ON l.employee_id = e.ID
        LEFT JOIN stock s ON l.stock_id = s.ID
        LEFT JOIN product pr ON s.product_id = pr.ID
        LEFT JOIN product pro ON l.product_id = pro.ID
        	ORDER BY l.time DESC LIMIT 0, 30");*/

        $logStmt	=	$dbConn->prepare("SELECT l.ID, u.username AS owner, us.username AS subject, l.action, l.time, l.parameter1, l.parameter2, p.name, p.type, e.voornaam, e.achternaam, li.ID, s.ID
	FROM log l 
		LEFT JOIN user u ON l.owner_id = u.user_id
		LEFT JOIN user us ON l.user_id = us.user_id
        LEFT JOIN product p ON l.product_id = p.ID
        LEFT JOIN employee e ON l.employee_id = e.ID
        LEFT JOIN stock s ON l.stock_id = s.ID
        LEFT JOIN licence li ON l.licence_id = li.ID
        	ORDER BY l.time DESC LIMIT 0, 30");
		$logStmt	->	execute();
		$logStmt	->	bind_result($logID, $owner, $subjectUser, $action, $time, $param1, $param2, $productName, $productType, $empFirstname, $empLastname, $licenceID, $stockID);

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
										echo $owner . " heeft de medewerker " . $empFirstname ." " . $empLastname . " verwijderd."; 
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
										echo $owner . " heeft " . $param1 . " nieuwe " . $productName . " " . $productType . " toegevoegd aan de voorraad."; 
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
										echo $owner . " heeft de status van het item " . $productName . " aangepast naar: " . $statusText; 
									break;

									case 61:
										//New Software Added to database
										echo $owner . " heeft het softwarepakket " . $productName . " toegevoegd aan de lijst met beschikbare producten."; 
										break;

									case 62:
										//Software edited
										echo $owner . " heeft het softwarepakket " . $productName . " bewerkt.";
										break;

									case 71:
										//Licences added to database (parameter1 amount)
										echo $owner . " heeft " . $param1 . " licenties van " . $productName . " " . $productType . " toegevoegd."; 
										break;

									case 72:
										//Licence status changed (parameter1 status)
										echo $owner . " heeft licentie #" . $licenceID . " losgekoppeld.";
										break;

									case 73:
										//Licence status changed (parameter1 status)
										echo $owner . " heeft licentie #" . $licenceID . " gekoppeld aan medewerker " . $empFirstname ." " . $empLastname;
										break;

									case 74:
										//Licence status changed (parameter1 status)
										echo $owner . " heeft licentie #" . $licenceID . " gekoppeld aan systeem #" . $stockID;
										break;

									default:
										echo "Log entry unknown (" . $action . ")";
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