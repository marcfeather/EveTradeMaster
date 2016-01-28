<?php
require ('scripts/session.php');
require ('bootstrapper.php'); //includes link, session, content, utils and pheal autoloaders along with 
                              //header/nav specific information from now on
require ('scripts/fusioncharts/php-wrapper/fusioncharts.php');
?>
    <script src="scripts/fusioncharts/js/fusioncharts.js"></script>
    <!--<script src="scripts/jquery.js"></script>-->
<!DOCTYPE html>
<html lang="en">
    <script src="../bower_components/datatables/media/js/jquery.dataTables_dash.min.js"></script>
    <script src="../bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js"></script>
<?php
use Pheal\Pheal;
    $title = "Dashboard";
    
    function drawDashPanel($character_get, $newContracts, $newTransactions, $newOrders)
    {
?>
                                <div class="col-lg-3 col-md-6">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-comments fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php echo $newContracts?></div>
                                    <div>New Contracts</div>
                                </div>
                            </div>
                        </div>
                        <a href="contracts.php?character=<?php echo $character_get?>&new=<?php echo $newContracts?>">
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="panel panel-green">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-tasks fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php echo $newTransactions?></div>
                                    <div>New Transactions</div>
                                </div>
                            </div>
                        </div>
                        <a href="transactions.php?character=<?php echo $character_get?>&new=<?php echo $newTransactions?>">
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="panel panel-yellow">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-shopping-cart fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php echo $newOrders?></div>
                                    <div>New Orders</div> 
                                </div>
                                
                            </div>
                            
                        </div>
                        <a href="marketorders.php?character=<?php echo $character_get?>">
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                        
                    </div>
                    
                </div>
               
                <div class="col-lg-3 col-md-6">
                    
            </div>
            <!-- /.row -->
<?php
    }
    
    function drawDashTable($character_get, $getTransactions)
    {
        $content2 = new content();
        
?>
            <div class="panel-body">
                 <div class="dataTable_wrapper">
                    <table class="table table-striped table-bordered table-hover" id="dataTables-dashboard">
                       <thead>
                            <tr>
                            <th>Item</th>
                            <th>Time</th>
                            <th>Type</th>
                            <th>Q</th>
                            <th>ISK/u</th>
                            <th>Station</th>
                            </tr>
                        </thead>
                        <tbody>
                                               
                                                <?php
                                                while($pow = mysqli_fetch_array($getTransactions, MYSQLI_ASSOC))
                                                {
                                                    $typeid = $pow['item_id'];
                                                    $name = $pow['item_name'];
                                                    $time = $pow['time'];
                                                    $transaction = $pow['transaction_type'];
                                                        if($transaction == 'Buy')
                                                        {
                                                            $icon_trans = "<img src='../assets/buy.png'>";
                                                            $t = "<font color='white'>1</font>";
                                                        }
                                                        else
                                                        {
                                                            $icon_trans = "<img src='../assets/sell.png'>";
                                                            $t = "<font color='white'>2</font>";
                                                        }
                                                    
                                                    $quantity = $pow['quantity'];
                                                    $price_unit = $pow['price_unit'];
                                                    $imgpath = "../Types/".$typeid."_32.png";
                                                    $stationName = $pow['station_name'];
                                                    
                                                    echo "<tr><td>" . "<img src='$imgpath'>" . $name . "</td>".
                                                   // "<td>"  "</td>" .
                                                    "<td>" . $time . "</td>" .
                                                    "<td>" . $t .$icon_trans . "</td>" . 
                                                    "<td align = 'right'>" . $quantity . "</td>" . 
                                                    "<td align = 'right'>" . number_format($price_unit) . "</td>".
                                                    "<td>" . $stationName . "</td></tr>";
                                                }
                                                ?>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- /.table-responsive -->
                                <!-- /.col-lg-4 (nested) -->
                                <!-- /.col-lg-8 (nested) -->
                            <!-- /.row -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <?php $content2->drawFooter();?>
                        </div>
                        <!-- /.panel-body -->
                    
                    <!-- /.panel -->
                </div>
<?php
    }
    
    
    function drawAssetsGraph()
    {
?>
            <div class="col-lg-4">
                    <!-- /.panel -->
                    <div class="panel panel-default">
                           
                        <div class="panel-heading">
                            <i class="fa fa-dribbble fa-fw"></i> Assets Distribution
                      
                        <div class="panel-body">
                   
                           <div id="chart-2"></div>
                        <!-- /.panel-body -->
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <!-- /.panel-body -->
                    
                        <!-- /.panel-footer -->
                  
                    <!-- /.panel .chat-panel -->
                </div>
          <!-- /.col-lg-4 -->
            </div>
<?php    
    }
    
    
    /*if (isset($_COOKIE['width'])) //reads the cookie set at the login to determine the pie chart size
    {
        $width = $_COOKIE['width'];
    }*/
    if(isset($_GET['int'])) //the time interval selected in the table
    {
        $interval = $_GET['int'];
    }
    else
    {
        $interval = 24;
    }

    $getTransactions = mysqli_query($con, "SELECT * FROM `v_transaction_details` WHERE character_id = '$character_get'
            AND time > DATE_SUB(NOW(), INTERVAL '$interval' HOUR) ORDER BY time DESC")
            or die(mysqli_error($con));
    
    $getNewInfo = mysqli_query($con, "SELECT contracts, transactions, orders FROM new_info 
        WHERE characters_eve_idcharacters = '$character_get'") 
            or die(mysqli_error($con));
    
    $newInfo = mysqli_fetch_array($getNewInfo, MYSQLI_ASSOC); //newInfo used to update the dashboard heading
    $newContracts = $newInfo['contracts'];
    $newTransactions = $newInfo['transactions'];
    $newOrders = $newInfo['orders'];
    
    //set New Info to 0 after the page is done loading:
    $reset_new_info = mysqli_query($con, "UPDATE `trader`.`new_info` SET `contracts` = '0', `transactions` = '0', `orders` = '0' WHERE `new_info`.`characters_eve_idcharacters` = '$character_get'")
            or die(mysqli_error($con));
    
    $getCharacterInfo = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM characters WHERE eve_idcharacter = '$character_get'"),MYSQLI_ASSOC);
          
    $accountBalance = $getCharacterInfo['balance'];
    $networth = $getCharacterInfo['networth'];
    $escrow = $getCharacterInfo['escrow'];
    $sellOrders = $getCharacterInfo['total_sell'];
    
    $assetTypes = array("wallet", "assets", "escrow", "sellorders"); //pie chart specific data
    $assetValues = array($accountBalance,$networth,$escrow,$sellOrders);
    
         $caption = "";
         $subcaption = "Now";
                            
         $arrData["chart"] = array("caption"=> $caption,
            "subCaption"=> $subcaption,
            "paletteColors"=> "#0075c2,#1aaf5d,#f2c500,#f45b00,#8e0000",
            "bgColor"=> "#ffffff",
            "showBorder"=> "0",
            "use3DLighting"=> "0",
            "showShadow"=> "0",
            "enableSmartLabels"=> "0",
            "startingAngle"=> "0",
            "showPercentValues"=> "1",
            "showPercentInTooltip"=> "0",
            "decimals"=> "1",
            "captionFontSize"=> "0",
            "subcaptionFontSize"=> "0",
            "subcaptionFontBold"=> "0",
            "toolTipColor"=> "#000000",
            "toolTipBorderThickness"=> "0",
            "toolTipBgColor"=> "#ffffff",
            "toolTipBgAlpha"=> "80",
            "toolTipBorderRadius"=> "2",
            "toolTipPadding"=> "5",
            "showHoverEffect"=> "1",
            "showLegend"=> "1",
            "legendBgColor"=> "#ffffff",
            "legendBorderAlpha"=> "0",
            "legendShadow"=> "0",
            "legendItemFontSize"=> "12",
            "legendItemFontColor"=> "#666666",
             "labelfontsize" => "0",
            "useDataPlotColorForLabels"=> "1"  );
 
         $arrData["data"] = array();

        for($i=0;$i<count($assetTypes);$i++)
        {
            array_push($arrData["data"], array("label" => (string)$assetTypes[$i],
                "value" => (string)$assetValues[$i]));
        }
           
        $arrData["chart"];
        $jsonEncodedData = json_encode($arrData);

            if($width < 1920) //resize the pie chart
            {
                $pieChart = new FusionCharts("pie3d", "myFirstChart" , "100%", 250, "chart-2", "json", $jsonEncodedData);
            }
            else 
            {
                $pieChart = new FusionCharts("pie3d", "myFirstChart" , "100%", 250, "chart-2", "json", $jsonEncodedData);
            }
                $pieChart->render();
          
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
    $content->drawHeader($getCharacterPortrait, $characterName, "Dashboard");
?>
    <div class="row">
<?php
    drawDashPanel($character_get, $newContracts, $newTransactions, $newOrders);
?>
    <div class="row">
    <div class="col-lg-8">
<?php
    $icon = "fa-bar-chart-o";
    $content->drawPanel($icon);
    echo "Last ";

        if(isset($interval) && $interval == '72') {echo "3 days";}
            else
            if(isset($interval) && $interval == '168') {echo "7 days";}
                else
                if(isset($interval) && $interval == '720') {echo "30 days";}
                    else
                    echo "24 hour";
?> transactions 
<?php
    $dashboard = new dropdown();
        $dashboard->setTitle("Date");
        $dashboard->setCharacter($character_get);
        $dashboard->setTargetURL("dashboard.php");
        $dashboard->addOption('last 3 days', 'int=72');
        $dashboard->addOption("last 7 days", "int=168");
        $dashboard->addOption("last 30 days", "int=720");
        $dashboard->renderDropdown();
?>  </div>
                        <!-- /.panel-heading -->
    
<?php
    drawDashTable($character_get, $getTransactions);
    drawAssetsGraph();
?>

        <!-- /#page-wrapper -->
    </div>
    <!-- /#wrapper -->
  
    <!-- render the table and order the rows -->
    <script> 
    $(document).ready(function() {
        $('#dataTables-dashboard').DataTable({
                responsive: true,
                "order": [[ 1, "desc" ]],
				"lengthMenu": [[50, 100, 200], [50, 100, 200]]
        });
    });
    </script>
</body>
</html>