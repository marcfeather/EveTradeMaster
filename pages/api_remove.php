<?php
require ('scripts/session.php');
require ('bootstrapper.php');

    use Pheal\Pheal;
    $activeUser = $_SESSION['user'];
  
function api_remove_table($activeUser,$con, $character_get)
{
  if(isset($_GET['rm']))
  {
      $remove = mysqli_real_escape_string($con, $_GET['rm']);
      $remove_name = utils::mysqli_result(mysqli_query($con, "SELECT name FROM characters WHERE eve_idcharacter = '$remove'"),0,0);
      //character is only dissossiated with the account, not removed from the database
      
      
      $remove_character_account = mysqli_query($con, "DELETE FROM aggr WHERE user_iduser = (SELECT iduser FROM user WHERE username = '$activeUser') AND character_eve_idcharacter = '$remove'")
          or die(mysqli_error($con));
      //$remove_character = mysqli_query($con, "DELETE FROM characters WHERE eve_idcharacter = '$remove'") or die(mysqli_error($con));

      echo "Character " . $remove_name . " removed successfully.";
      return;
  }
  else 
  {
   $charsKeys = mysqli_query($con, "SELECT character_eve_idcharacter, name, username, apikey FROM v_user_characters WHERE username = '$activeUser'")
            or die(mysqli_error($con));
    ?>
   <table class='table table-striped table-bordered table-hover' id='dataTables-api'>
       <tr><th align="center">Character</th>
           <th align="center">API Key</th>
           <th></th>
<?php
    while($chars = mysqli_fetch_array($charsKeys))
    {
        $name = $chars['name'];
        $api = $chars['apikey'];
        $charid = $chars['character_eve_idcharacter'];
        $imgpath = "https://image.eveonline.com/Character/".$charid."_32.jpg";
        
        
        echo "<tr><td>" . "<img src=" .$imgpath . ">". "  ".$name . "</td><td >"  . $api . "</td><td align='center'>" . "<a href= 'api_remove.php?character=$character_get&rm=$charid'<button type='button' class='btn btn-danger'>Remove</button>" . "</td></tr>";
    }
    
?>
   </table>
<?php
    }
}
    $title = "Remove API Keys/Characters";
    $content = new content();
    $content->drawMeta($title);
?>
    <div id="wrapper">
<?php
    $content->drawNav($accountBalance, $networth, $sellOrders, $escrow, $username, $character_get, $getCharacterList);
?>
    <div id="page-wrapper">
 <?php
    $content->drawHeader($getCharacterPortrait, $username, "API Management (remove)");
?>
    <div class="row">
<?php
    $content->columns_def();
?>    <!-- /.row -->
 <div class="row">
     <div class="col-md-6 col-md-offset-3">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Remove characters</h3>
                    </div>
                    <div class="panel-body">
                        <i class="fa fa-warning fa-fw"></i> If you remove every character from your account you'll be asked to provide a new API Key the next time you login. <br><br>
<?php
    api_remove_table($activeUser, $con, $character_get);
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
    <script>
    $(document).ready(function() {
        $('#dataTables-api').DataTable({
                responsive: true,
                "order": [[ 0, "desc" ]]
        });
    });
    </script>
    
</body>

</html>
    

?>
