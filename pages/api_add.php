<?php
require_once ('scripts/session.php');
require_once('bootstrapper.php');
?>
<!DOCTYPE html>
    <html lang="en">
    
<head>
<?php

use Pheal\Pheal;
    
    $activeUser = $_SESSION['user'];
    
    function checkXML($xml) //Used to catch an exception when a wrong API key is supplied.
        {
            if ($xml == "") {
                throw new Exception("Invalid API Key or VCode");
                }
                return true;
        }
    
    function failed_validation_2()
    {
        $character_get = $_GET['character'];
        echo "I have no idea how you managed to get here, but just in case:";
        echo "<br><a href = 'api_add_character.php?character=$character_get'>Return</a>";
    }
    
    function catch_fatal_error_api()
    {
    // Getting Last Error
    $last_error =  error_get_last();
  
    // Check if Last error is of type FATAL
            if(isset($last_error['type']) && $last_error['type']== E_ERROR)
            {  
            $character_get = $_GET['character'];
            echo "Invalid API key or Vcode";
            echo "<br><a href = 'api_add_character.php?character=$character_get'>Return</a>";
            }
    }
     register_shutdown_function('catch_fatal_error_api'); //for fake API/vcode key. Unfortunately this produces a fatal error
     
    
    function api_val_form($character_get,$con)
    {
    $activeUser = $_SESSION['user'];
    if(!empty($_POST['Send_2']))
     {
        $apikey_final = mysqli_real_escape_string($con, $_POST['api']);
        $vcode_final = mysqli_real_escape_string($con, $_POST['vcode']);

        $chars = array();
        
         if(isset($_POST['char1'])) { $char1 = $_POST['char1']; array_push($chars, $char1);} else {$char1 = "";}
        if(isset($_POST['char2'])) { $char2 = $_POST['char2']; array_push($chars, $char2);} else {$char2 = "";}
         if(isset($_POST['char3'])) { $char3 = $_POST['char3']; array_push($chars, $char3);} else {$char3 = "";}
         
        //$chars = array($char1,$char2,$char3);
         
         //FINAL SERVER VALIDATION #2 (just in case someone sneaks in HTML5)
           
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
                
                if (array_intersect(array_diff($chars,$chars_api), $chars_api) != $empty)
                    {
                     echo "Character does not belong to Eve account";
                     failed_validation_2();
                     }
                    else
                     {
                     $query_insert_apikey = mysqli_query($con, "insert ignore INTO `trader`.`api` (`apikey`, `vcode`) "
                        . "VALUES "
                        . "('$apikey_final', "
                        . "'$vcode_final');") or die (mysqli_error($con));
                                
                        //  print_r($chars);
                        $pheal3 = new Pheal($apikey_final, $vcode_final, "char");
                               
                        foreach($chars as $row) 
                            {
                            $row;
                  
                            $response_final = $pheal3->CharacterSheet(array("characterID" =>$row));
                            $name_char = mysqli_real_escape_string($con,$response_final->name);
                            
                            $activeUserID = utils::mysqli_result(mysqli_query($con, "SELECT iduser FROM user WHERE username = '$activeUser'"),0,0);
                            
                            $check_existing_character_user = mysqli_query($con, "SELECT * FROM aggr WHERE character_eve_idcharacter = '$row'")
                                    or die(mysqli_error($con));
                            
                                if(mysqli_num_rows($check_existing_character_user) != 1)
                                {
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
                                else
                                {
                                 //check if one of the provided characters already exists in the DB. We don't allow for this.
                                    $checkExistingCharacter = mysqli_query($con, "SELECT name FROM v_user_characters WHERE character_eve_idcharacter IN (" . implode(",",$chars) . ") AND username != '$activeUser'")  or die(mysqli_error($con));
                 
                                    if (mysqli_num_rows($checkExistingCharacter) > 0)
                                    {
                                    $duplicates = array();
                                    while ($existing_characters = mysqli_fetch_array($checkExistingCharacter))
                                    {
                                    array_push($duplicates, $existing_characters['name']);
                                    }
                        
                                echo implode(" and ",$duplicates) . " already belong to another account.";
                                echo  "<meta http-equiv='refresh' content='3;URL=api_add.php?character=$character_get'>";
                                return;        
                                    }
                                    else
                                        {
                                        echo  "Character(s) already belongs to this account";
                                        echo  "<meta http-equiv='refresh' content='3;URL=api_add.php?character=$row'>";
                                        return;
                                        }
                                }
                            }
                             
                            
                         // echo $activeUser;
                            
                                    //create aggregation between characters and account
                            foreach($chars as $row2)
                                {
                                  //check if the character number has been exceeded
                                    echo $character_count = utils::mysqli_result(mysqli_query($con, "SELECT COUNT(character_eve_idcharacter) "
                                    . "FROM aggr "
                                    . "WHERE user_iduser = "
                                    . "(SELECT iduser FROM user WHERE username = '$activeUser') "), 0,0);
                            
                                    if($character_count >= 10)
                                    {
                                        echo "You have exceeded your character limit (currently 10)";
                                        echo  "<meta http-equiv='refresh' content='3;URL=api_add.php?character=$character_get'>";
                                        return;
                                    }
                                    
                                    $query_insert_aggr = mysqli_query($con,"INSERT IGNORE INTO `trader`.`aggr` "
                                            . "(`idaggr`, "
                                            . "`user_iduser`, "
                                            . "`character_eve_idcharacter`) "
                                            . "VALUES "
                                            . "(NULL, "
                                            . "'$activeUserID', "
                                            . "'$row2');") or die(mysqli_error($con));
                                }
                                    //check if everything is right before commit
                                    if($query_insert_apikey && $query_insert_character && $query_insert_aggr)
                                    {
                                        mysqli_query($con, "COMMIT");
                                        echo "API added successfully." . "<br>" . "You will now logoff so we can update your new character data. <br>";
                                        session_destroy();
                                        echo  "<meta http-equiv='refresh' content='5;URL=login.php'>";
                                    }
                                    else
                                    {
                                        mysqli_query($con, "ROLLBACK");
                                        echo "error";
                                    }

                        }
        }
         

      
     if(!empty($_POST['Send']))
         {
         $apikey = mysqli_real_escape_string($con, $_POST['api']);
         $vcode = mysqli_real_escape_string($con, $_POST['vcode']);
         
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
            echo  "<meta http-equiv='refresh' content='3;URL=api_add.php?character=$character_get'>";
            return;
            }
        }
        //var_dump($apiInfo->result->key);
    // close curl resource to free up system resources
            curl_close($ch);
    
        if($accessMask != '82317323' && $accessMask != '1073741823') 
            {
            echo "Your access mask is " . $accessMask . " which has different permissions than requested. Please <a href = 'https://community.eveonline.com/support/api-key/CreatePredefined?accessMask=82317315' target='_blank'>create one here</a> with the correct permissions and <a href= 'api_add?character?=$character_get.php'>try again </a>.";
            }
            
            else
            {
                echo "<b>Choose which characters to import:</b><br>";
                        //get character List from API KEY using Pheal
                        $pheal = new Pheal($apikey, $vcode);
                        $result = $pheal->accountScope->APIKeyInfo();
                        $count = 0;
                        echo "<table class='table table-striped table-bordered table-hover' id='dataTables-api'>";
                        echo "<form action = 'api_add.php?character=$character_get' method = 'POST' >";
                        foreach ($result->key->characters as $character) 
                            {$count = $count+1;  echo "<tr><td>". "<img src='https://image.eveonline.com/Character/".$character->characterID."_64.jpg'" . "</td><td>" 
                                   . $character->characterName . "</td><td>" ."<input type = 'checkbox' name = 'char$count' value = '$character->characterID'>" . "</td></tr>";
                        //there is a KEY HEADER BEFORE THE CHARACTERS ROWSET
 
                            }
                echo "</table><br>";
                   //rest of the parameters
                    echo "<input type ='hidden' name='api' value='$apikey'>";
                    echo "<input type ='hidden' name='vcode' value='$vcode'>";
                    echo "<input type ='Submit' name='Send_2' value ='Send' class='btn btn-lg btn-success btn-block'  />";
                    echo "</form>";
            }
         }
        
         else 
        {
             api_add_form($character_get);
        }
    }
        
    function api_add_form($character_get)
        {
            echo "<form accept-charset='utf-8' action = 'api_add.php?character=$character_get'; method = 'POST'>";
?>
     
            <span class="input-group-addon">API Key <a href = "https://community.eveonline.com/support/api-key/CreatePredefined?accessMask=82317323" target="_blank">(generate</a>)</span>
            <input type="number" name="api" class="form-control" placeholder="access mask: 82317323" required = "required">
            
            <span class="input-group-addon">verification code</span>
            <input type="text" name="vcode" class="form-control" placeholder="this should be in your Eve API Management page" required = "required">
            
            <br><input type ="Submit" name="Send" value ="Submit" class="btn btn-lg btn-success btn-block" />
        </form>
<?php        

        }
 
    $title = "Add API Keys/Characters";
    $content = new content();
    $content->drawMeta($title);
?>
    <div id="wrapper">
<?php
    $content->drawNav($accountBalance, $networth, $sellOrders, $escrow, $username, $character_get, $getCharacterList);
?>
    <div id="page-wrapper">
 <?php
    $content->drawHeader($getCharacterPortrait, $username, "API Management (add)");
?>
    <div class="row">
<?php
    $content->columns_def();
?>    <!-- /.row -->
 <div class="row">
     <div class="col-md-3 col-md-offset-4">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Insert new API Key</h3>
                    </div>
                    <div class="panel-body">
                        <i class="fa fa-warning fa-fw"></i> Please only add relevant characters to your account <br>
                        <i class="fa fa-warning fa-fw"></i> More characters means longer login times <br><br>
<?php
    api_val_form($character_get, $con);
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