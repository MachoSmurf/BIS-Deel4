<?php
	if (!defined("IN_SYSTEM"))
	{
		header("Location: ../index.php");
		die();
	}

	$showOverview = false;

	if (isset($_POST["submit"]))
	{
		//see if a form needs to be processed
		$action	=	null;
		if (isset($_POST["action"]))
			{	$action 			=	$_POST["action"];	}

	/************************************************************************************
	/*
	/*		Show form to add new software to the database
	/*
	/***********************************************************************************/

		if ($_POST["submit"] == "Nieuw Softwarepakket Toevoegen")
		{
			?>
			<div class="inputContainer">
			<form action="?p=licentie" method="post">
				<div class="inputLine">
					<div class="inputLeft">Naam:</div>
					<div class="inputRight"> <input type="text" name="name"></div>
				</div>
				<div class="inputLine">
					<div class="inputLeft">Versie:</div>
					<div class="inputRight"> <input type="text" name="type"></div>
				</div>
				<div class="inputLine">
					<div class="inputLeft">Omschrijving:</div>
					<div class="inputRight"> <input type="text" name="description"></div>
				</div>
				<div class="inputLine">
					<div class="inputLeft"><input type="hidden" name="action" value="newSoftware">
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
		
		if (($action == "newSoftware") && ($_SESSION["level"] == 2))
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
				$result = addSoftware($name, $type, $description);
				switch ($result){
					case 1:
						?>
						Software succesvol toegevoegd.
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
	/*		Show form to add new licence. 
	/*
	/***********************************************************************************/

		if ($_POST["submit"] == "Licenties Toevoegen")
		{
			$stmt	=	$dbConn->prepare("SELECT DISTINCT(name) FROM product WHERE software=1");
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

			<div class="inputContainer">

			<script>
				function checkDate(){
					if (document.forms["addForm"]["expiryDate"].value == "")
					{
						var response = confirm("Weet u zeker dat u deze licentie wilt toevoegen zonder vervaldatum?");
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

			<form action="?p=licentie" method="post" onsubmit="return checkDate();" name="addForm">
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

				<script>
				function checkboxCheck()
				{
					if (document.getElementById('volumeCheckbox').checked == true)
					{
						document.getElementById('aantalDiv').style = "display: block;";
					}
					else
					{
						document.getElementById('aantalDiv').style = "display: none;";
					}
				}
				</script>

				<div class="inputLine">
					<div class="inputLeft">Volume Licentie: </div>
					<div class="inputRight"><input type="checkbox" id="volumeCheckbox" onchange="checkboxCheck()" name="volume"/></div>
				</div>
				
				<div class="inputLine" id="aantalDiv" style="display: none;">
					<div class="inputLeft">Aantal: </div>
					<div class="inputRight"><input type="number" min="1" step="1" name="amount" value="1"></div>
				</div>
				<div class="inputLine">
					<div class="inputLeft">Vervaldatum (YYYY-MM-DD): </div>
					<div class="inputRight"><input type="text" name="expiryDate"></div>
				</div>
				<div class="inputLine">
					<div class="inputLeft">Licentiecode: </div>
					<div class="inputRight"><input type="text" name="key"></div>
				</div>
				<div class="inputLine">
					<input type="hidden" name="action" value="newLicence">
					<input type="submit" name="submit" value="Voeg Toe">
				</div>
			</form>
			</div>
			<?php
		}

	/************************************************************************************
	/*
	/*		Add new licences
	/*
	/***********************************************************************************/

		if ($action == "newLicence")
		{
			if ((isset($_POST["name"]) != "") && (isset($_POST["amount"]) != ""))
			{
				$name			=	$_POST["name"];
				$typeID 		=	null;				
				$expiryDate		= 	null;
				$key		=	null;
				$amount			=	1;

				if (isset($_POST["type"]))
					{	$typeID 			=	$_POST["type"];	}
				if (isset($_POST["expiryDate"]))
					{	$expiryDate 			=	$_POST["expiryDate"];	}
				if (isset($_POST["key"]))
					{	$key 		=	$_POST["key"];	}
				if (isset($_POST["amount"]))
					{	$amount 		=	$_POST["amount"];	}

				//add to database
				$result = addLicence($name, $typeID, $amount, $expiryDate, $key);
				switch ($result){
					case 1:
						?>
						Licentie succesvol toegevoegd.
						<?
						break;
					case 2:
						echo $dbConn -> error;
						break;
					default:
						echo "Unknown error";
						break;
				}
			}
			else
			{
				echo "Niet alle vereiste velden zijn ingevuld. Ga terug en probeer het nog eens.";
			}
		}

	}
	

	if ((isset($_GET["a"])) && (isset($_GET["id"])))
	{
		include("./content/licenceMaintenance.inc.php");
	}

	if (((!isset($_POST["submit"])) || ($showOverview)) && (!isset($_GET["a"])))
	{

	?>
		<div>	
			<? if ($_SESSION["level"] == 2)
			{?>
			<form action="?p=licentie" method="post" style="display: inline;">
				<input type="submit" value="Nieuw Softwarepakket Toevoegen" name="submit">
			</form>
			<? } ?>
			<form action="?p=licentie" method="post" style="display: inline;">
				<input type="submit" value="Licenties Toevoegen" name="submit">
			</form>
		</div>	
		<?php

		//haal huidige Lijst met totale licenties op
		$query = $dbConn->prepare("SELECT DISTINCT p.ID, p.name, p.type, COUNT(l.ID) FROM product p, licence l WHERE l.product_id=p.ID AND p.software=1 GROUP BY p.name");
		$query -> execute();
		$query -> bind_result($prod_id, $prod_name, $prod_type, $count);

		?>
		<div>
			<table class="tbl_standard">
				<tr>
					<th>ID</th>
					<th>Softwarepakket</th>
					<th>Versie</th>
					<th>Totaal</th>
				</tr>
			<?

			while($query -> fetch())
			{				
				?>
				<tr>
					<td><? echo $prod_id; ?></td>
					<td><a href="?p=licentie&a=details&id=<? echo $prod_id; ?>"><? echo $prod_name; ?></a></td>
					<td><? echo $prod_type; ?></td>
					<td><? echo $count; ?></td>
				</tr>
				<?
			}

			?>
		</table>
		</div>
	<?
	}