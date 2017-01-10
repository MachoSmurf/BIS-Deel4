<?
	if (!defined("IN_SYSTEM"))
	{
		header("Location: ../index.php");
		die();
	}	

	//this page gives a per-person overview of everything assigned to them

	//first, make sure we know which employee we want to show the overview of. If the user was send to this page through a stock_id link, find the employee attached to that stock

	$employeeID = null;

	if (isset($_GET["stock_id"]))
	{
		$stock_id	=	$_GET["stock_id"];

		$stmt	=	$dbConn->prepare("SELECT employee_id FROM stock WHERE ID = ?");
		$stmt	->	bind_param("i", $stock_id);
		$stmt	-> 	bind_result($emp_ID);
		$stmt	->	execute();	
		$stmt	-> 	fetch();	
		$employeeID = $emp_ID;
		$stmt	->	close();
	}

	if (isset($_GET["employee_id"]))
	{
		$employeeID = $_GET["employee_id"];

		if (isset($_POST["submit"]))
		{
			//assign a licences to stock
			if ((isset($_POST["modalSystemID"])) && (isset($_POST["product_id"])))
			{
				$stockID 	=	$_POST["modalSystemID"];
				$productID 	=	$_POST["product_id"];
				$assignResult = null;
				if ($stockID == 0)
				{
					//assign to the employee instead of a system					
					$assignResult 	=	assignLicenceToEmployee($productID, $employeeID);
				}
				else
				{
					$assignResult	=	assignLicenceToSystem($stockID, $productID);
				}				
				if ($assignResult[0] == 1)
				{
					//succes
					if ($stockID != 0)
					{
						?>
						<div class="infoSucces">Licentie succesvol gekoppeld aan systeem <? echo $stockID; ?>. Key: <? echo $assignResult[2]; ?></div>
						<?
					}
					else
					{
						?>
						<div class="infoSucces">Licentie succesvol gekoppeld. Key: <? echo $assignResult[2]; ?></div>
						<?
					}
				}
				if ($assignResult[0] == 2)
				{
					//sql error
					?><div class="infoError">Licentie koppelen mislukt</div><?
				}
				if ($assignResult[0] == 3)
				{
					//no licences available
					?><div class="infoWarning">Geen licenties beschikbaar om te koppelen</div><?
				}
			}
		}
	}

	if (isset($_GET["disconnectLicence"]))
	{
		$licenceDisconnectID	=	$_GET["disconnectLicence"];

		if (disconnectLicence($licenceDisconnectID))
		{
			?><div class="infoSucces">Licentie succesvol ontkoppeld.</div><?
		}	
		else
		{
			?><div class="infoError">Licentie onkoppelen mislukt.</div><?
		}
	}

	if (isset($_GET["disconnectSystem"]))
	{
		$stockDisconnectID	=	$_GET["disconnectSystem"];

		if (disconnectStock($stockDisconnectID, 1))
		{
			?><div class="infoSucces">Systeem succesvol ontkoppeld.</div><?
		}	
		else
		{
			?><div class="infoError">Systeem onkoppelen mislukt.</div><?
		}
	}

	if ($employeeID != null)
	{
		//show employee info
		$stmt	=	$dbConn->prepare("SELECT voornaam, achternaam, email FROM employee WHERE ID = ?");
		$stmt	->	bind_param("i", $employeeID);
		$stmt	->	execute();
		$stmt	->	bind_result($voornaam, $achternaam, $email);
		$stmt	->	fetch();
		$stmt	-> 	close();

		?>
		<div>
			Medewerker Overzicht:<br>
			Voornaam: <? echo $voornaam; ?><br>
			Achternaam: <? echo $achternaam; ?><br>
			Email: <? echo $email; ?><br>
		</div>
		<div>
			<h4>Toegewezen Systemen:</h4>
			<?
			$stmt	=	$dbConn->prepare("SELECT p.name, p.type, s.ID, s.warranty, s.status, s.servicetag, s.ip FROM stock s, product p WHERE s.product_id = p.ID AND s.employee_id = ?");
			$stmt	->	bind_param("i", $employeeID);
			$stmt	->	bind_result($productName, $productType, $stockID, $warranty, $status, $servicetag, $ip);
			$stmt	->	execute();
			
			$stmt 	->	store_result();

			?>
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
			<script>
			var main = function() {
			  $("thead").on("click", function() {
			    $(this).parents("table").find("tbody").toggle();
			  });

			}
			$(document).ready(main);
			</script>

			
			<table class="tbl_overview_head">
				<thead>
					<tr>
						<th class="col1"></th>
						<th class="col2">Voorraad ID</th>
						<th class="col3">Product</th>
						<th class="col4">Type</th>
						<th class="col5">Garantie</th>
						<th class="col6">Status</th>
						<th class="col7">Servicetag</th>
						<th class="col8">IP</th>
					</tr>
				</thead>
			</table>					
			<?

			while($stmt	->	fetch())
			{
				?>
			<table class="tbl_overview_systemTable">
				<thead>
					<tr class="item">
						<td class="col1"><a href="?p=overview&employee_id=<? echo $employeeID; ?>&disconnectSystem=<? echo $stockID; ?>" onclick="return confirm('Weet u zeker dat u dit systeem wilt ontkoppelen? Alle gekoppelde licenties blijven gekoppeld aan dit systeem.');"><img src="./img/minus.png"/></a></td>
						<td class="col2"><? echo $stockID; ?></td>
						<td class="col3"><? echo $productName; ?></td>
						<td class="col4"><? echo $productType; ?></td>
						<td class="col5"><? echo $warranty; 
							if (isset($warranty))
							{
								if (strtotime($warranty) > time())
								{
									?><img src="./img/Checked-48.png" class="licenceExpCheck"><?
								}
								if (strtotime($warranty) < time())
								{
									?><img src="./img/Cancel-50.png" class="licenceExpCheck"><?
								}
							}
						?></td>
						<td class="col6"><? switch ($status) {
						case 1:
							echo "Beschikbaar";
							break;

						case 2:
							echo "Serviceable";
							break;

						case 3:
							echo "Defect";
							break;

						case 4:
							echo "In reparatie";
							break;

						case 5:
							echo "Afgeschreven";
							break;
						
						default:
							echo "Onbekend";
							break;
					} ?></td>
						<td class="col7"><? echo $servicetag; ?></td>
						<td class="col8"><? echo $ip; ?></td>
					</tr>
				</thead>
				<?
				//fetch Licences attached to this system

				$highlightLicence = null;
				if (isset($_GET["highlightLicence"]))
					{ $highlightLicence = $_GET["highlightLicence"]; }
				?>

				<tbody>
					<tr>
						<td colspan=1>&nbsp;</td>
						<td colspan=7>
								<table style="width: 100%;">	
								<tr class="darkRow">
									<th></th>
									<th colspan="4">Gekoppelde Licenties</th>
								</tr>
								<tr class="darkRow">
									<th style="width: 2%;"></th>
									<th>Pakket</th>
									<th>Versie</th>
									<th>Verloopdatum</th>
									<th>Key</th>
								</tr>
								<?
								if ($licenceStmt	=	$dbConn->prepare("SELECT l.ID, p.ID, p.name, p.type, l.exp_date, l.licencekey FROM licence l, product p WHERE l.product_id = p.ID AND l.stock_id = ?"))
								{
									$licenceStmt	->	bind_param("i", $stockID);
									$licenceStmt	->	execute();
									$licenceStmt	->	bind_result($licenceID, $productID, $productName, $productVersion, $licenceExpDate, $licenceKey);
									while($licenceStmt	->	fetch())
									{
										?>
										<tr class="item" <? if ($highlightLicence == $licenceID) {?> id="highlightRow"<?} ?>>
											<td style="width: 2%;"><a href="?p=overview&employee_id=<? echo $employeeID; ?>&disconnectLicence=<? echo $licenceID; ?>" onclick="return confirm('Weet u zeker dat u deze licentie wilt ontkoppelen?');"><img src="./img/minus.png"/></a></td>
											<td><span title="Licentie ID: <? echo $licenceID; ?>"><a href="?p=licentie&a=details&id=<? echo $productID; ?>&highlight=<? echo $licenceID; ?>"><? echo $productName; ?></a></span></td>
											<td><? echo $productVersion; ?></td>
											<td><? echo $licenceExpDate; 
											if (isset($licenceExpDate))
											{
												if (strtotime($licenceExpDate) > time())
												{
													?><img src="./img/Checked-48.png" class="licenceExpCheck"><?
												}
												if (strtotime($licenceExpDate) < time())
												{
													?><img src="./img/Cancel-50.png" class="licenceExpCheck"><?
												}
											} 
											?></td>
											<td><? echo $licenceKey; ?></td>
										</tr>										
										<?
									}
								}									
								?>
								<tr class="item">
									<td style="width: 2%; margin: auto;"><a href="#" onclick="showModal(<? echo $stockID; ?>); return false;"><img src="./img/add.png" style="width: 16px; height: 16px;"/></a></td>
									<td colspan="4"><a href="#" onclick="showModal(<? echo $stockID; ?>); return false;"> Licentie toevoegen</a></td>
								</tr>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
					<?	
			}

			$stmt	->	close();

			?>
		</div>
		<div class="spacer">
				
		</div>

		<div>
			<h4>Toegewezen Licenties:</h4>
				<table class="tbl_standard">
					<tr>
						<th style="width: 2%;"></th>
						<th>Licentie ID</th>
						<th>Pakket</th>
						<th>Versie</th>
						<th>Verloopdatum</th>
						<th>Key</th>
					</tr>
			<?	
				if ($licenceStmt	=	$dbConn->prepare("SELECT l.ID, p.ID, p.name, p.type, l.exp_date, l.licencekey FROM licence l, product p, employee e WHERE l.product_id = p.ID AND l.employee_id=e.ID AND e.ID =?"))
								{
									$licenceStmt	->	bind_param("i", $employeeID);
									$licenceStmt	->	execute();
									$licenceStmt	->	bind_result($licenceID, $productID, $productName, $productVersion, $licenceExpDate, $licenceKey);
									while($licenceStmt	->	fetch())
									{
										?>
										<tr<? if ($highlightLicence == $licenceID) {?> id="highlightRow" <?} ?>>
											<td style="width: 2%;"><a href="?p=overview&employee_id=<? echo $employeeID; ?>&disconnectLicence=<? echo $licenceID; ?>" onclick="return confirm('Weet u zeker dat u deze licentie wilt ontkoppelen?');"><img src="./img/minus.png"/></a></td>
											<td><? echo $licenceID; ?></td>
											<td><span title="Licentie ID: <? echo $licenceID; ?>"><a href="?p=licentie&a=details&id=<? echo $productID; ?>&highlight=<? echo $licenceID; ?>"><? echo $productName; ?></a></span></td>
											<td><? echo $productVersion; ?></td>
											<td>
											<? 
											echo $licenceExpDate;
											if (isset($licenceExpDate))
											{
												if (strtotime($licenceExpDate) > time())
												{
													?><img src="./img/Checked-48.png" class="licenceExpCheck"><?
												}
												if (strtotime($licenceExpDate) < time())
												{
													?><img src="./img/Cancel-50.png" class="licenceExpCheck"><?
												}
											}
											?>												
											</td>
											<td><? echo $licenceKey; ?></td>
										</tr>
										<?
									}
								}
			?>
					<tr>
						<td style="width: 2%; margin: auto;"><a href="#" onclick="showModal(0); return false;"><img src="./img/add.png" style="width: 16px; height: 16px;"/></a></td>
						<td colspan="5"><a href="#" onclick="showModal(0); return false;">Licentie toevoegen</a></td>
					</tr>
				</table>
		</div>

		<!--Modal for adding licences to a system-->
		<div id="myModal" class="modal">

		  <!-- Modal content -->
		  <div class="modal-content" style="width: 30%;">
		    <div class="modal-header">
		      <span class="close" onclick="document.getElementById('myModal').style.display = 'none'">×</span>
		      <h4>Licentie Toewijzen</h4>
		    </div>
		    <div class="modal-body">
		      <div class="modal-form">
		      	<form action="?p=overview&employee_id=<? echo $employeeID; ?>" method="post">
		    		<input type="hidden" id="modalSystemID" name="modalSystemID" value="">
		      		Selecteer product om toe te wijzen:
		      		<select name="product_id">
		      			<?
		      				$stmt	=	$dbConn->prepare("SELECT DISTINCT p.ID, p.name, p.type FROM product p, licence l WHERE l.product_id=p.ID AND p.software=1 AND l.stock_id IS NULL AND l.employee_id IS NULL GROUP BY p.name");
		      				$stmt	->	execute();
		      				$stmt	-> 	bind_result($productID , $productName, $productType);

		      				while($stmt	->	fetch())
		      				{
		      					?>
		      						<option value="<? echo $productID;?>"><? echo $productName . " " . $productType; ?></option>
		      					<?
		      				}
		      			?>
		      		</select>
		      		<input type="submit" value="Toewijzen" name="submit">
		      	</form>
		      </div>
		    </div>
		    <div class="modal-footer">		  	
		  	</div>
		  </div>
		  

		</div>

		<script>

		// Get the modal
		var modal = document.getElementById('myModal');

		// When the user clicks anywhere outside of the modal, close it
		window.onclick = function(event) {
		    if (event.target == modal) {
		        modal.style.display = "none";
		    }
		}

		function showModal(systemID, $employeeID)
		{
			document.getElementById("myModal").style.display	= 	"block";
			document.getElementById("modalSystemID").value 		=	systemID;
		}

		</script>



		<?
	}
?>