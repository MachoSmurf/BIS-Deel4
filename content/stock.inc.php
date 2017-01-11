<?php
	if (!defined("IN_SYSTEM"))
	{
		header("Location: ../index.php");
		die();
	}

	$showOverview	=	false;

	if (isset($_POST["submit"]))
	{
		$action	=	null;
		if (isset($_POST["action"]))
			{	$action 			=	$_POST["action"];	}

	/************************************************************************************
	/*
	/*		Show form to add new product to the database
	/*
	/***********************************************************************************/

		if ($_POST["submit"] == "Nieuw Product Toevoegen")
		{
			?>
			<div class="inputContainer">
			<form action="?p=stock" method="post">
				<div class="inputLine">
					<div class="inputLeft">Naam:</div>
					<div class="inputRight"> <input type="text" name="name"></div>
				</div>
				<div class="inputLine">
					<div class="inputLeft">Model/Type:</div>
					<div class="inputRight"> <input type="text" name="type"></div>
				</div>
				<div class="inputLine">
					<div class="inputLeft">Omschrijving:</div>
					<div class="inputRight"> <input type="text" name="description"></div>
				</div>
				<div class="inputLine">
					<div class="inputLeft"><input type="hidden" name="action" value="newProduct">
					<input type="submit" name="submit" value="Voeg Toe"></div>
				</div>
			</form>
			</div>
			<?php
		}

	/************************************************************************************
	/*
	/*		Add new product to the database
	/*
	/***********************************************************************************/
		
		if (($action == "newProduct") && ($_SESSION["level"] == 2))
		{
			if (isset($_POST["name"]) != "")
			{
				$name			=	$_POST["name"];
				$type 			=	null;	
				$description	=	null;

				if (isset($_POST["type"]))
					{	$type 			=	$_POST["type"];			}
				if (isset($_POST["description"]))
					{	$description 	=	$_POST["description"];	}


				//add to database
				$result = addProduct($name, $type, $description);
				switch ($result){
					case 1:
						?>
						Product succesvol toegevoegd.
						<?
						break;
					case 2:
						echo $dbConn -> error;
						break; 
					case 3:
						echo "Dit product bestaat al!";
						break;
				}
			}
			else
			{
				echo "Niet alle vereiste velden zijn ingevuld. Ga terug en probeer het nog eens.";
			}
		}

	/************************************************************************************
	/*
	/*		Show form to add new stock. 
	/*
	/***********************************************************************************/

		if ($_POST["submit"] == "Voorraad Toevoegen")
		{
			$stmt	=	$dbConn->prepare("SELECT DISTINCT(name) FROM product WHERE software=0");
			$stmt	->	execute();
			$stmt	->	bind_result($n);

			?>	
			<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
			<script>
				function ajaxRequest(obj)
				  {
				    $('#select2').empty()
				    var dropDown = document.getElementById("select1");
				    var prodName = dropDown.options[dropDown.selectedIndex].value;
				    $.ajax({
				            type: "POST",
				            //url: "/BIS-deel3/content/types.php",
				            url: "/content/types.php",
				            data: { 'prodName': prodName  },
				            datatype: 'json',
				            async: false,
				            success: function(data){
							    $.each(data, function(index, element) {
						            $('#select2').append($('<option value =' + element.id + '>' + element.type + '</option>'));
						        });
				            }
				        });
				  }
			</script>		

			<script>
				function checkWarranty(){
					if (document.forms["addForm"]["warranty"].value == "")
					{
						var response = confirm("Weet u zeker dat u dit product wilt toevoegen zonder garantiedatum?");
						if (response == true)
						{
							return true;
						}
						else
						{
							return false;
						}
					}
				}
			</script>

			<div class="inputContainer">
			<form action="?p=stock" method="post" name="addForm" onsubmit="return checkWarranty();">
				<div class="inputLine">
					<div class="inputLeft">Naam: </div>
					<div class="inputRight"><select name="name" id="select1" onchange="ajaxRequest(this)">
									<option value=""></option>
						<?php
							while($stmt	->	fetch())
							{
								?>
									<option value="<? echo $n; ?>"><? echo $n; ?></option>
								<?
							}
						?>						
						</select>
					</div>
				</div>
				<div class="inputLine">
					<div class="inputLeft">Model/Type: </div>
					<div class="inputRight"><select name="type" id="select2">						
								<option>Kies eerst een product</option>						
							</select></div>
				</div>
				<div class="inputLine">
					<div class="inputLeft">Aantal: </div>
					<div class="inputRight"><input type="number" min="1" step="1" name="amount" value="1"></div>
				</div>
				<div class="inputLine">
					<div class="inputLeft">Garantie (YYYY-MM-DD): </div>
					<div class="inputRight"><input type="text" name="warranty"></div>
				</div>
				<div class="inputLine">
					<div class="inputLeft">Service Tag: </div>
					<div class="inputRight"><input type="text" name="serviceTag"></div>
				</div>
				<div class="inputLine">
					<input type="hidden" name="action" value="newStock">
					<input type="submit" name="submit" value="Voeg Toe">
				</div>
			</form>
			</div>
			<?php
		}

	/************************************************************************************
	/*
	/*		Quick assign product to an employee
	/*
	/***********************************************************************************/

		if ($_POST["submit"] == "Toewijzen")
		{
			$showOverview = true;
			$assign	=	assignStock($_POST["employee_id"], $_POST["prodID"]);
			switch ($assign[0]) {
				case 0:
					?>
						<script>alert('Geen product beschikbaar om toe te wijzen');</script>
					<?
					break;
					
				case 1:
					?>
						<script>alert('Product toegewezen. Vooraad ID: <? echo $assign[1]; ?>, Servicetag: <? echo $assign[2]; ?>');</script>
					<?
					break;

				case 2:
						echo $dbConn->error;
					break;
			}

			
		}

	/************************************************************************************
	/*
	/*		Add new stock
	/*
	/***********************************************************************************/

		if ($action == "newStock")
		{
			if ((isset($_POST["name"]) != "") && (isset($_POST["amount"]) != ""))
			{
				$name			=	$_POST["name"];
				$typeID 		=	null;				
				$warranty		= 	null;
				$serviceTag		=	null;
				$amount			=	$_POST["amount"];

				if (isset($_POST["type"]))
					{	$typeID 			=	$_POST["type"];	}
				if (isset($_POST["warranty"]))
					{	$warranty 			=	$_POST["warranty"];	}
				if (isset($_POST["serviceTag"]))
					{	$serviceTag 		=	$_POST["serviceTag"];	}

				//add to database
				$result = addStock($name, $typeID, $amount, $warranty, $serviceTag);
				switch ($result){
					case 1:
						?>
						Product succesvol toegevoegd.
						<?
						break;
					case "2":
						echo $dbConn -> error;
						break; 
					case 3:
						echo "Dit product bestaat al!";
						break;
				}
			}
			else
			{
				echo "Niet alle vereiste velden zijn ingevuld. Ga terug en probeer het nog eens.";
			}
		}
	}

	/************************************************************************************
	/*
	/*		Check if product details need to be updated
	/*
	/***********************************************************************************/


	if ((isset($_GET["a"])) && (isset($_GET["id"])))
	{
		include("./content/productMaintenance.inc.php");
	}

	if (((!isset($_POST["submit"])) || ($showOverview)) && (!isset($_GET["a"])))
	{

	/************************************************************************************
	/*
	/*		Show the currently available stock
	/*
	/***********************************************************************************/

		?>
		<div>	
			<? if ($_SESSION["level"] == 2)
			{?>
			<form action="?p=stock" method="post" style="display: inline;">
				<input type="submit" value="Nieuw Product Toevoegen" name="submit">
			</form>
			<? } ?>
			<form action="?p=stock" method="post" style="display: inline;">
				<input type="submit" value="Voorraad Toevoegen" name="submit">
			</form>
		</div>	
		<?php
		
		//haal huidige Lijst met voorraad op
		$query = $dbConn->prepare("SELECT DISTINCT s.product_id, p.name, p.type, COUNT(s.product_id) FROM stock s, product p WHERE p.id=s.product_id AND p.software=0 GROUP BY s.product_id ORDER BY p.name, p.type ASC");
		$query -> execute();
		$query -> bind_result($prod_id, $prod_name, $prod_type, $count);
		$query -> store_result();

		?>
		<div>
			<table class="tbl_standard">
				<tr>
					<th>Product ID</th>
					<th>Product</th>
					<th>Model/Type</th>
					<th>Aantal Beschikbaar</th>
					<th></th>
				</tr>
			<?

			while($query -> fetch())
			{
				$countStmt	=	$dbConn->prepare("SELECT ID FROM stock WHERE product_id=? AND status=1");
				$countStmt	->	bind_param("i", $prod_id);
				$countStmt	->	execute();
				$countStmt	->	store_result();
				$stockAvailable = $countStmt->num_rows;

				?>
				<tr>
					<td><? echo $prod_id; ?></td>
					<td><a href="?p=stock&a=details&id=<? echo $prod_id; ?>"><? echo $prod_name; ?></a></td>
					<td><? echo $prod_type; ?></td>
					<td><? echo $stockAvailable."/".$count; ?></td>
					<td><a href="#" onclick="showModal(<? echo "'" . $prod_name . "'," . $prod_id; ?>); return false;">Toewijzen</a></td>
				</tr>
				<?
			}

			?>
			</table>
		</div>

		<!-- Trigger/Open The Modal -->
 
		
 		<!-- The Modal -->
		<div id="myModal" class="modal">

		  <!-- Modal content -->
		  <div class="modal-content">
		    <div class="modal-header">
		      <span class="close" onclick="document.getElementById('myModal').style.display = 'none'">×</span>
		      <h4>Product Toewijzen</h4>
		    </div>
		    <div class="modal-body">
		      <div class="modal-form">
		      	<form action="?p=stock" method="post">
		      		<input type="hidden" id="modal_prodID" name="prodID" value="">
		      		<span id="modal_prodName">prodName</span> Toewijzen aan: 
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

		function showModal(prodName, prodID)
		{
			document.getElementById("myModal").style.display 		= 	"block";
			document.getElementById("modal_prodName").textContent	= 	prodName;
			document.getElementById("modal_prodID").value 			=	prodID;
		}

		</script>
		<?		
	}
?>