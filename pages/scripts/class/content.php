<?php
define('APP_DIR', 'C:\Users\Starkey\Dropbox\ht\traderv3');
class content{
    
    private $character;
    
    function __construct() {
        if(isset($_GET['character']))
        {
        $this->character = $_GET['character'];
        }
    }
    
    public function drawFooter()
    { //https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=E92PVNRT3L9EQ
?>  <div class ="panel-body">
    <p align='center'>
        © Eve Trade Master BETA 2015 - design and development by <a href= "https://gate.eveonline.com/Profile/Nick%20Starkey" target='_blink'>Nick Starkey</a><br> 
        Eve Online, the Eve logo and all associated logos and designs are intellectual property of CCP hf, <br> 
        and are under copyright. That means copying them is not right. <br>
        
        <a href="../index.html">Home </a> | 
        <?php if (isset($this->character)) { ?><a href ="submit.php?character=<?php echo $this->character?>"><?php } ?> Feedback & Report a bug </a>|
        <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=E92PVNRT3L9EQ" target='_blank' >Donate</a><br>
		<b>NEW! Help shape ETM's future development by submitting a brief <a href='http://goo.gl/forms/Oke20Mr1cQ' target=_blank>survey</a> (1-2 minutes)</b><br>
		</p>

    </div>
<?php
    }
    
    public function drawMeta($title)
    {
?>
    <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Eve Online trading/profit and asset management app" />
    <meta name="author" content="">
	

    <title><?php echo $title; ?></title>

<?php
    }
    
    public function drawNav($accountBalance, $networth, $sellOrders, $escrow, $username, $character_get, $getCharacterList)
    {
?>
    <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                   
                </button>
                <a class="navbar-brand" href="index.html"></a>
            </div>
            
            <!-- /.navbar-header -->
            <ul class="nav navbar-top-links navbar-right">
                
                <img src='../Types/isk.gif' title="Amount of ISK in your wallet"><b>Wallet:</b> <?php echo number_format($accountBalance). " ISK " ?> 
                <img src='../Types/23_32.png' title = "Aproximate value of your assets"><b>Assets:</b> <?php echo number_format($networth). " ISK " ?> 
                <img src='../Types/2354_32.png' title = "Aproximate value of your sell orders"><b>Sell Orders:</b> <?php echo number_format($sellOrders). " ISK " ?>
                <img src='../Types/2244_32.png' title = "Amount of ISK you have tied to buy orders"><b>Escrow:</b> <?php echo number_format($escrow). " ISK  " ?>
                <a href = "select.php"><i class="fa fa-refresh fa-fw" title ="Refresh data"></i></a>
              
            
                Welcome, <?php echo $username?>!
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-user fa-fw"></i>  <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <li><a href="select.php"><i class="fa fa-refresh fa-fw"></i> Refresh</a>
                        </li>
                        <li><a href="settings_password.php?character=<?php echo $character_get?>"><i class="fa fa-lock fa-fw"></i> Change password</a>
                        </li>
                        <li><a href="settings_email.php?character=<?php echo $character_get?>"><i class="fa fa-envelope-o fa-fw"></i> Change e-mail</a>
                        </li>
                        <li><a href="settings_reports.php?character=<?php echo $character_get ?>"><i class="fa fa-print fa-fw"></i> Reports</a>
                        </li>
                        <li><a href="#"><i class="fa fa-css3 fa-fw"></i> Themes (soon™)</a>
                        </li>
                        <li class="divider"></li>
                        <li><a href="logout.php"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                        </li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
            </ul>
            <!-- /.navbar-top-links -->
            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
					<b><p align='center'><a href= "../"><img src="../assets/logo-beta.png"></a></b></p>
            
                        
                        <li>
                            <a href="dashboard.php?character=<?php echo $character_get ?>"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
	
                        </li>
                        <li>
                            <a href="#"><i class="fa fa-bar-chart-o fa-fw"></i> Profit Tracking<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                
                                <li>
                                    <a href="profit.php?character=<?php echo $character_get ?>">Detailed Profits</a>
                                </li>
                                <li>
                                    <a href="statistics.php?character=<?php echo $character_get ?>">Statistics</a>
                                </li>
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
                         <li>
                            <a href="marketorders.php?character=<?php echo $character_get ?>"><i class="fa fa-edit fa-fw"></i> Market Orders</a>
                        </li>
                       
                         <li>
                            <a href="transactions.php?character=<?php echo $character_get ?>"><i class="fa fa-table fa-fw"></i> Market Transactions</a>
	
                        </li>   
                         
                        <li>
                           <a href="contracts.php?character=<?php echo $character_get?>"><i class="fa fa-file-o fa-fw"></i> Contracts</a>
                        </li>   
              
                        <li>
                            <a href="assets.php?character=<?php echo $character_get ?>"><i class="fa fa-suitcase fa-fw"></i> Assets</a>
                        </li>
                    
                        
                        <li>
                            <a href="regionaltrade.php?character=<?php echo $character_get ?>"><i class="fa fa-files-o fa-fw"></i> Regional Trade Assistant</span></a>
                           
                            <!-- /.nav-second-level -->
                        </li>
                            <li>
                            <a href="#"><i class="fa fa-wrench fa-fw"></i> API Key Management<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                 <li>
                                    <a href="api_add.php?character=<?php echo $character_get ?>">Add Character/API Key</a>
                                </li>
                                <li>
                                    <a href="api_remove.php?character=<?php echo $character_get ?>">Remove Character/API Key</a>
                                </li>
                               
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
                        <li>
                            <a href="#"><i class="fa fa-sitemap fa-fw"></i> Character List<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <?php
                                while ($row = mysqli_fetch_array($getCharacterList, MYSQLI_ASSOC))
                                {
                                    $name = $row['name'];
                                    $idchar = $row['character_eve_idcharacter'];
                                    echo "<li>";
                                    echo "<a href='dashboard.php?character=$idchar'>". $name. "</a>";
                                    echo "</li>";
                                }
                                
                                ?>
                                    <!-- /.nav-third-level -->
                                </li>
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
                    </ul>
                </div>
                <!-- /.sidebar-collapse --><br><!--test-->
            </div>
            <!-- /.navbar-static-side -->
        </nav>
<?php
    }
    
