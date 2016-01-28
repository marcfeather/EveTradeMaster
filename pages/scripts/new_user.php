<?php
require_once('scripts/class/link.php');
require_once('scripts/class/utils.php');
    
    session_start();
    $link = new link();
    $con = $link->connect();
    //check the number of logins. if 1 (first), ignore the session handler
    $username = $user = $_SESSION['user'];
    $logins = utils::mysqli_result(mysqli_query($con, "SELECT login_count FROM user WHERE username='$user'"),0,0);
    
        if($logins > 1)
        {
            require_once ('scripts/session.php');
        }
        
        else
        {
            $user = $user_session = $username = $_SESSION['user'];
        }
        
?>