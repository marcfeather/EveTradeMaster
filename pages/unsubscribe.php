<?php
session_start();
require_once("scripts/class/link.php");
require_once("scripts/class/utils.php");

    $link = new link();
    $con = $link->connect();

    if(isset($_GET['user'])) {$user_get = strtolower($_GET['user']);}
    if(isset($_GET['email'])) {$email_get = strtolower($_GET['email']);}
    
	$useremail = utils::mysqli_result(mysqli_query($con, "SELECT email FROM user WHERE username = '$user_get'"),0,0);
	
    if($useremail == $email_get && isset($email_get))
    {

    $unsub = mysqli_query($con, "UPDATE user SET reports = 'none' WHERE username = '$user_get'")
        or die(mysqli_error($con));

        if($unsub)
        {
            echo "You have unsubscribed from the Eve Trade Master mailing report. <br> You can return anytime in your account settings.";
        }
    
        else
        {
            echo "Comunication error. Try again";
        }
        
    }
    
    else
    {
        echo "Invalid request.";
    }

?>

