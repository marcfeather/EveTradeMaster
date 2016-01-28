<?php
require_once('scripts/class/link.php');
require_once('scripts/class/utils.php');
require_once('scripts/class/content.php');

if (!isset($_SESSION))
{
    session_start();
}
//utils::setWidthCookie();
?>
<!DOCTYPE html>
    <html lang="en">
    <link href="../bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
    <!-- Custom Fonts -->
    <link href="../bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <!-- DataTables CSS -->
    <link href="../bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css" rel="stylesheet">
    <!-- DataTables Responsive CSS -->
    <link href="../bower_components/datatables-responsive/css/dataTables.responsive.css" rel="stylesheet">

<?php
$link    = new link();
$con     = $link->connect();
$title   = "Login";
$content = new content($title);
$content->drawMeta($title);


function login_form()
{
?>
    <form method="POST" action="../pages/login.php">
    <fieldset>
        <div class="form-group">
            <input class="form-control" placeholder="Username" name="username" type="text" autofocus>
        </div>
            <div class="form-group">
            <input class="form-control" placeholder="Password" name="password" type="password" value="">
        </div>
            <div class="checkbox">
    <label>
        <input name="remember" type="checkbox" value="1">Permanent login (requires cookies)
    </label>
         </div>
        <!-- Change this to a button or input when using this as a form -->
        <input type="submit" name="Submit" value="Login" class="btn btn-lg btn-success btn-block">
    </fieldset>
    </form>
    <p align='center'><a href="forgot_username.php">Forgot username</a> | <a href="forgot_password.php">Forgot password</a></p>
<?php
}
?>
</head>
<body>
    
<?php
$message = "Login";
$content->form_container($message);

if (isset($_POST['Submit']))
{
    $user = $_POST['username'];
    
    $salt = utils::mysqli_result(mysqli_query($con, "SELECT salt FROM user WHERE username = '$user'"), 0, 0);
    
    $password = crypt($_POST['password'], $salt);
    //$password = md5($_POST['password']);
    
    isset($_POST['remember']) ? $remember = $_POST['remember'] : $remember = "";

    //check if cookie exists and corresponds to the right info
    if(isset($_COOKIE['name']) && isset($_COOKIE['password']))
    {
		unset($_COOKIE['name']);
        unset($_COOKIE['password']);
        $cookiename = $_COOKIE['name'];
        $cookiepw = $_COOKIE['password'];
        $password_db = utils::mysqli_result(mysqli_query($con, "SELECT password FROM user WHERE username = '$user'"),0,0);
       
        //check if password is correct
        if ($password_db == $cookiepw && $cookiename == $user)
        {
            session_start();
            $_SESSION['user'] = $user;
            //echo "Session was set by cookie";
           //redirect to select page
            $count_chars = utils::mysqli_result(mysqli_query($con, "SELECT count(character_eve_idcharacter) FROM aggr WHERE user_iduser = (SELECT iduser FROM user WHERE username = '$user')"), 0, 0);
        
            if ($count_chars >= 1) //user has more than 1 character, regular login
            {
                ?>
            
<?php
                echo "<meta http-equiv='refresh' content='0; url=../pages/select.php'>";
            }
                else //user has 0 characters, must submit an API key before continuing
            {
                echo "<meta http-equiv='refresh' content='0; url=../pages/submit_api_login.php'>";
            }
                     
            
        }
        
    }
    // $q = "SELECT username, password FROM user WHERE username = '$user' AND password = '$password' LIMIT 1";
    
    $q = $con->prepare('SELECT username, password FROM user WHERE username = ? AND password = ?');
    $q->bind_param("ss", $user, $password); //ss stands for 2 strings
    $q->execute();
    $result = $q->get_result();
    
    //$check_query = mysqli_query($con, $q) or die(mysqli_error($con));	
    //$check = mysqli_fetch_array($check_query, MYSQLI_ASSOC);
    
    if (mysqli_num_rows($result) == 1)
    {
        echo "You have logged in. Updating your characters information...";
        mysqli_query($con, "UPDATE user SET login_count = login_count +1 WHERE username='$user'") or die(mysqli_error($con));
        
        if($remember == '1') //if the user set the remember me button, then:
        {
            $cookie_name = $user;
            $cookie_value = $password;
            setcookie('name', $user, time() + (86400 * 30* 12), "/"); // 86400 = 1 day
            setcookie('password', $password, time() + (86400 * 30* 12), "/"); // 86400 = 1 day
        }
        
        
        if (!isset($_SESSION['user']))
        {
            session_start();
            $_SESSION['user'] = $user;
 
        }
?>
       <p align='center'><img src='../assets/wheel_2.GIF'></p>
                        
<?php
        $count_chars = utils::mysqli_result(mysqli_query($con, "SELECT count(character_eve_idcharacter) FROM aggr WHERE user_iduser = (SELECT iduser FROM user WHERE username = '$user')"), 0, 0);
        
        if ($count_chars >= 1) //user has more than 1 character, regular login
        {
            echo "<meta http-equiv='refresh' content='0; url=../pages/select.php'>";
        }
        else //user has 0 characters, must submit an API key before continuing
        {
            echo "<meta http-equiv='refresh' content='0; url=../pages/submit_api_login.php'>";
        }
?>                        <br>
                        
<?php
    }
    else
    {
        echo "Wrong credentials";
?>
                        <meta http-equiv="refresh" content="3;URL='../pages/login.php'" />
<?php
    }
}
//Simple auth form
else
{
    if (isset($_SESSION['user']))
    {
        echo "<meta http-equiv='refresh' content='0; url=../pages/select.php'>";
        echo "You have logged in. Updating your characters information...";
        echo "<p align='center'><img src='../assets/wheel_2.GIF'></p>";
        return;
    }
    else
    {
        login_form();
    }
}
?>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-body">
<?php
$content->drawFooter();
?>
        </div>
    </div>
    
</body>
</html>

