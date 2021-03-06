<?php
	if (!defined("IN_SYSTEM"))
	{
		header("Location: ../index.php");
		die();
	}


/**
	*	checks whether the user is logged in through the PHP session
	*
	*	@return boolean returns true or false on the login
	*/
	function checkLogin()
	{
		global $settings;

		$loggedIn = false;
		if (isset($_SESSION["login"]))
		{
			if (($_SESSION["login"] == true) && (($_SESSION["lasttime"] + $settings["timeout"]) >= time()))
			{
				//user is logged in and timeout hasn't passed yet
				$_SESSION["lasttime"] 	= time();
				$loggedIn 				= true;
			}
			else
			{
				//user hasn't logged in or timeout has passed
				if (!$_SESSION["login"]){
					logout();
				}
				else{
					//logout was due to a session timeout. Show this to avoid user confusion
					logout("timeout", true);
				}
			}
		}	
		return $loggedIn;
	}

/**
	*	checks user credentials and sets session vars if ok
	*
	*	@return boolean returns true or false on the login
	*/
	function preformLogin($username, $password)
	{
		$login = false;
		global $settings;
		global $dbConn;

		//fetch the salt for this user from the database
		$query = $dbConn->prepare("SELECT `salt`, `password`, `username`, `user_id`, `level` FROM `user` WHERE `username` = ? AND level > 0");
		$query -> bind_param("s", $username);
		$query -> execute();
		$query -> bind_result($salt, $passwordHash, $username, $uID, $level);
		$query -> fetch();

		if  (($passwordHash != hash("sha256", $password . $salt)) || ($salt == null))
		{
			return $login;
		}
		else
		{
			//set session variables
			$_SESSION["login"]		=	true;
			$_SESSION["lasttime"]	=	time();
			$_SESSION["username"]	=	$username;
			$_SESSION["uID"]		=	$uID;
			$_SESSION["level"]		=	$level;			
			return true;
		}

	}

	/**
	*	remove the session data and redirect the user back to the loginpage
	*
	*	@param getVar string (optional) the GET variable that should be passed on the logout redirect
	*
	*	@param val string/bool/int (optional) the value that should be passed on the getVar set in the first param
	*/
	function logout($getVar = NULL, $val = NULL)
	{
		unset($_SESSION["login"]);
		unset($_SESSION["lasttime"]);
		unset($_SESSION["username"]);
		unset($_SESSION["uID"]);
		unset($_SESSION["level"]);
		$_SESSION 	=	array();
		if (($getVar != NULL) && ($val != NULL)){
			header("Location: index.php?" . $getVar . "=" . $val);
			}
		else{
			header("Location: index.php");
		}
	}

	/**
	*	fetches page information and calls the correct file
	*/
	function handlePage()
	{
		global $settings;
		global $dbConn;

		$breadcrumbs = array();	
		$breadcrumbs[0] = array("Home", "<a href=\"index.php?p=home\">");

		$page = "";
		if (isset($_GET["p"]))
		{
			$page = $_GET["p"];
		}

		switch ($page) {
			case 'home':
				outputFramework("Home", "home");
				include './content/home.inc.php';
				break;

			case 'logout':
				logout();
				break;

			case 'stock':
				outputFramework("Voorraad Beheer", "stock");		
				include './content/stock.inc.php';
				break;

			case 'licentie':
				outputFramework("Licentie Beheer", "licentie");
				include './content/lic_beheer.inc.php';
				break;

			case 'systeem':
				outputFramework("Systeem Registratie", "systeem");
				include './content/sys_registratie.inc.php';
				break;

			case 'settings':
				outputFramework("Instellingen", "settings");
				include './content/usrSettings.inc.php';
				break;

			case 'users':
				outputFramework("Gebruikers Beheer", "users");
				include './content/users.inc.php';
				break;

			case 'employees':
				outputFramework("Medewerkers Beheer", "employees");
				include './content/employees.inc.php';
				break;

			case 'log':
				outputFramework("Logboek", "log");
				include './content/log.inc.php';
				break;

			case 'errortest':
				outputFramework("Errortest", "error");
				include './content/errortest.inc.php';
				break;

			case 'overview':
				outputFramework("Overzicht", "overzicht");
				include './content/overview.inc.php';
				break;

			default:
				outputFramework("Home", "home");
				include './content/home.inc.php';
				break;
		}

		closeFramework();
	}

	/**
	 * Outputs the HTML framework with stylesheet info and page title
	 * 
	 * @param string $pageTitle sets the title of the HTML title tag
	 * 
	 */
	function outputFramework($pageTitle, $activePage)
	{
		global $settings;
		$title =	$settings["page_title_prefix"] . $pageTitle;
		include './inc/framework.inc.php';
	}

	/**
	 * Outputs the HTML elements to close the page after the content
	 * 
	 */
	function closeFramework()
	{
		include './inc/frameworkEnd.inc.php';
	}









	/************************************************************************************
	/*
	/*		USER REGISTRATION FUNCTIONS START HERE
	/*
	/*
	/***********************************************************************************/

	function addUser($username, $password, $email, $level, $voornaam, $achternaam)
	{
		global $dbConn;

		if (!checkUser($username))
		{

			$q = $dbConn->prepare("INSERT INTO `user` (username, email, level, password, salt, voornaam, achternaam) VALUES (?, ?, ?, ?, ?, ?, ?)");			
			$salt = generateSalt();
			$hash = hash("sha256", $password . $salt);

			$q	->	bind_param("ssissss", $username, $email, $level, $hash, $salt, $voornaam, $achternaam);

			if ($q	->	execute())
			{			
				$id = $q->insert_id;
				addLogEntry($id, null, null, null, null, 1, null, null);	
				$result = 1;
			}
			else
			{
				echo $dbConn-> error;
				$result = 2;
			}

			return $result;
		}
		else
		{

			return 3;
		}
	}

	function generateSalt()
	{
		global $settings;
		$characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	    $charactersLength = strlen($characters);
	    $randomString = "";
	    for ($i = 0; $i < $settings["salt_lenght"]; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

	function checkUser($username)
	{
		global $dbConn;
		//check if username allready exists
		$stmt = $dbConn->prepare("SELECT 1 FROM user WHERE username=?");
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$stmt->bind_result($check);
		$stmt->fetch();

		if ($check != 1)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	function checkPass($password)
	{
		global $dbConn;

		$stmt 	=	$dbConn->prepare("SELECT salt, password FROM user WHERE user_id=?");
		$stmt 	->	bind_param("i", $_SESSION["uID"]);
		$stmt 	-> 	execute();
		$stmt 	-> 	bind_result($salt, $hash);
		$stmt 	->	fetch();

		$testHash	=	hash("sha256", $password . $salt);
		if ($testHash == $hash)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function changePass($newpass)
	{
		global $dbConn;

		$salt = generateSalt();
		$hash = hash("sha256", $newpass . $salt);

		$stmt 	=	$dbConn->prepare("UPDATE user SET password=?, salt=? WHERE user_id=?");
		$stmt 	->	bind_param("ssi", $hash, $salt, $_SESSION["uID"]);
		if ($stmt -> execute())
		{
			return true;
		}
		else
		{
			return false;
		}
		$stmt 	->	close();
	}


	function userUpdateRights($userID, $level)
	{
		global $dbConn;

		$stmt	=	$dbConn->prepare("UPDATE user SET level=? WHERE user_id=?");
		$stmt 	->	bind_param("ii", $level, $userID);
		if ($stmt ->	execute())
		{
			return true;
		}
		else
		{
			return false;
		}
		
	}


	/************************************************************************************
	/*
	/*		EMPLOYEE REGISTRATION FUNCTIONS START HERE
	/*
	/***********************************************************************************/

	function addEmployee($voornaam, $achternaam, $email)
	{
		global $dbConn;

		if (!checkEmployee($email))
		{

			$q = $dbConn->prepare("INSERT INTO `employee` (voornaam, achternaam, email) VALUES (?, ?, ?)");	

			$q	->	bind_param("sss", $voornaam, $achternaam, $email);

			if ($q	->	execute())
			{				
				$newID	=	$q->insert_id;
				$result = 	1;
				addLogEntry(null, null, $newID, null, null, 21, null, null);
			}
			else
			{
				//echo $dbConn-> error;
				$result = 2;
			}

			return $result;
		}
		else
		{

			return 3;
		}
	}


	function checkEmployee($email)
	{
		//HR will take care of assigning a new employee a unique e-mail adress. This is why we check for double e-mail adresses, not for double names (these aren't necessarily unique).

		global $dbConn;
		//check if username allready exists
		$stmt = $dbConn->prepare("SELECT 1 FROM employee WHERE email=?");
		$stmt->bind_param("s", $email);
		$stmt->execute();
		$stmt->bind_result($check);
		$stmt->fetch();

		if ($check != 1)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	function deleteEmployee($employeeID)
	{
		global $dbConn;

		//fetch licences assigned to employee
		$licenceArray = array();
		$licenceStmt	=	$dbConn->prepare("SELECT ID FROM licence WHERE employee_id = ?");
		$licenceStmt	->	bind_param("i", $employeeID);
		$licenceStmt	->	execute();
		$licenceStmt	->	bind_result($licenceID);

		while($licenceStmt ->	fetch())
		{
			array_push($licenceArray, $licenceID);
		}
		$licenceStmt -> close();
		//unlink licences

		foreach($licenceArray as $licence)
		{
			$unlinkStmt	=	$dbConn->prepare("UPDATE licence SET employee_id = NULL WHERE ID=?");
			$unlinkStmt	->	bind_param("i", $licence);
			$unlinkStmt ->	execute();
			$unlinkStmt	->	close();
		}

		//fetch stock assigned to employee
		$stockArray = array();
		$stockStmt	=	$dbConn->prepare("SELECT ID FROM stock WHERE employee_id = ?");
		$stockStmt	->	bind_param("i", $employeeID);
		$stockStmt	->	execute();
		$stockStmt	->	bind_result($stockID);

		while($stockStmt ->	fetch())
		{
			array_push($stockArray, $stockID);
		}
		$stockStmt -> close();

		//unlink stock
		foreach($stockArray as $stock)
		{
			$unlinkStmt	=	$dbConn->prepare("UPDATE stock SET employee_id = NULL WHERE ID=?");
			$unlinkStmt	->	bind_param("i", $stock);
			$unlinkStmt ->	execute();
			$unlinkStmt	->	close();
		}

		//set employee to inactive
		$inactiveStmt	=	$dbConn->prepare("UPDATE employee SET status = 0 WHERE ID=?");
		$inactiveStmt	->	bind_param("i", $employeeID);
		if ($inactiveStmt ->	execute())
		{
			//addLogEntry($userID, $stockID, $employeeID, $licenceID, $productID, $actionCode, $param1, $param2)
			addLogEntry($employeeID, null, null, null, null, 23, null, null);
			return true;
		}
		else
		{
			return false;
		}
		$inactiveStmt	->	close();
	}



	/************************************************************************************
	/*
	/*		PRODUCT AND STOCK MANAGEMENT FUNCTIONS START HERE
	/*
	/***********************************************************************************/


	function addProduct($name, $type, $description)
	{
		global $dbConn;

		if (!checkProduct($name, $type))
		{

			$q = $dbConn->prepare("INSERT INTO `product` (name, type, description) VALUES (?, ?, ?)");	

			$q	->	bind_param("sss", $name, $type, $description);

			if ($q	->	execute())
			{				
				$newID	=	$q->insert_id;
				$result = 	1;
				addLogEntry(null, null, null, null, $newID, 41, null, null);
			}
			else
			{
				//echo $dbConn-> error;
				$result = 2;
			}

			return $result;
		}
		else
		{

			return 3;
		}
	}


	function checkProduct($name, $type)
	{
		global $dbConn;
		//check if username allready exists
		$stmt = $dbConn->prepare("SELECT 1 FROM product WHERE name=? AND type=?");
		$stmt->bind_param("ss", $name, $type);
		$stmt->execute();
		$stmt->bind_result($check);
		$stmt->fetch();

		if ($check != 1)
		{
			return false;
		}
		else
		{
			return true;
		}
	}


	function addStock($name, $typeID, $amount, $warranty, $servicetag)
	{
		global $dbConn;

		if($typeID != null)
		{	
			$result;
			for ($i=0; $i<$amount; $i++)
			{
				$stmt;
				if ($warranty != null)
				{
					$stmt	=	$dbConn->prepare("INSERT INTO stock (product_id, warranty, servicetag, status) VALUES (?, ?, ?, ?)");
					$status = 	1;
					$stmt 	-> 	bind_param("issi", $typeID, $warranty, $servicetag, $status);
				}
				else
				{
					$stmt	=	$dbConn->prepare("INSERT INTO stock (product_id, servicetag, status) VALUES (?, ?, ?)");
					$status = 	1;
					$stmt 	-> 	bind_param("isi", $typeID, $servicetag, $status);
				}
				//simply add this product to the stock for i times (amount)
				
				echo $dbConn->error;
				
				if ($stmt->execute())
				{				
					$result = 1;
					
				}
				else
				{
					$result = 2;
				}
				$stmt -> close();	
			}
			
			if ($result == 1)
			{
				addLogEntry(null, null, null, null, $typeID, 51, $amount, null);
			}
			return $result;		
		}
	}



	/************************************************************************************
	/*
	/*		STOCK MANAGEMENT FUNCTIONS START HERE
	/*
	/***********************************************************************************/

	function assignStock($employeeID, $productID)
	{
		global $dbConn;

		//fetch an available stock ID from the database
		$stmtFetch	=	$dbConn->prepare("SELECT ID, servicetag FROM stock WHERE product_id=? AND status=1");
		$stmtFetch	->	bind_param("i", $productID);
		$stmtFetch	->	execute();
		$stmtFetch	->	bind_result($stockID, $servicetag);
		$stmtFetch	->	fetch();
		$stmtFetch	->	close();

		if ($stockID != null)
		{
			//assign this stockID to $employeeID
			$newStatus	=	2;
			$stmtAssign	=	$dbConn->prepare("UPDATE stock SET employee_id=?, status=? WHERE ID=?");
			$stmtAssign	->	bind_param("iii", $employeeID, $newStatus, $stockID);
			if ($stmtAssign	->	execute())
			{
				return array(1, $stockID, $servicetag);
				addLogEntry(null, $typeID, $employeeID, null, null, 52, $newStatus, null);
			}
			else
			{
				return array(2);
			}
		}
		else
		{
			//no unasigned product available
			return array(0);
		}		
	}


	function updateStatus($stockID, $newStatus, $noLog=false)
	{
		global $dbConn;

		$stmt	=	$dbConn->prepare("UPDATE stock SET status=?, employee_id=NULL WHERE ID=?");
		$stmt	->	bind_param("ii", $newStatus, $stockID);
		if ($stmt	->	execute())
		{			
			if (!$noLog)
			{	
				addLogEntry(null, $stockID, null, null, null, 52, $newStatus, null);
			}
			return true;
		}
		else
		{
			return false;
		}
	}

	function disconnectStock($stockID, $status)
	{
		global $dbConn;

		if (updateStatus($stockID, $status, true))
		{
			//addLogEntry($userID, $stockID, $employeeID, $licenceID, $productID, $actionCode, $param1, $param2)
			//addLogEntry(null, $stockID, null, $licenceID, null, 74, null, null);
			return true;
		}
		else
		{
			return false;
		}
	}


	/************************************************************************************
	/*
	/*		LOG FUNCTIONS START HERE
	/*
	/***********************************************************************************/


	function addLogEntry($userID, $stockID, $employeeID, $licenceID, $productID, $actionCode, $param1, $param2)
	{
		//for action code references, see actions.txt

		global $dbConn;

		$stmt 	=	$dbConn->prepare("INSERT INTO log (owner_id, user_id, stock_id, employee_id, licence_id, product_id, action, parameter1, parameter2) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)") or die ($dbConn->error);

		$stmt 	->	bind_param("iiiiiiiss", $_SESSION["uID"], $userID, $stockID, $employeeID, $licenceID, $productID, $actionCode, $param1, $param2);
		if ($stmt 	-> execute())
		{
			return true;
		}
		else
		{	
			die ($dbConn -> error);
			return false;
		}
		$stmt 	->	close();
	}


	/************************************************************************************
	/*
	/*		SOFTWARE AND LICENCE MANAGEMENT FUNCTIONS START HERE
	/*
	/***********************************************************************************/


	function addSoftware($name, $type, $description)
	{
		global $dbConn;

		if (!checkProduct($name, $type))
		{

			$q = $dbConn->prepare("INSERT INTO `product` (name, type, description, software) VALUES (?, ?, ?, 1)");	

			$q	->	bind_param("sss", $name, $type, $description);

			if ($q	->	execute())
			{				
				$newID	=	$q->insert_id;
				$result = 	1;
				addLogEntry(null, null, null, null, $newID, 61, null, null);
			}
			else
			{
				//echo $dbConn-> error;
				$result = 2;
			}

			return $result;
		}
		else
		{

			return 3;
		}
	}


	function addLicence($name, $typeID, $amount, $expiryDate, $key)
	{
		global $dbConn;

		if($typeID != null)
		{	
			$result;
			/*if ($expiryDate == null)
			{
				$expiryDate = nu;
			}*/
			for ($i=0; $i<$amount; $i++)
			{
				//simply add this product to the stock for i times (amount)
				if ($expiryDate == null)
				{
					$stmt	=	$dbConn->prepare("INSERT INTO licence (product_id, licencekey) VALUES (?, ?)");
					$stmt 	-> 	bind_param("is", $typeID, $key);
				}
				else
				{
					$stmt	=	$dbConn->prepare("INSERT INTO licence (product_id, exp_date, licencekey) VALUES (?, ?, ?)");
					$stmt 	-> 	bind_param("iss", $typeID, $expiryDate, $key);
				}
				
				
				if ($stmt->execute())
				{				
					$result = 1;		
				}
				else
				{
					echo $stmt->error;
					$result = 2;
				}
				$stmt -> close();	
			}
			
			if ($result == 1)
			{
				addLogEntry(null, null, null, null, $typeID, 71, $amount, null);
			}
			return $result;		
		}
	}



	function assignLicenceToSystem($stockID, $productID)
	{
		global $dbConn;

		//fetch an available licence ID from the database
		$stmtFetch	=	$dbConn->prepare("SELECT ID, licencekey FROM licence WHERE product_id=? AND stock_id IS NULL AND employee_id IS NULL");
		$stmtFetch	->	bind_param("i", $productID);
		$stmtFetch	->	execute();
		$stmtFetch	->	bind_result($licenceID, $licenceKey);
		$stmtFetch	->	fetch();
		$stmtFetch	->	close();

		if ($licenceID != null)
		{
			//assign this stockID to $employeeID
			$stmtAssign	=	$dbConn->prepare("UPDATE licence SET stock_id=? WHERE ID=?");
			$stmtAssign	->	bind_param("ii", $stockID, $licenceID);
			if ($stmtAssign	->	execute())
			{

				//addLogEntry($userID, $stockID, $employeeID, $licenceID, $productID, $actionCode, $param1, $param2)
				addLogEntry(null, $stockID, null, $licenceID, null, 74, null, null);
				return array(1, $licenceID, $licenceKey);				
			}
			else
			{
				//SQL error
				return array(2);
			}
		}
		else
		{
			//no unasigned product available
			return array(0);
		}		
	}

	function assignLicenceToEmployee($productID, $employeeID)
	{
		global $dbConn;

		//fetch an available licence ID from the database
		$stmtFetch	=	$dbConn->prepare("SELECT ID, licencekey FROM licence WHERE product_id=? AND stock_id IS NULL AND employee_id IS NULL");
		$stmtFetch	->	bind_param("i", $productID);
		$stmtFetch	->	execute();
		$stmtFetch	->	bind_result($licenceID, $licenceKey);
		$stmtFetch	->	fetch();
		$stmtFetch	->	close();

		if ($licenceID != null)
		{
			//assign this stockID to $employeeID
			$stmtAssign	=	$dbConn->prepare("UPDATE licence SET employee_id=? WHERE ID=?");
			$stmtAssign	->	bind_param("ii", $employeeID, $licenceID);
			if ($stmtAssign	->	execute())
			{
				return array(1, $licenceID, $licenceKey);
				//addLogEntry($userID, $stockID, $employeeID, $licenceID, $productID, $actionCode, $param1, $param2)
				addLogEntry(null, null, $employeeID, $licenceID, null, 73, null, null);
			}
			else
			{
				//SQL error
				return array(2);
			}
		}
		else
		{
			//no unasigned product available
			return array(0);
		}		
	}

	function disconnectLicence($licenceID)
	{
		global $dbConn;

		$stmt 	=	$dbConn->prepare("UPDATE licence SET employee_id=NULL, stock_id=NULL WHERE ID=?");
		$stmt 	->	bind_param("i", $licenceID);
		if ($stmt 	->	execute())
		{
			echo $stmt->error;
			echo $dbConn->error;
			//addLogEntry($userID, $stockID, $employeeID, $licenceID, $productID, $actionCode, $param1, $param2)
			addLogEntry(null, null, null, $licenceID, null, 72, null, null);
			return 1;
		}
		else
		{
			return 0;
		}
		$stmt 	->	close();
	}


?>