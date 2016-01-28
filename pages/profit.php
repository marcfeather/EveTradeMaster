<?php
require_once ('scripts/session.php');
require_once('bootstrapper.php');
require_once("scripts/fusioncharts/php-wrapper/fusioncharts.php");
require_once("scripts/class/tax.php");
?>

<!DOCTYPE html>
<html lang="en">

    <!-- DataTables JavaScript -->
    <script src="../bower_components/datatables/media/js/jquery.dataTables.min.js"></script>
    <script src="../bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js"></script>

<head>
<?php
            //SELECT station.`name`, count(*) FROM `transaction` join station where station.eve_idstation = transaction.`station_eve_idstation` group by `station_eve_idstation`
    $currentday = date("d");
    $currentmonth = date("m");
    $currentyear = date("Y");

    if(isset($_GET['last_filter']))
        {
            $last_filter = $_GET['last_filter'];
            
            switch($last_filter)
            {
            case '24':
                $text_filter = " for last 24 hours";
                break;
            case '168':
                $text_filter = " for last 7 days";
                break;
            case '720':
                $text_filter = " for last 30 days";
                break;
            case '20160':
                $text_filter = " for last year";
                break;
            }
        }
        else
        {
            $last_filter = 24;
            $text_filter = " for last 24 hours";
        }
		
	   
   //GETs for table sorting 
   isset($_GET['difference']) ? $dur= $_GET['difference'] : $dur = "desc";
   isset($_GET['margin']) ? $margin=$_GET['margin'] : $margin = "desc";
   isset($_GET['item']) ? $item=$_GET['item'] : $item = "desc";
   isset($_GET['quantity_sell']) ? $q=$_GET['quantity_sell'] : $q = "desc";
   isset($_GET['profit_total']) ? $profit=$_GET['profit_total'] : $profit = "desc";
   
   //filtering
   isset($_GET['item_id']) ? $typeid=$_GET['item_id'] : $n=1;
   isset($_GET['station_buy_id']) ? $fromid=$_GET['station_buy_id'] : $n=2;
   isset($_GET['station_sell_id']) ? $toid=$_GET['station_sell_id'] : $n = 3;	
		
		
        
    if(isset($_GET['item_id']))
    {
        $item_id = $_GET['item_id'];
        $getItemName = utils::mysqli_result(mysqli_query($con, "SELECT name FROM item WHERE eve_iditem = '$item_id'"),0);
               
            if($getItemName)
            {
                $text_filter .= " for item ". $getItemName;
            }
    }
    
    if(isset($_GET['station_buy_id']))
    {
        $station_buy_id = $_GET['station_buy_id'];
        $getStationBuyName = utils::mysqli_result(mysqli_query($con, "SELECT name FROM station WHERE eve_idstation = '$station_buy_id'"),0);
               
            if($getStationBuyName)
            {
                $text_filter .= " for station:buy ". $getStationBuyName;
            }
    }
    
    if(isset($_GET['station_sell_id']))
    {
        $station_sell_id = $_GET['station_sell_id'];
        $getStationSellName = utils::mysqli_result(mysqli_query($con, "SELECT name FROM station WHERE eve_idstation = '$station_sell_id'"),0);
               
            if($getStationSellName)
            {
                $text_filter .= " for station:sell ". $getStationSellName;
            }
    }
       
    
    
    function getProfitGraphData($caption,$subcaption)
    {
        return
        $arrData = array( //graph parameters
                        "chart" => array(
                        "caption" => $caption,
                        "subCaption" => $subcaption,
                        "xAxisName"=> "Day",
                        "yAxisName"=> "ISK Profit",
                        "lineThickness"=> "3",
                        "paletteColors"=> "#0075c2",
                        "baseFontColor"=> "#333333",
                        "baseFont"=> "Helvetica Neue,Arial",
                        "captionFontSize"=> "14",
                        "subcaptionFontSize"=> "14",
                        "subcaptionFontBold"=> "0",
                        "showBorder"=> "0",
                        "bgColor"=> "#ffffff",
                        "showShadow"=> "0",
                        "canvasBgColor"=> "#ffffff",
                        "canvasBorderAlpha"=> "0",
                        "divlineAlpha"=> "100",
                        "divlineColor"=> "#999999",
                        "divlineThickness"=> "1",
                        "divLineDashed"=> "1",
                        "divLineDashLen"=> "1",
                        "divLineGapLen"=> "1",
                        "showXAxisLine"=> "1",
                        "xAxisLineThickness"=> "1",
                        "xAxisLineColor"=> "#999999",
                        "showAlternateHGridColor"=> "0"  
              	)
           	);
    }
    
    
    function drawProfitPanel()
    {
?>
    <div class="pull-right">
        <div class="btn-group">
                                                        
         </div>
            </div>
            <div class="panel-heading">
            <i class="fa fa-bar-chart-o fa-fw"></i>
                  <div class="pull-right">         
                  </div>
        Detailed Profits
           <br>   <i class="fa fa-info fa-fw"></i> This view detects all item resales, and calculates profits made. Broker fees are always assumed regardless if you bought an item from a buy order or a sell order.
           <br>   <i class="fa fa-info fa-fw"></i> You can aggregate different characters from your account toguether, so that buying an item with one character and re-selling it with another character is also recognized as profit. 
           <br>   <i class="fa fa-info fa-fw"></i> Broker fees and transaction taxes are already included in prices
            </div>
<?php
    }
    $user_session=utils::mysqli_result(mysqli_query($con, "SELECT username FROM v_user_characters WHERE character_eve_idcharacter = '$character_get'"), 0,0);
    $title = "Detailed profits";
    $content = new content();
    $content->drawMeta($title);
