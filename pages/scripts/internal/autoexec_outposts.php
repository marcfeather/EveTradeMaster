<?php

//Updates all player made outpost names in the DB. This script runs once a day.
include('/var/www/html/pages/scripts/class/link.php');
include('/var/www/html/pages/scripts/vendor/autoload.php');
$link = new link();
$con = $link->connect();
    
    use Pheal\Pheal;

   $pheal_outposts = new Pheal();
   $response_outposts = $pheal_outposts->eveScope->ConquerableStationList();
   
   $index = -1;
   $list_final = array();
   //add parameters
    foreach($response_outposts->outposts as $outposts)
    {
        $index++;
        $idoutposts = $outposts['stationID'];
        $name = $outposts['stationName'];
        $eve_idsystem = $outposts['solarSystemID'];
        
        $list[$index] = array($idoutposts, $name, $eve_idsystem);
        array_push($list_final, $list[$index]); 
        
    }
    
        $i = -1;
    //var_dump($list_final);
        foreach ($list_final as $vals)
        {
            $i++;
            $values_list[$i] = "(" . "'" . mysqli_real_escape_string($con, $vals[0]) . "'" . "," . "'" . mysqli_real_escape_string($con, $vals[1]) ."'". "," . "'" . mysqli_real_escape_string($con, $vals[2]) . "'". "," . "'" . "1". "'". ")";
            
        }
            $values = implode(",",$values_list);
            
            //var_dump($values);
            
            
            $insert_outposts = mysqli_query($con, "INSERT IGNORE into `trader`.`station` (`eve_idstation`, `name`, `system_eve_idsystem`, `corporation_eve_idcorporation`) "
                    . "VALUES" . $values) 
                    or die(mysqli_error($con)); 
					
					if($insert_outposts)
					{
						echo "Updated successfully.";
					}
					else
					{
						echo "Error";
					}
    
?>



