<?php
session_start();
$user_session = $_SESSION['user'];
//headers
    include ('includes/bootstrapper.php');
    //import namespace
    use Pheal\Pheal;
   // use Pheal\Core\Config;
    include('includes/connect.php');
  
    //check session
        if(!isset($user_session))
        {
            echo "gtfo";
        }
        else 
        {
            //check character ID provided
            if(isset($_GET['character'] ))
            {
               $character_get = $_GET['character'];
                //check if GET character id belongs to user stored in session
               $check_char_belong_session = mysqli_query($con, "SELECT * FROM V_user_characters WHERE username = '$user_session' AND character_eve_idcharacter = '$character_get'")
                       or die (mysqli_error($con));
               
               if(mysqli_num_rows($check_char_belong_session) == 0)
               {
                   echo "Invalid character ID";
               }
               else {
              //fetch transaction data from DB
                $getTransactions = mysqli_query($con, "SELECT time, 
                        item_id,
                        item_name, 
                        quantity, 
                        price_unit, 
                        price_total,
                        transaction_type, 
                        station_name
                        FROM v_transaction_details
                        WHERE character_id = '$character_get'
                        ORDER BY time DESC
                        LIMIT 20")
                        or die(mysqli_error($con));
                $getBalance;
                $getNetworth;
                   
                   echo "Recent transactions" . "<br>";
                   echo "<table border = '1'>";
                   echo "<tr><td>Time and Date</td>"
                   . "<td>Item</td>"
                   . "<td>Icon</td>"
                   . "<td>Quantity</td>"
                   . "<td>ISK/unit</td>"
                   . "<td>ISK total</td>"
                   . "<td>Type</td>"
                   . "<td>Station</td></tr>";
                
                while($pow = mysqli_fetch_array($getTransactions, MYSQLI_ASSOC))           
                {   
                 $date = $pow['time'];
                 $itemName = $pow['item_name'];
                 $itemID = $pow['item_id'];//$itemName = mysqli_result(mysqli_query($con, "SELECT name FROM item WHERE eve_iditem = '$itemID' "),0,0);
                 $quantity = $pow['quantity'];
                 $price_unit = $pow['price_unit'];
                 $price_total = $pow['price_total'];
                 $transType = $pow['transaction_type'];
                 $stationName = $pow['station_name'];
                 //$stationName = mysqli_result(mysqli_query($con, "SELECT name FROM station WHERE eve_idstation = '$stationID'"),0,0);
                    
                    echo "<tr><td>".$date."</td>"
                            . "<td>".$itemName. "</td>"
                            . "<td> <img src='http://image.eveonline.com/Type/".$itemID."_32.png'</td>"
                            . "<td>".$quantity."</td>"
                            . "<td>".$price_unit."</td>"
                            . "<td>".$price_total."</td>"
                            . "<td>".$transType."</td>"
                            . "<td>".$stationName."</td>"
                            . "</tr>";  
                }
                           //print_r(implode(',',$items));
                           //continue
                           echo "Links";
                           echo "<a href='transactions.php?character=$character_get'>Transactions</a>";
                           echo "Contracts";
                           echo "Industry Jobs";
                           echo "Most profitable items";
                           echo "Best customers";
                         
                           echo "Best timezones";
                           echo "<a href='assets3.php?character=$character_get'>Asset List</a>";
                           echo "Aggregate character";
                           echo "<a href='profit.php?character=$character_get'>Profit History</a>";
                           echo "<a href ='orders.php?character=$character_get'>Market orders </a>";
                           echo "<a href='regionaltrade.php?character=$character_get'>Regional Trader Assistant </a>";
                           echo "<a href='add_api_character.php?character=$character_get'>ADD API CHARACTER</a>";
                   }
             }
            else
            {
            echo "Select character:";
        
            echo "Transaction page". "<br>";
               echo "<table border = '1'>";
               echo "<tr><td>Character</td>".
                    "<td>Name</td>".
                    "<td>ISK Balance</td>".
                    "<td>Networth Asset Value</td></td>";
  
            $character_list = mysqli_query($con, "SELECT character_eve_idcharacter FROM aggr WHERE user_iduser = "
                    . "(SELECT iduser FROM user WHERE username = '$user_session')")
                    or die(mysqli_error($con));
           
            while($row = mysqli_fetch_array($character_list, MYSQLI_ASSOC))
            {
              $idcharacter = $row['character_eve_idcharacter'];
              
              $api_q = mysqli_query($con, "SELECT api_apikey AS api FROM `characters` WHERE eve_idcharacter = '$idcharacter'")
                           or die(mysqli_error($con));
                   $api_a = mysqli_fetch_array($api_q, MYSQLI_ASSOC);
                   $apikey = $api_a['api'];
                   
              $vcode_q = mysqli_query($con, "SELECT vcode FROM api WHERE apikey = '$apikey'")
                           or die(mysqli_error($con));
                   $vcode_a = mysqli_fetch_array($vcode_q);
                   $vcode = $vcode_a['vcode'];
       
              $getCharacterInfo = mysqli_fetch_array(mysqli_query($con, "SELECT name, balance, networth FROM characters WHERE eve_idcharacter = '$idcharacter'", MYSQLI_ASSOC));
              $name = $getCharacterInfo['name'];
              (double)$balance = $getCharacterInfo['balance'];
              (double)$networth = $getCharacterInfo['networth'];
    
              //Here we will update all of our character specific data:
              //Account balance update
              $pheal = new Pheal($apikey, $vcode, "char"); //set scope
              $response = $pheal->AccountBalance(array("characterID" => $idcharacter)); //add parameters
                
                foreach($response->accounts as $row)
                {
                $balance = $row->balance;
                }
                
              //Broker relations skill
              $url = "https://api.eveonline.com/char/CharacterSheet.xml.aspx?keyID=$apikey&vCode=$vcode&characterID=$idcharacter";
              $xml = simplexml_load_file($url);    
              $get_broker = $xml->xpath('/eveapi/result/rowset/row[@typeID=3446]');
                      //print_r($skill_level);
              if(boolval($get_broker) == 1 ){$level_broker = $get_broker[0]['level'];} else {$level_broker = 0;}
                   
               //Accounting level update
              $xml2 = simplexml_load_file($url);
              $get_acc = $xml2->xpath('/eveapi/result/rowset/row[@typeID=16622]');
              if(boolval($get_acc) == 1 ){$level_acc = $get_acc[0]['level'];} else {$level_acc = 0;} 
              
              $update_character = mysqli_query($con, "UPDATE `trader`.`characters` "
                            . "SET `balance` = '$balance', "
                            . "`broker_relations` = '$level_broker', "
                            . "`accounting` = '$level_acc' "
                            . "WHERE "
                            . "`characters`.`eve_idcharacter` = '$idcharacter'")
                                   or die(mysqli_error($con));
              
            //update All faction and corp standings  
                $pheal3 = new Pheal($apikey, $vcode, "char");    
                $result3 = $pheal->Standings(array("characterID" => $idcharacter)); //add parameters
 
            //$corpStandingsArray = array();
            $corpStandingsArray = array();
            foreach ($result3->characterNPCStandings->NPCCorporations as $corpStandings) 
            {
               //$standing = $corpStandings->standing;
               //$corpStandingsArray[$standing] = $corpStandings->fromID;
               $corpStandingsArray[] = "(NULL," . $idcharacter . "," . $corpStandings->fromID . "," . $corpStandings->standing . ")";
            }
            if(!empty($corpStandingsArray)){
             $update_standings_corp = mysqli_query($con, "INSERT IGNORE INTO `trader`.`standings_corporation` 
                      (`idstandings_corporation`, 
                      `characters_eve_idcharacters`, 
                      `corporation_eve_idcorporation`, 
                      `value`) 
                       VALUES" . implode (', ', $corpStandingsArray) ) or die (mysqli_error($con));
            }

            $factionStandingsArray = array();
            foreach ($result3->characterNPCStandings->factions as $factionStandings) 
            {
               //$standing = $factionStandings->standing;
               //$factionStandingsArray[$standing] = $factionStandings->fromID;
               $factionStandingsArray[] = "(NULL," . $idcharacter . "," . $factionStandings->fromID . "," . $factionStandings->standing . ")";
            }
           
            if(!empty($factionStandingsArray)){
            $update_standings_faction = mysqli_query($con, "INSERT IGNORE INTO `trader`.`standings_faction` 
                      (`idstandings_faction`, 
                      `characters_eve_idcharacters`, 
                      `faction_eve_idfaction`, 
                      `value`) 
                       VALUES" . implode (', ', $factionStandingsArray) ) or die (mysqli_error($con));
            }

           //Update ALL transactions for each character
                $pheal4 = new Pheal($apikey, $vcode, "char");
                $wallet_response = $pheal4->WalletTransactions(array("characterID" => $idcharacter));
                $i=-1;
                    $null = (string)"NULL";      
                    foreach($wallet_response->transactions as $row2)
                    {
                        $i++;
                        $typeid = $row2->typeID;
                        $dateTime = $row2->transactionDateTime;
                        $quantity = $row2->quantity;
                        $price_unit = $row2->price;
                        $transactionType = $row2->transactionType;
                        $station = $row2->stationName;
                        $price_total = $price_unit*$quantity;
                        $station_id = $row2->stationID;
                        $transkey = $row2->transactionID;
                        $clientName = $row2->clientName;
                        
                        $item[$i] = array("$null", //array with transaction data
                                "'".$dateTime."'", 
                                "'".$quantity."'", 
                                "'".$price_unit."'", 
                                "'".$price_total."'", 
                                "'".$transactionType."'", 
                                "'".$idcharacter."'",
                                "'".$station_id."'",
                                "'".$typeid."'",
                                "'".$transkey."'",
                                "'".str_replace("'",".",$clientName)."'"); 
                    }
                    
                    for($j=0;$j<=$i;$j++)
                    {
                        $arrayfinal[$j] = $item[$j];
                    }
                
                    $values = array();
            
                foreach ($arrayfinal as $rowValues) 
                {
                    foreach ($rowValues as $key => $rowValue) 
                        {
                        $rowValues[$key] = $rowValues[$key];
                        }
                    $values[] = "(" . implode(', ', $rowValues) . ")";
                }
        
                if(!empty($values))
                {
                //var_dump($values);
                $query_insert = "INSERT IGNORE INTO `trader`.`transaction` (`idbuy`, `time`, `quantity`, `price_unit`, `price_total`, `transaction_type`, `character_eve_idcharacter`, `station_eve_idstation`, `item_eve_iditem`, `transkey`, `client`) "
                    . "VALUES " . implode (', ', $values);
               
                $insert_transactions =   mysqli_query($con, $query_insert) or die(mysqli_error($con));
                }
                    $newTransactions = mysqli_affected_rows($con);
     
                ///Update contracts
                $pheal_contracts = new Pheal($apikey, $vcode, "char"); //set scope
                $response = $pheal_contracts->Contracts(array("characterID" => $idcharacter)); //add parameters
                
                $i=-1;
                foreach($response->contractList as $row)
                {
                $contractID = $row->contractID;
                $issuerID = $row->issuerID;
                $acceptorID = $row->acceptorID; if($acceptorID == "") {$acceptorID = "NULL";}
                $startStationID = $row->startStationID; if($startStationID == "") {$startStationID = "NULL";}
                $endStationID = $row->endStationID; if($endStationID == "") {$endStationID = "NULL";}
                $type = $row->type;
                $status = $row->status;
                $title = $row->title;
                $availability = $row->availability;
                $price = $row->price; if($price == "") {$price = "NULL";}
                $reward = $row->reward; if($reward == "") {$reward = "NULL";}
                $collateral = $row->collateral; if($collateral == "") {$collateral = "NULL";}
                $volume = $row->volume;
                $dateIssued = $row->dateIssued;
                $dateExpired = $row->dateExpired;
                $dateAccepted = $row->dateAccepted;
                $dateCompleted = $row->dateCompleted;
            
                $getIssuerName = "https://api.eveonline.com/eve/CharacterName.xml.aspx?ids=$issuerID";
                $xml = simplexml_load_file($getIssuerName);
                
                    foreach ($xml->result->rowset->row as $r) 
                    {
                    $issuerName = $r['name'];
                    }
                
                    if(isset($acceptorID) && $acceptorID != "")
                    {
                    $getAcceptorName = "https://api.eveonline.com/eve/CharacterName.xml.aspx?ids=$acceptorID";
                    $xml = simplexml_load_file($getAcceptorName);
                
                        foreach ($xml->result->rowset->row as $r) 
                        {
                        $acceptorName = $r['name'];
                        }
                        $i++;    
                        $contracts_list[$i] = array( 
                                "'".$contractID."'", 
                                "'".$issuerName."'", 
                                "'".$acceptorName."'", 
                                "'".$status."'", 
                                "'".$availability."'", 
                                "'".$type."'",
                                "'".$dateIssued."'",
                                "'".$dateExpired."'",
                                "'".$dateAccepted."'",
                                "'".$dateCompleted."'",
                                "'".$price."'",
                                "'".$reward."'",
                                "'".$collateral."'",
                                "'".$volume."'",
                                "'".$startStationID."'",
                                "'".$endStationID."'",
                                "'".$idcharacter."'");         
                    }
                }
                    
                     for($j=0;$j<=$i;$j++)
                    {
                        $arrayfinal_contracts[$j] = $contracts_list[$j];
                    }
                
                    $values_contracts = array();
            
                foreach ($arrayfinal_contracts as $rowValues_contracts) 
                {
                    foreach ($rowValues_contracts as $key => $rowValue_contracts) 
                        {
                        $rowValues_contracts[$key] = $rowValues_contracts[$key];
                        }
                    $values_contracts[] = "(" . implode(', ', $rowValues_contracts) . ")";
                }    
   
            $update_contract = mysqli_query($con,"INSERT IGNORE INTO `trader`.`contracts` "
                        . "(`eve_idcontracts`, "
                        . "`issuer_name`, "
                        . "`acceptor_name`, "
                        . "`status`, "
                        . "`availability`,"
                        . "`type`, "
                        . "`creation_date`, "
                        . "`expiration_date`, "
                        . "`accepted_date`, "
                        . "`completed_date`, "
                        . "`price`, "
                        . "`reward`, "
                        . "`colateral`,"
                        . "`volume`, "
                        . "`fromStation_eve_idstation`, "
                        . "`toStation_eve_idstation`, "
                        . "`characters_eve_idcharacters`) "
                        . "VALUES " . implode (', ', $values_contracts))
                        or die(mysqli_error($con));
                $newContracts = mysqli_affected_rows($con);
      
              //Update Market Orders
                $i=-1;
            $pheal_orders = new Pheal($apikey, $vcode, "char"); //set scope
            $response_orders = $pheal->MarketOrders(array("characterID" => $idcharacter)); //add parameters
            
            foreach($response_orders->orders as $row)
            {
                $i++;
                $orderID = $row->orderID;
                $charID = $row->charID;
                $stationID = $row->stationID;
                $volEntered = $row->volEntered;
                $volRemaining = $row->volRemaining;
                $minVolume = $row->minVolume;
                $orderState = $row->orderState;
                $typeID = $row->typeID;
                $range = $row->range;
                $duration = $row->duration;
                $escrow = $row->escrow;
                $price = $row->price;
                $bid = $row->bid; if($bid == True) {$bid = 'buy';} else {$bid = 'sell';}
                $issuedDate = $row->issued;
            
                switch($orderState)
                {
                    case '0':
                        $orderStateName = "open";
                        break;
                    case '1':
                        $orderStateName = "closed";
                         break;
                    case '2':
                        $orderStateName = "expired";
                         break;
                    case '3':
                        $orderStateName = "canceled";
                         break;
                    case '4':
                        $orderStateName = "pending";
                         break;
                    case '5':
                        $orderStateName = "character_deleted";
                         break;
                }
                
                $orders[$i] = array("NULL", 
                                "'".$typeID."'", 
                                "'".$stationID."'", 
                                "'".$idcharacter."'", 
                                "'".$price."'", 
                                "'".$volRemaining."'", 
                                "'".$duration."'",
                                "'".$escrow."'",
                                "'".$bid."'",
                                "'".$orderStateName."'",
                                "'".$range."'",
                                "'".$issuedDate."'",
                                "'".$orderID."'"); 

            }
                 for($j=0;$j<=$i;$j++)
                    {
                        $arrayfinal_orders[$j] = $orders[$j];
                    }
                
                    $values_orders = array();
            
                foreach ($arrayfinal_orders as $rowValues_orders) 
                {
                    foreach ($rowValues_orders as $key => $rowValue_orders) 
                        {
                        $rowValues_orders[$key] = $rowValues_orders[$key];
                        }
                    $values_orders[] = "(" . implode(', ', $rowValues_orders) . ")";
                }
        
                if(!empty($values_orders))
                {
                //var_dump($values);
                $update_orders = mysqli_query($con, "INSERT IGNORE INTO `trader`.`orders` "
                        . "(`idorders`, "
                        . "`eve_item_iditem`, "
                        . "`station_eve_idstation`, "
                        . "`characters_eve_idcharacters`, "
                        . "`price`, "
                        . "`volume_remaining`, "
                        . "`duration`, "
                        . "`escrow`, "
                        . "`type`, "
                        . "`order_state`, "
                        . "`order_range`, "
                        . "`date`,"
                        . "`transkey`) "
                        . "VALUES " . implode (', ', $values_orders))
                        or die(mysqli_error($con));
                        
                $newOrders = mysqli_affected_rows($con);
       
                if($insert_transactions && 
                        $update_standings_faction && 
                        $update_standings_corp && 
                        $update_character && 
                        $update_orders &&
                        $update_contract)
                    {
                    {echo "Updated character data for " . $name . "," . 
                            $newTransactions . " new transactions." .
                            $newOrders . " new orders." .
                            $newContracts . " new contracts.". "<br>";
                    }
                    }                
                }
              echo "<tr><td>" . "<a href = 'dashboard.php?character=$idcharacter'> <img src='https://image.eveonline.com/Character/".$idcharacter."_64.jpg'"."</td>"
                      . "<td>".$name."</td>"
                      . "<td>".number_format($balance,2)."</td>"
                      . "<td>".number_format($networth,2). "</td>"
                      . "</tr>";
            }
            echo "</table>";
            exit();
            //$pheal = new Pheal($apikey, $vcode, $charid);
            }
        }
    ?>

