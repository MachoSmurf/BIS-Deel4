<?php
    header('Content-Type: application/json');

    session_start();
    define("IN_SYSTEM", true);
    require "../inc/settings.inc.php";
    require "../inc/functions.inc.php";
    require "../inc/db.inc.php";

    $dbConn = DB_connect();

    if(isset($_POST['prodName']) && checklogin())
    {
        //Correcte JSON output!

        /*$option1 = array('id' => '1', 'type' => 'one');
        $option2 = array('id' => '2', 'type' => 'two');
        $option3 = array('id' => '3', 'type' => 'three');

        $reply = array($option1, $option2, $option3);

        $json = json_encode($reply);*/

        //Einde correct voorbeeld JSON 

        $dataArray = array();

        $stmt = $dbConn->prepare("SELECT id, type FROM product WHERE name=?");
        $stmt -> bind_param("s", $_POST["prodName"]);
        $stmt -> execute();
        $stmt -> bind_result($id, $type);

        while ($stmt -> fetch())
        {
            $dataArray[] = array('id' => $id, 'type' => $type);
        }

        $json = json_encode($dataArray);
    }
    else
    {
        $reply = array('error' => true);
    }

    echo $json; 
?>