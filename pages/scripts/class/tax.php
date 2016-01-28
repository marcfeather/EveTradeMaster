<?php 
    use Pheal\Pheal;
    use Pheal\Core\Config;

class tax {
    
    public $stationFromID; //id from starting station
    public $stationToID; //id from destination station
    public $con; // db connection
    public $character_get; //character to fetch standings
    
    public $transFrom; //starting station transaction (buy, sell)
    public $transTo; //starting station transaction (buy, sell)
    
    //private $corpOwnerIDFromStation; //starting station corp owner id
    //private $corpOwnerIDToStation; //destination station  corp owner id
    //private $factionOwnerIDFromStation; //starting station faction owner id
    //private $factionOwnerIDToStation; //destination station faction owner id
    private $fromCorpStandingValue; //starting station corp standing 
    private $toCorpStandingValue; //destination station corp standing
    private $fromFactionStandingValue; //starting station faction standing
    private $toFactionStandingValue; //ending station faction standing
    
    private $level_broker; //broker relations skill lvl
    private $level_acc; //accounting skill lvl
    
    public $brokerFeeFrom;
    public $brokerFeeTo;
    
    public $transTaxFrom = 1; // transaction tax is only for selling
    public $transTaxTo;
    

    public function __construct($stationFromID, $stationToID, $con, $character_get, $transFrom, $transTo) 
    {
        $this->stationFromID = $stationFromID;
        $this->stationToID = $stationToID;
        $this->con = $con;
        $this->character_get = $character_get;
        $this->transFrom = $transFrom;
        $this->transTo = $transTo;
        
        $this->getFromCorpStanding($this->getCorpOwnerIDFromStation());
        $this->getToCorpStanding($this->getCorpOwnerIDToStation());
        $this->getFromFactionStanding($this->getFactionOwnerIDFromStation($this->getCorpOwnerIDFromStation()));
        $this->getToFactionStanding($this->getFactionOwnerIDToStation($this->getCorpOwnerIDToStation()));
        
        $this->getBrokerLevel();
        $this->getAccountingLevel();
    }
    
    public function getCorpOwnerIDFromStation()
    {
        return
        $corpOwnerIDFromStation = utils::mysqli_result(mysqli_query($this->con, "SELECT corporation_eve_idcorporation FROM station WHERE eve_idstation = '$this->stationFromID'"),0,0);
    }
    
    public function getCorpOwnerIDToStation()
    {
        return
        $corpOwnerIDToStation = utils::mysqli_result(mysqli_query($this->con, "SELECT corporation_eve_idcorporation FROM station WHERE eve_idstation = '$this->stationToID'"),0,0);             
    }
    
    public function getFactionOwnerIDFromStation($corpOwnerIDFromStation)
    {
        return
        $factionOwnerIDFromStation = utils::mysqli_result(mysqli_query($this->con, "SELECT faction_eve_idfaction FROM corporation WHERE eve_idcorporation = '$corpOwnerIDFromStation'"),0,0);          
    }
    
    public function getFactionOwnerIDToStation($corpOwnerIDToStation)
    {
        return
        $factionOwnerIDToStation = utils::mysqli_result(mysqli_query($this->con, "SELECT faction_eve_idfaction FROM corporation WHERE eve_idcorporation = '$corpOwnerIDToStation'"),0,0);
    }
    
    public function getFromCorpStanding($corpOwnerIDFromStation)
    {
         $fromCorpStanding = mysqli_query($this->con, "SELECT value 
                    FROM standings_corporation 
                    WHERE characters_eve_idcharacters = '$this->character_get' 
                    AND corporation_eve_idcorporation = '$corpOwnerIDFromStation'");
                    
                    if(mysqli_num_rows($fromCorpStanding) == 0)
                    {
                        return $this->fromCorpStandingValue = 0;
                    }
                    else
                    {
                        return $this->fromCorpStandingValue = utils::mysqli_result($fromCorpStanding,0,0);
                    }
        
    }
    
    public function getToCorpStanding($corpOwnerIDToStation)
    {
        $toCorpStanding = mysqli_query($this->con, "SELECT value 
                    FROM standings_corporation 
                    WHERE characters_eve_idcharacters = '$this->character_get' 
                    AND corporation_eve_idcorporation = '$corpOwnerIDToStation'");
            
                    if(mysqli_num_rows($toCorpStanding) == 0)
                    {
                        return $this->toCorpStandingValue = 0;
                    }
                    else
                    {
                        return $this->toCorpStandingValue = utils::mysqli_result($toCorpStanding,0,0);
                    }
    }
    
    public function getFromFactionStanding($factionOwnerIDFromStation)
    { 
        $fromFactionStanding = mysqli_query($this->con, "SELECT value
                    FROM standings_faction
                    WHERE characters_eve_idcharacters = '$this->character_get'
                    AND faction_eve_idfaction = '$factionOwnerIDFromStation'");        
                    
                    if(mysqli_num_rows($fromFactionStanding) == 0)
                    {
                        return $this->fromFactionStandingValue = 0;
                    }
                    else
                    {
                        return $this->fromFactionStandingValue = utils::mysqli_result($fromFactionStanding,0,0);
                    }
    }
    
    public function getToFactionStanding($factionOwnerIDToStation)
    {
        $toFactionStanding = mysqli_query($this->con, "SELECT value 
                    FROM standings_faction 
                    WHERE characters_eve_idcharacters = '$this->character_get' 
                    AND faction_eve_idfaction = '$factionOwnerIDToStation'");
            
                    if(mysqli_num_rows($toFactionStanding) == 0)
                    {
                        return $this->toFactionStandingValue = 0;
                    }
                    else
                    {
                        return $this->toFactionStandingValue = utils::mysqli_result($toFactionStanding,0,0);
                    }        
    }
    
    public function getBrokerLevel()
    {
        return
        $this->level_broker = utils::mysqli_result(mysqli_query($this->con, "SELECT broker_relations "
                    . "FROM characters "
                    . "WHERE eve_idcharacter = '$this->character_get'"),0,0);
    }
    
    public function getAccountingLevel()
    {
        return
         $this->level_acc = utils::mysqli_result(mysqli_query($this->con, "SELECT accounting "
                    . "FROM characters "
                    . "WHERE eve_idcharacter = '$this->character_get'"),0,0);
    }
    
    public function calculateBrokerFrom()
    {
        if($this->transFrom == 'buy')
                      {
                          return
                          $this->brokerFeeFrom = 1+((0.01 - 0.0005 * (float)$this->level_broker) / pow(2,(0.1400 * (float)$this->fromFactionStandingValue + 0.06000 * (float)$this->fromCorpStandingValue))); 
                      }
                      
                      else
                      {
                          return 1; //it's actually 0 but this makes our maths easier later
                      }
    }
    
    public function calculateBrokerTo()
    {
        if($this->transTo == 'sell')
                      {
                        return
                        $this->brokerFeeTo = 1-((0.01 - 0.0005 * (float)$this->level_broker) / pow(2,(0.1400 * (float)$this->toFactionStandingValue + 0.06000 * (float)$this->toCorpStandingValue)));   
                      }
                      else 
                      {
                       return 1;
                      }
    }
    
    public function calculateTaxFrom()
    {
        return $this->transTaxFrom;
    }
    
    public function calculateTaxTo()
    {
        return
        $this->transTaxTo = 1-((1.5-(0.1*1.5*$this->level_acc))/100); //returns in 1.x
    }
    
 
}
