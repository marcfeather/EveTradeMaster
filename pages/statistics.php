<?php
  require_once('scripts/session.php');
  require_once('bootstrapper.php');
  require_once("scripts/fusioncharts/php-wrapper/fusioncharts.php");
  require_once("scripts/class/tax.php");
  
  use Pheal\Pheal;
  
  $content = new content();
  
  if (isset($_GET['int']) && ($_GET['int'] == 24 || $_GET['int'] == 168 || $_GET['int'] == 720))
  {
      $int = $_GET['int'];
      switch ($int)
      {
          case "24":
              $intStr = "for last 24 hours";
              break;
          case "168":
              $intStr = "for last 7 days";
              break;
          case "720":
              $intStr = "for last 30 days";
              break;
      }
  }
  else
  {
      $int    = 168;
      $intStr = "for last 7 days";
  }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <script src="../bower_components/datatables/media/js/jquery.dataTables_dash.min.js"></script>
        <script src="../bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js"></script>
        <script src="scripts/fusioncharts/js/fusioncharts.js"></script>
<?php
  $title = "Statistics";
  $content->drawMeta($title);
  
  function getSalesGraphData($caption, $subcaption, $subsubcaption)
  {
      return $arrData = array(
          "chart" => array(
              "caption" => $caption,
              "subCaption" => $subcaption,
              "xAxisName" => "day",
              "yAxisName" => $subsubcaption,
              "numberPrefix" => "ISK",
              "paletteColors" => "#0075c2",
              "bgColor" => "#ffffff",
              "borderAlpha" => "20",
              "canvasBorderAlpha" => "0",
              "usePlotGradientColor" => "0",
              "plotBorderAlpha" => "10",
              "placevaluesInside" => "0",
              "showvalues" => "0",
              "rotatevalues" => "1",
              "valueFontColor" => "#ffffff",
              "showXAxisLine" => "1",
              "xAxisLineColor" => "#999999",
              "divlineColor" => "#999999",
              "divLineDashed" => "1",
              "showAlternateHGridColor" => "0",
              "subcaptionFontBold" => "0",
              "subcaptionFontSize" => "14"
          )
      );
  }
  
  function getProblematicItems($character_get, $con, $int)
  {
      $getProblematicItems = mysqli_query($con, "SELECT 
        item.eve_iditem as item_id, 
        item.name as item, 
        sum(profit.quantity_profit*profit.profit_unit) as profit
        FROM profit
        JOIN transaction ON profit.transaction_idbuy_sell = transaction.idbuy
        JOIN item ON item.eve_iditem = transaction.item_eve_iditem
        WHERE date(profit.timestamp_sell) >  (DATE(NOW()) - INTERVAL '$int' HOUR)
        AND profit.characters_eve_idcharacters_OUT = '$character_get'
        AND profit.quantity_profit*profit.profit_unit < 0
        GROUP BY item.eve_iditem
        ORDER BY profit.quantity_profit*profit.profit_unit ASC
        LIMIT 5");
      
      echo "<table border ='1' class='table table-striped table-bordered table-hover'>";
      echo "<tr><th></th><th>Item</th><th>Profit</th></tr>";
      
      if (mysqli_num_rows($getProblematicItems) == 0)
      {
          echo "<tr><td colspan='3'>No results to display</td></tr>";
      }
      
      while ($now = mysqli_fetch_array($getProblematicItems, MYSQLI_ASSOC)) //raw profits table
      {
          $itemRaw  = $now['item'];
          $valueRaw = $now['profit'];
          $itemID   = $now['item_id'];
          echo "<tr><td> <img src='http://evetrademaster.com/Types/" . $itemID . "_32.png'</td><td>" . $itemRaw . "</td><td>" . number_format($valueRaw) . " ISK" . "</td></tr>";
      }
      
      
      echo "</table>";
  }
  
  function getLastWeekProfits($character_get, $con)
  {
      $profits      = array();
      $margins      = array();
      $dates        = array();
      $total_profit = 0;
      $total_margin = 0;
      
      echo "<table border ='1' class='table table-striped table-bordered table-hover'>";
      echo "<tr><th>Day</th><th>Total Buy</th><th>Total Sell</th><th>Total Profit</th><th>Margin</th></tr>";
      
      $getLastWeekProfits = mysqli_query($con, "SELECT * FROM history
             WHERE characters_eve_idcharacters = '$character_get' AND date >= DATE( DATE_SUB( NOW() , INTERVAL 7 DAY ))
             AND date <= 'today' GROUP BY date ORDER BY date DESC") or die(mysqli_error($con));
      
      while ($values = mysqli_fetch_array($getLastWeekProfits, MYSQLI_ASSOC))
      {
          $total_buy    = $values['total_buy'];
          $total_sell   = $values['total_sell'];
          $total_profit = $values['total_profit'];
          $margin       = $values['margin'];
          $date         = $values['date'];
          $color        = "";
          if ($total_profit > 0)
          {
              $color = "class='success'";
          }
          else if ($total_profit < 0)
          {
              $color = "class='danger'";
          }
          
          
          echo "<tr $color><td>" . $date . "</td>" . "<td>" . number_format($total_buy) . "</td>" . "<td>" . number_format($total_sell) . "</td>" . "<td>" . number_format($total_profit) . "</td>" . "<td>" . number_format($margin, 2) . " %" . "</td>" . "</tr>";
      }
      
      echo "</table>";
  }
  
  $getCharacterName = utils::mysqli_result(mysqli_query($con, "SELECT name FROM characters WHERE eve_idcharacter = '$character_get'"), 0, 0);
  
  $bestItemsRaw = mysqli_query($con, "SELECT 
            item.eve_iditem as item_id, 
            item.name as item, 
            sum(profit.quantity_profit*profit.profit_unit) as profit
            FROM profit
            JOIN transaction ON profit.transaction_idbuy_sell = transaction.idbuy
            JOIN item ON item.eve_iditem = transaction.item_eve_iditem
            WHERE date(profit.timestamp_sell) >  (DATE(NOW()) - INTERVAL '$int' HOUR)
            AND profit.characters_eve_idcharacters_OUT = '$character_get'
            GROUP BY item.eve_iditem
            ORDER BY profit.quantity_profit*profit.profit_unit DESC
            LIMIT 5");
  
  $bestItemsMargin = mysqli_query($con, "SELECT item.eve_iditem as item_id,
            item.name as item,
            sum(profit.profit_unit)/sum(t1.price_unit) as margin
            FROM profit
            JOIN transaction t1 ON t1.idbuy = profit.transaction_idbuy_buy
            JOIN transaction t2 ON t2.idbuy = profit.transaction_idbuy_sell
            JOIN characters on t1.character_eve_idcharacter = characters.eve_idcharacter
            JOIN item on t1.item_eve_iditem = item.eve_iditem
            WHERE date(t2.time) >= (DATE(NOW()) - INTERVAL '$int' HOUR)
            AND (characters.eve_idcharacter = '$character_get' OR characters.eve_idcharacter = '$character_get')
            group by item.eve_iditem
            order by sum(profit.profit_unit)/sum(t1.price_unit) DESC
            limit 5");
  
  $bestCustomersRawProfit = mysqli_query($con, "SELECT t2.client AS soldTo, sum(profit.profit_unit * profit.quantity_profit) as profit
            FROM profit
            JOIN transaction t1 on profit.transaction_idbuy_buy = t1.idbuy
            JOIN transaction t2 on profit.transaction_idbuy_sell = t2.idbuy
            WHERE date(t2.time) >= (DATE(NOW()) - INTERVAL '$int' HOUR)
            AND (t2.character_eve_idcharacter = '$character_get'  OR t1.character_eve_idcharacter = '$character_get') 
            GROUP BY t2.client
            ORDER BY sum(profit.profit_unit * profit.quantity_profit) DESC
            LIMIT 5");
  
  /* $bestCustomersMargin = mysqli_query($con, "SELECT soldTo, AVG(profit_total/price_total_buy) AS margin
  FROM v_profit_details
  WHERE date(time_sell) >= DATE(NOW() - INTERVAL 7 DAY)
  AND character_sell_id = '$character_get'
  OR character_buy_id = '$character_get'
  GROUP BY item
  ORDER BY AVG(profit_total/price_total_buy) DESC
  LIMIT 5" ); */
  
  $getTimeZonesProfit = mysqli_query($con, "SELECT t2.time as time_sell, 
            profit.quantity_profit*profit.profit_unit as profit_total
            FROM profit
            JOIN transaction t2 on profit.transaction_idbuy_sell = t2.idbuy
            WHERE date(t2.time) >= DATE(NOW() - INTERVAL '$int' HOUR)
            AND t2.character_eve_idcharacter = '$character_get'
            AND date(t2.time) >= (DATE(NOW()) - INTERVAL '$int' HOUR)
            ORDER BY t2.time asc");
  
  $EU_profit = 0;
  $AU_profit = 0;
  $US_profit = 0;
  while ($cow = mysqli_fetch_array($getTimeZonesProfit, MYSQLI_ASSOC))
  {
      $time_sell = $cow['time_sell'];
      $profit    = $cow['profit_total'];
      $hour      = substr(substr($time_sell, -8), 0, 2);
      if ($hour > 15 && $hour < 23)
      {
          $EU_profit = $EU_profit + $profit;
      }
      else
      {
          if ($hour > 7 && $hour < 15)
          {
              $AU_profit = $AU_profit + $profit;
          }
          else
          {
              $US_profit = $US_profit + $profit;
          }
      }
  }
  //  var_dump($EU_profit);
  //  var_dump($US_profit);
  //  var_dump($AU_profit);
  $tz_profits = array(
      "eu" => $EU_profit,
      "us" => $US_profit,
      "au" => $AU_profit
  );
  
  function getFastestTurnovers($character_get, $con, $int)
  {
      $getFastestTurnOvers = mysqli_query($con, "SELECT item.name as item, timediff(t2.time,t1.time) as difference,
            quantity_profit*profit_unit as total, item.eve_iditem as item_id
            FROM profit
            JOIN transaction t1 on profit.transaction_idbuy_buy = t1.idbuy
            JOIN transaction t2 on profit.transaction_idbuy_sell = t2.idbuy
            JOIN characters on t2.character_eve_idcharacter = characters.eve_idcharacter
            JOIN item ON t2.item_eve_iditem = item.eve_iditem
            WHERE date(t2.time) >= DATE(NOW() - INTERVAL '$int' HOUR)
            AND t2.character_eve_idcharacter = '$character_get'
            AND date(t2.time) >= (DATE(NOW()) - INTERVAL '$int' HOUR)
             order by timediff(t2.time,t1.time) asc
            limit 3
        ");
      
      echo "<table border ='1' class='table table-striped table-bordered table-hover'>";
      echo "<tr><th></th><th>Item</th><th>Time</th><th>Profit</th></tr>";
      
      if (mysqli_num_rows($getFastestTurnOvers) == 0)
      {
          echo "<tr><td colspan='4'>No results to display</td></tr>";
      }
      
      while ($sales = mysqli_fetch_array($getFastestTurnOvers, MYSQLI_ASSOC))
      {
          $item   = $sales['item_id'];
          $name   = $sales['item'];
          $time   = $sales['difference'];
          $profit = $sales['total'];
          
          echo "<tr><td> <img src='http://evetrademaster.com/Types/" . $item . "_32.png'></td><td>" . $name . "</td><td>" . $time . "</td><td>" . number_format($profit) . "</td></tr>";
      }
      echo "</table>";
  }
  
  //var_dump($tz_profits);
  
  $sales_list     = array();
  $expenses_list  = array();
  $days_list      = array();
  $caption1       = "Sales by day";
  $subcaption1    = "Last 7 days";
  $subsubcaption1 = "ISK";
  
  $caption2       = "Expenses by day";
  $subcaption2    = "Last 7 days";
  $subsubcaption2 = "ISK";
  
  $arrData  = getSalesGraphData($caption1, $subcaption1, $subsubcaption1);
  $arrData2 = getSalesGraphData($caption2, $subcaption2, $subsubcaption2);
  
  $arrData["data"]  = array();
  $arrData2["data"] = array();
  $index            = -1;
  
  $today = new DateTime('now');
  date_sub($today, date_interval_create_from_date_string("8 day"));
  
  
  for ($i = 7; $i >= 0; $i--)
  {
      $index = $index + 1;
      //$day = $day+1;
      //$k = $i+1; //1 day interval
      date_add($today, date_interval_create_from_date_string("1 day"));
      $today_a = $today->format("Y-m-d");
      
      
      $get_7_days_sales = mysqli_query($con, "SELECT date, total_sell FROM history 
                WHERE characters_eve_idcharacters = '$character_get' 
                AND date LIKE '$today_a'") or die(mysqli_error($con));
      
      
      $get_7_days_expenses = mysqli_query($con, "SELECT date, total_buy FROM history 
                WHERE characters_eve_idcharacters = '$character_get' 
                AND date LIKE '$today_a'") or die(mysqli_error($con));
      
      //set sales data for each day in array
      $sales_data_7_days = mysqli_fetch_array($get_7_days_sales, MYSQLI_ASSOC);
      $sales             = $sales_data_7_days['total_sell'];
      
      
      //$dates = $sales_data_7_days['date'];
      
      $getDates = mysqli_fetch_array(mysqli_query($con, "SELECT days FROM calendar WHERE days >= DATE_SUB(CURDATE(), INTERVAL $i day) ORDER BY days ASC LIMIT 7"));
      $dates    = $getDates['days'];
      
      //set expenses data for each day in array
      $expenses_data_7_days = mysqli_fetch_array($get_7_days_expenses, MYSQLI_ASSOC);
      $expenses             = $expenses_data_7_days['total_buy'];
      
      
      
      array_push($days_list, $dates); //revert both arrays to get results in ascending order
      array_push($sales_list, $sales);
      array_push($expenses_list, $expenses);
      
      array_push($arrData['data'], array(
          "label" => (string) $days_list[$index], //merges both timestamp array with sales or expenses data
          "value" => (string) $sales_list[$index]
      ));
      array_push($arrData2['data'], array(
          "label" => (string) $days_list[$index], //merges both timestamp array with sales or expenses data
          "value" => (string) $expenses_list[$index]
      ));
  }
  
  
  $jsonEncodedData  = json_encode($arrData);
  $jsonEncodedData2 = json_encode($arrData2);
  $columnChart      = new FusionCharts("column2d", "sales", 1000, 300, "chart-1", "json", $jsonEncodedData);
  $columnChart2     = new FusionCharts("column2d", "expenses", 1000, 300, "chart-2", "json", $jsonEncodedData2);
  
  
  $columnChart->render();
  $columnChart2->render();
  
  $content->drawNav($accountBalance, $networth, $sellOrders, $escrow, $username, $character_get, $getCharacterList);
?>
    <div id="page-wrapper">
        <?php
  $content->drawHeader($getCharacterPortrait, $characterName, "Statistics");
?>
        <div class="row">
        <?php
  $content->columns_def();
?>
            <head>
                <title>Statistics</title>
                <!-- Bootstrap Core CSS -->
                <link href="../bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
                <!-- MetisMenu CSS -->
                <link href="../bower_components/metisMenu/dist/metisMenu.min.css" rel="stylesheet">
                <!-- Custom CSS -->
                <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
                <!-- Custom Fonts -->
                <link href="../bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

            </head>

            <body>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-bar-chart-o fa-fw"></i> Statistics <?php
  echo $intStr;
?>
        <?php
  $trans_dropdown = new dropdown();
  $trans_dropdown->setTitle("View results for...");
  $trans_dropdown->setCharacter($character_get);
  $trans_dropdown->setTargetURL("statistics.php");
  $trans_dropdown->addOption('Last 24 hours', "int=24");
  $trans_dropdown->addOption("Last 7 days", "int=168");
  $trans_dropdown->addOption("Last 30 days", "int=720");
  $trans_dropdown->renderDropdown();
?>
                                <div class="pull-right">
                                </div>
                            </div>
                            <!-- /.panel-heading -->
                            <div class="panel-body">
                                Here you can check some more in-depth data about your revenue. 
                                This page will be updated frequently with more graphs and data in future.   
                            </div>
                            <!-- /.panel-body -->
                        </div>
                    </div>


                </div>

                <!-- /.row -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-bar-chart-o fa-fw"></i> Trade Volumes
                                <div class="pull-right">

                                </div>
                            </div>
                            <!-- /.panel-heading -->
                            <div class="panel-body">
                                <div id="chart-1"></div><div id="chart-2"></div>
                            </div>
                            <!-- /.panel-body -->
                        </div>
                    </div>


                </div>
                <!-- /.row -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-thumbs-o-up"></i> Best items (by profit)
                                <div class="pull-right">

                                </div>
                            </div>
                            <!-- /.panel-heading -->
                            <div class="panel-body">
                                <table border='1' class="table table-striped table-bordered table-hover" >
<?php
  echo "<tr><th></th><th>Item</th><th>Profit</th></tr>";
  if (mysqli_num_rows($bestItemsRaw) == 0)
  {
      echo "<tr><td colspan='3'>No results to display</td></tr>";
  }
  
  while ($now = mysqli_fetch_array($bestItemsRaw, MYSQLI_ASSOC)) //raw profits table
  {
      $itemRaw  = $now['item'];
      $valueRaw = $now['profit'];
      $itemID   = $now['item_id'];
      echo "<tr><td> <img src='../Types/" . $itemID . "_32.png'</td><td>" . $itemRaw . "</td><td>" . number_format($valueRaw) . " ISK" . "</td></tr>";
  }
  echo "</table>";
?>             
                            </div>
                            <!-- /.panel-body -->
                        </div>
                        <!-- /.panel -->
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-smile-o"></i> Best customers (by profit)
                                <div class="pull-right">
                                </div>
                            </div>
                            <!-- /.panel-heading -->
                            <div class="panel-body">
                                <table border='1' id="dataTables-1" class="table table-striped table-bordered table-hover">
<?php
  echo "<tr><th></th><th>Client</th><th>Profit</th></tr>";
  //$name="Nick Starkey";
  //fetch character ID from Name     
  if (mysqli_num_rows($bestCustomersRawProfit) == 0)
  {
      echo "<tr><td colspan='3'>No results to display</td></tr>";
  }
  while ($tow = mysqli_fetch_array($bestCustomersRawProfit, MYSQLI_ASSOC)) //CUSTOMER raw profits table
  {
      $soldTo = mysqli_real_escape_string($con, $tow['soldTo']);
      $profit = $tow['profit'];
      
      //check if customer already exists in db first. if yes, then use cached data. if not, then query the eve API (slow), then insert the new entry to the DB
      $search_customer_ID = mysqli_query($con, "SELECT eve_idcharacters FROM characters_public WHERE name = '$soldTo'") or die(mysqli_error($con));
      
      if (mysqli_num_rows($search_customer_ID) == 1)
      {
          $customerID = utils::mysqli_result($search_customer_ID, 0, 0);
      }
      else
      {
          $getCustomerID = "https://api.eveonline.com/eve/CharacterID.xml.aspx?names=$soldTo";
          $xml           = simplexml_load_file($getCustomerID);
          
          foreach ($xml->result->rowset->row as $r)
          {
              $customerID = $r['characterID'];
          }
          //add new ID to db
          $insert_new_customer = mysqli_query($con, "INSERT IGNORE INTO `trader`.`characters_public` (`eve_idcharacters`, `name`) VALUES ('$customerID', '$soldTo')");
      }
      
      echo "<tr><td> <img src='https://image.eveonline.com/Character/" . $customerID . "_32.jpg'</td><td>" . $soldTo . "</td><td>" . number_format($profit) . " ISK" . "</td></tr>";
  }
  echo "</table>";
?>
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="table-responsive">

                                            </div>
                                            <!-- /.table-responsive -->
                                        </div>
                                        <!-- /.col-lg-4 (nested) -->
                                        <div class="col-lg-6">

                                        </div>
                                        <!-- /.col-lg-8 (nested) -->
                                    </div>
                                    <!-- /.row -->
                            </div>
                            <!-- /.panel-body -->
                        </div>
                        <!-- /.panel -->
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-thumbs-o-down"></i> Problematic items
                            </div>
                            <!-- /.panel-heading -->
                            <div class="panel-body">
                                    <?php
  getProblematicItems($character_get, $con, $int);
?>
                            </div>
                            <!-- /.panel-body -->
                        </div>
                        <!-- /.panel -->
                    </div>
                    <!-- /.col-lg-8 -->
                    <div class="col-lg-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-money"></i> Best Items (by margin)
                            </div>
                            <!-- /.panel-heading -->
                            <div class="panel-body">
                                <table border='1' class="table table-striped table-bordered table-hover"> 
                                    <?php
  echo "<tr><th></th><th>Item</th><th>Margin</th></tr>";
  if (mysqli_num_rows($bestItemsMargin) == 0)
  {
      echo "<tr><td colspan='3'>No results to display</td></tr>";
  }
  while ($pow = mysqli_fetch_array($bestItemsMargin, MYSQLI_ASSOC)) //margin profits table
  {
      $itemMargin  = $pow['item'];
      $valueMargin = $pow['margin'];
      $itemID      = $pow['item_id'];
      
      echo "<tr><td> <img src='../Types/" . $itemID . "_32.png'</td><td>" . $itemMargin . "</td><td>" . number_format($valueMargin * 100, 2) . " %" . "</td></tr>";
  }
  
  //     echo "<tr><td>" .$item . "</td><td>" . $value . "</td><td></tr>";
  echo "</table>";
?>
                                    <!-- /.list-group -->

                            </div>
                            <!-- /.panel-body -->
                        </div>
                        <!-- /.panel -->
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-clock-o"></i> Fastest turnovers
                            </div>
                            <div class="panel-body">
<?php
  getFastestTurnovers($character_get, $con, $int);
?>
                            </div>
                            <!-- /.panel-body -->
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-rotate-left"></i> Best timezones
                            </div>
                            <div class="panel-body">  

                                <table border='1'  class="table table-striped table-bordered table-hover">
                                    <tr><th>Timezone</th><th>Profit</th></tr>
<?php
  arsort($tz_profits);
  foreach ($tz_profits as $key => $val)
  {
      echo "<tr><td>" . "<img src='../assets/" . $key . ".png'>" . "</td><td>" . number_format($val) . " ISK" . "</td></tr>";
  }
  echo "</table>";
?>
                            </div>
                            <!-- /.panel-body -->
                        </div>
                        <!-- /.panel -->

                        <!-- /.panel .chat-panel -->
                    </div>
                    <!-- /.col-lg-4 -->
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-file-excel-o"></i> Last 7 days recap
                                <div class="pull-right">

                                </div>
                            </div>
                            <!-- /.panel-heading -->
                            <div class="panel-body">
<?php
  getLastWeekProfits($character_get, $con);
?>
                            </div>
                            <!-- /.panel-body -->
                        </div>
                    </div>


                </div>
                <!-- /.row -->
        </div>
<?php
  $content->drawFooter();
?>
        <!-- /#page-wrapper -->

    </div>

</body>

</html>
