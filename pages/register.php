<?php
require_once ('scripts/class/link.php');
require_once ('scripts/class/utils.php');
require_once ('scripts/class/content.php');
require_once ('scripts/class/utils.php');
require_once ('scripts/vendor/autoload.php');

    $link = new link();
    $con = $link->connect();
    
    use Pheal\Pheal; //Import namespace for Pheal
    use Pheal\Core\Config;

    Config::getInstance()->cache = new \Pheal\Cache\FileStorage('/var/www/html/phealcache/');
    Config::getInstance()->access = new \Pheal\Access\StaticCheck();

        function register_form()
    {
?>		<br><p align='center'>If you have troubles creating an account please drop me a mail to etmdevelopment42_at_gmail.com or send an eve-mail to Nick Starkey </p>
        <form accept-charset="utf-8" action = "../pages/register.php" method = "POST">
            <br>
               Username
            <input type="text" name="username" class="form-control" placeholder="5 characters minimum" required = "required" pattern=".{5,20}" >
            
            <br>
            
               Password
            <input type="password" name="password" class="form-control" placeholder="6 characters minimum" required = "required" pattern=".{6,20}" >
               Do not use the same username/password as your Eve-Online account.
            <br>
            <br>
            
               Confirm password
            <input type="password" name= "password2" class="form-control" placeholder="passwords must match" required = "required" pattern=".{6,20}" >
            
            <br>
            
               Key ID <a href = "https://community.eveonline.com/support/api-key/CreatePredefined?accessMask=82317323" target="_blank">(generate</a>)
            <input type="number" name="api" class="form-control" placeholder="access mask: 82317323 OR 1073741823 (full)" required = "required">
               Note: newly generated API Keys may take a few minutes to activate.
            <br>
            <br>
               Verification code
            <input type="text" name="vcode" class="form-control" placeholder="this should be in your Eve API Management page" required = "required">
            
            <br>
            
               e-mail
            <input type="email" name="email" class="form-control" placeholder="please enter a valid e-mail" required = "required">
            
            <br>
            
               E-mail reports
            <select class="form-control" name = "reports">
                
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
				<option value="none">Never</option>
            </select>
               Allow ETM to e-mail you detailed earnings reports. 
            <br>   This can be changed anytime.
            <br><br>
                <br><br><p align='center'><input type ="Submit" name="Send" value ="Submit" class="btn btn-lg btn-success" /></p>
<?php
    }


    function checkXML($xml) //Used to catch an exception when a wrong API key is supplied.
        {
            if ($xml == "") {
                throw new Exception("Invalid API Key or VCode");
                }
                return true;
        }
    
    function redirect_error()
    {
         echo "<meta http-equiv='refresh' content='3; url=../pages/register.php'>";
    }
    
    
    function redirect_login()
    {
        echo "<meta http-equiv='refresh' content='3; url=../pages/login.php'>";
    }
    
    
    function failed_validation_2()
    {
       
        echo "I have no idea how you managed to get here, but just in case:";
        redirect_error();
    }
    
    /*function catch_fatal_error_api()
    {
    // Getting Last Error
    $last_error =  error_get_last();
  
    // Check if Last error is of type FATAL
            if(isset($last_error['type']) && $last_error['type']== E_ERROR)
            {  
            echo "Invalid API key or vCode";
            redirect_error();
            }
    }
     register_shutdown_function('catch_fatal_error_api'); //for fake API/vcode key. Unfortunately this produces a fatal error
    */
     
    function register_val($con)
    {
         //require_once('includes/bootstrapper.php');
         //require_once('includes/connect.php');

    //second send validation
     if(!empty($_POST['Send_2']))
     {
         $username_final = mysqli_real_escape_string($con, $_POST['username']);
         $password_final = mysqli_real_escape_string($con, $_POST['password']);
         $apikey_final = mysqli_real_escape_string($con, $_POST['api']);
         $vcode_final = mysqli_real_escape_string($con, $_POST['vcode']);
         $email_final = mysqli_real_escape_string($con, $_POST['email']);
         $reports_final = mysqli_real_escape_string($con, $_POST['reports']);
         
        $dt = new DateTime();
        $tz = new DateTimeZone('Europe/Lisbon');
        $dt->setTimezone($tz);
        $datetime = $dt->format('Y-m-d H:i:s');
        
        $chars = array();
        
         if(isset($_POST['char1'])) {$char1 = $_POST['char1']; array_push($chars, $char1);} else {$char1 = "";}
        if(isset($_POST['char2'])) {$char2 = $_POST['char2']; array_push($chars, $char2);} else {$char2 = "";}
         if(isset($_POST['char3'])) {$char3 = $_POST['char3']; array_push($chars, $char3);} else {$char3 = "";}
         
        //$chars = array($char1,$char2,$char3);
         
         //FINAL SERVER VALIDATION #2 (just in case someone sneaks in HTML5)
         if (strlen($username_final) < 5 || $username_final == "")
         {
             
             echo "Username is too short (5 characters minimum)";
             failed_validation_2();
         }
         else
         {
             if ($password_final == "")
             {
                 echo "Input a proper password";
                 failed_validation_2();
             }
             else 
             {
                 if(!filter_var($email_final, FILTER_VALIDATE_EMAIL))
                 {
                     echo "Wrong email format.";
                     failed_validation_2();
                     
                 }
                 else
                 {
                     if (!in_array($reports_final, array('none', 'daily', 'weekly', 'monthly'))) 
                     { 
                         echo "Invalid report type selection";
                         failed_validation_2();
                     }
                     else
                     {
                         //check if characters belong to API KEY by intersecting both arrays
                         $pheal2 = new Pheal($apikey_final, $vcode_final);
                         $chars_api = array();
                         $chars_name = array();
                         $empty = array();
                         $result2 = $pheal2->accountScope->APIKeyInfo();
                         foreach ($result2->key->characters as $character) 
                            {array_push($chars_api,$character->characterID);
                            array_push($chars_name, $character->characterName);
                            }

                            if (array_intersect(array_diff($chars,$chars_api), $chars_api) != $empty) //this shouldn't be needed but it's here as a precaution
                            {
                                echo "Character does not belong to account";
                                failed_validation_2();
                            }
                            else
                            {
                                $cost = 10;
                                //Before creating the account, let's securely hash the password:
                                $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');

                                // Prefix information about the hash so PHP knows how to verify it later.
                                // "$2a$" Means we're using the Blowfish algorithm. The following two digits are the cost parameter.
                                $salt = sprintf("$2a$%02d$", $cost) . $salt;
                                // Hash the password with the salt
                                $password_final = crypt($password_final, $salt);
                                
           
                                //Everything is validated, prepare SQL transaction
                                mysqli_query($con, "START TRANSACTION");
                                  
                                $query_insert_user = $con->prepare("INSERT INTO `trader`.`user` ("
                                        . "`iduser`, "
                                        . "`username`, "
                                        . "`registration_date`, "
                                        . "`password`, "
                                        . "`reports`, "
                                        . "`email`, `salt`, `login_count`) "
                                        . "VALUES ("
                                        . "NULL, "
                                        . "?, "
                                        . "?, "
                                        . "?, "
                                        . "?, "
                                        . "?, ?, ?);");
                                $zero = 0;
                                $query_insert_user ->bind_param("ssssssi", $username_final, $datetime, $password_final, $reports_final, $email_final, $salt, $zero); //ss stands for 2 strings
                                $query_insert_user->execute();
                                
                                
                                $last_id_user = mysqli_insert_id($con);
                                //Insert ignore because api key may already exist
                                $query_insert_apikey = mysqli_query($con, "insert ignore into `trader`.`api` (`apikey`, `vcode`) "
                                        . "VALUES "
                                        . "('$apikey_final', "
                                        . "'$vcode_final');") or die (mysqli_error($con));

                              //  print_r($chars);
                                $pheal3 = new Pheal($apikey_final, $vcode_final, "char");
                               
                                foreach($chars as $row) 
                                    {
                                    //echo $row;
                                    $response_final = $pheal3->CharacterSheet(array("characterID" =>$row));
                                    $name_char = mysqli_real_escape_string($con, $response_final->name);
                              
                                    
                                    $checkExistingCharacter = mysqli_query($con, "SELECT name FROM v_user_characters WHERE character_eve_idcharacter IN" . "(" . implode(",",$chars) . ")")  or die(mysqli_error($con));
                 
                                    if (mysqli_num_rows($checkExistingCharacter) > 0) //check if aharacter exists somewhere else
                                    {
                                        $duplicates = array();
                                        while ($existing_characters = mysqli_fetch_array($checkExistingCharacter))
                                        {
                                        array_push($duplicates, $existing_characters['name']);
                                        }
                        
                                    echo implode(" and ",$duplicates) . " already belong to another account.";
                                    echo  "<meta http-equiv='refresh' content='3;URL=register.php'>";
                                    return;      
                                    }
                                    else{
                                    $query_insert_character = mysqli_query($con, "replace INTO `trader`.`characters` "
                                            . "(`eve_idcharacter`, "
                                            . "`name`, "
                                            . "`balance`, "
                                            . "`api_apikey`,"
                                            . "`networth`,"
                                            . "`escrow`,"
                                            . "`total_sell`,"
                                            . "`broker_relations`,"
                                            . "`accounting`) "
                                            . "VALUES "
                                            . "($row, "
                                            . "'$name_char', " //query PHEALNG
                                            . "'0', " //balance starts at zero for now
                                            . "'$apikey_final',"
                                            . "'0',"
                                            . "'0',"
                                            . "'0',"
                                            . "'0',"
                                            . "'0');") 
                                            or die (mysqli_error($con));
                                    }
                                    }
                                    //create aggregation between characters and account
                                    foreach($chars as $row2){
                                    $query_insert_aggr = mysqli_query($con,"INSERT INTO `trader`.`aggr` "
                                            . "(`idaggr`, "
                                            . "`user_iduser`, "
                                            . "`character_eve_idcharacter`) "
                                            . "VALUES "
                                            . "(NULL, "
                                            . "'$last_id_user', "
                                            . "'$row2');") or die(mysqli_error($con));
                                    }
                                    //check if everything is right before commit
                                    if($query_insert_user && $query_insert_apikey && $query_insert_character && $query_insert_aggr)
                                    {
                                        mysqli_query($con, "COMMIT");
                                        echo "Account created sucessfully" ."<br>". "You may now login.";
                                        echo "<br><br>";
										
										$dt = new DateTime();
										$tz = new DateTimeZone('Europe/Lisbon');
										$dt->setTimezone($tz);
										$datetime = $dt->format('Y-m-d H:i:s');
										mysqli_query($con, "INSERT INTO `trader`.`log` (`idlog`, `user_iduser`, `type`, `datetime`) VALUES (NULL, '$last_id_user', 'register', '$datetime')")
											or die(mysqli_error($con));	
                                        redirect_login();
                                    }
                                    else
                                    {
                                        mysqli_query($con, "ROLLBACK");
                                        echo "There was a problem creating your account. Try again.";
                                        echo "<br>";
                                        redirect_error();
                                    }
                                
                            }
                         
                     }
                 }
             }
         }

         
     }
     else 
     {
   
     //first send validation
     if(!empty($_POST['Send'])){
    
    $username = mysqli_real_escape_string($con, $_POST['username']);
    //password encryption
        $password1 = $_POST['password'];
        $password2 = $_POST['password2'];
    
        $cost = 10;
        // Create a random salt

    //$password1 = mysqli_real_escape_string($con,md5($_POST['password']));
    //$password2 = mysqli_real_escape_string($con,md5( $_POST['password2']));
        
        if($password1 == $password2) {$pw_encr = $password1;}
    
        unset($_POST['password']);
    
    $apikey = mysqli_real_escape_string($con, $_POST['api']);
    $vcode = mysqli_real_escape_string($con, $_POST['vcode']);
    $reports = mysqli_real_escape_string($con, $_POST['reports']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    
    /*$pheal = new Pheal('4458709', 'vR9VUNKD3hSHD9KJRbTOUnPDiRC1Rb87ETUEbKsaxa4c9gXCtiNDNCPwKvdrt0tu');
    $result = $pheal->accountScope->APIKeyInfo();
        foreach($result->key as $res) {echo $res->accessMask, $res->type;}
    */
    //Using CURL to fetch API Access Mask
    $curl_url = "https://api.eveonline.com/account/APIKeyInfo.xml.aspx?keyID=". $apikey ."&vCode=" . $vcode;
    
    // create curl resource
    $ch = curl_init($curl_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);

    // $response contains the XML response string from the API call
    $response = curl_exec($ch);

    // If curl_exec() fails/throws an error, the function will return false
    if($response === false)
    {
        // Could add some 404 headers here
        echo 'Curl error: ' . curl_error($ch);
    }
        else
        {
        $apiInfo = new SimpleXMLElement($response);
        
           try{
               checkXML($apiInfo->result->key);
                $accessMask = (int)$apiInfo->result->key->attributes()->accessMask;
           } 
           catch(Exception $e) {
            echo 'Error: ' .$e->getMessage();
            echo  "<meta http-equiv='refresh' content='3;URL=register.php'>";
            return;
            }
        }
    // close curl resource to free up system resources
    curl_close($ch);
    
    //***********SERVER VALIDATION #1***************
    //check if email is already taken
    $check_email = mysqli_query($con, "SELECT email FROM user WHERE email = '$email'") or die(mysqli_error($con));
    if(mysqli_num_rows($check_email) != 0)
    {
        echo "Email is already taken";
        redirect_error();
    }
    else {
    //check if access mask is correct
        if($accessMask != '82317323' && $accessMask != '1073741823') 
            {
              echo "Your access mask is " . $accessMask . " which has different permissions than requested. Please <a href = 'https://community.eveonline.com/support/api-key/CreatePredefined?accessMask=82317323' target='_blank'>create one here</a> with the correct permissions and <a href= 'register.php'>try again </a>.";
            }
            else
            {
            //check if passwords match
            if($password1 != $password2)
                {
                echo "Your passwords must match.";
                redirect_error();
                }
                else
                {
                    //check if username is already taken
                     $check_username = mysqli_query($con, "SELECT username FROM user WHERE username = '$username'") or die(mysqli_error($con));    
                    if (mysqli_num_rows($check_username) != 0)
                    {
                    echo "Username is already taken";
                    redirect_error();
                    }
                    else
                    { //check if API KEY is valid
                        echo "<b>Choose which characters to import:</b><br>";
                        //get character List from API KEY using Pheal
                        $pheal = new Pheal($apikey, $vcode);
                        $result = $pheal->accountScope->APIKeyInfo();
                        $count = 0;
                        echo "<table border ='1'>";
                        echo "<form action = {$_SERVER['PHP_SELF']} method = 'POST' >";
                        foreach ($result->key->characters as $character) 
                            {$count = $count+1;  echo "<tr><td>". $character->characterName . "</td><td>" 
                                    ."<img src='https://image.eveonline.com/Character/".$character->characterID."_64.jpg'" . "</td><td>" ."<input type = 'checkbox' name = 'char$count' value = '$character->characterID'>" . "</td></tr>";
                        //there is a KEY HEADER BEFORE THE CHARACTERS ROWSET
  
                    }
                    echo "</table><br>";
                   //rest of the parameters
                    echo "<input type ='hidden' name='username' value='$username'>";
                    echo "<input type ='hidden' name='password' value='$pw_encr'>";
                    echo "<input type ='hidden' name='api' value='$apikey'>";
                    echo "<input type ='hidden' name='vcode' value='$vcode'>";
                    echo "<input type ='hidden' name='reports' value='$reports'>";
                    echo "<input type ='hidden' name='email' value='$email'>";
    
                    echo "<input type ='Submit' name='Send_2' value ='Send' class='btn btn-lg btn-success btn-block'  />";
                    echo "</form>";
                }
               
                
                }
    }
    }
     }
    else 
        {
    register_form();    

        } 
     
    }
    }
    
    $title = "Register";
    $link = new link();
    $con = $link->connect();
    $content = new content();
?>
<html lang="en">
<head> 
    <!-- Bootstrap Core CSS -->
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
    $content->drawMeta($title);
	  if  ((date('H') >= 11) && (date('H') < 12) && date('i') <=20)
        {
            echo "Can't register accounts right now because the server is under scheduled maintenance. (between 11:00-11:20 AM GMT)";
			exit();			
        }
?>
</head>
<body>
<div class="container">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Sign Up</h3>
                    </div>    
<?php
    register_val($con);
?>
                </div>
            </div>
        </div>
</div>
<?php
    $content->drawFooter();
?>
<!-- jQuery -->
    <script src="../bower_components/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap Core JavaScript -->
    <script src="../bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- Metis Menu Plugin JavaScript -->
    <script src="../bower_components/metisMenu/dist/metisMenu.min.js"></script>
    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>
</body>
</html>