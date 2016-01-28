<?php
require_once('class/link.php');
    $link = new link();
    $con = $link->connect();

 $q=$_GET['q'];
 $my_data=mysqli_real_escape_string($con, $q);
 //$mysqli=mysqli_connect('localhost','username','password','databasename') or die("Database Error");
 $sql="SELECT name FROM station WHERE name LIKE '$my_data%' ORDER BY name";
 $result = mysqli_query($con,$sql) or die(mysqli_error());

 if($result)
 {
  while($row=mysqli_fetch_array($result))
  {
   echo $row['name']."\n";
  }
 }
?>

