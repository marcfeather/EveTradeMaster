<?php
require_once('scripts/session.php');
require ('bootstrapper.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script src="../bower_components/datatables/media/js/jquery.dataTables_dash.min.js"></script>
    <script src="../bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js"></script>
<?php
    $dt = new DateTime();
    $tz = new DateTimeZone('Europe/Lisbon');
    $dt->setTimezone($tz);
    $dt->format('Y-m-d H:i:s');
    $today = date("D M d, Y G:i");
    
   
    
    function drawTableTransactions($con, $getTransactions, $today, $timeFilter)
    {
?>
        <div class="dataTable_wrapper">
          
        <table class="table table-striped table-bordered table-hover" id="dataTables-transactions">
            
            <thead>
            <tr>
                <th>Time</th>
                <th>Item</th>
                <th>Q</th>
                <th>ISK/unit</th>
                <th>ISK total</th>
                <th>Type</th>
                <th>other party</th>
                <th>Station</th>
                <th>Region</th>
            </tr>
            </thead>
            <tbody>
                                        
    <?php
        
        while($mow = mysqli_fetch_array($getTransactions))
        {
            //$i++; //$i is item number
            $typeid = $mow['item_id'];
            $dateTime = $mow['time'];
            $quantity = $mow['quantity'];
            $price_unit = $mow['price_unit'];
            $transactionType = $mow['transaction_type'];
                if($transactionType == 'Buy')
                    {
                     $icon_trans = "<img src='../assets/buy.png'>";
                     $t = "<font color='white'>1</font>";
                    }
                    else
                    {
                     $icon_trans = "<img src='../assets/sell.png'>";
                     $t = "<font color='white'>2</font>";
                    }
            $station = $mow['station_name'];
            $price_total = $price_unit*$quantity;
            $station_id = $mow['station_id'];
            $transkey = $mow['transkey'];
            $itemName2 = $mow['item_name'];
            $clientName = $mow['client'];
            $region = $mow['region_name'];
            
            $itemName_str = strtolower($itemName2);
            $systemName_str = strtolower($station);
            $regionName_str = strtolower($region);
            
            $dateTimeObject = strtotime($dateTime);
            $today_str = strtotime($today);
            
            $difference_days = ($today_str - $dateTimeObject)/86400;
           // $dt_str = strtotime($dt);
           //echo  $tnc = ($dateTimeObject - $today_str)/86400;
           //echo ":";
            
                if(isset($nameFilter) && $nameFilter != "" && !(strpos($itemName_str,$nameFilter) !== false) ||
                   isset($systemFilter) && $systemFilter != "" && !(strpos($systemName_str,$systemFilter) !== false) ||
                   isset($regionFilter) && $regionFilter != "" && $regionFilter != "any" && !(strpos($regionName_str, $regionFilter) !== false) ||
                   isset($timeFilter) && $timeFilter == "day" &&   $difference_days > 1 ||
                   isset($timeFilter) && $timeFilter == "week" && $difference_days > 7 ||
                   isset($timeFilter) && $timeFilter == "month" &&  $difference_days > 30
                   ) 
                {
                }
                else
                {           
                 echo "<tr><td>" . $dateTime . "</td>";
                 echo "<td>". "<img src= '../Types/" .$typeid. "_32.png'>"." ". $itemName2."</td>";
  
                 echo "<td align = 'right'>" . number_format($quantity) . "</td>";
                 echo "<td align = 'right'>" . number_format($price_unit) . "</td>";
                 echo "<td align = 'right'>" . number_format($price_total) . "</td>";
                 echo "<td>" . $t. $icon_trans . "</td>";
                 echo "<td>" . $clientName . "</td>";
                 echo "<td>$station</td>";
                 echo "<td>$region</td> </tr>";
                }
        }
                                        ?>      
                                    </tbody>
                                </table>
                            </div>
<?php

    }

    //import namespace
    use Pheal\Pheal;
    
    //get filter variables
            if(isset($_GET['item_filter'])) {$nameFilter = strtolower($_GET['item_filter']);}
            if(isset($_GET['system_filter'])) {$systemFilter = strtolower($_GET['system_filter']);}
            if(isset($_GET['last_filter'])) {$timeFilter = strtolower($_GET['last_filter']);}
            if(isset($_GET['region_filter'])) {$regionFilter = strtolower($_GET['region_filter']);}
                 //a value of 0 means no dropdown
    //echo "Transaction List";
    $getRegionList = mysqli_query($con, "SELECT eve_idregion, name FROM region WHERE isKS = '1' ORDER BY name ASC") 
            or die(mysqli_error($con));
 
        //$i=-1;
        //$null = (string)"NULL";
        $getTransactions = mysqli_query($con, "SELECT * FROM v_transaction_details 
                WHERE character_id = '$character_get' 
                ORDER BY time DESC")
                or die(mysqli_error($con));
        
         if(isset($_GET['new'])) //new transactions
         {
         $new = $_GET['new'];
    
         $getNewTransactions = mysqli_query($con, "SELECT * FROM v_transaction_details 
                WHERE character_id = '$character_get' 
                ORDER BY time DESC LIMIT $new")
                or die(mysqli_error($con));
         }

    $content = new content();
    $title = "Transactions";
    $content->drawMeta($title);
?>
</head>
<body>
<?php
     $content->drawNav($accountBalance, $networth, $sellOrders, $escrow, $username, $character_get, $getCharacterList);
?>
    <div id="page-wrapper">
 <?php
    $content->drawHeader($getCharacterPortrait, $characterName, "Market Transactions");
?>
    <div class="row">
<?php
    $content->columns_def();
?>    <!-- /.row -->
   <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="pull-right">
                                <div class="btn-group">
                                </div>
                </div>
    <div class="panel-heading">
    <i class="fa fa-check fa-fw"></i>
<?php
    $trans_dropdown = new dropdown();
        $trans_dropdown->setTitle("Date");
        $trans_dropdown->setCharacter($character_get);
        $trans_dropdown->setTargetURL("transactions.php");
        $trans_dropdown->addOption('Last 24 hours', "int=day");
        $trans_dropdown->addOption("Last 7 days", "int=week");
        $trans_dropdown->addOption("Last 30 days", "int=month");
       
        $trans_dropdown->renderDropdown();
        
    echo "Transactions ";
    
    if(isset($_GET['new']))
    {
       echo " - " .$new . " new transactions since your last visit. Use the dropdown at the left to view all data.";
       $timeFilter = 'day';
    }
    else
    {
        
    if(isset($_GET['int']))
        {
        $timeFilter = $_GET['int'];
        }
        else 
            {
            $timeFilter = 'day';
            }

    if(isset($timeFilter) && $timeFilter != "")
        {
        echo " during last " . $timeFilter;
        };
    }
?>
    </div>
   <!-- /.panel-heading -->
    <div class="panel-body">
      <div class="dataTable_wrapper">
<?php
    if(isset($_GET['new']))
    {
        $getTransactions = $getNewTransactions;
    }
        drawTableTransactions($con, $getTransactions, $today, $timeFilter);
    
?>
    </div>
       <!-- /.table-responsive -->   
      </div></div>
<?php
    $content->drawFooter();
?>
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
    
    <script>
    $(document).ready(function() {
        $('#dataTables-transactions').DataTable({
                responsive: true,
                "order": [[ 0, "desc" ]],
                "lengthMenu": [[50, 100, 200], [50, 100, 200]]
        });
    });
    </script>
    
</body>

</html>