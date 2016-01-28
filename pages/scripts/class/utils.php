<?php
class utils{ //this static class contains misc. functions needed to operate. It does not require instantiation.
    
    public static function mysqli_result($result, $row, $field = 0) //used to quickly retrieve the first result in mysqli queries
    {
        // Adjust the result pointer to that specific row
        $result->data_seek($row);
        // Fetch result array
        $data = $result->fetch_array();
        return $data[$field];
    }
    
    public static function formating_profit($value) //used for conditional formating of profits values
    {
        if($value > 20)
        {
            $color = "#32CD32";
        }
        else if ($value >10)
        {
            $color = "#FFA500";
        }
        else if ($value > 5)
        {
            $color = "#B8860B";
        }
        else
        {
            $color = "#FF4500";
        }
        $output = "<font color='$color'>".$value."</font>";
        return $output;
    }
    
    public static function formating_type($value) //used for conditional formating for transaction types
    {
        if($value == 'Buy')
        {
            $color = "#7FFF00";
        }
        else if ($value = 'Sell')
        {
            $color = "#0000FF";
        }
        $output = "<font color='$color'>".$value."</font>";
        return $output;
    }
    
    //used to set a cookie to determine screen size and adjust fusionchart graphs size (since they're not responsive)
    public static function setWidthCookie() 
    {
?>      <script> 
        var width = screen.width;
        var height = screen.height;
        // window.location.href = "testres.php?width=" + width + "&height=" + height; 
        document.cookie="width="+ width;
        </script>
<?php
    }
    
    //This function is used to check locationID's wether they refer to stations or solar systems
    public static function startsWith($haystack, $needle) 
    { 
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    
    }
}

?>

