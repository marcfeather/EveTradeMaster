<?php
session_start();
require_once ('scripts/class/content.php');
?>
<!DOCTYPE html>
    <html lang="en">
    <link href="../bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
    <!-- Custom Fonts -->
    <link href="../bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <!-- DataTables CSS -->
    <link href="../bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css" rel="stylesheet">
    <!-- DataTables Responsive CSS -->
    <link href="../bower_components/datatables-responsive/css/dataTables.responsive.css" rel="stylesheet">

<?php
    
    $title = "Logout";
    $content = new content($title);
    $content->drawMeta($title);
    
?>
</head>
<body>
    
<?php
    $message = "Logout";
    $content->form_container($message);
    session_destroy();
	unset($_COOKIE['user']);
    setcookie('user', '', time() - 3600, '/');

	unset($_COOKIE['password']);
    setcookie('password', '', time() - 3600, '/');
    echo "You have logged out";
    echo "<meta http-equiv='refresh' content='1; url=../index.php'>";
    
?>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-body">
<?php
        $content->drawFooter();
?>
        </div>
    </div>
    
</body>
</html>