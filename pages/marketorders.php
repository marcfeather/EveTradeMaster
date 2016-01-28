<?php
require_once ('scripts/session.php');
require ('bootstrapper.php');
?>
<!DOCTYPE html>

<html lang="en">
    <script src="../bower_components/datatables/media/js/jquery.dataTables.min.js"></script>
    <script src="../bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js"></script>
<head>
<?php
use Pheal\Pheal;

    function tableMarketOrders($con, $getActiveMarketOrders, $tablename)
    {
?>
        <div class="dataTable_wrapper">
          
        <table class="table table-striped table-bordered table-hover" id="<?php echo $tablename ?>">
            <thead>
            <tr>
                <th>Time</th>
                <th>Item</th>
                <th align = 'right'>Q</th>
                <th align = 'right'>ISK/unit</th>
                <th>Station</th>
                <th>State</th>
                                          
            </tr>
            </thead>
            <tbody>
                                        
<?php
        while($orders = mysqli_fetch_array($getActiveMarketOrders))
        {
          $time = $orders['date'];
          $itemID = $orders['eve_item_iditem'];
         
          $itemName = utils::mysqli_result(mysqli_query($con, "SELECT name FROM item WHERE eve_iditem = '$itemID'"),0,0);
          $stationID = $orders['station_eve_idstation'];
          $stationName = utils::mysqli_result(mysqli_query($con, "SELECT name FROM station WHERE eve_idstation = '$stationID'"),0,0);
          $price_unit = $orders['price'];
          $volume = $orders['volume_remaining'];
          $state = $orders['order_state'];
          /*$regionName = utils::mysqli_result(mysqli_query($con, "SELECT name FROM region "
                  . "WHERE eve_idregion = "
                  . "(SELECT region_eve_idregion FROM system WHERE eve_idsystem = "
                  . "(SELECT system_eve_idsystem FROM station WHERE eve_idstation = '$stationID'))"),0,0);*/
          
          echo "<tr><td>" . $time . "</td>".
                   "<td>" . "<img src= '../Types/" .$itemID. "_32.png'>" .$itemName . "</td>".
                   "<td align = 'right'>" . number_format($volume) . "</td>".
                   "<td align = 'right'>" . number_format($price_unit) . "</td>".
                   "<td>" . $stationName . "</td>".
                   "<td>" . $state . "</td></tr>";
                
        }
 ?>      
                    </tbody>
                </table>
            </div>
<?php
    }
    $title = "Market Orders";
    
    if(isset($_GET['typea']))
    {
        $typea= mysqli_real_escape_string($con,$_GET['typea']);
    }
    else
        $typea="sell";
    
    if(isset($_GET['typei']))
    {
        $typei= mysqli_real_escape_string($con,$_GET['typei']);
    }
    else
        $typei="sell";

        $getActiveMarketOrders = mysqli_query($con, "SELECT * FROM orders 
            WHERE characters_eve_idcharacters = '$character_get'
            AND order_state = 'open'
            AND type='$typea'")
                or die(mysqli_error($con));
    
        $getInactiveMarketOrders = mysqli_query($con, "SELECT * FROM orders 
            WHERE characters_eve_idcharacters = '$character_get'
            AND order_state != 'open'
            AND type='$typei'")
                or die(mysqli_error($con));
    
        
    $content = new content();
    $content->drawMeta($title);
?>    

</head>
<body>
<?php
    $content->drawNav($accountBalance, $networth, $sellOrders, $escrow, $username, $character_get, $getCharacterList);
?>
    <div id="page-wrapper">
 <?php
    $content->drawHeader($getCharacterPortrait, $characterName, "Market Orders");
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
    $orders_dropdown = new dropdown();
        $orders_dropdown->setTitle("Order type");
        $orders_dropdown->setCharacter($character_get);
        $orders_dropdown->setTargetURL("marketorders.php");
            $typei_sell = "typea=sell&typei=".$typei;
            $typei_buy = "typea=buy&typei=".$typei;
        $orders_dropdown->addOption('Sell Orders', $typei_sell);
        $orders_dropdown->addOption("Buy Orders", $typei_buy);
       
        $orders_dropdown->renderDropdown();
    
    echo "Active ";                  
    if(isset($_GET['typea']))
        {
           $filter = $_GET['typea'];
        }
        else 
        {
           $filter = 'sell';
        }

    if(isset($filter) && $filter != "")
        {
            echo $filter . " orders";
        };
 ?>
        </div>
    <div class ="panel-body">
            <?php   
    tableMarketOrders($con, $getActiveMarketOrders, "table_active");
?>  
    </div>
     </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
             <div class="pull-right">
                <div class="btn-group">   
                          </div>
                            </div>
                        <div class="panel-heading">
                            <i class="fa fa-times fa-fw"></i>
<?php
    //$content->dropdownMarketOrders_inactive($character_get, $typei);
    $orders_dropdown = new dropdown();
        $orders_dropdown->setTitle("Order type");
        $orders_dropdown->setCharacter($character_get);
        $orders_dropdown->setTargetURL("marketorders.php");
            $typea_sell = "typei=sell&typea=".$typea;
            $typea_buy = "typei=buy&typea=".$typea;
        $orders_dropdown->addOption('Sell Orders', $typea_sell);
        $orders_dropdown->addOption("Buy Orders", $typea_buy);
       
        $orders_dropdown->renderDropdown();

    echo "Inactive ";
                           
    if(isset($_GET['typei']))
        {
            $filter = $_GET['typei'];
        }
        else 
        {
            $filter = 'sell';
        }

    if(isset($filter) && $filter != "")
        {
            echo $filter . " orders";
        };
?>        
        </div>                 <!-- /.panel-heading -->
    <div class ="panel-body">
<?php
    tableMarketOrders($con, $getInactiveMarketOrders, "table_inactive");
?>
    </div>
        </div>
<?php
    $content->drawFooter();
    //require_once('scripts/class/dropdownData.php');
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
     
    <!-- this is for the table ordering and initialization-->
    <script>
    $(document).ready(function() {
        $('#table_active').DataTable({
                responsive: true,
                "order": [[ 0, "desc" ]],
                "lengthMenu": [[50, 100, 200], [50, 100, 200]]
        });
    });
    </script>
    
    <script>
    $(document).ready(function() {
        $('#table_inactive').DataTable({
                responsive: true,
                "order": [[ 0, "desc" ]],
                "lengthMenu": [[50, 100, 200], [50, 100, 200]]
        });
    });
    </script>
</body>
</html>                          
                           