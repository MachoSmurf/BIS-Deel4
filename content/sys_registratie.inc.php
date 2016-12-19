<?php
	if (!defined("IN_SYSTEM"))
	{
		header("Location: ../index.php");
		die();
	}

	$stmt	=	$dbConn->prepare("SELECT e.voornaam, e.achternaam, p.name, p.type FROM employee e, stock s, product p WHERE e.ID = s.employee_id AND s.product_id=p.ID");
	$stmt	->	execute();
	$stmt	->	bind_result($voornaam, $achternaam, $product, $type);

	?>
	<table class="tbl_standard">
		<tr>
			<th>Medewerker</th>
			<th>Product</th>
			<th>Model/Type</th>
		</tr>
		<?
			while ($stmt	->	fetch())
				{
					?>
					<tr>
						<td><? echo $voornaam . " " . $achternaam;?></td>
						<td><? echo $product;?></td>
						<td><? echo $type;?></td>
					</tr>
					<?
				}
		?>
	</table>
	<?

?>