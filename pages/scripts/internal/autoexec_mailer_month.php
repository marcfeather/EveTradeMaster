<?php
require_once('/var/www/html/mailer/PHPMailerAutoload.php');
require_once('/var/www/html/pages/scripts/class/link.php');
require_once ('/var/www/html/pages/scripts/class/utils.php');
require_once ('/var/www/html/pages/scripts/vendor/autoload.php');
require_once ('/var/www/html/pages/scripts/class/tax.php');

define('GUSER', 'etmdevelopment42@gmail.com'); // GMail username
define('GPWD', '6ff32apang'); // GMail password's

    $link = new link();
    $con = $link->connect();


    
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
        $mail->IsHTML(true);
        //$mail->SMTPDebug = 2;
        if(!$mail->Send()) 
                {
		//echo $error = 'Mail error: '.$mail->ErrorInfo; 
                $error = "Error sending email. Try again later.";
		return false;
                } 
                else 
                {
		echo $error = 'Message sent to '. $to ;
		return true;
                }
       
    }
    
    
    
    function getExpensesChar($ids_string,$con)
    {
        $expenses = utils::mysqli_result(mysqli_query($con, "SELECT coalesce(SUM(price_total),0) FROM transaction 
            WHERE character_eve_idcharacter IN  $ids_string
            AND transaction_type =  'Buy'
            AND time > DATE_SUB(CURDATE(), INTERVAL 30 day) " ),0,0);
            
            return $expenses;
    }
    
    function getRevenuesChar($ids_string,$con)
    {
        $revenues = utils::mysqli_result(mysqli_query($con, "SELECT coalesce(SUM(price_total),0) FROM transaction 
            WHERE character_eve_idcharacter IN $ids_string
            AND transaction_type =  'Sell'
            AND time > DATE_SUB(CURDATE(), INTERVAL 30 day) " ),0,0);
            
            return $revenues;
    }
    
    function getProfitsChar($ids_string,$con)
    {
        $profits = utils::mysqli_result(mysqli_query($con, "SELECT coalesce(SUM(profit_total),0) FROM v_profit_details 
            WHERE character_buy_id IN $ids_string
            AND time_sell > DATE_SUB(CURDATE(), INTERVAL 30 day) "),0,0);
        
            return $profits;
    }
    
    function getBestItemsProfit ($ids_string, $con)
    {
        $bestItemsRaw = mysqli_query($con, 
            "SELECT item_id, item, sum(profit_total) AS profit
            FROM v_profit_details
            WHERE date(time_sell) >= DATE_SUB(CURDATE(), INTERVAL 30 day)
            AND (character_sell_id IN $ids_string
            OR character_buy_id IN $ids_string)
            GROUP BY item
            ORDER BY sum(profit_total) DESC
            LIMIT 6");
        
        echo "<table border ='1'>";
        echo "<tr><td></td><td>Item</td><td>Profit (total)</td></tr>";
        
        if(mysqli_num_rows($bestItemsRaw) == 0)
        {
            echo "<tr><td colspan='3'>No results to display</td></tr>";
        }
        
        while($items = mysqli_fetch_array($bestItemsRaw, MYSQLI_ASSOC))
        {
            while($now = mysqli_fetch_array($bestItemsRaw, MYSQLI_ASSOC)) //raw profits table
            {
            $itemRaw = $now['item'];
            $valueRaw = $now['profit'];
            $itemID = $now['item_id'];
            echo "<tr><td> <img src='http://evetrademaster.com/Types/" .$itemID. "_32.png'</td><td>". $itemRaw . "</td><td>" .number_format($valueRaw) ." ISK" . "</td></tr>";
            }
            
        }
        echo "</table>";
    }
    
    function getBestItemsMargin ($ids_string, $con)
    {
        $bestItemsMargin = mysqli_query($con,
            "SELECT item_id, item, AVG(profit_unit/price_unit_buy) AS margin
            FROM v_profit_details
            WHERE date(time_sell) >= (DATE(NOW()) - INTERVAL 30 day)
            AND (character_sell_id IN $ids_string
            OR character_buy_id IN $ids_string)
            GROUP BY item 
            ORDER BY AVG(profit_unit/price_unit_buy) DESC 
            LIMIT 5");
        
        echo "<table border ='1'>";
        echo "<tr><td></td><td>Item</td><td>Margin</td></tr>";
        
        if(mysqli_num_rows($bestItemsMargin) == 0)
        {
            echo "<tr><td colspan='3'>No results to display</td></tr>";
        }
        
        while($pow = mysqli_fetch_array($bestItemsMargin, MYSQLI_ASSOC)) //margin profits table
            {
            $itemMargin = $pow['item'];
            $valueMargin = $pow['margin'];
            $itemID = $pow['item_id'];    
            echo "<tr><td> <img src='http://evetrademaster.com/Types/" .$itemID. "_32.png'</td><td>". $itemMargin . "</td><td>" . number_format($valueMargin*100,2) . " %" . "</td></tr>";
            }
               
           //     echo "<tr><td>" .$item . "</td><td>" . $value . "</td><td></tr>";
            echo "</table>";
    }
    
    function getBestCustomersProfit($ids_string, $con)
    {
        $bestCustomersRawProfit = mysqli_query($con,
            "SELECT soldTo, sum(profit_total) AS profit
            FROM v_profit_details 
            WHERE date(time_sell) >= (DATE(NOW()) - INTERVAL 30 day)
            AND (character_sell_id IN $ids_string 
            OR character_buy_id IN $ids_string) 
            GROUP BY soldTo 
            ORDER BY sum(profit_total) 
            DESC LIMIT 5");
        
        echo "<table border ='1'>";
        echo "<tr><td></td><td>Customer</td><td>Profit (total)</td></tr>";
        
        if(mysqli_num_rows($bestCustomersRawProfit) == 0)
        {
            echo "<tr><td colspan='3'>No results to display</td></tr>";
        }
        
        while($tow = mysqli_fetch_array($bestCustomersRawProfit, MYSQLI_ASSOC)) //CUSTOMER raw profits table
            {
            $soldTo = mysqli_real_escape_string($con,$tow['soldTo']);
            $profit = $tow['profit'];
            
                //check if customer already exists in db first. if yes, then use cached data. if not, then query the eve API (slow), then insert the new entry to the DB
            $search_customer_ID = mysqli_query($con, "SELECT eve_idcharacters FROM characters_public WHERE name = '$soldTo'") 
                    or die(mysqli_error($con)); 
                    
                if(mysqli_num_rows($search_customer_ID) ==1)
                    {
                        $customerID = utils::mysqli_result($search_customer_ID,0,0);
                    }
                    else
                    {
                        $getCustomerID = "https://api.eveonline.com/eve/CharacterID.xml.aspx?names=$soldTo";
                        $xml = simplexml_load_file($getCustomerID);
                
                            foreach ($xml->result->rowset->row as $r) 
                            {
                                $customerID = $r['characterID'];
                            }
                        //add new ID to db
                        $insert_new_customer = mysqli_query($con, "INSERT IGNORE INTO `trader`.`characters_public` (`eve_idcharacters`, `name`) VALUES ('$customerID', '$soldTo')");
                    }

            echo "<tr><td> <img src='https://image.eveonline.com/Character/" .$customerID. "_32.jpg'</td><td>". $soldTo . "</td><td>" . number_format($profit) . " ISK" . "</td></tr>";
            }
        echo "</table>";
    }
    
    function getLastWeekProfits($ids_string, $con)
    {
         $profits = array();
         $margins = array();
         $dates = array();
         $total_profit = 0;
         $total_margin = 0;
         
         
         echo "<table border ='1'>";
         echo "<tr><th>Day</th><th>Profit</th><th>Margin</th></tr>";
         
        /* $getLastWeekProfits = mysqli_query($con, "SELECT DATE(timestamp_sell) as date, SUM(profit.quantity_profit * profit.profit_unit) as profit,  (AVG( profit.profit_unit / transaction.price_unit ) *100) as margin  
            FROM profit
            JOIN transaction ON profit.transaction_idbuy_buy = transaction.idbuy
            WHERE  `characters_eve_idcharacters_OUT` = '$ids_string'
            and date(timestamp_sell) = DATE_SUB( NOW( ) , INTERVAL $i" );*/
    
         for($i=0;$i<=30;$i++)
         {
            
             $q1 = "SELECT SUM( profit.quantity_profit * profit.profit_unit ) AS profit
                FROM profit
                JOIN transaction ON profit.transaction_idbuy_buy = transaction.idbuy
                WHERE  `characters_eve_idcharacters_OUT` 
                IN $ids_string
                AND DATE( timestamp_sell ) = DATE( DATE_SUB( NOW( ) , INTERVAL '$i' DAY ) )" or die (mysqli_error($con));
             
             
             $q2 = "select (sum(price_unit_sell*profit_quantity)-sum(price_unit_buy*profit_quantity))/sum(price_unit_buy*profit_quantity)*100
                    from v_profit_details where character_sell_id IN $ids_string
                     AND DATE(time_sell) = DATE( DATE_SUB( NOW( ) , INTERVAL '$i' DAY ) )" 
                     or die (mysqli_error($con));

             
             $q3 = "SELECT days from calendar where days = DATE( DATE_SUB( NOW( ),INTERVAL '$i' DAY ))";
             
            $value1 = utils::mysqli_result(mysqli_query($con, $q1 ),0); if (empty($value1)) {$value1 = 0;}
            
            $value2 = utils::mysqli_result(mysqli_query($con, $q2 ),0); if (empty($value2)) {$value2 = 0;}
   
           // $lastdate = $value3;
            $pqp = mysqli_query($con, $q3 );
            $value3 = utils::mysqli_result(mysqli_query($con, $q3 ),0,0); 
            
             array_push($profits, $value1);
             
             array_push($margins, $value2);
             
             array_push($dates, $value3);
             
             $total_profit = $total_profit + $profits[$i];
             $total_margin = $total_margin + $margins[$i];
             
             echo "<tr><td>". $dates[$i] . "</td><td>" . number_format($profits[$i]) . "</td><td>" . number_format($margins[$i],2) . " %". "</td></tr>";
             
         }
         //echo $total_profit;
         echo "<tr><td>Total/avg</td><td><b>" . number_format($total_profit) . "</b></td><td><b>" . number_format($total_margin/30,2) . " %". "</b></td></tr>";
         echo "</table>";
    }
    
    function getProblematicItems ($ids_string, $con)
    {
        $getProblematicItems = mysqli_query($con, 
            "SELECT item_id, item, sum(profit_total) AS profit
            FROM v_profit_details
            WHERE date(time_sell) >= (DATE(NOW()) - INTERVAL 30 day)
            AND (character_sell_id IN $ids_string
            OR character_buy_id IN $ids_string)
            and profit_total < 0
            GROUP BY item
            ORDER BY sum(profit_total) ASC
            LIMIT 6");
        
        echo "<table border ='1'>";
        echo "<tr><td></td><td>Item</td><td>Profit</td></tr>";
        
        if(mysqli_num_rows($getProblematicItems) == 0)
        {
            echo "<tr><td colspan='3'>No results to display</td></tr>";
        }
        
            while($now = mysqli_fetch_array($getProblematicItems, MYSQLI_ASSOC)) //raw profits table
            {
            $itemRaw = $now['item'];
            $valueRaw = $now['profit'];
            $itemID = $now['item_id'];
            echo "<tr><td> <img src='http://evetrademaster.com/Types/" .$itemID. "_32.png'</td><td>". $itemRaw . "</td><td>" .number_format($valueRaw) ." ISK" . "</td></tr>";
            }
           
       
         echo "</table>";
    }
    
    function getFastestTurnovers($ids_string, $con)
    {
        $getFastestTurnOvers = mysqli_query($con, "SELECT difference, item, item_id, (profit_unit*profit_quantity) as total
            FROM v_profit_details 
            WHERE character_sell_id IN $ids_string 
            AND date(time_sell) >= (DATE(NOW()) - INTERVAL 30 day)
            ORDER BY difference ASC
            LIMIT 3"); 
        
        echo "<table border ='1'>";
        echo "<tr><td></td><td>Item</td><td>Time</td><td>Profit</td></tr>";
        
        if(mysqli_num_rows($getFastestTurnOvers) == 0)
        {
            echo "<tr><td colspan='4'>No results to display</td></tr>";
        }
        
        while($sales = mysqli_fetch_array($getFastestTurnOvers, MYSQLI_ASSOC))
        {
            $item = $sales['item_id'];
            $name = $sales['item'];
            $time = $sales['difference'];
            $profit = $sales['total'];
            
            echo "<tr><td> <img src='http://evetrademaster.com/Types/" .$item. "_32.png'></td><td>". $name . "</td><td>". $time . "</td><td>" . number_format($profit) . "</td></tr>"; 
            
        }
        echo "</table>";
    }
    
    
    //Mailer script
    //Finds every user in the db that chose daily reports
    //Updated all account info (simulate login)
    //Calculates graphs and tables using the statistical functions
    //Sends the report to each user, with a delay between requests to prevent landing on spam lists
    //$email_list = array();
    
    /*$daily_mail_addresses = mysqli_query($con, "SELECT user.iduser, user.username, user.email, count(aggr.character_eve_idcharacter)
            FROM user join aggr on aggr.user_iduser = user.iduser WHERE user.reports = 'daily' 
            group by username order by iduser")
            or die(mysqli_error($con));*/
    
    $daily_mail_addresses = mysqli_query($con, "SELECT user.iduser, user.username, user.email, count(aggr.character_eve_idcharacter)
            FROM user join aggr on aggr.user_iduser = user.iduser WHERE user.reports = 'monthly'
            group by username order by iduser")
            or die(mysqli_error($con)); 
    
    
        while($users = mysqli_fetch_array($daily_mail_addresses))
        {
			ob_start();
            $today = date('Y-m-d');
            $lastmonth = date('Y-m-d',strtotime('-30 days'));
            $email = $users['email'];
            $username = $users['username'];

            echo "Monthly earnings report for " . $username . "<br>";
            
            echo "From: ".$lastmonth. " GMT"."<br>";
            echo "To: " .$today. " GMT"."<br><br>";
			echo "<b>New:</b> Help shape ETM's future development by submitting a very small <a href='http://goo.gl/forms/Oke20Mr1cQ' target=_blank>survey</a> (1 minute)<br><br>";
			
            echo "Last 30 days snapshot: ", "<br>";
            
            $ids = array();
            echo "<table border = '1'>";
             echo "<tr><th>Character</th><th>Purchases (ISK)</th><th>Revenue (ISK)</th><th>Profit (ISK)</th></tr>";
            
            $characterList = mysqli_query($con, "SELECT * FROM v_user_characters WHERE username = '$username'");
                
                $characters = array();
                $user_session = $username;
           
                
                while($chars = mysqli_fetch_array($characterList, MYSQLI_ASSOC))
                {
                    $idcharacter = "(".$chars['character_eve_idcharacter'] . ")";
                    $name = $chars['name'];
                    array_push($ids, $chars['character_eve_idcharacter']);
                    
                    echo "<tr><td>" .$name. "</td><td>". number_format(getExpensesChar($idcharacter,$con)) . "</td><td>". number_format(getRevenuesChar($idcharacter,$con)) . "</td><td>" . number_format(getProfitsChar($idcharacter,$con)) . "</td></tr>";
                 //   echo "<tr><td>" ."Total". "</td><td>". number_format(getExpensesChar($idcharacter,$con)) . "</td><td>". number_format(getRevenuesChar($idcharacter,$con)) . "</td><td>" . number_format(getProfitsChar($idcharacter,$con)) . "</td></tr>";

                    
                }
                $ids_string = "(". implode(",",$ids) . ")";
                echo "<tr><td>Total</td><td><b>". number_format(getExpensesChar($ids_string,$con)) . "</b></td><td><b>". number_format(getRevenuesChar($ids_string,$con)) . "</b></td><td><b>" . number_format(getProfitsChar($ids_string,$con)) . "</b></td></tr>";
                echo "</table><br>";
                
                
              
?>
<br>Best items by profit:<br>
<?php
    getBestItemsProfit($ids_string, $con);
?>
    <br>Best items by profit margin:<br>
<?php
    getBestItemsMargin($ids_string, $con);
?>
    <br>Problematic items:<br>
<?php
    getProblematicItems($ids_string, $con);
?>
    <br>Best customers by profit:<br>
<?php
    getBestCustomersProfit($ids_string, $con);
?>
    <br>Fastest turnovers:<br>
<?php
    getFastestTurnovers($ids_string, $con);
?>
    <br>Last 30 days profits:<br>           
 <?php               
    getLastWeekProfits($ids_string, $con);
?>
    <br>If you found this tool useful, feel free to tip a small amount of ISK to 'Nick Starkey' ingame or to <a href='https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=E92PVNRT3L9EQ'>Paypal</a> to help me keep up with server costs.
    <br>Note: You are receiving this e-mail because you have subscribed to evetrademaster.com automated reports. 
    If you do not wish to recieve any more reports, click <a href='www.evetrademaster.com/pages/unsubscribe.php?user=<?php echo $username ?>&email=<?php echo $email?>'>here</a> to unsubscribe.
                                                                                                               
<?php
    $body = ob_get_contents();
    ob_end_clean();
    
    $from = "etmdevelopment42@gmail.com";
    $from_name = "ETM";
    $subject = "Monthly earnings report for " . $today;
    $to = $email;
    
    
    smtpmailer($to, $from, $from_name, $subject, $body); 
    }


?>
