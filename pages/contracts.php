<?php
require_once ('scripts/session.php');
require_once('bootstrapper.php');
?>
<html lang="en">
    <script src="../bower_components/datatables/media/js/jquery.dataTables_contracts.min.js"></script>
     <script src="../bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js"></script>
     
<?php

    function drawContractsTable($con, $getContracts, $tablename)
    {
?>
             <div class="dataTable_wrapper">
          
                <table class="table table-striped table-bordered table-hover" id="<?php echo $tablename?>">
                    <thead>
                        <tr>
                           <th>Creation</th>
                           <th>Issuer</th>
                           <th>Acceptor</th>
                                           
                           <th>Avail.</th>
                           <th>Price/Reward</th>
                           <th>Type</th>
                           <th>Station</th>   
                        </tr>
                    </thead>
                     <tbody>
                                        
    <?php
        while($contracts = mysqli_fetch_array($getContracts))
        {
            $issuerID = $contracts['issuer_id'];
            $acceptorID = $contracts['acceptor_id'];
            $status = $contracts['status'];
            $availability = $contracts['availability'];
            $price = number_format($contracts['price']);
            $reward = number_format($contracts['reward']);
            $price_reward = max($price,$reward);
            $collateral = number_format($contracts['colateral']);
            $stationFrom = $contracts['fromStation_eve_idstation'];
            $stationTo = $contracts['toStation_eve_idstation'];
            $type = $contracts['type'];
            $date = $contracts['creation_date'];
            $stationNameFrom = utils::mysqli_result(mysqli_query($con, "SELECT name FROM station WHERE eve_idstation = '$stationFrom'"),0,0);
            $stationNameTo = utils::mysqli_result(mysqli_query($con, "SELECT name FROM station WHERE eve_idstation = '$stationTo'"),0,0);
            
            if($availability == 'Public') {$iconAv = "<i class='fa fa-user fa-fw'></i>";}
                else if ($availability == 'Private') {$iconAv = "<i class='fa fa-unlock-alt fa-fw '></i>";}
                
            if($type == 'Courier') {$iconType = "<i class='fa fa-truck fa-fw'></i>";}
                else if ($type == 'ItemExchange') {$iconType = "<i class='fa fa-euro fa-fw'></i>";}
                    else if ($type == 'Loan') {$iconType = "<i class='fa fa-credit-card fa-fw'></i>";}
                        else if ($type == 'Auction') {$iconType = "<i class='fa fa-bank fa-fw'></i>";}
                
            
            if($issuerID != 0) //if there's a valid issuer search in DB for name
            {
            $search_issuer_name = mysqli_query($con, "SELECT name FROM characters_public WHERE eve_idcharacters = '$issuerID'") or die(mysqli_error($con));
                
                if(mysqli_num_rows($search_issuer_name) == 1) 
                {
                    $issuerName = utils::mysqli_result($search_issuer_name,0,0);
                    $issuerPic = "https://image.eveonline.com/Character/".$issuerID."_32.jpg";
                }
                else //if name is not found, query the public API
                {
                $getIssuerName = "https://api.eveonline.com/eve/CharacterName.xml.aspx?ids=".$issuerID;
                $xml = simplexml_load_file($getIssuerName);
                
                    foreach ($xml->result->rowset->row as $r) //Insert newfound name in DB
                    {
                    $issuerName = $r['name'];
                    $insert_name = mysqli_query($con, "INSERT INTO `trader`.`characters_public` (`eve_idcharacters`, `name`) VALUES ('$issuerID', '$issuerName')");
                    $issuerPic = "https://image.eveonline.com/Character/".$issuerID."_32.jpg";
                    }
                }
            }
             else
             {
                $issuerName = "n/a";
                $issuerPic = "";
             }
              
        
            if($acceptorID != 0) //if there's a valid issuer search in DB for name
            {
            $search_acceptor_name = mysqli_query($con, "SELECT name FROM characters_public WHERE eve_idcharacters = '$acceptorID'") or die(mysqli_error($con));
                
                if(mysqli_num_rows($search_acceptor_name) == 1) 
                {
                    $acceptorName = utils::mysqli_result($search_acceptor_name,0,0);
                    $acceptorPic = "https://image.eveonline.com/Character/".$acceptorID."_32.jpg";
                }
                else //if name is not found, query the public API
                {
                $getacceptorName = "https://api.eveonline.com/eve/CharacterName.xml.aspx?ids=".$acceptorID;
                $xml = simplexml_load_file($getacceptorName);
                
                    foreach ($xml->result->rowset->row as $r) //Insert newfound name in DB
                    {
                    $acceptorName = $r['name'];
                    $insert_name = mysqli_query($con, "INSERT INTO `trader`.`characters_public` (`eve_idcharacters`, `name`) VALUES ('$acceptorID', '$acceptorName')");
                    $acceptorPic = "https://image.eveonline.com/Character/".$acceptorID."_32.jpg";
                    }
                }
            }
             else
             {
                $acceptorName = "n/a";
                $acceptorPic = "";
             }
             
             echo "<tr><td>".$date."</td><td>". $issuerName ."</td>".
                      "<td>". $acceptorName ."</td>".
                    
                      "<td>". $iconAv . $availability."</td>".
                      "<td align = 'right'>". $price_reward."</td>".
                      "<td>". $iconType . $type. "</td>".
                      "<td>". $stationNameFrom . "</td></tr>";
        }
                                        ?>      
                                    </tbody>
                                </table>
                            </div>
<?php
    }
    //new contracts (last X entries) query
    
      if (isset($_GET['new']))
        {
          $new = mysqli_real_escape_string($con,$_GET['new']);
          $getNewContracts = mysqli_query($con, "SELECT * FROM contracts WHERE characters_eve_idcharacters = '$character_get' AND status = 'outstanding' ORDER BY eve_idcontracts DESC LIMIT $new");
        }

    //active/inactive selection handler
    if(isset($_GET['typea']) && $_GET['typea'] != 'all')
    {
        $typea= mysqli_real_escape_string($con,$_GET['typea']);
        $getActiveContracts = mysqli_query($con, "SELECT * FROM contracts WHERE characters_eve_idcharacters = '$character_get' AND 
            status = 'outstanding' AND type = '$typea'")
            or die(mysqli_error($con));
    }
    else
    {
        $typea = "all";
        $getActiveContracts = mysqli_query($con, "SELECT * FROM contracts WHERE characters_eve_idcharacters = '$character_get' AND 
            status = 'outstanding'")
            or die(mysqli_error($con));
    }
    
    
    if(isset($_GET['typei']) && $_GET['typei'] != 'all')
    {
        $typei= mysqli_real_escape_string($con,$_GET['typei']);
        $getInactiveContracts = mysqli_query($con, "SELECT * FROM contracts WHERE characters_eve_idcharacters = '$character_get' AND 
            status != 'outstanding' AND type = '$typei'")
            or die(mysqli_error($con));
    }
    else
    {
        $typei = "all";
        $getInactiveContracts = mysqli_query($con, "SELECT * FROM contracts WHERE characters_eve_idcharacters = '$character_get' AND
            status != 'outstanding'")
            or die(mysqli_error($con));
    }
    
    $title = "Contracts";
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
    $section = "Contracts";
    $content->drawHeader($getCharacterPortrait, $characterName, $section);
?>
    <div class="row">
<?php
    $content->columns_def();
?>
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
    $contracts_dropdown = new dropdown();
        $contracts_dropdown->setTitle("Contract type");
        $contracts_dropdown->setCharacter($character_get);
        $contracts_dropdown->setTargetURL("contracts.php");

            $all_a = "typea=all&typei=".$typei;
            $exch_a = "typea=ItemExchange&typei=".$typei;
            $cour_a = "typea=Courier&typei=".$typei;
            $loan_a = "typea=Loan&typei=".$typei;
            $auc_a = "typea=Auction&typei=".$typei;
            
        $contracts_dropdown->addOption('All', $all_a);
        $contracts_dropdown->addOption("ItemExchange", $exch_a );
        $contracts_dropdown->addOption("Courier", $cour_a );
        $contracts_dropdown->addOption("Loan", $loan_a );
        $contracts_dropdown->addOption("Auction", $auc_a );
        
        $contracts_dropdown->renderDropdown();
        
        echo "Active ";

        if(isset($_GET['new']))
        {
            echo " - " .$new . " new contracts since your last visit. Use the dropdown at the left to view all data.";
        }
        else
        {
            
        if(isset($_GET['typea']))
            {
            $filter_active = $_GET['typea'];
            }
            else 
                {
                $filter_active = "all";
                }

        if(isset($filter_active) && $filter_active != "")
            {
            echo $filter_active . " contracts";
            };
         }
?>
        </div>
    <div class="panel-body">
<?php
    if (isset($_GET['new']))
        {$new = $_GET['new'];
        $getActiveContracts = $getNewContracts;
        }
    drawContractsTable($con, $getActiveContracts, "dataTables-contracts");
?>
                           </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
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
        $contracts_dropdown2 = new dropdown();
        $contracts_dropdown2->setTitle("Contract type");
        $contracts_dropdown2->setCharacter($character_get);
        $contracts_dropdown2->setTargetURL("contracts.php");

            $all_i = "typea=".$typea."&typei=all";
            $exch_i = "typea=".$typea."&typei=ItemExchange";
            $cour_i = "typea=".$typea."&typei=Courier";
            $loan_i = "typea=".$typea."&typei=Loan";
            $auc_i = "typea=".$typea."&typei=Auction";
            
        $contracts_dropdown2->addOption('All', $all_i);
        $contracts_dropdown2->addOption("ItemExchange", $exch_i);
        $contracts_dropdown2->addOption("Courier", $cour_i );
        $contracts_dropdown2->addOption("Loan", $loan_i );
        $contracts_dropdown2->addOption("Auction", $auc_i );
        
        $contracts_dropdown2->renderDropdown();
      
        echo "Inactive ";
                         
            if(isset($_GET['typei']))
                {
                $filter_inactive = $_GET['typei'];
                }
                   else 
                       {
                       $filter_inactive = "all";
                       }

           if(isset($filter_inactive) && $filter_inactive != "")
                {
                echo $filter_inactive . " contracts";
                };
?>
                        </div>
    <div class="panel-body">
<?php
    drawContractsTable($con, $getInactiveContracts, "dataTables-contracts2");
?>
                </div>
                        <!-- /.panel-body -->
        </div>
                    <!-- /.panel -->
    </div>
                <!-- /.col-lg-12 -->
 </div>
<?php
    $content->drawFooter();
?>    
    </div>
            <!-- /.row -->
    </div>
        <!-- /#page-wrapper -->
    </div>
    <!-- this is for the table ordering and initialization-->
    <script>
    $(document).ready(function() {
        $('#dataTables-contracts').DataTable({
                responsive: true,
                "order": [[ 0, "desc" ]],
                "lengthMenu": [[50, 100, 200], [50, 100, 200]]
        });
    });
    </script>
    
    <script>
    $(document).ready(function() {
        $('#dataTables-contracts2').DataTable({
                responsive: true,
                "order": [[ 0, "desc" ]],
                "lengthMenu": [[50, 100, 200], [50, 100, 200]]
        });
    });
    </script>
    
</body>

</html>