<?php
//ini_set("display_errors", "off");
error_reporting(E_ERROR | E_WARNING | E_PARSE);
 
require_once 'scripts/vendor/autoload.php';
require_once 'scripts/class/link.php';
require_once 'scripts/class/utils.php';
require_once 'scripts/class/content.php';
require_once 'scripts/class/dropdown.php';
   
   
    //initializes dependencies and application variables for HTML, JS and CSS, as well as the model
    use Pheal\Pheal; //Import namespace for Pheal
    use Pheal\Core\Config;
 
    Config::getInstance()->cache = new \Pheal\Cache\FileStorage('/var/www/html/phealcache/');
    Config::getInstance()->access = new \Pheal\Access\StaticCheck();
    
    $link = new link(); 
    $con = $link->connect(); //create DB connection
	
	/*if(isset($_COOKIE['name']) && isset($_COOKIE['password'])) //auto-logs if cookie is valid
    {
        $cookiename = $_COOKIE['name'];
        $cookiepw = $_COOKIE['password'];
        $password_db = utils::mysqli_result(mysqli_query($con, "SELECT password FROM user WHERE username = '$user'"),0,0);
       
        //check if password is correct
        if ($password_db == $cookiepw && $cookiename == $user && !isset($_SESSION['user']))
        {
            session_start();
            $_SESSION['user'] = $user;
        }
    }*/
	

    $character_get = $_GET['character'];
    //fetch API Key/vCode for future requests:
    $api_q = mysqli_query($con, "SELECT api_apikey AS api FROM `characters` WHERE eve_idcharacter = '$character_get'")
                           or die(mysqli_error($con));
                   $api_a = mysqli_fetch_array($api_q, MYSQLI_ASSOC);
    $apikey = $api_a['api'];
                   
    $vcode_q = mysqli_query($con, "SELECT vcode FROM api WHERE apikey = '$apikey'")
                           or die(mysqli_error($con));
                   $vcode_a = mysqli_fetch_array($vcode_q);
    $vcode = $vcode_a['vcode'];
    
    //don't allow spying
    
    $check_char_belong_session = mysqli_query($con, "SELECT * FROM v_user_characters
                    WHERE username = '$username' AND character_eve_idcharacter = '$character_get'")
                        or die (mysqli_error($con));
                
        if(mysqli_num_rows($check_char_belong_session) == 0)
            {
            $session_state = 0;
            echo "Sneaky sneaky";
            session_destroy();
            echo "<meta http-equiv='refresh' content='1; url=../pages/login.php'>";
            exit();
            }
    
    $characterName = utils::mysqli_result(mysqli_query($con, "SELECT name FROM characters WHERE eve_idcharacter = '$character_get'"),0,0);
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

    <link href="../bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
    <!-- Custom Fonts -->
    <link href="../bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <!-- DataTables CSS -->
    <link href="../bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css" rel="stylesheet">
    <!-- DataTables Responsive CSS -->
    <link href="../bower_components/datatables-responsive/css/dataTables.responsive.css" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="../bower_components/jquery/dist/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="../bower_components/bootstrap/dist/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../bower_components/metisMenu/dist/metisMenu.min.js"></script>
    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

      <!-- DataTables JavaScript -->
    
