<?php
ini_set('max_execution_time', 10000);
//Updates the price in the DB for ALL marketable items according to Jita's current sell price. This script runs twice a day.
//This uses Eve-Central's API for fetching data, done in batches of 20 for each request
//To prevent excessive server load (and subsequent bans), there is a small delay between each batch sent.
//There are aproximately 10,000 marketable items as of current release (03/11/2015)

include('/var/www/html/pages/scripts/class/link.php');
$link = new link();
$con = $link->connect();
    

    $getMarketablesList = mysqli_query($con, "SELECT eve_iditem AS item FROM item WHERE type != '0' AND eve_iditem <= 40339 ORDER BY eve_iditem ASC") 
            or die(mysqli_error($con));
    
    $itemList = array();

    while($row = mysqli_fetch_array($getMarketablesList))
    {
        array_push($itemList,$row['item']);
    }
    
    $chunks = (array_chunk($itemList, 20));
   
       $array_final = array();
       $chunks_number = 0;
        foreach($chunks as $chunk)
        {
            $chunks_number++;
            $typeids = array();
            $itemprices = array();
            
            $type_index = -1;
            $k=-1;
            sleep(0.5); //to prevent overloading the API
            
            for($m=0;$m<=count($chunk)-1;$m++) //typeids contains all the item ids for the current chunk
            {
                $type_index++;
                array_push($typeids,$chunk[$m]);
            }
                
                $itemprices = array();
                $url="http://api.eve-central.com/api/marketstat?&usesystem=30000142&typeid=".join('&typeid=',$typeids);
               // echo $url;
                $pricexml=file_get_contents($url);
                $xml=new SimpleXMLElement($pricexml);
                
                //var_dump($typeids);
                foreach($typeids as $typeid)
                {
                    $k++;
                    $types_and_prices = array();
                    $item=$xml->xpath('/evec_api/marketstat/type[@id='.$typeid.']');
                    $price= (float) $item[0]->sell->min;
                    $price=max(0,round($price,2));
					
					
                    $price2= (float) $item[0]->buy->max;
                    $price2=max(0,round($price2,2));
					
					$pricef = ($price+$price2)/2;
					
                    
                    array_push($itemprices,$pricef);
                    $types_and_prices[$k] = array($typeids[$k],$itemprices[$k]);
                    //var_dump($types_and_prices);
                    array_push($array_final,$types_and_prices[$k]);
                    //echo $typeids[$k] ."item added" . "<br>";
                }
        }
             
              //  var_dump($array_final);
                $values_pair = array();
                $index = 0;
                foreach($array_final as $vals)
                {
                    $index++;
                    $values_pair[$index] = "(" . $vals[0] . "," . $vals[1] .")";
                }
                $values =  implode(",",$values_pair);
 

            //correct values for ALL batches
            $insert_price_data = mysqli_query($con, "REPLACE INTO `trader`.`item_price_data` (`item_eve_iditem`, `price_evecentral`) VALUES $values")
        or die(mysqli_error($con));
    
	if($insert_price_data)
	{
		echo "Updated sucessfully";
	}
	else
	{
		echo "Error";
	}
    
?>



