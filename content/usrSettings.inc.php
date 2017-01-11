<?
	if (!defined("IN_SYSTEM"))
	{
		header("Location: ../index.php");
		die();
	}	

	if (isset($_POST["submit"]))
	{
		//process password
		if ((isset($_POST["oldPass"])) && (isset($_POST["newPass"])) && (isset($_POST["checkPass"])))
		{			
			$oldPass	=	$_POST["oldPass"];
			$newPass	=	$_POST["newPass"];
			$checkPass	=	$_POST["checkPass"];

			if (checkPass($oldPass))
			{
				if ($newPass == $checkPass)
				{
					if (changePass($newPass))
					{
						?>
						<div class="infoSucces">Wachtwoord succesvol aangepast, log vanaf nu in met uw nieuwe wachtwoord</div>
						<?
					}
					else
					{
						?><div class="infoError">Wachtwoord veranderen mislukt</div><?
					}
				}
				else
				{
					?><div class="infoError">Nieuw wachtwoord komt niet overeen met controlle</div><?
				}
			}
			else
			{
				?><div class="infoError">Huidig wachtwoord niet correct</div><?
			}
		}
	}
?>
	<script>
		function validateForm(){
			if ((document.forms["passwordForm"]["oldPass"].value == "") || (document.forms["passwordForm"]["newPass"].value == "") || (document.forms["passwordForm"]["checkPass"].value == ""))
			{
				alert("Vul alle velden in.");
				return false;						
			}
			else
			{
				//check password match
				if (document.forms["passwordForm"]["newPass"].value == document.forms["passwordForm"]["checkPass"].value )
				{					
					return true;
				}
				else
				{
					alert("De nieuwe wachtwoorden komen niet overeen!");
					return false;
				}		
			}
		}
	</script>

	<div>
		Wachtwoord aanpassen:
	</div>
	<div class="inputContainer">
		<form action="?p=settings" method="post" onsubmit="return validateForm();" name="passwordForm">
			<div class="inputLine">
				<div class="inputLeft">Huidig Wachtwoord:</div>
				<div class="inputRight"> <input type="password" name="oldPass"></div>
			</div>
			<div class="inputLine">
				<div class="inputLeft">Nieuw Wachtwoord:</div>
				<div class="inputRight"> <input type="password" name="newPass"></div>
			</div>
			<div class="inputLine">
				<div class="inputLeft">Besvestig Wachtwoord:</div>
				<div class="inputRight"> <input type="password" name="checkPass"></div>
			</div>
			<div class="inputLine">
				<input type="submit" name="submit" value="Opslaan"></div>
			</div>
		</form>
	</div>