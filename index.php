<?php
include('link.php');
$link = new link();
$con = $link->connect();

$profit = mysqli_query($con, "SELECT sum(profit_unit*quantity_profit) AS value FROM profit");
$profit_result = mysqli_fetch_array($profit, MYSQLI_ASSOC);
$profitval = $profit_result['value'];

$transactions = mysqli_query($con, "SELECT count(idbuy) as value FROM transaction");
$trans_result = mysqli_fetch_array($transactions, MYSQLI_ASSOC);
$transval = $trans_result['value'];
?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <meta name="author" content="">
	
	<meta name="description" content="Eve Online trading/profit and asset management app" />

    <title>Eve Trade Master</title>

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/grayscale.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="http://fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic" rel="stylesheet" type="text/css">
    <link href="http://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body id="page-top" data-spy="scroll" data-target=".navbar-fixed-top">

    <!-- Navigation -->
    <nav class="navbar navbar-custom navbar-fixed-top" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse">
                    <i class="fa fa-bars"></i>
                </button>
                <a class="navbar-brand page-scroll" href="pages/register.php">
                    <i class="fa fa-play-circle"></i>  <span class="light">Sign</span> Up
                </a>
                <a class="navbar-brand page-scroll" href="pages/login.php">
                    <i class="fa fa-play-circle"></i>  <span class="light">Login</span>
                </a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse navbar-right navbar-main-collapse">
                <ul class="nav navbar-nav">
                    <!-- Hidden li included to remove active class from about link when scrolled up past about section -->
                    <li class="hidden">
                        <a href="#page-top"></a>
                    </li>
                 
                    <li>
                        <a class="page-scroll" href="#features">Features</a>
                    </li>
                    <li>
                        <a class="page-scroll" href="#gallery">Gallery</a>
                    </li>
                    <li>
                        <a class="page-scroll" href="#future">Upcoming</a>
                    </li>
                    <li>
                        <a class="page-scroll" href="#getstarted">Get started</a>
                    </li>
                    
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container -->
    </nav>

    <!-- Intro Header -->
    
    <header class="intro">
        <div class="intro-body">
            <div class="container">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        <section id="top">
                        <h1 class="brand-heading">EVE TRADE MASTER</h1>
                        <p class="intro-text">A simple, yet powerful Eve Online trading manager</p>
						<p class="intro-text">Keeping track of <?php echo number_format($transval) ?> transactions and <?php echo number_format($profitval) ?> ISK in profit!</p>
                        <a href="#features" class="btn btn-circle page-scroll">
                            <i class="fa fa-angle-double-down animated"></i>
                            
                        </a>
                         </section>
                    </div>
                </div>
            </div>
        </div>
    </header>
   

    <!-- About Section -->
    <section id="features" class="container content-section text-center">
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <h2>Features</h2>
                <p><i class="fa fa-check fa-fw"></i> Manage all your characters in one place</p>
                <p><i class="fa fa-check fa-fw"></i> Simulate earnings between different starsystems and regions</p>
                <p><i class="fa fa-check fa-fw"></i> Automatic profit tracking between characters</p>
                <p><i class="fa fa-check fa-fw"></i> Detailed graphs and statistics, also available by e-mail</p>
                <p><i class="fa fa-check fa-fw"></i> Contracts, orders, transactions and assets lists</p>
            </div>
        </div>
    </section>

    <!-- Download Section -->
    <section id="gallery" class="content-section text-center">
        <div class="download-section">
            <div class="container">
                <div class="col-lg-8 col-lg-offset-2">
                    <h2>Gallery</h2>
                    
		
		
