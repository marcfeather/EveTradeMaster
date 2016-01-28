<?php
error_reporting(E_ALL);
define('APP_DIR', 'C:\Users\Starkey\Dropbox\ht\traderv3');
require_once '/pages/scripts/vendor/autoload.php';
require_once '/pages/scripts/class/link.php';
require_once '/pages/scripts/session.php';
require_once '/pages/scripts/utils.php';
    
    //initializes dependencies and application variables
    use Pheal\Pheal; //Import namespace for Pheal
    use Pheal\Core\Config;
 
    Config::getInstance()->cache = new \Pheal\Cache\FileStorage('/var/www/html/phealcache');
    Config::getInstance()->access = new \Pheal\Access\StaticCheck();
    
    $username = $_SESSION['user']; //set session variable
    $link = new link(); 
    $con = $link->connect(); //create DB connection
    
    $session = new session($username);
    $session->checkSession($username); //boots the user to the login screen in case a session isn't valid

    //fetch API Key/vCode for future requests:
    $api_q = mysqli_query($con, "SELECT api_apikey AS api FROM `characters` WHERE eve_idcharacter = '$character_get'")
                           or die(mysqli_error($con));
                   $api_a = mysqli_fetch_array($api_q, MYSQLI_ASSOC);
    $apikey = $api_a['api'];
                   
    $vcode_q = mysqli_query($con, "SELECT vcode FROM api WHERE apikey = '$apikey'")
                           or die(mysqli_error($con));
                   $vcode_a = mysqli_fetch_array($vcode_q);
    $vcode = $vcode_a['vcode'];
    
    
    //

    $username = $_SESSION['user'];
    $characterName = mysqli_result(mysqli_query($con, "SELECT name FROM characters WHERE eve_idcharacter = '$character_get'"),0,0);
    $getCharacterPortrait = "https://image.eveonline.com/Character/".$character_get."_64.jpg";
    
    $getCharacterList = mysqli_query($con, "SELECT name, character_eve_idcharacter
            FROM v_user_characters WHERE username = '$username'
            AND character_eve_idcharacter != '$character_get'") 
            or die(mysqli_error($con));
    
    /*$characterList = array();
    $characterids = array();
        while ($row = mysqli_fetch_array($getCharacterList, MYSQLI_ASSOC))
        {
         array_push($characterList, $row['name']);
         array_push($characterids, $row['character_eve_idcharacter']);
        }
    */    
        //var_dump($characterList);
     $getCharacterInfo = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM characters "
            . "WHERE eve_idcharacter = '$character_get'"),MYSQLI_ASSOC);
          
    $accountBalance = $getCharacterInfo['balance'];
    $networth = $getCharacterInfo['networth'];
    $escrow = $getCharacterInfo['escrow'];
    $sellOrders = $getCharacterInfo['total_sell'];
    $total = $accountBalance + $networth + $escrow + $sellOrders;
    
    $getLatestTransaction = mysqli_query($con, "SELECT transaction_type AS type, item_name FROM v_transaction_details "
            . "WHERE character_id = '$character_get'")
            or die(mysqli_error($con));
            
    $latestTransactionData = mysqli_fetch_array($getLatestTransaction, MYSQLI_ASSOC);
       $latestItem = $latestTransactionData['item_name'];
       $latestTransType = $latestTransactionData['type'];
    
    
?>
