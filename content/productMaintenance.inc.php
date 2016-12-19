<?
	if (!defined("IN_SYSTEM"))
	{
		header("Location: ../index.php");
		die();
	}	

	//check if the status of a product needs to be updated
	if (isset($_POST["submit"]))
	{
		if ((isset($_POST["prodID"])) && (isset($_POST["newStatus"])))
		{

			$changeProdId	=	$_POST["prodID"];
			$newStatus		=	$_POST["newStatus"];

			if (updateStatus($changeProdId, $newStatus))
			{
				?>
				<script>alert('De status is succesvol bijgewerkt');</script>
				<?
			}
			else
			{
				?>
				<script>alert('Er ging iets fout bij het bijwerken van de status: <? echo $dbConn->error; ?>');</script>
				<?
			}
		}
	}


	$prodID;
	if (isset($_GET["id"]))
	{	$prodID	=	$_GET["id"];}

	//fetch product info
	$stmt	=	$dbConn	->	prepare("SELECT name, type, description FROM product WHERE `ID` = ?");
	$stmt	-> 	bind_param("i", $prodID);
	$stmt	->	execute();
	$stmt	->	bind_result($name, $type, $description);
	$stmt	->	fetch();
	$stmt 	->	close();

	?>
	<div>
		Product: <? echo $name;?><br>
		Model/Type:	<? echo $type; ?><br>
		Omschrijving: <? echo $description; ?><br>
	</div>
	<?

	$stmt 	=	$dbConn-> prepare("SELECT s.ID, s.warranty, s.servicetag, s.status, s.ip, e.voornaam, e.achternaam FROM stock s LEFT JOIN employee e ON s.employee_id = e.ID WHERE s.product_id=? ORDER BY s.status");
	$stmt	-> 	bind_param("i", $prodID);
	$stmt	->	execute();
	$stmt	->	bind_result($stockID, $warranty, $servicetag, $status, $ip, $voornaam, $achternaam);

	?>
	Voorraad
	<table class="tbl_standard">
		<tr>
			<th>Voorraadnummer</th>
			<th>Status</th>
			<th>Garantie</th>
			<th>Uitgegeven aan</th>
			<th>ServiceTag</th>
			<th>IP</th>
			<th></th>
		</tr>

		<?
		while ($stmt->fetch())
		{
			?>
			<tr>
				<td><? echo $stockID; ?></td>
				<td>
					<?
					switch ($status) {
						case 1:
							echo "Beschikbaar";
							break;

						case 2:
							echo "Uitgegeven";
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
					}
					?>
				</td>
				<td><? echo $warranty; ?></td>
				<td><? echo $voornaam . " " . $achternaam; ?></td>
				<td><? echo $servicetag; ?></td>
				<td><? echo $ip; ?></td>
				<td><a href="#" onclick="showModal('<? echo $name; ?>', '<? echo $stockID; ?>', '<? echo $status; ?>'); return false;">Wijzig status</a></td>
			</tr>
			<?
		}
		$stmt	->	close();
		?>
	</table>



	<!-- The Modal -->
		<div id="myModal" class="modal">

		  <!-- Modal content -->
		  <div class="modal-content" style="width: 30%;">
		    <div class="modal-header">
		      <span class="close" onclick="document.getElementById('myModal').style.display = 'none'">×</span>
		      <h4>Status aanpassen</h4>
		    </div>
		    <div class="modal-body">
		      <div class="modal-form">
		      	<form action="?p=stock&a=details&id=<? echo $prodID; ?>" method="post">
		      		<input type="hidden" id="modal_prodID" name="prodID" value="">
		      		<span id="modal_prodName">prodName</span> wijzigen in: 
		      		<select name="newStatus">	
		      			<option value="1">Beschikbaar</option>
		      			<option value="3">Defect</option>
		      			<option value="4">In Reparatie</option>
		      			<option value="5">Afgeschreven</option>		      				
		      		</select>
		      		<input type="submit" value="Status Aanpassen" name="submit">
		      	</form>
		      </div>
		    </div>
		    <div class="modal-footer">
		  	<h5>Let op! Het wijzigen van de status in dit scherm zorgt er in alle gevallen voor dat de koppeling tussen product en werknemer verbroken wordt!</h5>
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

		function showModal(prodName, prodID, status)
		{
			document.getElementById("myModal").style.display 		= 	"block";
			document.getElementById("modal_prodName").textContent	= 	prodName;
			document.getElementById("modal_prodID").value 			=	prodID;
		}

		</script>
	<?
	
?>