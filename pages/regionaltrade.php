<?php
require_once ('scripts/session.php');
require_once('bootstrapper.php');
require 'scripts/class/tax.php';
?>
<!DOCTYPE html>
<html lang="en">

    <!-- DataTables JavaScript -->
    <script src="../bower_components/datatables/media/js/jquery.dataTables.min.js"></script>
    <script src="../bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js"></script>
    
<head>
<?php
        use Pheal\Pheal;

    function getEveCentralData($typeID, $systemID, $trans)
    {
    $url2="http://api.eve-central.com/api/marketstat?&usesystem=$systemID&typeid=".$typeID;
                $ch = curl_init($url2);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                $response2 = curl_exec($ch);
                
                if($response2 === false)
                {
                echo 'Curl error: ' . curl_error($ch);
                }
                else
                {
                $xml=new SimpleXMLElement($response2);
                $item=$xml->xpath('/evec_api/marketstat/type[@id='.$typeID.']');
                
                if($trans == 'sell'){$price= (float) $item[0]->sell->min;}
                if($trans == 'buy'){$price= (float) $item[0]->buy->max;}
                
                $price=round($price,2);
                return  $price;
                } 
  }
    
  function regionalTrader($con,$character_get)
  {
    $dt = new DateTime();
    $tz = new DateTimeZone('Europe/Lisbon');
    $dt->setTimezone($tz);
    $datetime = $dt->format('Y-m-d H:i:s');
    
   if(isset($_POST['Submit3']))
   {
   //User added another item to the table
     $newItemName = mysqli_real_escape_string($con,$_POST['tag4']);
     $itemID = utils::mysqli_result(mysqli_query($con, "SELECT eve_iditem FROM item WHERE name= '$newItemName'"),0,0);
        
     $transFrom = mysqli_real_escape_string($con,$_GET['tr1']);
     $transTo = mysqli_real_escape_string($con,$_GET['tr2']);
        
     $stationFromID = mysqli_real_escape_string($con,$_GET['sys1']);
     $stationToID =  mysqli_real_escape_string($con,$_GET['sys2']);
        
            $systemFromName = utils::mysqli_result(mysqli_query($con, "SELECT name FROM system WHERE eve_idsystem = "
                        . "(SELECT system_eve_idsystem FROM station WHERE eve_idstation = '$stationFromID')"),0,0) or mysqli_error($con);
            $systemToName = utils::mysqli_result(mysqli_query($con, "SELECT name FROM system WHERE eve_idsystem = "
                        . "(SELECT system_eve_idsystem FROM station WHERE eve_idstation = '$stationToID')"),0,0) or mysqli_error($con);
     
            $systemFromID = utils::mysqli_result(mysqli_query($con, "SELECT system_eve_idsystem FROM station WHERE eve_idstation = '$stationFromID'"),0,0) or mysqli_error($con);  
            $systemToID = utils::mysqli_result(mysqli_query($con, "SELECT system_eve_idsystem FROM station WHERE eve_idstation = '$stationToID'"),0,0) or mysqli_error($con); 
            
            $stationFromName = utils::mysqli_result(mysqli_query($con, "SELECT name FROM station WHERE eve_idstation = '$stationFromID'"),0,0) 
                    or die(mysqli_error($con));
            $stationToName = utils::mysqli_result(mysqli_query($con, "SELECT name FROM station WHERE eve_idstation = '$stationToID'"),0,0) 
                    or die(mysqli_error($con));
            
             if($stationFromID == "" | $stationToID == "")
                {
                    echo "Invalid solar system provided.";
                    echo "<a href='regionaltrade.php?character=$character_get'>Regional Trader Assistant </a>";
                    die();
                }
        
                if($itemID == "")
                {
                    echo "Invalid item provided";
                    //send hidden GET with solarsys+trans
                 
                   echo " <form name='regional_3' action = 'regionaltrade.php?character=$character_get&sys1=$systemFromID&sys2=$systemToID&tr1=$transFrom&tr2=$transTo' method='POST'>";
                   //form that sends the user back to submit=3 so we don't lose previously submitted items    
                    die();
                }
                //calculate tax - all tax variables come from here
               $taxcalc = new tax($stationFromID, $stationToID, $con, $character_get, $transFrom, $transTo);
                                
                                ($transTaxFrom = $taxcalc->calculateTaxFrom());
                                ($transTaxTo = $taxcalc->calculateTaxTo());
                                ($brokerFeeFrom = $taxcalc->calculateBrokerFrom());
                                ($brokerFeeTo = $taxcalc->calculateBrokerTo());
                
                
               //taxcalc($stationFromID,$stationFromID,$character_get,$con,$transFrom,$transTo);
            //draw table            
     echo "<h4>" . $stationFromName . "(". $transFrom .")" . " <i class='fa fa-arrow-circle-o-right fa-fw'></i> " .$stationToName . "(". $transTo .")" ."</h4><br>";
     
     //Tax info
        echo "<i class='fa fa-chevron-circle-right fa-fw'></i>" ."<b>Broker(origin):</b>" . max(0,number_format(($brokerFeeFrom-1)*100,2)) . "%" . "  ". "<i class='fa fa-chevron-circle-right fa-fw'></i>" .
             "<b>Tax (origin):</b>" . number_format(($transTaxFrom-1)*100,2) . "%" . "  ". "<i class='fa fa-chevron-circle-right fa-fw'></i>" .
             "<b>Broker(dest.):</b>" . max(0,number_format(($brokerFeeTo-1)*100,2)) . "%" . "  ". "<i class='fa fa-chevron-circle-right fa-fw'></i>" .
             "<b>Tax (dest.):</b>" . abs(number_format(($transTaxTo-1)*100,2)) . "%". "  ". "<br><br>";   
     
        echo "<table class='table table-striped table-bordered table-hover' id='dataTables-example'>";
        echo "<thead><tr>"
             . "<th>" . "Item" . "</th>"
             . "<th>" . $systemFromName . " " . $transFrom . " price" . "</th>"
             . "<th align = 'right'>" . "Broker fee" . "</th>"

             . "<th>" . $systemToName . " " . $transTo. " price" . "</th>"
             . "<th align = 'right'>" . "Broker fee" . "</th>"
             . "<th align = 'right'>" . "Transaction Tax" . "</th>"             
             . "<th align = 'right'>" . "Raw profit" . "</th>"
             . "<th align = 'right'>" . "Margin (%)" . "</th></tr></thead>";
      
    //To avoid re-calculating all previous entries, we create a temp cache that stores all previous values submitted in this session. 
    $get_cache_results = mysqli_query($con, "SELECT DISTINCT * FROM cache_tool WHERE character_eve_idcharacter = '$character_get' ORDER BY timestamp ASC")
            or die(mysqli_error($con));
            //check if item is already submitted
             
        $priceFrom = (getEveCentralData($itemID, $systemFromID, $transFrom));// * $brokerFeeFrom * $transTaxFrom; 
        $priceTo = (getEveCentralData($itemID, $systemToID, $transTo));// * $brokerFeeTo * $transTaxTo;
        $profitRaw = $priceTo*$brokerFeeTo*$transTaxTo - $priceFrom*$brokerFeeFrom;
        $brokerFeeFromVal = max(100,($priceFrom*($brokerFeeFrom-1)));
        $brokerFeeToVal = max(100,($priceTo*(1-$brokerFeeTo)));
        $transTaxToVal = $priceFrom*(1-$transTaxTo);
             
       if($priceFrom >0 && $priceTo >0) 
           {
           $profitMargin = ($profitRaw/$priceFrom)*100;
           }
        else 
           {
           $profitMargin = "Error";    
           }
    
    //add results to table from cache, then add the last submitted item at the end
    while ($row2 = mysqli_fetch_array($get_cache_results))
    {
        $cachedItemID = $row2['item_eveiditem'];
        $cachedPriceFrom = $row2['priceFrom']; //add exceptions for when buy or sell = 0
        $cachedPriceTo = $row2['priceTo'];
        $cachedProfit = $row2['profit'];
        $cachedMargin = $row2['margin'];
        $cachedItemName = $row2['item_name'];
        $cachedBrokerFrom = $row2['brokerFrom'];
        $cachedBrokerTo = $row2['brokerTo'];
        $cachedTransTo = $row2['transTo'];
		
		$cachedProfit > 0 ? $color = "class='success'" : $color = "class='danger'";

         echo "<tr $color>" //cached items
                     ."<td>" ."<img src='../Types/" .$cachedItemID. "_32.png'>". " ". $cachedItemName . "</td>"
                     ."<td align = 'right'>" . number_format((double)$cachedPriceFrom) . "</td>"
                     ."<td align = 'right'>" . number_format((double)$cachedBrokerFrom) . "</td>"
                     ."<td align = 'right'>" . number_format((double)$cachedPriceTo) . "</td>"
                     ."<td align = 'right'>" . number_format((double)$cachedBrokerTo) . "</td>"
                     ."<td align = 'right'>" . number_format((double)$cachedTransTo) . "</td>"
                     ."<td align = 'right'>" . number_format((double)$cachedProfit) . "</td>"
                     ."<td align = 'right'>" . utils::formating_profit(round($cachedMargin,2)) . "</td></tr>";    
       // print_r($row2); var_dump($row2);
    }
        $check_duplicate_item = mysqli_query($con, "SELECT * FROM cache_tool WHERE item_eveiditem = '$itemID' AND character_eve_idcharacter = '$character_get'")
            or die(mysqli_error($con));
            
            if(mysqli_num_rows($check_duplicate_item) >0)
            {
                echo "<i class='fa fa-exclamation fa-fw'></i><b>Item already exists in this list</b><br>";
                ?>
            </tbody></table>
            <link rel="stylesheet" type="text/css" href="scripts/jquery.autocomplete.css" />
            <script type="text/javascript" src="scripts/jquery.js"></script>
            <script type="text/javascript" src="scripts/jquery.autocomplete.js"></script>
            <script>var jQueryAutocomplete = $.noConflict(true);</script>
            <script>
            $(document).ready(function(){
            jQueryAutocomplete("#tag4").autocomplete("scripts/autocomplete_i.php", {
            selectFirst: true
        });
        });
        </script>
        </head>
        <body>
        <?php
       echo " <form name='regional_3' action = 'regionaltrade.php?character=$character_get&sys1=$stationFromID&sys2=$stationToID&tr1=$transFrom&tr2=$transTo' method='POST'>";
        //send  both post and get at same time
        ?>
        <label>Add Item: </label>
        <input name="tag4" type="text" id="tag4" size="50"/>
        <input type="Submit" value="Submit" name="Submit3" class="btn btn-success"" />    
        </form>      
                <?php  
            }
            else 
            {
         $profitRaw > 0 ? $color = "class='success'" : $color = "class='danger'";       
         echo "<tr $color>"
                     ."<td>" ."<img src='../Types/" .$itemID. "_32.png'>". " ". $newItemName . "</td>"
                     ."<td align = 'right'>" . number_format($priceFrom) . "</td>"
                     ."<td align = 'right'>" . number_format($brokerFeeFromVal) . "</td>"
                     //."<td>" . number_format($priceFrom*($transTaxFrom-1)) . "</td>"
                     ."<td align = 'right'>" . number_format($priceTo) . "</td>"
                     ."<td align = 'right'>" . number_format($brokerFeeToVal) . "</td>"
                     ."<td align = 'right'>" . number_format($transTaxToVal) . "</td>"
                     ."<td align = 'right'>" . number_format($profitRaw) . "</td>"
                     ."<td align = 'right'>" . utils::formating_profit(round($profitMargin,2))  . "</td></tr>"; 
    
     mysqli_query($con, "INSERT INTO `trader`.`cache_tool` (`idcache_tool`, `priceFrom`, `priceTo`, `profit`, `margin`, `timestamp`, `item_eveiditem`, `character_eve_idcharacter`, `item_name`, `brokerFrom`, `brokerTo`, `transTo`) 
                         VALUES (NULL, '$priceFrom', '$priceTo', '$profitRaw', '$profitMargin', '$datetime', '$itemID', '$character_get', '$newItemName', '$brokerFeeFromVal', '$brokerFeeToVal', '$transTaxToVal')") or die(mysqli_error($con));
        
    echo "</table>";
    ?>
            <link rel="stylesheet" type="text/css" href="scripts/jquery.autocomplete.css" />
            <script type="text/javascript" src="scripts/jquery.js"></script>
            <script type="text/javascript" src="scripts/jquery.autocomplete.js"></script>
            <script type="text/javascript">var jQueryAutocomplete = $.noConflict(true);</script>
            
            <script>    
            $(document).ready(function(){
            jQueryAutocomplete("#tag4").autocomplete("scripts/autocomplete_i.php", {
            selectFirst: true
        });
        });
        </script>
        </head>
        <body>
        <?php
       echo " <form name='regional_3' action = 'regionaltrade.php?character=$character_get&sys1=$stationFromID&sys2=$stationToID&tr1=$transFrom&tr2=$transTo' method='POST'>";
        //send  both post and get at same time
        ?>
        <label>Add Item: </label>
        <input name="tag4" type="text" id="tag4" size="50"/>
        <input type="Submit" value="Submit" name="Submit3" class="btn btn-success" />    
        </form>
        
<?php
   }
   }
   else
   {
    if(isset($_POST['Submit2']))
    {
        //User submits an item to the list
        //Update data list
        $itemName = mysqli_real_escape_string($con, $_POST['tag3']);
        //echo $itemName;
        $itemID = utils::mysqli_result(mysqli_query($con, "SELECT eve_iditem FROM item WHERE name= '$itemName'"),0,0);
        
        $stationFromID = mysqli_real_escape_string($con,$_GET['sys1']);
        $stationToID = mysqli_real_escape_string($con,$_GET['sys2']);
        
                if($stationFromID == "" | $stationToID == "")
                {
                    echo "Invalid solar system provided.";
                    echo "<a href='regionaltrade.php?character=$character_get'>Regional Trader Assistant </a>";
                    die();
                }
        
                $systemFromName = utils::mysqli_result(mysqli_query($con, "SELECT name FROM system WHERE eve_idsystem = (SELECT system_eve_idsystem FROM station WHERE eve_idstation = '$stationFromID')"),0,0) or die(mysqli_error($con));
                $systemToName = utils::mysqli_result(mysqli_query($con, "SELECT name FROM system WHERE eve_idsystem = (SELECT system_eve_idsystem FROM station WHERE eve_idstation = '$stationToID')"),0,0) or die(mysqli_error($con));

                $systemFromID = utils::mysqli_result(mysqli_query($con, "SELECT system_eve_idsystem FROM station WHERE eve_idstation = '$stationFromID'"),0,0) or mysqli_error($con);  
                $systemToID = utils::mysqli_result(mysqli_query($con, "SELECT system_eve_idsystem FROM station WHERE eve_idstation = '$stationToID'"),0,0) or mysqli_error($con); 
                
                $stationFromName = utils::mysqli_result(mysqli_query($con, "SELECT name FROM station WHERE eve_idstation = '$stationFromID'"),0,0) 
                    or die(mysqli_error($con));
                $stationToName = utils::mysqli_result(mysqli_query($con, "SELECT name FROM station WHERE eve_idstation = '$stationToID'"),0,0) 
                    or die(mysqli_error($con));
                
        $transFrom = mysqli_real_escape_string($con,$_GET['tr1']);
        $transTo = mysqli_real_escape_string($con,$_GET['tr2']);
      
                if($itemID == "")
                {
                    echo "Invalid item provided";
                    echo "<a href='regionaltrade.php?character=$character_get'>Regional Trader Assistant </a>";
                    die();
                }
        
               //calculate tax
                $taxcalc = new tax($stationFromID, $stationToID, $con, $character_get, $transFrom, $transTo);
                                
                                ($transTaxFrom = $taxcalc->calculateTaxFrom());
                                ($transTaxTo = $taxcalc->calculateTaxTo());
                                ($brokerFeeFrom = $taxcalc->calculateBrokerFrom());
                                ($brokerFeeTo = $taxcalc->calculateBrokerTo());
                        /*var_dump($toCorpStandingValue);
                        var_dump($fromCorpStandingValue);
                        
                        var_dump($brokerFeeFrom);
                        var_dump($brokerFeeTo);
                        var_dump($transTaxFrom);
                        var_dump($transTaxTo);*/
   
        //echo  $key=array_search('1000004',$corpArray);
                
        //Get EVE Central Data
        //getEveCentralData($itemID, $systemFromID, $transTo);
        
        $items_list = array();
        array_push($items_list,$itemID);
        //echo "it is"; print_r($items_list);
        
        echo "<h4>" . "<b>". $stationFromName ."</b>". "(". $transFrom .")" . " <i class='fa fa-arrow-circle-o-right fa-fw'></i> " . "<b>". $stationToName ."</b>" . "(". $transTo .")" ."</h4><br>";
       
         echo "<i class='fa fa-chevron-circle-right fa-fw'></i>" ."<b>Broker(origin):</b>" . max(0,number_format(($brokerFeeFrom-1)*100,2)) . "%" . "<i class='fa fa-chevron-circle-right fa-fw'></i>" .
             "<b>Tax (origin):</b>" . number_format(($transTaxFrom-1)*100,2) . "%" . "<i class='fa fa-chevron-circle-right fa-fw'></i>" .
             "<b>Broker(dest.):</b>" . abs(number_format(($brokerFeeTo-1)*100,2)) . "%" . "<i class='fa fa-chevron-circle-right fa-fw'></i>" .
             "<b>Tax (dest.):</b>" . abs(number_format(($transTaxTo-1)*100,2)) . "%". "<br><br>";   
         
        echo "<table class='table table-striped table-bordered table-hover' id='dataTables-example'>";
        echo "<thead><tr>"
             . "<th>" . "Item" . "</th>"
             . "<th>" . $systemFromName . " " . $transFrom . " price" . "</th>"
             . "<th>" . "Broker fee" . "</th>"

             . "<th>" . $systemToName . " " . $transTo. " price" . "</th>"
             . "<th align = 'right'>" . "Broker fee" . "</th>"
             . "<th align = 'right'>" . "Transaction tax" . "</th>"             
             . "<th align = 'right'>" . "Raw profit" . "</th>"
             . "<th align = 'right'>" . "Margin (%)" . "</th></tr></thead>";
        
             foreach($items_list as $row)
             {
                 //prices include tax modifiers
                 $priceFrom = (getEveCentralData($itemID, $systemFromID, $transFrom));
                 $priceTo = (getEveCentralData($itemID, $systemToID, $transTo));
                 $profitRaw = $priceTo*$brokerFeeTo*$transTaxTo - $priceFrom*$brokerFeeFrom;
                 $brokerFeeFromVal = max(100,($priceFrom*($brokerFeeFrom-1)));
                 $brokerFeeToVal = $priceTo*(1-$brokerFeeTo);
                 $transTaxToVal = $priceFrom*(1-$transTaxTo);
                 $profitRaw > 0 ? $color = "class='success'" : $color = "class='danger'";
				 
                   if($priceFrom >0 && $priceTo >0) 
                    {
                        $profitMargin = ($profitRaw/$priceTo)*100;
                    }
                    else 
                    {
                        $profitMargin = "Error";    
                    }
                 
                 mysqli_query($con, "INSERT INTO `trader`.`cache_tool` (`idcache_tool`, `priceFrom`, `priceTo`, `profit`, `margin`, `timestamp`, `item_eveiditem`, `character_eve_idcharacter`, `item_name`, `brokerFrom`, `brokerTo`, `transTo`) 
                         VALUES (NULL, '$priceFrom', '$priceTo', '$profitRaw', '$profitMargin', '$datetime', '$itemID', '$character_get', '$itemName', '$brokerFeeFromVal', '$brokerFeeToVal', '$transTaxToVal')") or die(mysqli_error($con));
                         
                 echo "<tr $color>"
                     ."<td>" ."<img src='../Types/" .$itemID. "_32.png'>". " ".  $itemName . "</td>"
                     ."<td align = 'right'>" . number_format($priceFrom) . "</td>"
                     ."<td align = 'right'>" . number_format($brokerFeeFromVal) . "</td>"
                     //."<td>" . number_format($priceFrom*($transTaxFrom-1)) . "</td>"
                     ."<td align = 'right'>" . number_format($priceTo) . "</td>"
                     ."<td align = 'right'>" . number_format($brokerFeeToVal) . "</td>"
                     ."<td align = 'right'>" . number_format($transTaxToVal) . "</td>"
                     ."<td align = 'right'>" . number_format($profitRaw) . "</td>"
                     ."<td align = 'right'>" . utils::formating_profit(round($profitMargin,2)) . "</td></tr>"; 
             }
        echo "</table><br>";
        
        //Add item form
        ?>

            <link rel="stylesheet" type="text/css" href="scripts/jquery.autocomplete.css" />
            <script type="text/javascript" src="scripts/jquery.js"></script>
            <script type="text/javascript" src="scripts/jquery.autocomplete.js"></script>
            <script type="text/javascript">
            var jQueryAutocomplete = $.noConflict(true);</script>
            <script>
            $(document).ready(function(){
            jQueryAutocomplete("#tag4").autocomplete("scripts/autocomplete_i.php", {
            selectFirst: true
        });
        });
        </script>
        </head>
        <body>
        <?php
       echo " <form name='regional_3' action = 'regionaltrade.php?character=$character_get&sys1=$stationFromID&sys2=$stationToID&tr1=$transFrom&tr2=$transTo' method='POST'>";
        //send  both post and get at same time
        ?>
        <label>Add Item: </label>
        <input name="tag4" type="text" id="tag4" size="50"/>
        <input type="Submit" value="Submit" name="Submit3" class="btn btn-success" />    
        </form>

        <?php
    }
    else
    {
        if(isset($_POST['Submit']))
        { 
            //getEveCentralData(34, 'buy');
            
            if(isset($_POST['tag'])) {$stationFromName = mysqli_real_escape_string($con,$_POST['tag']);}
            if(isset($_POST['tag2'])) {$stationToName = mysqli_real_escape_string($con,$_POST['tag2']);}
            
                $systemFromName = utils::mysqli_result(mysqli_query($con, "SELECT name FROM system WHERE eve_idsystem = "
                        . "(SELECT system_eve_idsystem FROM station WHERE name = '$stationFromName')"),0,0) or mysqli_error($con);
                $systemToName = utils::mysqli_result(mysqli_query($con, "SELECT name FROM system WHERE eve_idsystem = "
                        . "(SELECT system_eve_idsystem FROM station WHERE name = '$stationToName')"),0,0) or mysqli_error($con);
        
                $stationFromID = utils::mysqli_result(mysqli_query($con, "SELECT eve_idstation FROM station WHERE name = '$stationFromName'"),0,0)
                        or die(mysqli_error($con));
                $stationToID = utils::mysqli_result(mysqli_query($con, "SELECT eve_idstation FROM station WHERE name = '$stationToName'"),0,0)
                        or die(mysqli_error($con));
                
       
            if(isset($_POST['transtype_1'])) {$transtype1 = $_POST['transtype_1'];}
            if(isset($_POST['transtype_2'])) {$transtype2 = $_POST['transtype_2'];}
            
            $systemFromID=utils::mysqli_result(mysqli_query($con, "SELECT eve_idsystem FROM system WHERE name = '$systemFromName'"),0,0);
            $systemToID=utils::mysqli_result(mysqli_query($con, "SELECT eve_idsystem FROM system WHERE name = '$systemToName'"),0,0);

            //var_dump($systemFromID);
            //var_dump($systemToID);
                if($systemFromID == "" | $systemToID == "")
                {
                    echo "Invalid solar system provided.";
                    echo "<a href='regionaltrade.php?character=$character_get'>Regional Trader Assistant </a>";
                    die();
                }
            
            
            //User submitted both stations
            echo "From " . "<b>" .$systemFromName ."</b>". " to " . "<b>".$systemToName ."</b>";
            ?>

            <br><link rel="stylesheet" type="text/css" href="scripts/jquery.autocomplete.css" />
            <script type="text/javascript" src="scripts/jquery.js"></script>
            <script type="text/javascript" src="scripts/jquery.autocomplete.js"></script>
            <script type="text/javascript">
            var jQueryAutocomplete = $.noConflict(true);</script>
            
            <script>
            $(document).ready(function(){
            jQueryAutocomplete("#tag3").autocomplete("scripts/autocomplete_i.php", {
            selectFirst: true
        });
        });
        </script>
        </head>
        <body>
        <?php
       echo " <form name='regional_2' action = 'regionaltrade.php?character=$character_get&sys1=$stationFromID&sys2=$stationToID&tr1=$transtype1&tr2=$transtype2' method='POST'>";
       
        ?>
        <label>Item: </label>
        <input name="tag3" type="text" id="tag3" size="50"/>
        <input type="Submit" value="Submit" name="Submit2" class="btn btn-success" />    
          <?php  
            
        }
        else
        {
?>

    <link rel="stylesheet" type="text/css" href="scripts/jquery.autocomplete.css" />
            <script type="text/javascript" src="scripts/jquery.js"></script>
            <script type="text/javascript" src="scripts/jquery.autocomplete.js"></script>
             <script type="text/javascript">
            var jQueryAutocomplete = $.noConflict(true);</script>
    <script>
    $(document).ready(function(){
    jQueryAutocomplete("#tag").autocomplete("scripts/autocomplete_s1.php", {
        selectFirst: true
    });
        });
    </script>
    </head>
    <body>
        <?php
         mysqli_query($con, "DELETE FROM cache_tool WHERE character_eve_idcharacter = '$character_get'") or die(mysqli_error($con)); //send  both post and get at same time //send  both post and get at same time
        
       echo " <form name='regional_1' action = 'regionaltrade.php?character=$character_get' method='POST'>";
      ?>
    <label>Origin: </label>
    <input name="tag" type="text" id="tag" size="70"/>
    purchase from: <input type="radio" name="transtype_1" value="buy" checked>buy order
    <input type="radio" name="transtype_1" value="sell">sell order
    <br><br>
    
      <link rel="stylesheet" type="text/css" href="scripts/jquery.autocomplete.css" />
            <script type="text/javascript" src="scripts/jquery.js"></script>
            <script type="text/javascript" src="scripts/jquery.autocomplete.js"></script>
             <script type="text/javascript">
            var jQueryAutocomplete = $.noConflict(true);</script>
        <script>
        $(document).ready(function(){
        jQueryAutocomplete("#tag2").autocomplete("scripts/autocomplete_s1.php", {
        selectFirst: true
        });
        });
        <?php
      
        ?>
        
        </script>
     <label>Destination: </label>
    <input name="tag2" type="text" id="tag2" size="70"/>
    sell as: <input type="radio" name="transtype_2" value="buy">buy order
    <input type="radio" name="transtype_2" value="sell" checked>sell order
    <br><br>
    
    <input type ="Submit" name="Submit" value="Submit" class="btn btn-success"/>
    
    </form>
        </body>  
    </html>
<?php
        }
    }
    }

  }

    $title = "Regional Trade Assistant";
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
    $content->drawHeader($getCharacterPortrait, $characterName, "Regional Trade Assistant");
?>
    <div class="row">
<?php
    $content->columns_def();
?>    <!-- /.row -->
 <div class="row">
 <div class="col-lg-12">  
     <div class="panel panel-default">   
     <div class="panel-heading">
        <i class="fa fa-arrows fa-fw"></i>
        Regional Trade Assistant.
        <br>   <i class="fa fa-info fa-fw"></i>You can quickly check price differences between systems and regions here. Simply select the starting and destination stations, how you will buy/sell the items along with your items.
        <br>   <i class="fa fa-info fa-fw"></i>Taxes and broker fees are calculated according to your selected character's skills and standings for the relevant stations.
     </div>    
        <div class="panel-body">  
         <div class="dataTable_wrapper">
<?php
    regionalTrader($con,$character_get);
?>
</div>
                        <!-- /.panel-heading -->
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                <!-- /.col-lg-12 -->
            </div>
<?php
    $content->drawFooter();
?>
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
        $('#dataTables-example').DataTable({
                responsive: true,
                "order": [[ 7, "desc" ]]
        });
    });
    </script>
    
</body>

</html>    
    