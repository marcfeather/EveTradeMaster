<?php
require_once('scripts/class/link.php');
require_once('scripts/class/utils.php');
require_once('../mailer/PHPMailerAutoload.php');
require_once('scripts/class/content.php');

    $link = new link();
    $con = $link->connect();
    
    define('GUSER', '@gmail.com'); // GMail username
    define('GPWD', ''); // GMail password's

    $title = "Forgot Username";
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
		echo $error = 'Mail error: '.$mail->ErrorInfo; 
		return false;
                } 
                else 
                {
		echo $error = 'Message sent! Check your inbox.';
		return true;
                }
       
    }
    
    function forgot_username($con)
    {
        if(isset($_POST['Submit']))
        {
            $email = $_POST['email'];
            
            $check_email_exists = $con->prepare("SELECT username FROM user WHERE email = ?");
            $check_email_exists->bind_param("s", $email);
            $check_email_exists->execute();
            $check_email_exists->store_result();
            //$data = $check_email_exists->fetch_array();
            //$username = $data['username'];
            //$user_r = mysqli_stmt_get_result($check_email_exists);
            //$user = mysqli_fetch_array($user_r, MYSQLI_ASSOC);
            //$username = $user['username'];
            //$check_email_exists = mysqli_query($con, "SELECT username FROM user WHERE email = '$email' ");
           $username = utils::mysqli_result(mysqli_query($con, "SELECT username FROM user WHERE email = '$email'"), 0,0);
        
             if($check_email_exists->num_rows >= 1)
                {
                    $mail = smtpmailer($email, "noreply@etm.com", "ETM Automated mail", "Eve Trade Master - login details", "You have recently requested your login details at www.evetrademaster.com. Your username is '$username'");
                }
             else
             {
                echo "Email not found in our records";
                echo "<meta http-equiv='refresh' content='2; url=../pages/forgot_username.php'>";
             }
        }
    
        else
        {
 
        echo "Forgot your username? Just type the e-mail associated to your account and we'll send it to you:" . "<br><br>";
    
?>
        <form method="POST" action="forgot_username.php" ><fieldset>
        <div class="form-group">
                <input class="form-control" type = "text" size="30" name="email">
                </div>
                <div class="form-group">
        
                    <p align='center'><input type ="Submit" name="Submit" value="Submit" class="btn btn-lg btn-success"></p>
            </fieldset></form>

<?php
        }
    }
    
    $content->form_container("Forgot username");
    forgot_username($con);
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

    
    

