<?php
require_once ('/var/www/html/pages/scripts/class/link.php');
require_once ('/var/www/html/pages/scripts/class/utils.php');

    $link = new link();
    $con = $link->connect();
    //Updates the total sales and purchases for each day for every character since 24/10/2015


    $getCharacters = mysqli_query($con, "SELECT eve_idcharacter FROM characters") 
        or die(mysqli_error($con));
    
    while($chars = mysqli_fetch_array($getCharacters, MYSQLI_ASSOC))
    {
        $characterID = $chars['eve_idcharacter'];
        //iterate dates from 24/10/2015
        $getDates = mysqli_query($con, "SELECT days FROM calendar WHERE days > '2015-10-24' AND days <= '2016-01-27'")
            or die(mysqli_error($con));
        
        while($days = mysqli_fetch_array($getDates, MYSQLI_ASSOC))
        {
            $date = $days['days'];
            //get sum of sales
            $getSalesSum = mysqli_query($con, "SELECT SUM(price_total) FROM transaction
                WHERE character_eve_idcharacter = '$characterID' AND transaction_type = 'Sell' AND date(time) = '$date'");
				$salesSumVal = utils::mysqli_result($getSalesSum, 0,0);
            
            //get sum of purchases
            $getPurchasesSum = mysqli_query($con, "SELECT SUM(price_total) FROM transaction
                WHERE character_eve_idcharacter = '$characterID' AND transaction_type = 'Buy' AND date(time) = '$date'");
				$purchasesSumVal = utils::mysqli_result($getPurchasesSum, 0,0);
            
            $getProfitsSum = mysqli_query($con, "SELECT SUM(profit_unit*quantity_profit) FROM profit WHERE date(timestamp_sell) = '$date'
                    AND characters_eve_idcharacters_OUT = '$characterID'");
				$profitsSumVal = utils::mysqli_result($getProfitsSum,0,0);
            
            $getMargin = mysqli_query($con, "select (sum(profit_total))/sum(price_unit_buy*profit_quantity)*100
                    from v_profit_details where character_sell_id = '$characterID'
                     AND DATE(time_sell) = '$date'")  or die (mysqli_error($con));	 
				$marginSumVal = utils::mysqli_result($getMargin,0,0);
            
           
                $addSales = mysqli_query($con, "REPLACE INTO history (idhistory, characters_eve_idcharacters, date, total_buy, total_sell, total_profit, margin)
                VALUES(NULL, '$characterID', '$date', '$purchasesSumVal', '$salesSumVal', '$profitsSumVal', '$marginSumVal')")
                        or die (mysqli_error($con));
            
                echo "Updated " . $characterID . " for " . $date . "<br>";
                
        }
  
    }


?>