?>
    </head>
        <body>
            
    <div id="wrapper">
<?php
    $content->drawNav($accountBalance, $networth, $sellOrders, $escrow, $username, $character_get, $getCharacterList);
?>
    <div id="page-wrapper">
 <?php
    $content->drawHeader($getCharacterPortrait, $characterName, "Detailed profits");
?>
    <div class="row">
<?php
    $content->columns_def();
?>    <!-- /.row -->
 <div class="row">
 <div class="col-lg-12">  
     <div class="panel panel-default">    
<?php
    drawProfitPanel();
?>
    <div class="panel-body">
        <div class="dataTable_wrapper">
<?php
//aggregation always starts with the current character ID
    $character_name = utils::mysqli_result(mysqli_query($con, "SELECT name FROM characters WHERE eve_idcharacter = '$character_get'"),0,0);
    $aggregation = array();
    $aggregation_ids = array();
    
     array_push($aggregation, $character_name);
     
    if(isset($_POST['Submit']))
    {
       $number_chars = count($_POST); //POST count -1 gives us the number of submitted characters
       //var_dump($_POST);
    
        foreach($_POST as $key => $value) //character 1 => 'name'
        {
            //unset($_SESSION[$key]);
            array_push($aggregation,$value);
            //$_SESSION[$key] = $value;
        }
        //array_pop($aggregation); //remove the Submit element
        array_pop($aggregation); //remove the Last_filter radio value
        $_SESSION['aggregation'] = $aggregation;
       
       //var_dump($aggregation);
        
  
   //stack with all requested character ID, but first we need the character IDs
    foreach($aggregation as $mow)
    {
        array_push($aggregation_ids, utils::mysqli_result(mysqli_query($con, "SELECT eve_idcharacter FROM characters WHERE name = '$mow'"),0,0));
    }
   //var_dump($aggregation_ids);
        $ids_string = "(" . implode(',',$aggregation_ids) . ")";
        unset($_SESSION['ids']);
        $_SESSION['ids_string'] = $ids_string;
       //var_dump($aggregation); //character names of all requested aggregations (includes active)
    }
    else
    {
        isset($_SESSION['ids_string']) ? $ids_string = $_SESSION['ids_string'] : $ids_string = "";
        isset($_SESSION['aggregation']) ? $aggregation = $_SESSION['aggregation'] : $aggregation = "";
    }
   
        
       
            //default values
         $transFrom = 'buy';
         $transTo = 'sell';
         
         echo "<form accept-charset='utf-8' name='aggr' method='POST' action='profit.php?character=$character_get&last_filter=$last_filter'>";
       //aggregation form
       $getCharacterList = mysqli_query($con, "SELECT DISTINCT name FROM v_user_characters WHERE username = '$user_session'") or die(mysqli_error($con));
       $character_get_name = utils::mysqli_result(mysqli_query($con, "SELECT name FROM characters WHERE eve_idcharacter = '$character_get'"),0,0); //active character in bold
       
       $characters = array();
       while ($row = mysqli_fetch_array($getCharacterList, MYSQLI_ASSOC))
       {
           $chars = $row['name'];
           array_push($characters, $chars);
       }
       //var_dump($characters);
      //remove active characters from array
        for ($i=0;$i<count($characters)-1;$i++)
        {
            if ($character_get_name == $characters[$i])
            {
                unset($characters[$i]);
               // echo $characters[$i];
            }
        }
        ?>
    
        <i class="fa fa-thumb-tack fa-fw"></i>
