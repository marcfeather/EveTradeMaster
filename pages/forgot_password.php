<?php
require_once('scripts/class/link.php');
require_once('scripts/class/utils.php');
require_once('../mailer/PHPMailerAutoload.php');
require_once ('scripts/class/content.php');
    
    $link = new link();
    $con = $link->connect();

    define('GUSER', 'etmdevelopment42@gmail.com'); // GMail username
    define('GPWD', '6ff32apang'); // GMail password's

    $title = "Forgot password";
    $content = new content();
    $content->drawMeta($title);
    
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
    
<head>
<?php
    
    function get_random_string($valid_chars, $length)
    {
        // start with an empty random string
        $random_string = "";

        // count the number of chars in the valid chars string so we know how many choices we have
        $num_valid_chars = strlen($valid_chars);

        // repeat the steps until we've created a string of the right length
        for ($i = 0; $i < $length; $i++)
        {
            // pick a random number from 1 up to the number of valid chars
            $random_pick = mt_rand(1, $num_valid_chars);

            // take the random character out of the string of valid chars
            // subtract 1 from $random_pick because strings are indexed starting at 0, and we started picking at 1
            $random_char = $valid_chars[$random_pick-1];

            // add the randomly-chosen char onto the end of our string so far
            $random_string .= $random_char;
        }

    // return our finished random string
    return $random_string;
    }
    
    function smtpmailer($to, $from, $from_name, $subject, $body) 
    { 
	global $error;
	$mail = new PHPMailer();  // create a new object
	$mail->IsSMTP(); // enable SMTP
	$mail->SMTPDebug = 0;  // debugging: 1 = errors and messages, 2 = messages only
	$mail->SMTPAuth = true;  // authentication enabled
	$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail
	$mail->Host = 'smtp.gmail.com';
	$mail->Port = 465; 
	$mail->Username = GUSER;  
	$mail->Password = GPWD;           
	$mail->SetFrom($from, $from_name);
	$mail->Subject = $subject;
	$mail->Body = $body;
	$mail->AddAddress($to);
        //$mail->SMTPDebug = 2;
        if(!$mail->Send()) 
                {
		//echo $error = 'Mail error: '.$mail->ErrorInfo; 
                $error = "Error sending email. Try again later.";
		return false;
                } 
                else 
                {
		echo $error = 'Message sent! Check your inbox!';
		return true;
                }
       
    }
    
    function forgot_password($con)
    {
        if(isset($_POST['Submit']))
        {
           
            $user = strtolower(mysqli_real_escape_string($con, $_POST['username']));
            $email = strtolower(mysqli_real_escape_string($con, $_POST['email']));
        
            $check_email_user = mysqli_query($con, "SELECT password, salt FROM user WHERE username= '$user' AND email = '$email'" )
                or die(mysqli_error($con));
        
            if(mysqli_num_rows($check_email_user) == 1) //generate random string, encrypt, store/REPLACE in DB and send email
            {
              
                $pw1 = get_random_string("abcdefghijklmnopqrstuwxyz1234567890_!#$%&=", 7);
                //create new salt, generate crypt and store new PW and salt
                $cost = 10;              
                $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
                $salt = sprintf("$2a$%02d$", $cost) . $salt;                
                $password_final = crypt($pw1, $salt);
                
                
                $mail = smtpmailer($email, "noreply@etm.com", "ETM Automated mail", "Eve Trade Master - login details", "You have recently requested a new password at www.evetrademaster.com. Your new password is '$pw1'");
            
                $update_pw_salt = mysqli_query($con, "UPDATE user SET password = '$password_final', salt = '$salt' WHERE username = '$user' ")
                    or die(mysqli_error($con));
                    
                if($update_pw_salt)
                {
                   
                }
                else
                {
                    echo "Error establishing database connection. Try again later";
                }

         
            }
            else
            {
                echo "Data doesn't match our records";
            }
        }
        else
        {
?>
        Forgot your password? Type your email and username and we'll send you a new one.
        <form method="POST" action="forgot_password.php" ><fieldset>
        <div class="form-group">
                Email <input class="form-control" type = "text" size="30" name="email">
                </div>
                <div class="form-group">
        Username<input class="form-control" type = "text" size="20" name="username">
         </div>
                <p align='center'><input type ="Submit" name="Submit" value="Submit" class="btn btn-lg btn-success"></p>
            </fieldset></form>
<?php
        }
    }

    
    $content->form_container("Forgot password");
    forgot_password($con);
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