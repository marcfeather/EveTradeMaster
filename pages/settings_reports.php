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
    
<?php
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
    $content->drawHeader($getCharacterPortrait, $username, "Settings: reports");
?>
    <div class="row">
<?php
    $content->columns_def();
?>    <!-- /.row -->
 <div class="row">
     <div class="col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                    Automated reports
                    </div>
                    <div class="panel-body">
                        <h4 class="panel-title"></h4>
            <?php
            if(isset($_POST['Send']))
            {
                $reports = $_POST['reports'];
                $user = $_POST['user'];
                $change_report = mysqli_query($con, "UPDATE user SET reports = '$reports' WHERE username = '$user'") or die(mysqli_error($con));
                
                echo ($change_report ? "Success!" : "Something went wrong. Please try again"); 
            }
            else
            {
            
?>
            <form accept-charset="utf-8" action = "../pages/settings_reports.php?character=<?php echo $character_get?>" method = "POST">

            <i class="fa fa-info fa-fw"></i>Automated reports include:<br>
                Last 24 hours/7 days/30 days snapshot for each character in your account<br>
                List of best items and customers (by raw profit and profit margin)<br>
                List of problematic items (items causing a net loss)<br>
                List of fastest and slowest moving items<br>
                Total earnings recap in the past 7/30 days<br>
            <br><i class="fa fa-info fa-fw"></i> Reports are typically sent around 13:00 GMT time<br>

           <br>    I'd like to receive them: <select class="form-control" name = "reports">
                <option value="none">Never</option>
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
            </select>
            
            <input type="hidden" name= "user" value=<?php echo $username ?>>
            
            <br>
            <p align='center'><input type ="Submit" name="Send" value ="Submit" class="btn btn-lg btn-success" /></p>
            </form>
<?php
            }
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