<?
	if (!defined("IN_SYSTEM"))
	{
		header("Location: ../index.php");
		die();
	}	

	//check if the status of a product needs to be updated
	if (isset($_POST["submit"]))
	{
		if (isset($_POST["keyID"]))
		{
			//assign key to user
			$keyID 	=	$_POST["keyID"];
			$empID 	=	$_POST["employee_id"];
			$stmt 	=	$dbConn -> prepare("UPDATE licence SET employee_id = ? WHERE ID = ?");
			$stmt 	->	bind_param("ii", $empID, $keyID);
			if ($stmt -> execute())
			{
				//addLogEntry($userID, $stockID, $employeeID, $licenceID, $productID, $actionCode, $param1, $param2)
				addLogEntry(null, null, $empID, $keyID, null, 73, null, null);
				?>
				<div class="infoSucces">Licentie succesvol gekoppeld!</div>
				<?
			}
			else
			{
				?>
				<div class="infoError">Licentie koppelen mislukt</div>
				<?
			}
		}
		else
			{echo "error";}
	}

	$prodID;
	if (!isset($_GET["id"]))
	{	
		echo "Unknown Licence ID";
	}
	else
	{
		$prodID	=	$_GET["id"];

		//fetch software info
		$stmt	=	$dbConn	->	prepare("SELECT name, type, description FROM product WHERE `ID` = ?");
		$stmt	-> 	bind_param("i", $prodID);
		$stmt	->	execute();
		$stmt	->	bind_result($name, $type, $description);
		$stmt	->	fetch();
		$stmt 	->	close();

		$stmt	=	$dbConn->prepare("SELECT COUNT(l.ID) AS teller FROM product p, licence l WHERE l.product_id=p.ID AND p.software=1 AND p.id=? AND l.employee_id IS NULL AND l.stock_id IS NULL");
		$stmt	->	bind_param("i", $prodID);
		$stmt	->	execute();
		$stmt 	-> 	bind_result($available);
		$stmt 	->  fetch();
		$stmt 	->	close();

		?>
		<div>
			Product: <? echo $name;?><br>
			Versie:	<? echo $type; ?><br>
			Omschrijving: <? echo $description; ?><br>
			Licenties Beschikbaar: <? echo $available; ?><br>
		</div>
		<?

		$highlight = null;
		if (isset($_GET["highlight"]))
		{
			$highlight = $_GET["highlight"];
		}

		$stmt 	=	$dbConn -> prepare("SELECT ID, stock_id, employee_id, exp_date, licencekey FROM licence WHERE product_id = ?");
		$stmt	-> 	bind_param("i", $prodID);
		$stmt	->	execute();
		$stmt	->	bind_result($keyID, $stock_id, $employee_id, $exp_date, $licencekey)

		?>

		<table class="tbl_standard">
			<tr>
				<th>Licenctie ID</th>
				<th>Uitgegeven</th>
				<th>Key</th>
				<th>Toewijzen</th>
				<th>Verloopdatum</th>
			</tr>
			<?
				while($stmt	->	fetch())
				{
					?>
						<tr <? if ($highlight == $keyID) { ?> id="highlightRow" <?}?>>
							<td><? echo $keyID; ?></td>
							<td><?  
								if (($stock_id != null) || ($employee_id != null))
								{
									//echo "Ja";
									?><input type="checkbox" disabled checked>
									<a href="?p=overview<? if ($stock_id != null) {echo "&stock_id=" . $stock_id;} else {echo "&employee_id=" . $employee_id;} ?>&highlightLicence=<? echo $keyID; ?>"><img src="./img/Attention-50.png" class="licenceExpCheck"></a>
									<?
								}
								else
								{
									//echo "Nee";
									?><input type="checkbox" disabled><?
								}
							?></td>
							<td><? echo $licencekey; ?></td>
							<td><? 
								if (($stock_id == null) && ($employee_id == null) && ((strtotime($exp_date) > time()) || ($exp_date == null)))
								{
									?><a href="#" onclick="showModal('<? echo $keyID; ?>'); return false;">Toewijzen</a>
								<?
								}
								?>
							</td>
							<td><? 
								echo $exp_date . "&nbsp;";
								if (isset($exp_date))
									{
									if (strtotime($exp_date) > time())
									{
										?><img src="./img/Checked-48.png" class="licenceExpCheck"><?
									}
									if (strtotime($exp_date) < time())
									{
										?><img src="./img/Cancel-50.png" class="licenceExpCheck"><?
									}
								}
							?></td>
						</tr>
					<?
				}
			?>
		</table>
		<?

		$stmt	->	close();

		//information Modal
		?>
		<div id="myModal" class="modal">

		  <!-- Modal content -->
		  <div class="modal-content" style="width: 30%;">
		    <div class="modal-header">
		      <span class="close" onclick="document.getElementById('myModal').style.display = 'none'">×</span>
		      <h4>Licentie Toewijzen</h4>
		    </div>
		    <div class="modal-body">
		      <div class="modal-form">
		      	<form action="?p=licentie&a=details&id=<? echo $prodID; ?>" method="post">
		    		<input type="hidden" id="modalKeyID" name="keyID" value="">
		      		Licentie toewijzen aan: 
		      		<select name="employee_id">
		      			<?
		      				$stmt	=	$dbConn->prepare("SELECT id, voornaam, achternaam FROM employee WHERE status=1");
		      				$stmt	->	execute();
		      				$stmt	-> 	bind_result($emp_id, $firstname, $lastname);

		      				while($stmt	->	fetch())
		      				{
		      					?>
		      						<option value="<? echo $emp_id;?>"><? echo $firstname . " " . $lastname; ?></option>
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

		function showModal(keyID)
		{
			document.getElementById("myModal").style.display	= 	"block";
			document.getElementById("modalKeyID").value 		=	keyID;
		}

		</script>		
		<?
	}
?>