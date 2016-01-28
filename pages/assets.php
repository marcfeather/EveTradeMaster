<?php
require_once ('scripts/session.php');
require_once('bootstrapper.php');
?>
    <!-- DataTables JavaScript -->
    <script src="../bower_components/datatables/media/js/jquery.dataTables_assets.min.js"></script>
    <script src="../bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js"></script>

<?php
if (isset($_GET['filter']))
    {
        $filter = $_GET['filter'];
    }
    
    else
    {
         $filter = 'yes';
    }
    
    function assets_heading($filter, $character_get, $percentage_significant)
    {
?>
    <div class="panel-heading">
        <i class="fa fa-suitcase fa-fw"></i> 
         Assets (this includes items stored in containers, ships or control towers)
         <br>   <i class="fa fa-info fa-fw"></i>Note: non-marketable items (such as BPC's) and items typically not sold on the market (supercarriers, etc) won't have a price estimate.
<?php
           if($filter == "yes"){
?>
             <br>   <i class="fa fa-info fa-fw"></i>Note: currently only showing most significant items which represent <b><?php echo $percentage_significant; ?>% </b> 
              of total networth. Click <a href="assets.php?character=<?php echo $character_get?>&filter=no">here to see the full item list</a> (page may be slower to load). <?php } ?>
    </div>
<?php   
    }
    
    function drawAssetsTable($con, $filter, $getAssetsSignificant, $getAssets)
    {
?>
       <div class="dataTable_wrapper">
       <table class="table table-striped table-bordered table-hover" id="dataTables-assets">
            <thead>
                <tr>
                <th>Item</th>
                <th>Q</th>
                <th>Location</th>
                <th>Est. Value (unit)</th>
                <th>Est. Value (stack)</th>
                </tr>
            </thead>
            <tbody>
                                        
    <?php
        if ($filter == "yes")
        {
            $getAssetsReal = $getAssetsSignificant;
        }
        else
        {
            $getAssetsReal = $getAssets;
        }
    
        while($assetList = mysqli_fetch_array($getAssetsReal, MYSQLI_ASSOC))
        {
           $itemID = $assetList['item_eve_iditem'];
           $itemName = utils::mysqli_result(mysqli_query($con, "SELECT name FROM item WHERE eve_iditem = '$itemID'"),0,0);
           $imgpath = "../Types/".$itemID."_32.png";
           $quantity = $assetList['quantity'];
           $locationID = $assetList['locationID'];
           $value = utils::mysqli_result(mysqli_query($con, "SELECT price_evecentral FROM item_price_data WHERE item_eve_iditem = '$itemID'"),0,0);
           $value_stack = $assetList['price_stack'];
          
           if(utils::startsWith($locationID, "6"))
           {
               //item is in a station
               $getStationName = mysqli_query($con, "SELECT name FROM station WHERE eve_idstation = '$locationID'")
                        or die(mysqli_error($con));
                
                if (mysqli_num_rows($getStationName) < 1) //item is in a player owned station if the location ID is not a known NPC station
                { 
                    $locationName = utils::mysqli_result(mysqli_query($con, "SELECT name FROM outposts WHERE eve_idoutposts = '$locationID'"),0,0); 
                }
                else
                {
                   $locationName = utils::mysqli_result($getStationName,0,0); 
                }
           }
           else if (utils::startsWith($locationID,"3")) //item is in a solar system outside a station (such as in space or inside a POS)
           {
               $locationName = utils::mysqli_result(mysqli_query($con, "SELECT name FROM system WHERE eve_idsystem = '$locationID'"),0,0);
           }
           
           echo "<tr>"
           . "<td><img src='$imgpath'>" . " " .$itemName . "</td>"
           . "<td align = 'right'>" . $quantity . "</td>"
           . "<td>" . $locationName . "</td>"
           . "<td align = 'right'>" . number_format($value) ."</td>"
           . "<td align = 'right'>" . number_format($value_stack) ."</td>"        
           . "</tr>";
        }
 ?>      
                                    </tbody>
                                </table>
                            </div>
<?php
    }

    $assets = "SELECT assets.idassets, assets.item_eve_iditem, assets.quantity, assets.locationID, (assets.quantity* item_price_data.price_evecentral) AS price_stack "
            . "FROM assets "
            . "LEFT JOIN item_price_data "
            . "ON assets.item_eve_iditem = item_price_data.item_eve_iditem "
            . "WHERE characters_eve_idcharacters='$character_get'";
    $getAssets = mysqli_query($con, $assets)
            or die(mysqli_error($con));
    
    $assetsSignificant = "SELECT assets.idassets, assets.locationID, assets.item_eve_iditem, assets.quantity, assets.quantity* item_price_data.price_evecentral AS price_stack "
            . "FROM assets, item_price_data "
            . "WHERE assets.quantity*item_price_data.price_evecentral > 0.2* "
            .   "(select avg(assets.quantity*item_price_data.price_evecentral) "
            .   "from assets, item_price_data "
            .   "WHERE assets.item_eve_iditem = item_price_data.item_eve_iditem "
            .   "and assets.characters_eve_idcharacters = '$character_get') "
            . "and assets.item_eve_iditem = item_price_data.item_eve_iditem "
            . "and assets.characters_eve_idcharacters = '$character_get'"
            . " ORDER BY `price_stack` ASC"; //
    $getAssetsSignificant = mysqli_query($con, $assetsSignificant) or die(mysqli_error($con));
    
     $worthSignificant = utils::mysqli_result(mysqli_query($con, "SELECT sum(assets.quantity* item_price_data.price_evecentral) "
            . "FROM assets, item_price_data "
            . "WHERE assets.quantity*item_price_data.price_evecentral > "
            .   "0.2* (select avg(assets.quantity*item_price_data.price_evecentral) "
            .   "from assets, item_price_data "
            .   "WHERE assets.item_eve_iditem = item_price_data.item_eve_iditem "
            .   "and assets.characters_eve_idcharacters = '$character_get') "
            . "and assets.item_eve_iditem = item_price_data.item_eve_iditem "
            . "and assets.characters_eve_idcharacters = '$character_get'"),0,0);
            
     $networth = utils::mysqli_result(mysqli_query($con, "SELECT networth FROM characters WHERE eve_idcharacter = '$character_get' "),0,0);
    
     $percentage_significant = number_format((($worthSignificant/$networth)*100),2);
          
?>
<!DOCTYPE html>
<html lang="en">
<head> 
<?php
    $title = "Assets";
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
    $content->drawHeader($getCharacterPortrait, $characterName, "Assets");
?>
    <div class="row">
<?php
    $content->columns_def();
?>    <!-- /.row -->
 <div class="row">
 <div class="col-lg-12">  
     <div class="panel panel-default">
<?php 
    assets_heading($filter, $character_get, $percentage_significant);    
?>
    <div class="panel-body">
<?php
   drawAssetsTable($con, $filter, $getAssetsSignificant, $getAssets);      
?>
</div>
                        <!-- /.panel-body -->
    </div>
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
     <!-- this is for the table ordering and initialization-->
    <script>
    $(document).ready(function() {
        $('#dataTables-assets').DataTable({
                responsive: true,
                "order": [[ 0, "asc" ]],
                "lengthMenu": [[50, 100, 200], [50, 100, 200]]
        });
    });
    </script>
    
</body>

</html>