<?php

require_once "material.php";

/**
  * Represents a material of any type.
  * 
  * @param mixed[] $array Array structure to count the elements of.
  *
  * @return int Returns the number of elements.
  */
  
 class Steel extends Material{
    
    private $steelDefaults = array(
        "poissonRatio"=>0.3,
        "E"=>210E9, //210 GPa
        "G"=>79E9,  //79 GPa
        "density"=>7800,  // kg/m&3
        "sigY"=>460E6,  // 460 MPa
        "sigUlt"=> 500E6,  // 500 MPa
        "epsUlt"=> 0.3,
        "coefThermExpan"=>13E-6,
        "name"=>"Default Steel"
        //"stressStrain" doesn't exist for standard steel
    );
    
    /**
      * Constructor
      * 
      * @param string $materialName Name of the library material to be represented. Else blank.
      * 
      */
    public function __construct( $input = "" ) {
    
      // Add steel-specific properties to allowedProperties.
      $this->allowedProperties = array_merge(array("epsUlt","sigY"),(array) $this->allowedProperties);
      //var_dump((array) $this->allowedProperties);
      /*
       * No material specified, return blank class to be filled later.
       */
      if($input == "") {
      
        //set default properties
        $properties = $this->steelDefaults;
      
      }
      else{
          
        $properties = $input;
        
      }
      
      $loadStatus = $this->loadFromArray($properties);
      if($loadStatus === true){
        return $this;
      }
      else{
        return false;
      }
      
    }
    
    
    /**
      * Load the properties of this material from an array of properties.
      * 
      * @param array $properties Load the properties of this material from an array of properties.
      * 
      */
    public function loadFromArray( $properties = false ) {
    
      if($properties == false) {
          return false;
      }
    
      foreach($properties as $key=>$value){
          
          //echo "hoo";
          //var_dump($this->allowedProperties);
        if(in_array($key,$this->allowedProperties)) {
          
          //if we wanted to check the types, we would do it here
          //echo "boo";
          //set the value of that property
          $this->$key = $value;
          
        }
          
      }
      
      return true;
      
      
    }  
    
  }
  $st = new Steel();
  
  var_dump($st);
  
  ?>