<?php 

       if($ids_string == "") //first time visit has no session or post variable.
            {
                $ids_string = "(".$character_get.")";
            }

		!empty($typeid) ? $itemIDString = "AND item_id = '$typeid'" : $itemIDString = "";
		
        $getTotalProfit = utils::mysqli_result(mysqli_query($con, "SELECT SUM(profit_total) FROM v_profit_details WHERE (character_buy_id IN $ids_string or character_sell_id IN $ids_string)". $itemIDString. " AND time_sell>= DATE_SUB(NOW(), INTERVAL $last_filter HOUR) "),0,0);
        $getTotalProfit == "" ? $getTotalProfit = 0 : $getTotalProfit = $getTotalProfit;
		

        echo "Currently showing results for <b>" . implode(",",$aggregation) ."</b>". $text_filter . "<br>";
        $number = 0;
        echo "Characters: " ."<input type='checkbox' onclick='return false;'checked> <b>$characterName</b>" . "  ";
        
        foreach($characters as $row2)
        {
            $number = $number+1;
            echo  "<input type = 'checkbox' name='characters$number' value='$row2'>" . " ".$row2 ."  ";
        }
        //echo "<br>";
     //  "<form accept-charset='utf-8' name='filters' method='POST' action='profit.php?character=$character_get'>";
             ?>
          
          <input type="Submit" class="btn btn-success" name="Submit" value="Submit">  
          <br>
    </form>
	
	<table class="table table-striped table-bordered table-hover" id="dataTables-profit_sum">
        <tr><td>Total Profit made <?php echo $text_filter . " ". implode(",",$aggregation)?> </td><td><?php echo number_format($getTotalProfit) ?> ISK</td></tr>   
        
    </table>
    
    
    Get results for past:
    <a href='profit.php?character=<?php echo $character_get?>&last_filter=24'>24 hours </a>
    <a href='profit.php?character=<?php echo $character_get?>&last_filter=168'>7 days </a>
    <a href='profit.php?character=<?php echo $character_get?>&last_filter=720'>30 days </a>
    <!--<a href='profit.php?character=<?php echo $character_get?>&last_filter=20160'>1 year </a> -->
    <br><br>
	
