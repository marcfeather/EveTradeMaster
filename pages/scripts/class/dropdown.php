<?php
class dropdown{
    
    public $title; //display tile
    
    public $character_get; //character to link
    
    public $options = array(); //dropdown values (GET and display name)
    
    public $targetURL; //destination link for each option (always the same in this context)
    
    public function __construct() {
        
    }
    
    public function setTitle($newTitle)
    {
        $this->title = $newTitle;
    }
    
    public function addOption($name, $uri)
    {
        $this->options[$name] = $uri;
      //  $this->options["uri"] = $uri;
    }
    
    public function setCharacter($character)
    {
        $this->character_get = $character;
    }
    
    public function setTargetURL($target)
    {
        $this->targetURL = $target;
    }
    
    public function renderDropdown()
    {
?>
        <div class="pull-right">
        <div class="btn-group">
            <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
<?php
    echo $this->title;
?>
        <span class="caret"></span>
            </button>
                <ul class="dropdown-menu pull-right" role="menu">
<?php

        foreach($this->options as $key => $value) //iterate trough the array we've set 
        {
        echo "<li><a href='";

            echo $this->targetURL . "?character=" . $this->character_get. "&". $value . "'>" . $key . "</a></li>"; 
        }
        echo "</ul></div></div>";   
    }
    
    
    
}


?>
