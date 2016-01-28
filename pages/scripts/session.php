<?php
require_once 'scripts/class/link.php';
require_once 'scripts/class/utils.php';

$link = new link();
$con  = $link->connect(); //create DB connection
$user = $username = "";

    if(!isset($_SESSION))
    {
        session_start();
    }
    //if no cookie is found, start a normal session which expires in 1 hour
        
    if(isset($_COOKIE['name']) && isset($_COOKIE['password'])) //auto-logins if cookie is valid
    {
        $cookiename = $_COOKIE['name'];
        $cookiepw = $_COOKIE['password'];
        $test_password_db = utils::mysqli_result(mysqli_query($con, "SELECT password FROM user WHERE username = '$cookiename'"),0,0);
       
        //check if password is correct
        if ($test_password_db == $cookiepw)
        {
            if(!isset($_SESSION))
            {
                session_start();
            }
            $_SESSION['user'] = $cookiename;
        }
    }
    
    if(isset($_SESSION['user']))
    {
        $user = $_SESSION['user'];
        $username = $_SESSION['user'];
    }
    
           
?>