<div id="myCarousel" class="carousel slide" style="width: 100%; margin: 0 auto">
  <div class="carousel-inner">
    <div class="item">
      <img src="img/1.png"
      alt="">
      <div class="carousel-caption">
         <h4>Dashboard</h4>

        <p></p>
      </div>
    </div>
    <div class="item">
       <img src="img/3.png"
      alt="">
      <div class="carousel-caption">
         <h4>Statistics</h4>

        <p></p>
      </div>
    </div>
    <div class="item active">
       <img src="img/2.png"
      alt="">
      <div class="carousel-caption">
         <h4>Profit Tracking</h4>

        <p></p>
      </div>
    </div>
         <div class="item">
      <img src="img/4.png"
      alt="">
      <div class="carousel-caption">
         <h4>Assets</h4>

        <p></p>
      </div>
    </div>
          <div class="item">
      <img src="img/5.png"
      alt="">
      <div class="carousel-caption">
         <h4>Trade simulator</h4>

        <p></p>
      </div>
    </div>
  </div> <a class="left carousel-control" href="#myCarousel" data-slide="prev">‹</a>

  <a
  class="right carousel-control" href="#myCarousel" data-slide="next">›</a>
</div>

                </div>
            </div>
        </div>
    </section>
    <section id="future" class="container content-section text-center">
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <h2>Upcoming features (comming soon™)</h2>
                <p><i class="fa fa-star fa-fw"></i> Full IGB compatibility</p>
                <p><i class="fa fa-star fa-fw"></i> More statistical data and graphs</p>
                <p><i class="fa fa-star fa-fw"></i> Regional Blueprint production simulator</p>
                <p><i class="fa fa-star fa-fw"></i> Corporation wallet API support</p>
                <p><i class="fa fa-star fa-fw"></i> Live market data CREST API support</p>
                <p><i class="fa fa-star fa-fw"></i> Aggregated assets and transactions</p>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="getstarted" class="container content-section text-center">
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <h2>Get started</h2>
                <p>ETM is 100% free, wether you're a simple daytrader or a market tycoon</p>
                <p>All you need is an API key with minimal permissions and you're good to go </p>
                
                <p>If you enjoy my work you can also donate via paypal or by sending ISK in-game to Nick Starkey</p>
                
                <ul class="list-inline banner-social-buttons">
                    <li>
                        <a href="pages/register.php" class="btn btn-default btn-lg"> <span class="network-name">Sign Up</span></a>
                    </li>
                    <li>
                        <a href="pages/login.php" class="btn btn-default btn-lg"> <span class="network-name">Login</span></a>
                    </li>
                    <li>
                        <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=E92PVNRT3L9EQ" target= "_blank" class="btn btn-default btn-lg"> <span class="network-name">Donate</span></a>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    
   

    <!-- Footer -->
    <footer>
        <div class="container text-center"><br><br>
            <p>Copyright &copy; ETM 2015. All rights reserved - Design and development by uplink42<br>
			<a href= 'https://www.reddit.com/r/Eve/comments/1p915i/what_you_need_to_know_before_playing_eve_online/' target= _blank>What is Eve Online?</a> | <a href='http://www.eveonline.com' target='_blank'>eveonline.com</a> |  <a href= 'https://secure.eveonline.com/trial/?invc=d230c784-13d6-4b36-92f9-a5a27b2d3929&action=buddy' target='_blank'>Play Eve!</a>
                <br>
            <br><h6>EVE Online, the EVE logo, EVE and all associated logos and designs are the intellectual property of CCP hf. All artwork, screenshots, characters, vehicles, storylines, world facts or other recognizable features of the intellectual property relating to these trademarks are likewise the intellectual property of CCP hf. EVE Online and the EVE logo are the registered trademarks of CCP hf. All rights are reserved worldwide. All other trademarks are the property of their respective owners. CCP hf. has granted permission to evetrademaster.com to use EVE Online and all associated logos and designs for promotional and information purposes on its website but does not endorse, and is not in any way affiliated with, evetrademaster.com. CCP is in no way responsible for the content on or functioning of this website, nor can it be liable for any damage arising from the use of this website.</h6>
        </div>
        
    </footer>

    <!-- jQuery -->
    <script src="js/jquery.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>

    <!-- Plugin JavaScript -->
    <script src="js/jquery.easing.min.js"></script>

  

    <!-- Custom Theme JavaScript -->
    <script src="js/grayscale.js"></script>

</body>

</html>
