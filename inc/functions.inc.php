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
				//simply add this product to the stock for i times (amount)
				$stmt	=	$dbConn->prepare("INSERT INTO stock (product_id, warranty, servicetag, status) VALUES (?, ?, ?, ?)");
				$status = 	1;
				$stmt 	-> 	bind_param("issi", $typeID, $warranty, $servicetag, $status);
				
				if ($stmt->execute())
				{				
					$result = 1;
					
				}
				else
				{
					echo $dbConn-> error . "<br>";
					$result = 2;
					//break;
				}
				$stmt -> close();	
			}
			
			if ($result)
			{
				addLogEntry(null, $typeID, null, null, null, 51, $amount, null);
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
		$stmtFetch	=	$dbConn->prepare("SELECT ID FROM stock WHERE product_id=? AND status=1");
		$stmtFetch	->	bind_param("i", $productID);
		$stmtFetch	->	execute();
		$stmtFetch	->	bind_result($stockID);
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
				return 1;
				addLogEntry(null, $typeID, $employeeID, null, null, 52, $newStatus, null);
			}
			else
			{
				return 2;
			}
		}
		else
		{
			//no unasigned product available
			return 0;
		}		
	}


	function updateStatus($stockID, $newStatus)
	{
		global $dbConn;

		$stmt	=	$dbConn->prepare("UPDATE stock SET status=?, employee_id=NULL WHERE ID=?");
		$stmt	->	bind_param("ii", $newStatus, $stockID);
		if ($stmt	->	execute())
		{				
			addLogEntry(null, $stockID, null, null, null, 52, $newStatus, null);
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

?>