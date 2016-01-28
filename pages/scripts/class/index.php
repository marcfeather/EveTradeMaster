<?php 
include("class_lib.php"); 

    $stefan = new person('bob');
    $stefan->set_name("Stefan Mischook");
    
    $jimmy = new person('nick jimmy');
    
    echo "Stefan's full name: " . $stefan->get_name(); echo "Nick's full name: " . $jimmy->get_name(); 
    
   // $stefan = new person("Stefan Mischook");
    $stefan->pinn_number;

?>
