<?php
require_once('tax.php');
require_once('link.php');

    $stationFromID = 60003760;
    $stationToID = 60008494;
    $link = new link();
    $con = $link->connect();
    $character_get = 95060857;
    $transFrom = "buy";
    $transTo = "sell";
    

    $taxcalc = new tax($stationFromID, $stationToID, $con, $character_get, $transFrom, $transTo);
   /* echo ($taxcalc->getCorpOwnerIDFromStation());
    
    echo ($taxcalc->getCorpOwnerIDToStation());
    
    echo ($taxcalc->getFactionOwnerIDFromStation($taxcalc->getCorpOwnerIDFromStation()));
    
    echo ($taxcalc->getFactionOwnerIDToStation($taxcalc->getCorpOwnerIDToStation()));
    
    echo $taxcalc->getFromCorpStanding($taxcalc->getCorpOwnerIDFromStation());
    
    echo $taxcalc->getToCorpStanding($taxcalc->getCorpOwnerIDToStation());
 
    echo ($taxcalc->getFromFactionStanding($taxcalc->getFactionOwnerIDFromStation($taxcalc->getCorpOwnerIDFromStation())));
    
    echo ($taxcalc->getToFactionStanding($taxcalc->getFactionOwnerIDToStation($taxcalc->getCorpOwnerIDToStation())));
    
    echo $taxcalc->getBrokerLevel();
    echo $taxcalc->getAccountingLevel();*/
    
    echo $taxcalc->calculateBrokerFrom();
    echo "<br>";
    echo $taxcalc->calculateBrokerTo();
      echo "<br>";
    echo $taxcalc->calculateTaxTo();