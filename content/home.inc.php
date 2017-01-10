<?php
	if (!defined("IN_SYSTEM"))
	{
		header("Location: ../index.php");
		die();
	}

	?>
	<div>
		<span style="font-weight: bold;"><?echo $_SESSION["username"];?></span> - <a href=\"?p=logout\">Log out</a>
	</div>

	<div class="summaryWrapper">	
		<div class="summaryBlock">
			<div class="statusSummaryHead">
				<div class="summaryText">
					Licentiestatus
				</div>
				<table class="statusTable">
					<thead>
						<tr>
							<th class="statusTableCol1">Pakket</th>
							<th class="statusTableCol2">Versie</th>
							<th class="statusTableCol3">Geldig/Totaal</th>
						</tr>
					</thead>
				</table>
			</div>

			<div class="statusSummaryContent">
				<table class="statusTable">
					<?
					$stmt 	=	$dbConn->prepare("SELECT p.ID, p.name, p.type, l1.expired, l2.total FROM product p
	    LEFT JOIN (SELECT product_id, COUNT(ID) AS expired FROM licence WHERE exp_date<NOW() GROUP BY product_id) l1 ON l1.product_id=p.ID
	    LEFT JOIN (SELECT product_id, COUNT(ID) AS total FROM licence GROUP BY product_id) l2  ON l2.product_id=p.ID
	    WHERE p.software=1
	    GROUP BY p.ID");
					$stmt 	->	execute();
					$stmt 	->	bind_result($productID, $productName, $productType, $expired, $total);

					while($stmt	->	fetch())
					{
						if ($expired == null)
						{
							$expired = 0;						
						}					
						$percentage = (($total-$expired)/$total)*100;
						?>
						<tr style="background-color: <? 
						if ($percentage == 100)
						{
							echo "#C7F293";
						}
						if (($percentage < 100) && ($percentage >= 75))
						{
							echo "#FCC38A";
						}
						if ($percentage < 75)
						{
							echo "#F47373";
						}
						?>">
							<td class="statusTableCol1"><a href="?p=licentie&a=details&id=<? echo $productID; ?>"><? echo $productName; ?></a></td>
							<td class="statusTableCol2"><? echo $productType; ?></td>
							<td class="statusTableCol3"><? 
								echo $total-$expired . "/".$total . " <span style='float: right;'>(".round($percentage)."%)</span>";
							?></td>
						</tr>
						<?
					}
					?>
				</table>
			</div>
		<!--end of block-->	
		</div>

		<div class="summaryBlock">
			<div class="statusSummaryHead">
				<div class="summaryText">
					Garantiestatus
				</div>
				<table class="statusTable">
					<thead>
						<tr>
							<th class="statusTableCol1">Systeem</th>
							<th class="statusTableCol2">Type</th>
							<th class="statusTableCol3">Geldig/Totaal</th>
						</tr>
					</thead>
				</table>
			</div>

			<div class="statusSummaryContent">
			<table class="statusTable">	
				<?
				$stmt 	=	$dbConn->prepare("SELECT p.ID, p.name, p.type, l1.expired, l2.total FROM product p
    LEFT JOIN (SELECT product_id, COUNT(ID) AS expired FROM stock WHERE warranty<NOW() GROUP BY product_id) l1 ON l1.product_id=p.ID
    LEFT JOIN (SELECT product_id, COUNT(ID) AS total FROM stock GROUP BY product_id) l2  ON l2.product_id=p.ID
    WHERE p.software=0
    GROUP BY p.ID");
				$stmt 	->	execute();
				$stmt 	->	bind_result($productID, $productName, $productType, $expired, $total);

				while($stmt	->	fetch())
				{
					if ($expired == null)
					{
						$expired = 0;						
					}					
					$percentage = (($total-$expired)/$total)*100;
					?>
					<tr style="background-color: <? 
					if ($percentage == 100)
					{
						echo "#C7F293";
					}
					if (($percentage < 100) && ($percentage >= 75))
					{
						echo "#FCC38A";
					}
					if ($percentage < 75)
					{
						echo "#F47373";
					}
					?>">
						<td class="statusTableCol1"><a href="?p=stock&a=details&id=<? echo $productID; ?>"><? echo $productName; ?></a></td>
						<td class="statusTableCol2"><? echo $productType; ?></td>
						<td class="statusTableCol3"><? 
							echo $total-$expired . "/".$total . " <span style='float: right;'>(".round($percentage)."%)</span>";
						?></td>
					</tr>
					<?
				}
				?>
			</table>
		</div>
		<!--end of block-->	
		</div>

		<? /*
		<div class="summaryBlock">

		<!--end of block-->	
		</div>

		<div class="summaryBlock">

		<!--end of block-->	
		</div> */?>
	</div>