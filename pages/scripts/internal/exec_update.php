<?php 

ini_set('mysql.connect_timeout', 3000);
ini_set('default_socket_timeout', 3000);

      
require_once ('/var/www/html/pages/scripts/class/link.php');
require_once ('/var/www/html/pages/scripts/class/utils.php');
require_once ('/var/www/html/pages/scripts/class/content.php');
require_once ('/var/www/html/pages/scripts/vendor/autoload.php');
require_once ('/var/www/html/pages/scripts/class/tax.php');
$number = 0;

   
    use Pheal\Core\Config; //Import namespace
    use Pheal\Pheal;
    
    Config::getInstance()->cache = new \Pheal\Cache\FileStorage('/var/www/html/phealcache/');
    Config::getInstance()->access = new \Pheal\Access\StaticCheck();



    $link = new link();
    $con = $link->connect();

    $title = "Character Select";
    $content = new content($title);
    //$content->drawMeta($title);
    
    //$user_session = $username;
    
    $balance_total = 0;
    $networth_total = 0;
    $orders_total = 0;
    $escrow_total = 0;
    
    $character_list = array();
    
    
    function checkAPI($apikey, $vcode,$char)
    {
        $url = "https://api.eveonline.com/account/APIKeyInfo.xml.aspx?keyID=".$apikey."&vCode=".$vcode;
    
        $xml = simplexml_load_file($url);
    
    //check if exists or expired
        if(!$xml->result)
        {
        //echo "invalid api key"; //-3
            return -3;
        }
            else if ($xml->result->key['accessMask'] != '82317323' && $xml->result->key['accessMask'] != '1073741823')
        {
        //echo "wrong permissions";
        //retur //-2
            return -2;
        }
        else if ($xml->result->key->rowset->row[0]['characterID'] != $char &&
             $xml->result->key->rowset->row[1]['characterID'] != $char &&
             $xml->result->key->rowset->row[2]['characterID'] != $char)
        {
        //echo "char does not belong to this api"; //-1
            return -1;
        }
        else 
        {
        //echo "all ok"; //1
            return 1;
     
        }

    }   
	
	function checkAPIPheal($apikey,$vcode,$char)
    {
	try {
      $phealAPI = new Pheal($apikey, $vcode, "account");
      $response = $phealAPI->APIKeyInfo(); //add parameters
                
      $accessMask = $response->key->accessMask;
	  $expiry = $response->key->expires;
            
      $apichars = array();
      
      foreach($response->key->characters as $row2)
      {
          $char_api = $row2->characterID;
          array_push($apichars,$char_api);
      }
      
      if($accessMask == "")
      {
          return -4; //api key does not exist
      }
      else if ($accessMask != '82317323' && $accessMask != '1073741823')
      {
          return -3; //api key has invalid permissions
      }
      else if (!in_array($char,$apichars))
      {
          return -2; //character does not belong to API key
      }
      else if (!isset($expiry))
	  {
		  return -1; //key has expired
	  }
	  else
      {
          return 1; //everything is ok
      }
		}
		catch (\Pheal\Exceptions\PhealException $e) {
		echo sprintf(
			"an exception was caught! Type: %s Message: %s",
			get_class($e),
			$e->getMessage()
		);
		}
        
    }
    
    
    
    //check if eve api AND eve central are online:
    function test_endpoint()
    {
       $pheal = new Pheal();
       $response = $pheal->serverScope->ServerStatus(); //test Eve-Api

       if(!is_numeric($response->onlinePlayers))
       {
           echo "Eve Online's API seems to be unavailable. Please try again shortly.";
           exit();
       }
    }
    


    //used to fetch transactions (walking backwards twice) for every character
    function fetchTransactions($apikey, $vcode, $refID, $idcharacter, $con)
    {
        $pheal4 = new Pheal($apikey, $vcode, "char", $refID);
        $wallet_response = $pheal4->WalletTransactions(array("characterID" => $idcharacter));
                
        if($refID !=0)
        {
            $wallet_response = $pheal4->WalletTransactions(array("fromID" => $refID)); 
        }
                
        $i=-1;
                //get the Eve transaction ID for the latest transaction (this might need some tweaking when they decide to recycle ids)
        $getLatestTransaction = utils::mysqli_result(mysqli_query($con, "SELECT MAX(transkey) AS val FROM transaction WHERE character_eve_idcharacter = '$idcharacter'"),0,0);
                
                //var_dump($getLatestTransaction);
        if(!isset($getLatestTransaction))
        {
            $latestTransaction = 0;
        }
        else
        {
            $latestTransaction = $getLatestTransaction;
        }
               // var_dump($latestTransaction);
        $arrayfinal = array();
        $array_refs = array();
                
        $null = (string)"NULL";      
        
        foreach($wallet_response->transactions as $row2)
        {
            $transkey = $row2->transactionID;
            $typeid = $row2->typeID;
            $dateTime = $row2->transactionDateTime;
            $quantity = $row2->quantity;
            $price_unit = $row2->price;
            $transactionType = $row2->transactionType;
            $station = $row2->stationName;
            $price_total = $price_unit*$quantity;
            $station_id = $row2->stationID;
            $clientName = $row2->clientName;
                       
            array_push($array_refs, $transkey);
                        
            if($transkey > $latestTransaction) //only update transactions not in the DB already
            {
                $i++;
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
        }
                    
        for($j=0;$j<=$i;$j++)
        {
            $arrayfinal[$j] = $item[$j];
        }
        
        $values_transactions = array();
            
        foreach ($arrayfinal as $rowValues) 
        {
            foreach ($rowValues as $key => $rowValue) 
            {
                $rowValues[$key] = $rowValues[$key];
            }
            //this array contains all transactions in this format: (x,y,z),(a,b,c),...
            $values_transactions[] = "(" . implode(', ', $rowValues) . ")"; 
        }
                
        if(!empty($values_transactions))
        {
                //var_dump($values);
            $query_insert = "INSERT IGNORE INTO `trader`.`transaction` (`idbuy`, `time`, `quantity`, `price_unit`, `price_total`, `transaction_type`, `character_eve_idcharacter`, `station_eve_idstation`, `item_eve_iditem`, `transkey`, `client`) "
                . "VALUES " . implode (', ', $values_transactions);

            $insert_transactions =   mysqli_query($con, $query_insert) or die(mysqli_error($con));
            return $newTransactions = mysqli_affected_rows($con);
                
            if(count($array_refs) == 2560) //check if we exceed the max transactions per request
            {
                $refID = end($array_refs);
                fetchTransactions($apikey, $vcode, $refID, $idcharacter, $con);
            }
        }
        else
        {
            return $newTransactions = 0;
            $insert_transactions = False;
        }           
    }

    
        test_endpoint();


            $allusers = mysqli_query($con, "SELECT * FROM user order by iduser") or die(mysqli_error($con));
            
            while($users = mysqli_fetch_array($allusers))
            {
                $user_session = $users['username'];
                $username = $users['username'];
                
            
            //if an API is not found or has invalid permissions we delete that api and any corresponding characters from the DB
            $apilist_user = mysqli_query($con, "SELECT api.apikey, api.vcode, characters.eve_idcharacter FROM api
                join characters on characters.api_apikey = api.apikey
                join aggr on aggr.character_eve_idcharacter = characters.eve_idcharacter
                join user on aggr.user_iduser = user.iduser
                where user.username = '$user_session'");
            
            $idActiveuser = utils::mysqli_result(mysqli_query($con, "SELECT iduser FROM user WHERE username = '$user_session'"),0,0);

            //get character list from current user account (invalid APis were excluded already)
            $character_list = mysqli_query($con, "SELECT character_eve_idcharacter FROM aggr WHERE user_iduser = (SELECT iduser FROM user WHERE username = '$user_session')")
                   or die(mysqli_error($con));
            
        

           
            
            ////////////////////////////////////
            ///Update character specific data//
            ///////////////////////////////////
            while($row = mysqli_fetch_array($character_list, MYSQLI_ASSOC)) //one iteration per character
            {
                echo $idcharacter = $row['character_eve_idcharacter'];
				echo "-";
              
                //fetch api/vcode associated to this character
                $api_q = mysqli_query($con, "SELECT api_apikey AS api FROM `characters` WHERE eve_idcharacter = '$idcharacter'")
                           or die(mysqli_error($con));
                   $api_a = mysqli_fetch_array($api_q, MYSQLI_ASSOC);
                   $apikey = $api_a['api'];
                   
                $vcode_q = mysqli_query($con, "SELECT vcode FROM api WHERE apikey = '$apikey'")
                           or die(mysqli_error($con));
                   $vcode_a = mysqli_fetch_array($vcode_q);
                   $vcode = $vcode_a['vcode'];
       
                $getCharacterInfo = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM characters WHERE eve_idcharacter = '$idcharacter'", MYSQLI_ASSOC));
                $name = $getCharacterInfo['name'];
                (double)$balance = $getCharacterInfo['balance'];
                (double)$networth = $getCharacterInfo['networth'];
                //$idchar = $getCharacterInfo['eve_idcharacter'];
              
                $escrowTotal = 0;
                $sellOrderValueTotal = 0;
               
                    if (checkAPIPheal($apikey, $vcode, $idcharacter) <1)
                    {
						
                        $charName = utils::mysqli_result(mysqli_query($con, "SELECT name FROM characters WHERE eve_idcharacter = $idcharacter "),0,0);
                            //invalid access mask or API not found. Delete API from account:
                        /*$delete_char_account = mysqli_query($con, "DELETE FROM aggr WHERE user_iduser = '$idActiveuser' AND character_eve_idcharacter = '$idcharacter'")
                                or die(mysqli_error($con));*/
                            //$remove_character = mysqli_query($con, "DELETE FROM characters WHERE eve_idcharacter = '$char2'") or die(mysqli_error($con));        
                            //$delete_api = mysqli_query($con, "DELETE FROM api WHERE apikey = '$apikey2' and vcode = '$vcode2'")
                            //    or die(mysqli_error($con));
                        echo "Character ". $charName . " has incorrect API data and has been removed from your account". "\n";
                        
                    }
                    
                    else {
                    
              
                mysqli_query($con, "START TRANSACTION");
                
          //  $path = "C:/xampp/tmp/phealcache/".$apikey."/".$vcode."/char/AccountBalance/Request_characterID#".$idcharacter.".xml";
            
          /*  if($xml=simplexml_load_file($path)){;
                //var_dump($xml->xpath('/eveapi/cachedUntil'));
                //print_r($xml->xpath('/eveapi/cachedUntil'))[0][0];
                 strtotime($cached = $xml->cachedUntil)+3600;
               (int)$timenow=time();
    
                if(($timenow < strtotime($cached)+3600))
                {
                   //echo "Too early to refresh data";
                   cachedTable($con, $user_session);
                   exit();
                   
                }
            }*/

            /////////////////////////////////
            ///Account balance update
            /////////////////////////////////
               echo $apikey;
               try {
                $pheal = new Pheal($apikey, $vcode, "char"); //set scope
               
                if($response = $pheal->AccountBalance(array("characterID" => $idcharacter)) == "")
                {
                    echo "Key expired";
                    break;
                }
                
                else
                {
                     $response = $pheal->AccountBalance(array("characterID" => $idcharacter)); //add parameters
                }
                
                
                
                foreach($response->accounts as $row)
                {
                    $balance = $row->balance;
                }
            //////////////////////////////////////  
            //Calculate Broker relations skill
            /////////////////////////////////////  
                $level_acc = 0;
                $level_broker = 0;
                //Using Pheal
                $pheal_broker = new Pheal($apikey, $vcode, "char"); //set scope
                $response = $pheal_broker->CharacterSheet(array("characterID" => $idcharacter)); //add parameters    
                foreach ($response->skills as $skills)
                {
                   // echo $skills->level;
                    
                    if(($skills->typeID) == 3446)
                    {
                        $level_broker = ($skills->level);
                    }

                }
                    
                    
                   
            ///////////////////////////
            ///Calculate Accounting level update
            ////////////////////////////////////
               /* $xml2 = simplexml_load_file($url);
                $get_acc = $xml2->xpath('/eveapi/result/rowset/row[@typeID=16622]');
                
                    if(boolval($get_acc) == 1 )
                    {
                        $level_acc = $get_acc[0]['level'];
                    }
                    else 
                    {
                        $level_acc = 0;   
                    } */
                  
                 //Using Pheal   
                $pheal_acc = new Pheal($apikey, $vcode, "char"); //set scope
                $response = $pheal_acc->CharacterSheet(array("characterID" => $idcharacter)); //add parameters    
                foreach ($response->skills as $skills2)
                {
                    if(($skills2->typeID) == 16622)
                    {
                        $level_acc = ($skills2->level);
                    }

                }     
              
             /////////////////////////////////////// 
             //update All faction and corp standings  
             /////////////////////////////////////////
                $pheal3 = new Pheal($apikey, $vcode, "char"); 
                 //var_dump($apikey); var_dump($vcode); var_dump($idcharacter);
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
                    else
                    {
                        $newStandingsCorp = 0;
                        $update_standings_corp = False;
                    }

                $factionStandingsArray = array();
                foreach ($result3->characterNPCStandings->factions as $factionStandings) 
                {
                //$standing = $factionStandings->standing;
                //$factionStandingsArray[$standing] = $factionStandings->fromID;
                    $factionStandingsArray[] = "(NULL," . $idcharacter . "," . $factionStandings->fromID . "," . $factionStandings->standing . ")";
                }
           
                    if(!empty($factionStandingsArray))
                    {
                        $update_standings_faction = mysqli_query($con, "INSERT IGNORE INTO `trader`.`standings_faction` 
                        (`idstandings_faction`, 
                        `characters_eve_idcharacters`, 
                        `faction_eve_idfaction`, 
                        `value`) 
                        VALUES" . implode (', ', $factionStandingsArray) ) or die (mysqli_error($con));
                    }
                    else
                    {
                        $newStandingsFaction = 0;
                        $update_standings_faction = False;
                    }

            
                $refID = 0;
            ///////////////////////////////////
           //Update ALL transactions for each character
           //////////////////////////////////// 

                $newTransactions = fetchTransactions($apikey, $vcode, $refID, $idcharacter, $con);
  
                ///////////////////////////
                ///Update contracts
                /////////////////////////
                $pheal_contracts = new Pheal($apikey, $vcode, "char"); 
                $response = $pheal_contracts->Contracts(array("characterID" => $idcharacter)); 
                $getLatestContract = utils::mysqli_result(mysqli_query($con, "SELECT MAX(eve_idcontracts) FROM contracts WHERE characters_eve_idcharacters = '$idcharacter'"),0,0);
                $arrayfinal_contracts = array(); //array with new contracts data
                
                $i=-1; //Store all new contracts, calculate how many contracts exceed the latest inserted ID, then delete all->insert all contracts from API again.
                $p=-1;
                $contracts_list_new = array(); //stores a reference to all new contracts
                
                foreach($response->contractList as $row)
                {
                    $contractID = $row->contractID;
                    $issuerID = $row->issuerID;
                    $acceptorID = $row->acceptorID; if($acceptorID == "") {$acceptorID = "NULL";}
                    $startStationID = $row->startStationID; if($startStationID == "") {$startStationID = "NULL";}
                    $endStationID = $row->endStationID; if($endStationID == "") {$endStationID = "NULL";}
                    $type = $row->type; 
                    $status = $row->status;
                    $title = $row->title; if($title == "") {$title = "NULL";}
                    $availability = $row->availability; 
                    $price = $row->price; if($price == "") {$price = "NULL";}
                    $reward = $row->reward; if($reward == "") {$reward = "NULL";}
                    $collateral = $row->collateral; if($collateral == "") {$collateral = "NULL";}
                    $volume = $row->volume; if($volume == "") {$volume = "NULL";}
                    $dateIssued = $row->dateIssued; if($dateIssued == "") {$dateIssued = "NULL";}
                    $dateExpired = $row->dateExpired; if($dateExpired == "") {$dateExpired = "NULL";}
                    $dateAccepted = $row->dateAccepted; if($dateAccepted == "") {$dateAccepted = "NULL";}
                    $dateCompleted = $row->dateCompleted; if($dateCompleted == "") {$dateCompleted = "NULL";}
            
                    $p++;
                        $contracts_list[$p] = array( 
                                "'".$contractID."'", 
                                "'".$issuerID."'", 
                                "'".$acceptorID."'", 
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
                //$getIssuerName = "https://api.eveonline.com/eve/CharacterName.xml.aspx?ids=$issuerID";
                //$xml = simplexml_load_file($getIssuerName);
                
                    //foreach ($xml->result->rowset->row as $r) 
                    //{
                    $issuerName = $issuerID; //values are stored as ids instead of names for now because the CHARACTERNAME ENDPOINT causes long delays
                    $acceptorName = $acceptorID; 
                    //if(isset($acceptorID) && $acceptorID != "")
                    //{
                    //$getAcceptorName = "https://api.eveonline.com/eve/CharacterName.xml.aspx?ids=$acceptorID";
                    //$xml = simplexml_load_file($getAcceptorName);
                    if($contractID > $getLatestContract)
                    {
                        $i++;
                        array_push($contracts_list_new, $contractID);  
                    }
                        
                }
                    
                $new_c = count($contracts_list_new);
                     
                for($j=0;$j<=$p;$j++)
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
                        $values_contracts[] = "(" . implode(', ', $rowValues_contracts) . ")"; //contains all contract data in this format: (x,y,z),(a,b,c),...
                    }    
   
                if(!empty($values_contracts))
                {
                    $delete_old_contracts = mysqli_query($con, "DELETE FROM contracts WHERE characters_eve_idcharacters = '$idcharacter'");
                    $update_contracts = mysqli_query($con,"INSERT IGNORE INTO `trader`.`contracts` "
                        . "(`eve_idcontracts`, "
                        . "`issuer_id`, "
                        . "`acceptor_id`, "
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
                    $newContracts = $new_c;
                }
                else 
                {
                    $newContracts = 0;
                    $update_contracts = False;
                }
		
				
            
            ////////////////////////////  
            //Update Market Orders
            /////////////////////////////
                $i=-1;
                $pheal_orders = new Pheal($apikey, $vcode, "char"); //set scope
                $response_orders = $pheal->MarketOrders(array("characterID" => $idcharacter)); //add parameters
                $arrayfinal_orders = array();
           
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
            
                    $escrowTotal = $escrowTotal + $escrow;
                //$getSellOrderEst = utils::mysqli_result(mysqli_query($con, "SELECT price_evecentral FROM item_price_data WHERE item_eve_iditem = '$typeID' "),0,0);
                
                /*if($orderState == '0' && $bid == 'sell' ) //check if it's an open sell order to add to the sellOrder amount
                {
                    $sellOrderValueTotal = $sellOrderValueTotal + ($getSellOrderEst*$volRemaining);
                }*/
                
                    switch($orderState) //The eve API reports order states with these codes
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
                    $values_orders[] = "(" . implode(', ', $rowValues_orders) . ")"; //array with all orders data
                }
        
                if(!empty($values_orders))
                {
                //var_dump($values);
                    $update_orders = mysqli_query($con, "replace INTO `trader`.`orders` "
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
                        
                    $numOrders = mysqli_affected_rows($con);
                //fetch the number of open orders from the last x inserted records
                    $newOrders = utils::mysqli_result(mysqli_query($con, "SELECT COUNT(idorders) FROM orders WHERE order_state = 'open' AND idorders > (SELECT max(idorders) FROM orders) - '$numOrders'"),0,0);
                }
                else
                {
                    $newOrders = 0;
                    $update_orders = False;
                }
                
                $sellOrderValueTotal = utils::mysqli_result(mysqli_query($con, "SELECT sum(orders.volume_remaining * item_price_data.price_evecentral) AS grand_total 
                    FROM orders 
                    JOIN item_price_data ON item_price_data.item_eve_iditem = orders.eve_item_iditem
                    WHERE characters_eve_idcharacters = '$idcharacter' AND orders.order_state = 'open' AND orders.type = 'sell'"),0,0);
                
                ////////////////////////////////
                //Update assets info
                /////////////////////////////////
                $pheal_assets = new Pheal($apikey, $vcode, "char"); //set scope
                $response_assets = $pheal->AssetList(array("characterID" => $idcharacter)); //add parameters
                $assetList = array();
                $i = 0; //for iterating each asset
                $index_asset = 0; //for iterating the final array with all assets
                (int)$networth = 0;

                foreach($response_assets->assets as $assets)
                {
                   $typeID_asset = $assets['typeID'];
                   $locationID = $assets['locationID'];
                   $quantity_asset = $assets['quantity'];
                   
                   $i++;
                  // array_push($assetIDList, $typeID_asset);
                 //  array_push($assetQList, $quantity_asset);
                   $assetList[$i] = array("NULL", $idcharacter, $typeID_asset, $quantity_asset, $locationID);
 
                   if(isset($assets->contents)) //iterate trough containers (might need a 3rd cycle for things like pos arrays->ships->modules
                   {
                       foreach($assets->contents as $assets_inside)
                       {
                        $typeID_sub = $assets_inside['typeID'];
                        $quantity_sub = $assets_inside['quantity'];
                        
                        $i++;
                        //array_push($assetIDList, $typeID_sub);
                       // array_push($assetQList, $quantity_sub);
                        $assetList[$i] = array("NULL", $idcharacter, $typeID_sub, $quantity_sub, $locationID);
                        
                        //(int)$getItemValue = utils::mysqli_result(mysqli_query($con, "SELECT price_evecentral FROM item_price_data WHERE item_eve_iditem = '$typeID_sub'"),0,0)*$quantity_sub;
                        //$networth = $networth + $getItemValue;
                        
                        if(isset($assets_inside->contents))
                        {
                            //foreach (assets->contents->contents)
                            foreach($assets_inside->contents as $assets_inside_2)
                            {
                                $typeID_sub_sub = $assets_inside_2['typeID'];
                                $quantity_sub_sub = $assets_inside_2['quantity'];
                        
                                $i++;
                                  //  array_push($assetIDList, $typeID_sub_sub);
                                   // array_push($assetQList, $quantity_sub_sub);
                                $assetList[$i] = array("NULL", $idcharacter, $typeID_sub_sub, $quantity_sub_sub, $locationID);
                        
                                    //(int)$getItemValue = utils::mysqli_result(mysqli_query($con, "SELECT price_evecentral FROM item_price_data WHERE item_eve_iditem = '$typeID_sub_sub'"),0,0)*$quantity_sub_sub;
                                    //$networth = $networth + $getItemValue;
                                    
                                    if(isset($assets_inside_2->contents))
                                    {
                                            //foreach (assets->contents->contents)
                                        foreach($assets_inside_2->contents as $assets_inside_3)
                                        {
                                            $typeID_sub_sub_sub = $assets_inside_3['typeID'];
                                            $quantity_sub_sub_sub = $assets_inside_3['quantity'];
                        
                                            $i++;
                                           // array_push($assetIDList, $typeID_sub_sub_sub);
                                          //  array_push($assetQList, $quantity_sub_sub_sub);
                                            $assetList[$i] = array("NULL", $idcharacter, $typeID_sub_sub_sub, $quantity_sub_sub_sub, $locationID);
                        
                                            //(int)$getItemValue = utils::mysqli_result(mysqli_query($con, "SELECT price_evecentral FROM item_price_data WHERE item_eve_iditem = '$typeID_sub_sub_sub'"),0,0)*$quantity_sub_sub_sub;
                                            //$networth = $networth + $getItemValue;
    
                                        }
                                    }
                            }
                       }
                       }
                   }

               }
        

           //    var_dump($assetList);
            $values_asset = array();
               
            foreach($assetList as $vals)
            {
                $index_asset++;
                $values_asset[$index_asset] = "(" . $vals[0] . "," . $vals[1] . "," . $vals[2] .  "," . $vals[3] .  "," . $vals[4] . ")";
            }
            
            $values_assets =  implode(",",$values_asset);
                   // var_dump($values_asset);
                   // var_dump ($values_assets);
                  //Before inserting assets, delete all old data from this character
            $delete_previous_assets = mysqli_query($con, "DELETE FROM assets WHERE characters_eve_idcharacters = '$idcharacter'") 
                            or die(mysqli_error($con));
                    
            $insert_assets = mysqli_query($con, "INSERT INTO `trader`.`assets` (`idassets`, `characters_eve_idcharacters`, `item_eve_iditem`, `quantity`, `locationID`) VALUES $values_assets")
                            or die(mysqli_error($con));
                
            $networth = utils::mysqli_result(mysqli_query($con, 
                "SELECT SUM(assets.quantity * item_price_data.price_evecentral) AS grand_total "
                . "FROM assets "
                . "JOIN item_price_data "
                . "ON item_price_data.item_eve_iditem = assets.item_eve_iditem "
                . "WHERE assets.`characters_eve_idcharacters` =  '$idcharacter'"),0,0);
               }
               catch (\Pheal\Exceptions\PhealException $e) {
    echo sprintf(
        "an exception was caught! Type: %s Message: %s",
        get_class($e),
        $e->getMessage()
    );
}
		$day = date("Y-m-d");
            $getDates = mysqli_query($con, "SELECT days FROM calendar WHERE days = '$day'")
                or die(mysqli_error($con));
            
        while($days = mysqli_fetch_array($getDates, MYSQLI_ASSOC))
        {
            $date = $days['days'];
            //get sum of sales
            $getSalesSum = mysqli_query($con, "SELECT SUM(price_total) FROM transaction
                WHERE character_eve_idcharacter = '$idcharacter' AND transaction_type = 'Sell' AND date(time) = '$date'");
            $salesSumVal = utils::mysqli_result($getSalesSum, 0,0);
            
            //get sum of purchases
            $getPurchasesSum = mysqli_query($con, "SELECT SUM(price_total) FROM transaction
                WHERE character_eve_idcharacter = '$idcharacter' AND transaction_type = 'Buy' AND date(time) = '$date'");
            $purchasesSumVal = utils::mysqli_result($getPurchasesSum, 0,0);
            
            $getProfitsSum = mysqli_query($con, "SELECT SUM(profit_unit*quantity_profit) FROM profit WHERE date(timestamp_sell) = '$date'
                    AND characters_eve_idcharacters_OUT = '$idcharacter'");
            $profitsSumVal = utils::mysqli_result($getProfitsSum,0,0);
            
            $getMargin = mysqli_query($con, "select (sum(profit_total))/sum(price_unit_buy*profit_quantity)*100
                    from v_profit_details where character_sell_id = '$idcharacter'
                     AND DATE(time_sell) = '$date'")  or die (mysqli_error($con));
            $marginSumVal = utils::mysqli_result($getMargin,0,0);

                $addTotals = mysqli_query($con, "REPLACE INTO history (idhistory, characters_eve_idcharacters, date, total_buy, total_sell, total_profit, margin)
                VALUES(NULL, '$idcharacter', '$date', '$purchasesSumVal', '$salesSumVal', '$profitsSumVal', '$marginSumVal')")
                        or die (mysqli_error($con));
            
               // echo "Updated " . $idcharacter . " for " . $date . "<br>";
                
        }

            ////////////////////////////////////////   
            //Update character info (networth?, balance, broker relation level, accounting level, escrow total and sell total
            ////////////////////////////////////////
            $update_character = mysqli_query($con, "UPDATE `trader`.`characters` "
                . "SET `balance` = '$balance', "
                . "`escrow` = '$escrowTotal', "
                . "`total_sell` = '$sellOrderValueTotal', "
                . "`broker_relations` = '$level_broker', "
                . "`accounting` = '$level_acc', "
                . "`networth` = '$networth' "
                . "WHERE "
                . "`characters`.`eve_idcharacter` = '$idcharacter'")
                    or die(mysqli_error($con));
                    
                //////////////////////////   
                ///final transaction check
                ///////////////////////////
               /* var_dump($insert_transactions); 
                var_dump($update_standings_corp);
                var_dump($update_standings_faction);
                var_dump($update_character);
                var_dump($update_orders);
                var_dump($update_contracts);
                var_dump($insert_assets);*/
            if( 
                ($update_standings_faction || $newStandingsFaction == 0) && 
                ($update_standings_corp || $newStandingsCorp == 0) && 
                 $update_character && 
                ($update_orders || $newOrders == 0) &&
                ($update_contracts || $newContracts == 0) &&
                 $insert_assets && $addTotals
              )
            {
                mysqli_query($con, "COMMIT"); //inserts all data
                   echo " ...done"."\n";
                $newInfo = mysqli_query($con, "INSERT INTO `trader`.`new_info` "
                    . "(`characters_eve_idcharacters`, "
                    . "`contracts`, "
                    . "`orders`, "
                    . "`transactions`) "
                    . "VALUES "
                    . "('$idcharacter', "
                    . "'$new_c', "
                    . "'$newOrders', "
                    . "'$newTransactions') on duplicate key update"
                    . " contracts = '$newContracts', "
                    . "orders = '$newOrders', "
                    . "transactions = '$newTransactions'")
                            
                    or die(mysqli_error($con));
                    
            }
            else
            {
                mysqli_query($con, "ROLLBACK");
                echo "An error has occured while processing your request. Please try again";
                echo  "<meta http-equiv='refresh' content='3;URL=login.php'>";
                return;
            }

            }
            }

           
          
            
           
           
            $buy_stack = array();
            $sell_stack = array();
           
            $buy_list = mysqli_query($con, "SELECT * FROM transaction WHERE transaction_type = 'Buy' AND character_eve_idcharacter IN (SELECT character_eve_idcharacter FROM aggr WHERE user_iduser = (SELECT iduser FROM user WHERE username = '$username') ORDER BY character_eve_idcharacter) ORDER BY time asc") or die(mysqli_error($con));
            $sell_list = mysqli_query($con, "SELECT * FROM transaction WHERE transaction_type = 'Sell' AND character_eve_idcharacter IN (SELECT character_eve_idcharacter FROM aggr WHERE user_iduser = (SELECT iduser FROM user WHERE username = '$username') ORDER BY character_eve_idcharacter) ORDER BY time asc")  or die(mysqli_error($con));

            while($row = mysqli_fetch_array($buy_list))
            {
                array_push($buy_stack, array($row['idbuy'], $row['item_eve_iditem'], $row['quantity'], $row['time'], $row['price_unit']));
            }
           
            while($row2 = mysqli_fetch_array($sell_list))
            {
                array_push($sell_stack, array($row2['idbuy'], $row2['item_eve_iditem'], $row2['quantity'], $row2['time'], $row2['price_unit']));
            }
    
            $size_buy = sizeof($buy_stack);
            $size_sell = sizeof($sell_stack);
    
   
    
            for($i=0; $i<=$size_buy-1; $i++) //iterate BUY orders
            {
                $idbuy_b = $buy_stack[$i][0];
                $itemid_b = $buy_stack[$i][1];
                $quantity_b = $buy_stack[$i][2];
                $time_b = $buy_stack[$i][3];
                $price_unit_b = $buy_stack[$i][4];
               
                $quantity_b_calc = $buy_stack[$i][2];
               
                for($k=0; $k<=$size_sell-1; $k++)
                {
                    $idbuy_s = $sell_stack[$k][0];
                    $itemid_s = $sell_stack[$k][1];
                    $quantity_s = $sell_stack[$k][2];
                    $time_s = $sell_stack[$k][3];
                    $price_unit_s = $sell_stack[$k][4];
                    
                    if($itemid_s == $itemid_b && $time_s > $time_b && $quantity_b > 0) //we found a partial or total match
                    {
                        //$price_unit_s = (float)$price_unit_s; //add taxes
                        //$price_unit_b = (float)$price_unit_b;
                        //$quantity_b = (int)$quantity_b;
                        //$quantity_s = (int)$quantity_s;
                            
                        $sell_stack[$k][0] = "done_sell";
                        $sell_stack[$k][1] = "done_sell";
                        $sell_stack[$k][2] = $sell_stack[$k][2] - min($quantity_s, $quantity_b);
                        $sell_stack[$k][3] = "done_sell"; //remove the item from the sell array so it won't get counted again
                        $sell_stack[$k][4] = "done_sell";
                            
                            
                        $itemName = utils::mysqli_result(mysqli_query($con, "SELECT name FROM item WHERE eve_iditem = (SELECT item_eve_iditem FROM transaction WHERE idbuy= '$idbuy_b')"),0,0);
                        //$itemID = utils::mysqli_result(mysqli_query($con, "SELECT eve_iditem FROM item WHERE name = '$itemName'"),0,0);
                        $itemID = utils::mysqli_result(mysqli_query($con, "SELECT item_eve_iditem FROM transaction WHERE idbuy = '$idbuy_b'"),0,0);
                            
                            
                        $fromStation = utils::mysqli_result(mysqli_query($con, "SELECT name FROM station WHERE eve_idstation = (SELECT station_eve_idstation FROM transaction WHERE idbuy = '$idbuy_b')"),0,0);
                        $toStation = utils::mysqli_result(mysqli_query($con, "SELECT name FROM station WHERE eve_idstation = (SELECT station_eve_idstation FROM transaction WHERE idbuy = '$idbuy_s')"),0,0);
                          
                        //echo $fromStation;
                        $stationFromID = utils::mysqli_result(mysqli_query($con, "SELECT eve_idstation FROM station WHERE eve_idstation = (SELECT station_eve_idstation FROM transaction WHERE idbuy = '$idbuy_b')"),0,0);
                        $stationToID = utils::mysqli_result(mysqli_query($con, "SELECT eve_idstation FROM station WHERE eve_idstation = (SELECT station_eve_idstation FROM transaction WHERE idbuy = '$idbuy_s')"),0,0);      
                            
                        $price_total_b = $quantity_b_calc*$price_unit_b; //this variable does not get decremented
                        $price_total_s = $quantity_s*$price_unit_s;
                            
                        $date_buy = utils::mysqli_result(mysqli_query($con, "SELECT time FROM transaction WHERE idbuy = '$idbuy_b'"),0,0);
                        $date_sell = utils::mysqli_result(mysqli_query($con, "SELECT time FROM transaction WHERE idbuy = '$idbuy_s'"),0,0);
                            
                        $datestamp1 = strtotime($date_buy);
                        $datestamp2 = strtotime($date_sell);
                            
                        $characterBuy = utils::mysqli_result(mysqli_query($con, "SELECT name FROM characters WHERE eve_idcharacter = (SELECT character_eve_idcharacter FROM transaction WHERE idbuy ='$idbuy_b')"),0,0);
                        $characterSell = utils::mysqli_result(mysqli_query($con, "SELECT name FROM characters WHERE eve_idcharacter = (SELECT character_eve_idcharacter FROM transaction WHERE idbuy ='$idbuy_s')"),0,0);
                            
                        $characterBuyID = utils::mysqli_result(mysqli_query($con, "SELECT eve_idcharacter FROM characters WHERE eve_idcharacter = (SELECT character_eve_idcharacter FROM transaction WHERE idbuy ='$idbuy_b')"),0,0);
                        $characterSellID = utils::mysqli_result(mysqli_query($con, "SELECT eve_idcharacter FROM characters WHERE eve_idcharacter = (SELECT character_eve_idcharacter FROM transaction WHERE idbuy ='$idbuy_s')"),0,0);
                            
                            //calculate taxes
                            //include('taxcalc_1.php');
               
                        $taxcalc = new tax($stationFromID, $stationToID, $con, $characterSellID, "buy", "sell"); //for now all profits use the seller data
                                
                            $transTaxFrom = $taxcalc->calculateTaxFrom();
                            $transTaxTo = $taxcalc->calculateTaxTo();
                            $brokerFeeFrom = $taxcalc->calculateBrokerFrom();
                            $brokerFeeTo = $taxcalc->calculateBrokerTo();
                               // echo "found profit";
                            
                            $price_unit_b_taxed = $price_unit_b*$brokerFeeFrom*$transTaxFrom;
                            $price_total_b_taxed = $price_unit_b_taxed*min($quantity_b,$quantity_s);
                            $price_unit_s_taxed = $price_unit_s*$brokerFeeTo*$transTaxTo;
                            $price_total_s_taxed = $price_unit_s_taxed*min($quantity_s,$quantity_b);
                            
                           /* $brokerFeeFromVal = $priceFrom*($brokerFeeFrom-1);
                            $brokerFeeToVal = $priceTo*(1-$brokerFeeTo);
                            $transTaxToVal = $priceFrom*(1-$transTaxTo);
                            if($brokerFeeFromVal < 100) {$brokerFeeFromVal = 100;}
                            if($brokerFeeToVal < 100) {$brokerFeeToVal = 100;}*/    
                        $profit = ($price_unit_s_taxed - $price_unit_b_taxed) * min($quantity_b,$quantity_s);
                        $profit_unit = ($price_unit_s_taxed - $price_unit_b_taxed);
                        $margin = ($profit/$price_total_b_taxed)*100;
                             
                        $min = min($quantity_s, $quantity_b);
                 
                        $add_profit = mysqli_query($con, "insert ignore `trader`.`profit` (`idprofit`, `transaction_idbuy_buy`, `transaction_idbuy_sell`, `profit_unit`, `timestamp_buy`, `timestamp_sell`, `characters_eve_idcharacters_IN`, `characters_eve_idcharacters_OUT`, `quantity_profit`) "
                            . "VALUES (NULL, '$idbuy_b', '$idbuy_s', '$profit_unit', '$date_buy', '$date_sell', '$characterBuyID','$characterSellID', '$quantity_s' )"
                                        ) or die(mysqli_error($con));
                        
                        $quantity_b = $quantity_b - $quantity_s;
    
                    //echo mysqli_affected_rows($con);
                    }        
                } 
           }
            //$pheal = new Pheal($apikey, $vcode, $charid);
        }
    echo "end";
	
	include("autoexec_mailer_day.php");
    
?>

