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
?>
    <form accept-charset="utf-8" action = "../pages/settings_password.php?character=<?php echo $character_get?>" method = "POST">

            <span class="input-group-addon">current password</span>
            <input type="password" name="oldpassword" class="form-control" placeholder="type your current password here" required = "required" pattern=".{6,50}" >
            
            <br>
            
            <span class="input-group-addon">new password</span>
            <input type="password" name= "newpassword1" class="form-control" placeholder="6 characters minimum" required = "required" pattern=".{6,50}" >
            
            <br>
            
            <span class="input-group-addon">new password (confirm)</span>
            <input type="password" name= "newpassword2" class="form-control" placeholder="type your new password here (again)" required = "required" pattern=".{6,50}" >
            
            <input type="hidden" name= "user" value=<?php echo $username ?>>
            
            <br>
            <p align='center'><br><input type ="Submit" name="Send" value ="Submit" class="btn btn-lg btn-success" /></p>
            </form>
<?php
    }

    function val_password_form($character_get, $username, $con)
    {
        if(isset($_POST['Send']))
        {
            $oldpassword = $_POST['oldpassword'];
            $newpassword1 = $_POST['newpassword1'];
            $newpassword2 = $_POST['newpassword2'];
            $user = $_POST['user'];
        
            //hash the provided password with the salt and match it against the one stored in the database
            $salt = utils::mysqli_result(mysqli_query($con, "SELECT salt FROM user WHERE username = '$user'"),0,0);
                     
            $oldpassword_crypt = crypt($oldpassword, $salt);
                                
            $find_current_password = utils::mysqli_result(mysqli_query($con, "SELECT password FROM user WHERE username = '$user'"),0,0);
                                
            if($find_current_password != $oldpassword_crypt)
                {
                    echo "Incorrect password";
                }
                else 
                {
                    if($newpassword1 != $newpassword2)
                    {
                        echo "The new passwords provided don't match";
                    }
                    else
                    {
                        //passwords match.
                        //generate new salt
                        //hash the new password and store it in the database
                        $cost = 10;     
                        $new_salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
                        $new_salt = sprintf("$2a$%02d$", $cost) . $new_salt;
                        $newpassword_hash = crypt($newpassword1, $new_salt);
                        
                        $update_password = mysqli_query($con, "UPDATE user SET password = '$newpassword_hash', salt = '$new_salt' WHERE username = '$username'")
                                or die(mysqli_error($$con));
                    
                        if($update_password)
                        {
                            echo "Password successfully changed";
                        }
                        
                    }
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
    $content->drawHeader($getCharacterPortrait, $username, "Settings: password");
?>
    <div class="row">
<?php
    $content->columns_def();
?>    <!-- /.row -->
 <div class="row">
     <div class="col-md-5 col-md-offset-3">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                       Change password
                    </div>
                    <div class="panel-body">
                        <h3 class="panel-title"><i class="fa fa-warning fa-fw"></i>Don't use the same password as your Eve account</h3><br>
<?php
    val_password_form($character_get, $username, $con);
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