    public function drawHeader($getCharacterPortrait, $characterName, $section)
    {
?>
        
            <div class="row">
                
                <div class="col-lg-12">
                    
                    <h1 class="page-header"><img src=" <?php echo $getCharacterPortrait ?>"> <?php echo $characterName ?>'s <?php echo $section ?> </h1>
	 
                </div>
                <!-- /.col-lg-12 -->
            </div>
            
<?php    
    }

    
    public function drawDashTable($character_get, $getTransactions)
    {
?>
        
                        <!-- /.panel-heading -->
            <div class="panel-body">
                 <div class="dataTable_wrapper">
                    <table class="table table-striped table-bordered table-hover" id="dataTables-dashboard">
                       <thead>
                            <tr>
                            <th>Item</th>
                            <th>Time</th>
                            <th>Type</th>
                            <th>Q</th>
                            <th>Total</th>
                            <th>Station</th>
                            </tr>
                        </thead>
                        <tbody>
                                               
                                                <?php
                                                while($pow = mysqli_fetch_array($getTransactions, MYSQLI_ASSOC))
                                                {
                                                    $typeid = $pow['item_id'];
                                                    $name = $pow['item_name'];
                                                    $time = $pow['time'];
                                                    $transaction = $pow['transaction_type'];
                                                    $quantity = $pow['quantity'];
                                                    $price_total = $pow['price_total'];
                                                    $imgpath = "../Types/".$typeid."_32.png";
                                                    $stationName = $pow['station_name'];
                                                    
                                                    echo "<tr><td>" . "<img src='$imgpath'>" . $name . "</td>".
                                                   // "<td>"  "</td>" .
                                                    "<td>" . $time . "</td>" .
                                                    "<td>" . utils::formating_type($transaction) . "</td>" . 
                                                    "<td align = 'right'>" . $quantity . "</td>" . 
                                                    "<td align = 'right'>" . number_format($price_total) . "</td>".
                                                    "<td>" . $stationName . "</td></tr>";
                                                }
                                                ?>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- /.table-responsive -->
                                <!-- /.col-lg-4 (nested) -->
                                <!-- /.col-lg-8 (nested) -->
                            <!-- /.row -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <?php $this->drawFooter();?>
                        </div>
                        <!-- /.panel-body -->
                    
                    <!-- /.panel -->
                </div>
<?php
    }
    

    
    public function dropdownMarketOrders_active($character_get, $typei)
    {
?>
            
               <div class="pull-right">
                    <div class="btn-group">          
                    </div>
                </div>
            <div class="panel-heading">
        <i class="fa fa-check fa-fw"></i>
    <div class="pull-right">
        <div class="btn-group">
            <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                Order Type
                <span class="caret"></span>
            </button>
                <ul class="dropdown-menu pull-right" role="menu">
                    <li><a href="marketorders.php?character=<?php echo $character_get ?>&typea=sell&typei=<?php echo $typei?>">Sell Orders</a>
                    </li>
                    <li><a href="marketorders.php?character=<?php echo $character_get ?>&typea=buy&typei=<?php echo $typei?>">Buy Orders</a>
                    </li>
                </ul>
        </div>
    </div>        
<?php
    }
    
    public function dropdownMarketOrders_inactive($character_get, $typei)
    {
?>
            <div class="col-lg-12">
            <div class="panel panel-default">
               <div class="pull-right">
                    <div class="btn-group">          
                    </div>
                </div>
            <div class="panel-heading">
        <i class="fa fa-check fa-fw"></i>
    <div class="pull-right">
        <div class="btn-group">
            <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                Order Type
                <span class="caret"></span>
            </button>
                <ul class="dropdown-menu pull-right" role="menu">
                    <li><a href="marketorders.php?character=<?php echo $character_get ?>&typea=sell&typei=<?php echo $typei?>">Sell Orders</a>
                    </li>
                    <li><a href="marketorders.php?character=<?php echo $character_get ?>&typea=buy&typei=<?php echo $typei?>">Buy Orders</a>
                    </li>
                </ul>
        </div>
    </div>        
<?php
    }
    

    
    public function columns_def(){
?>
          <div class="col-lg-3 col-md-6">         
        </div>
            <div class="col-lg-3 col-md-6">  
            </div>
               
        <div class="col-lg-3 col-md-6">        
        </div> 
<?php
    }
    
    public function panel_default()
    {
?>
    <div class="col-lg-3 col-md-6">         
        </div>
            <div class="col-lg-3 col-md-6">  
            </div>
               
        <div class="col-lg-3 col-md-6">        
    </div> 
<?php
    }
    
    public function form_container($message)
    {
?>
         <div class="container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo $message?></h3>
                    </div>
                    <div class="panel-body">
<?php
    }
    
    public function drawPanel($icon)
    {
?>            
        <div class="panel panel-default">
        <div class="panel-heading">
        <i class="fa <?php echo $icon?> fa-fw"></i>
<?php
    }
    

    


    
    
    
    
                }
