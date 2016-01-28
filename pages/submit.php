<?php
require_once ('scripts/session.php');
require_once('bootstrapper.php');
require_once('../mailer/PHPMailerAutoload.php');
?>
<!DOCTYPE html>
    <html lang="en">
    
<head>
<?php

use Pheal\Pheal;
    
    define('GUSER', 'etmdevelopment42@gmail.com'); // GMail username
    define('GPWD', '6ff32apang'); // GMail password's
    
    $activeUser = $_SESSION['user'];
 
    $title = "Submit feedback";
    $content = new content();
    $content->drawMeta($title);
    
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
                $error = "Error sending message. Try again later.";
		return false;
                } 
                else 
                {
		echo $error = 'Message sent! Thanks for your time.';
		return true;
                }
       
    }
    
    
    
    function send_feedback($character_get, $con)
    {
        if (isset($_POST['Send']))
        {
            $message = $_POST['message'];
            if(strlen($message) <= 3)
            {
                echo "Message is too short. Be a little more creative.";
                echo "<meta http-equiv='refresh' content='2; url=../pages/submit.php?character=$character_get'>";
                return;
            }
            else
            {
               //get user email and account
               $getUserData = mysqli_query($con, "select user.username, user.email "
                       . "from user "
                       . "join aggr on user.iduser = aggr.user_iduser "
                       . "join characters on characters.eve_idcharacter = aggr.character_eve_idcharacter "
                       . "where characters.eve_idcharacter = $character_get") or die (mysqli_error($con));
               
               $userData = mysqli_fetch_array($getUserData);
               
               $user_email = $userData['email'];
               $user_name = $userData['username'];
               
               
               smtpmailer("etmdevelopment42@gmail.com", $user_email, $user_name, "New message from $user_name", $user_email . "said " .$message); 

            }
        }
        else 
        {
           //draw form 
          draw_form($character_get);  
        }
        
    }
    
    function draw_form($character_get)
    {
        echo "<form accept-charset='utf-8' action = 'submit.php?character=$character_get' method = 'POST'>";
?>
            <span class="input-group-addon">Message: </span>
            <textarea placeholder="pls no spam" rows="4" cols="50" name="message" class="form-control" required> </textarea>
            <p align='center'><br><input type ="Submit" name="Send" value ="Send" class="btn btn-lg btn-success" /></p>
        </form>
<?php        
    }
    
    
?>
    <div id="wrapper">
<?php
    $content->drawNav($accountBalance, $networth, $sellOrders, $escrow, $username, $character_get, $getCharacterList);
?>
    <div id="page-wrapper">
 <?php
    $content->drawHeader($getCharacterPortrait, $username, "Feedback");
?>
    <div class="row">
<?php
    $content->columns_def();
?>    <!-- /.row -->
 <div class="row">
     <div class="col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Feedback / Bug reports</h3>
                    </div>
                    <div class="panel-body">
                        <i class="fa fa-info fa-fw"></i> Suggest a new feature or report bugs you find here <br>
                        <i class="fa fa-info fa-fw"></i> In case of bug reports try to be specific <br><br>
<?php
    send_feedback($character_get, $con);
?>
</div>
                </div>
            </div>
        </div>
<?php
    $content->drawFooter();
?>
</div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-8 -->
                
                <!-- /.col-lg-4 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->

    </div>

</body>

</html>
