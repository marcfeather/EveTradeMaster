<?php
class link{ //

    public function __construct()
    {
        $this->con = 0;//only here to create an object reference
    }
    
    public function connect()
    {
       $hostname = "localhost";
       $username = "";
       $password = "";
       $database = "trader";
        
        $this->con = mysqli_connect($hostname, $username, $password, $database);
        
        if($this->con)
        {
            return $this->con; //returns mysqli object
        }
        else
        {
            return "Could not connect to DB";
        }
    } 
    
    public function disconnect()
    {
        mysqli_close($this->con);
    } 
}

?>

