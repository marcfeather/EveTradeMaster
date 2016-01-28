<?php
//include ("link.php");
class session{
    
    public $session_state;
    public $duration;
    
    public function __construct($username) //defines class constructor, with all variables inside this object
    {
    $this->username = $username;
    $this->session_state = 0;
    }    
    
    public function createSession($username)
    {
        ini_set('session.gc_maxlifetime', 3600);
        session_set_cookie_params(3600);
        session_start();
        $_SESSION['user'] = $username;
        $session_state = 1;
        return $session_state;
    }
    
    public  function destroySession($username)
    {
        if(isset($_SESSION['user']))
            unset($_SESSION['user']);
        $session_state = 0;
    }
        
    public  function checkSession($username)
    {
        $link = new link();
        $con = $link->connect();
        if(!isset($_SESSION['user']))
            {
                return "Session expired";
                $session_state = 0;
               
            }
            else
            {
                $user_active_session = $_SESSION['user'];
                $character_get = $_GET['character'];
                $check_char_belong_session = mysqli_query($con, "SELECT * FROM v_user_characters
                    WHERE username = '$username' AND character_eve_idcharacter = '$character_get'")
                        or die (mysqli_error($con));
                
                if(mysqli_num_rows($check_char_belong_session) == 0)
                {
                    $session_state = 0;
                    echo "Sneaky sneaky";
                    //exit();
                }
                else
                {
                    $session_state = 1;
                }
            }
    }
    
    public function getSessionState($username)
    {
        echo $this->session_state;
    } 
}
?>