<?php
	if(empty($typeid))
	{
?>      
<div id="chart-1"></div>
<?php
	}
   $buy_stack = array();
   $sell_stack = array();
 
   
   
   

   //isset($_GET['dayt']) ? $dayt=$_GET['dayt'] : maybe soon
   
   
          ?>
     <table class="table table-striped table-bordered table-hover" id="dataTables-profit">
         <thead>
        <tr>
            <th><a href='profit.php?character=<?php echo $character_get ?>&last_filter=<?php echo $last_filter?>&item=<?php echo ((!isset($item) || $item == "desc") ?  "asc" :  "desc");?>'>Item</a></th>
            <th></th>
            <th>Station</th>
            <th>Price/unit</th>
            <th><a href='profit.php?character=<?php echo $character_get ?>&last_filter=<?php echo $last_filter?>&quantity_sell=<?php echo ((!isset($q) || $q == "desc") ?  "asc" :  "desc");?>'>Q</a></th>
            <th>Price total</th>
            <th>Time</th>
            <th>Character</th>
            <th><a href='profit.php?character=<?php echo $character_get ?>&last_filter=<?php echo $last_filter?>&profit_total=<?php echo ((!isset($profit) || $profit == "desc") ?  "asc" :  "desc");?>'>Profit</a></th>
            <th><a href='profit.php?character=<?php echo $character_get ?>&last_filter=<?php echo $last_filter?>&margin=<?php echo ((!isset($margin) || $margin == "desc") ?  "asc" :  "desc");?>'>Margin</a></th>
            <th><a href='profit.php?character=<?php echo $character_get ?>&last_filter=<?php echo $last_filter?>&difference=<?php echo ((!isset($dur) || $dur == "desc") ?  "asc" :  "desc");?>'>Duration (h:m:s)</a></th>
        </tr>
         <thead><tbody>
    <?php

            //print_r($_GET);
            if(isset($_GET['difference']) || isset($_GET['margin']) || isset($_GET['item']) || isset($_GET['quantity_sell']) || isset($_GET['profit_total']))
            {
                $val = array_values($_GET)[2];
                $col = array_keys($_GET)[2];
            }
            
            if(isset($col))
            {
            $orderstr = "ORDER BY " .$col . " " . $val;
            }
            else
            {
                $orderstr = "ORDER BY time_sell DESC";
            }
            
            if(isset($_GET['item_id']) || isset($_GET['station_buy_id']) || isset($_GET['station_sell_id']))
            {
                $val2 = array_values($_GET)[1];
                $col2 = array_keys($_GET)[1];
            }
            
            if(isset($col2))
            {
            $andstr = " AND " .$col2."=".$val2. " ";
            }
            else
            {
                $andstr = "";
            }
            
            //echo $orderstr;
            //echo "<br>";
            //echo $andstr;
            
                    
       // echo $orderstr;
       // echo $_GET['item'];
       
        $profit_data_query = mysqli_query($con, "select profit.profit_unit AS profit_unit, 
        profit.characters_eve_idcharacters_IN, 
        profit.characters_eve_idcharacters_OUT, 
        profit.transaction_idbuy_buy, 
        profit.transaction_idbuy_sell,
        item.eve_iditem as item_id, 
        item.name AS item_name, 
        s1.name AS station_buy, 
        s2.name as station_sell,
        s1.eve_idstation as station_buy_id,
        s2.eve_idstation as station_sell_id,
        t1.time as time_buy,
        t2.time as time_sell,
        t1.price_unit as buy_price,
        t2.price_unit as sell_price,
        t1.quantity as quantity_buy,
        t2.quantity as quantity_sell,
        profit.profit_unit as profit_unit,
        profit.quantity_profit AS profit_quantity,
        c1.name as character_buy,
        c2.name as character_sell,
        timediff(t2.time,t1.time) as difference
        from profit
        join transaction t1 on t1.idbuy = profit.transaction_idbuy_buy
        join transaction t2 on t2.idbuy = profit.transaction_idbuy_sell
        join item on t1.item_eve_iditem = item.eve_iditem
        join station s1 on t1.station_eve_idstation = s1.eve_idstation
        join station s2 on t2.station_eve_idstation = s2.eve_idstation
        join characters c1 on t1.character_eve_idcharacter = c1.eve_idcharacter
        join characters c2 on t2.character_eve_idcharacter = c2.eve_idcharacter

        WHERE (profit.characters_eve_idcharacters_IN IN $ids_string $andstr
        and profit.characters_eve_idcharacters_OUT IN $ids_string) 
        AND t2.time>= DATE_SUB(NOW(), INTERVAL $last_filter HOUR) 
        $orderstr
        limit 250")
                or die(mysqli_error($con));
            

                /*$profit_data_query = mysqli_query($con, "SELECT * FROM v_profit_details WHERE (character_buy_id IN $ids_string $andstr and character_sell_id IN $ids_string) AND time_sell>= DATE_SUB(NOW(), INTERVAL $last_filter HOUR) $orderstr")
                               or die(mysqli_error($con)); */

                       while($profit_data = mysqli_fetch_array($profit_data_query))
                       {
                           $itemID_v = $profit_data['item_id'];
                           $itemName_v = $profit_data['item_name'];
                           
                           $stationFromID_v = $profit_data['station_buy_id'];
                           $fromStation_v = $profit_data['station_buy'];
                           
                           $stationToID_v = $profit_data['station_sell_id'];
                           $toStation_v = $profit_data['station_sell'];
                           
                           $price_unit_b_v = $profit_data['buy_price'];
                           $price_unit_s_v = $profit_data['sell_price'];
                           
                           $quantity_b_v = $profit_data['quantity_buy'];
                           $quantity_s_v = $profit_data['quantity_sell'];
                           
                           $date_buy_v = $profit_data['time_buy'];
                           $date_sell_v = $profit_data['time_sell'];
                           
                           $characterBuy_v = $profit_data['character_buy'];
                           $characterSell_v = $profit_data['character_sell'];
                           
                           $profit_v = $profit_data['profit_unit'] * $profit_data['profit_quantity'];
                           $profit_v > 0 ? $color = "class='success'" : $color = "class='danger'";
                           
                           $date_difference_v = $profit_data['difference'];
                        
   
                            $taxcalc2 = new tax($stationFromID_v, $stationToID_v, $con, $character_get, $transFrom, $transTo);
                                
                                $transTaxFrom = $taxcalc2->calculateTaxFrom();
                                $transTaxTo = $taxcalc2->calculateTaxTo();
                                $brokerFeeFrom = $taxcalc2->calculateBrokerFrom();
                                $brokerFeeTo = $taxcalc2->calculateBrokerTo();
 
                            $price_unit_b_taxed_v = $price_unit_b_v*$brokerFeeFrom*$transTaxFrom;
                            $price_total_b_taxed_v = $price_unit_b_taxed_v*$quantity_s_v;
                            $price_unit_s_taxed_v = $price_unit_s_v*$brokerFeeTo*$transTaxTo;
                            $price_total_s_taxed_v = $price_unit_s_taxed_v*$quantity_s_v;
                            
                           /* $brokerFeeFromVal = $priceFrom*($brokerFeeFrom-1);
                            $brokerFeeToVal = $priceTo*(1-$brokerFeeTo);
                            $transTaxToVal = $priceFrom*(1-$transTaxTo);
                            if($brokerFeeFromVal < 100) {$brokerFeeFromVal = 100;}
                            if($brokerFeeToVal < 100) {$brokerFeeToVal = 100;}    
                           /*  $profit_v = ($price_unit_s_taxed_v - $price_unit_b_taxed_v) * min($quantity_b_v,$quantity_s_v);
                             $profit_unit_v = ($price_unit_s_taxed_v - $price_unit_b_taxed_v);
                             $margin_v = ($profit_v/$price_total_b_taxed_v)*100;*/
                        $margin_v = ($profit_v/$price_total_b_taxed_v)*100;
                        $margin_n = ($profit_data['profit_unit']/$price_unit_b_taxed_v)*100;
			
                        

                         echo "<tr $color>".
                                    "<td rowspan=2>" . "<img src='../Types/" .$itemID_v. "_32.png'>" . " ". "<a href='profit.php?character=".$character_get."&item_id=".$itemID_v."&last_filter=".$last_filter."'>".$itemName_v . "</a></td>".
                                    "<td>" . "<img src='../assets/buy.png'>" ."</td>" .
                                    "<td><a href='profit.php?character=".$character_get."&station_buy_id=".$stationFromID_v."&last_filter=".$last_filter."'>".$fromStation_v . "</a></td>".
                                    "<td align='right'>" . number_format($price_unit_b_taxed_v,2). "</td>" .
                                    "<td align='right' rowspan=2>" . $quantity_s_v . "</td>".
                                    "<td align='right'>" . number_format($price_total_b_taxed_v,2) . "</td>" .
                                    "<td>" . $date_buy_v . "</td>".
                                    "<td>" . $characterBuy_v . "</td>".
                                    "<td align='right' rowspan=2>" . number_format($profit_v,2) . "</td>".
                                    "<td align='right'rowspan=2>" . utils::formating_profit(number_format($margin_n,2)) . "</td>".
                                    "<td align='right' rowspan=2>" . $date_difference_v . "</td></tr>" .
                                    
                                    "<tr $color><td>" . "<img src='../assets/sell.png'>" ."</td>" .
                                    "<td><a href='profit.php?character=".$character_get."&station_sell_id=".$stationToID_v."&last_filter=".$last_filter."'>".$toStation_v . "</a></td>" .
                                    "<td align='right'>" . number_format($price_unit_s_taxed_v,2) . "</td>" .
                                    "<td align='right'>" . number_format($price_total_s_taxed_v,2) . "</td>" .
                                    "<td>" . $date_sell_v . "</td>" ?>
                                    <td> <?php echo $characterSell_v ?> </td>    
<?php
                       }
           
           echo "</tbody></table>";
          
        //Graphs start here
  //echo $all_profits;
           ?>
        
        <script>
            var x = document.getElementById("dataTables-profit").rows.length;
            if(x == 1)
            {
                document.write("No re-sales/profits found.");
            }
        </script>
        
        <script src="scripts/fusioncharts/js/fusioncharts.js"></script>
        
<?php
           if(isset($_GET['last_filter']) && $_GET['last_filter'] == '168')
           {
           $sales_list = array();
           $purchases_list = array();
           $profit_list = array();
           $days_list = array();
           $caption = "ISK Profit by day";
           $subcaption = "Last 7 days";
           
           $arrData = getProfitGraphData($caption,$subcaption);
           $arrData["data"] = array();
           $index = -1; //date/profits array index
           //$day = 0; //week for incrementing
           
           //$arrData2 = getSalesGraphData($caption, $subcaption);
           //$arrData2["dataset"] = array();
           
           $today = new DateTime('now');
           date_sub($today,date_interval_create_from_date_string("8 day"));

           for($i=7;$i>=0;$i--)
           {
               $index = $index+1;
               //$day = $day+1;
               //$k = $i+1; //1 day interval
               date_add($today,date_interval_create_from_date_string("1 day"));
               $today_a = $today->format("Y-m-d");
                //SELECT SUM(profit_total), DATE(time_sell) FROM v_profit_details WHERE time_sell LIKE '2015-11-%' order BY time_sell
               
               /*$get_7_days = mysqli_query($con, "SELECT SUM(profit_total) AS profits
                       FROM v_profit_details 
                       WHERE time_sell > DATE_SUB( NOW() , INTERVAL '$k' DAY)
                       AND time_sell < DATE_SUB(NOW(), INTERVAL '$i' DAY)
                       AND character_buy_id IN $ids_string AND character_sell_id IN $ids_string ");*/
               
               $get_7_days = mysqli_query($con, "select total_profit as profits, date 
                   FROM history 
                   WHERE date = '$today_a%'
                   AND characters_eve_idcharacters IN $ids_string order by date asc")
                       or die(mysqli_error($con));

               $data_7_days = mysqli_fetch_array($get_7_days, MYSQLI_ASSOC); 
               $profit = $data_7_days['profits'];
                    if(!isset($profit) && $profit == "") {$profit = 0;}

               $dates = $data_7_days['date'];

               
               array_push($days_list, $dates); //revert both arrays to get results in ascending order
               array_push($profit_list, $profit);
               
               array_push($arrData['data'], array("label" => (string)$days_list[$index], //merges both timestamp array with profit array
                        "value" => (string)$profit_list[$index]));
               

               
              // $finalData = array_reverse($arrData['data']);
            }
                 $jsonEncodedData = json_encode($arrData);
               
                 //var_dump($arrData['data']);
                 $columnChart = new FusionCharts("line", "myFirstChart" , 600, 300, "chart-1", "json", $jsonEncodedData);
                 
                 

        	// Render the chart
        	 $columnChart->render();
              
           }
           //var_dump($arrData2);
           //for 30 days
           if(isset($_GET['last_filter']) &&  $_GET['last_filter'] == '720')
           {
           $profit_list = array();
           $days_list = array();
           
           $caption = "ISK Profit by day";
           $subcaption = "Last 30 days";
           
           $arrData = getProfitGraphData($caption,$subcaption);
           $arrData["data"] = array();
           $index = -1; //date/profits array index
           //$day = 0; //week for incrementing
           
           
           
           $today = new DateTime('now');
           date_sub($today,date_interval_create_from_date_string("31 day"));
           
           
           
           for($i=0;$i<=30;$i++)
            {
               $index = $index+1;
               date_add($today,date_interval_create_from_date_string("1 day"));
            
               $today_a = $today->format("Y-m-d");
              // $today_f = $today_f . "%";
               
               //$k = $i+1; //1 day interval
                 $get_30_days = mysqli_query($con, "select total_profit as profits, date 
                   FROM history 
                   WHERE date = '$today_a%'
                   AND characters_eve_idcharacters IN $ids_string order by date asc")
                       or die(mysqli_error($con));
               
               $data_30_days = mysqli_fetch_array($get_30_days, MYSQLI_ASSOC); 
               
               $profit = $data_30_days['profits'];
                    if(!isset($profit) && $profit == "") {$profit = 0;}
               
               $dates = $data_30_days['date'];
               //$hoursInterval = 60*60*24;
               
               //$now = strtotime("now");
               //$weekAgo = strtotime("-1 month");
               
               //$previousDate = $weekAgo + $day*$hoursInterval;
               //$previousDate_stamp = gmdate("Y-m-d", $previousDate); //get past 7 dates in ascending order
               
               array_push($days_list, $dates ); //revert both arrays to get results in ascending order
               array_push($profit_list, $profit);
               array_push($arrData['data'], array("label" => (string)$days_list[$index], //merges both timestamp array with profit array
                        "value" => (string)$profit_list[$index]));
              // $finalData = array_reverse($arrData['data']);
            }
                 $jsonEncodedData = json_encode($arrData);
                 //var_dump($arrData['data']);
                 $columnChart = new FusionCharts("line", "myFirstChart" , 600, 300, "chart-1", "json", $jsonEncodedData);

        	// Render the chart
        	 $columnChart->render();
           }
           
           
           ////1 
           
           
           if(isset($_GET['last_filter']) &&  $_GET['last_filter'] == '20160')
           {
           $profit_list = array();
           $days_list = array();
           
           $caption = "ISK Profit by month";
           $subcaption = "Last 12 months";
           
           $arrData = getProfitGraphData($caption,$subcaption);
           $arrData["data"] = array();
           $index = -1; //date/profits array index
           $day = 0; //week for incrementing
           
           
           
           for($i=11;$i>=0;$i--)
           {
               $index = $index+1;
               $day = $day+1;
               $k = $i+1; //1 day interval
               $get_12_months = mysqli_query($con, "SELECT SUM(profit_total) AS profits "
                       . "FROM v_profit_details "
                       . "WHERE time_sell > DATE_SUB( NOW() , INTERVAL '$k' MONTH) "
                       . "AND time_sell < DATE_SUB(NOW(), INTERVAL '$i' MONTH)"
                       . "AND character_buy_id IN $ids_string AND character_sell_id IN $ids_string ");
               
               $data_12_months = mysqli_fetch_array($get_12_months, MYSQLI_ASSOC); 
               $profit = $data_12_months['profits'];
                    if(!isset($profit) && $profit == "") {$profit = 0;}

               $hoursInterval = 60*60*24*30; 
               
               $now = strtotime("now");
               $weekAgo = strtotime("-1 year");
               
               $previousDate = $weekAgo + $day*$hoursInterval;
               $previousDate_stamp = gmdate("Y-m", $previousDate); //get past 7 dates in ascending order
              
               array_push($days_list, $previousDate_stamp); //revert both arrays to get results in ascending order
               array_push($profit_list, $profit);
               
               
               array_push($arrData["data"], array("label" => (string)$days_list[$index], //merges both timestamp array with profit array
                        "value" => (string)$profit_list[$index]));
              // $finalData = array_reverse($arrData['data']);
           }
                 $jsonEncodedData = json_encode($arrData);
                 //var_dump($arrData['data']);
                 $columnChart = new FusionCharts("line", "myFirstChart" , 600, 300, "chart-1", "json", $jsonEncodedData);

        	// Render the chart
        	 $columnChart->render();
           }
           ?>
            <thead>
           </thead>
           <tbody>
           </tbody>
      </table>
             </div>       <!-- /.table-responsive --> 
            </div>
        </div>
<?php
    $content->drawFooter();
   // http_post_data(", "tnc");
    

    // mysqli_query($con, "DELETE FROM profit");
?>
     
     </div>
 <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
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