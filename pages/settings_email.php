<?php
require_once ('scripts/session.php');
require_once('bootstrapper.php');
?>
<!DOCTYPE html>
<html lang="en">   
<head>
<?php

    function drawForm($character_get, $username)
    {
?><i class="fa fa-info fa-fw"></i> Your email is used for password retrieving and sending automated reports<br><br>
    <form accept-charset="utf-8" action = "../pages/settings_email.php?character=<?php echo $character_get?>" method = "POST">

            <span class="input-group-addon">current password</span>
            <input type="password" name="password" class="form-control" placeholder="type your current password here" required = "required" pattern=".{6,50}" >
            
            <br>
            
            <span class="input-group-addon">new email</span>
            
            <input type="email" name= "email" class="form-control" placeholder="please use a valid email address" required = "required" pattern=".{6,50}" > 
            <br>

            <input type="hidden" name= "user" value=<?php echo $username ?>>
            
            <br>
            <p align='center'><input type ="Submit" name="Send" value ="Submit" class="btn btn-lg btn-success" /></p>
            </form>
<?php
    }

    function val_email_form($character_get, $con, $username)
    {
        if(isset($_POST['Send']))
        {
            $new_email = mysqli_real_escape_string($con,$_POST['email']);
            $password = mysqli_real_escape_string($con,$_POST['password']);
            
            
           //check if password is correct, check if email is valid
            $get_password = utils::mysqli_result(mysqli_query($con, "SELECT password FROM user WHERE username = '$username'"),0,0);
            $get_salt = utils::mysqli_result(mysqli_query($con, "SELECT salt FROM user WHERE username = '$username'"),0,0);
            
            //hash provided pw with salt
            $newpassword_hash = crypt($password, $get_salt);
            if($newpassword_hash == $get_password)
            {
                //passwords match, check if email is valid (again)
                if(!filter_var($new_email, FILTER_VALIDATE_EMAIL)) 
                {
                    echo "Invalid email format";
                    return;
                }
                
                else
                {
                    //email is valid, password is valid, proceed to change
                    $update_email = mysqli_query($con, "UPDATE user SET email = '$new_email' WHERE username ='$username' ");
                    if($update_email)
                    {
                        echo "Email changed sucessfully";
                        
                    }
                    else
                    {
                        echo "There was an error processing your request. Try again later.";
                    }
                    
                }
            }
            else
            {
                echo "Wrong password";
                return;
            }
            
            
            //validation goes here
        }
        else
        {
            drawForm($character_get, $username);    
        }
    }

    use Pheal\Pheal;
    
    $activeUser = $_SESSION['user'];
 
    $title = "Settings";
    $content = new content();
    $content->drawMeta($title);
?>
    <div id="wrapper">
<?php
    $content->drawNav($accountBalance, $networth, $sellOrders, $escrow, $username, $character_get, $getCharacterList);
?>
    <div id="page-wrapper">
 <?php
    $content->drawHeader($getCharacterPortrait, $username, "Settings: email");
?>
    <div class="row">
<?php
    $content->columns_def();
?>    <!-- /.row -->
 <div class="row">
     <div class="col-md-5 col-md-offset-3">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                    Change email
                    </div>
                    <div class="panel-body">
                        <h4 class="panel-title"></h4>

<?php
    val_email_form($character_get, $con, $username);
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