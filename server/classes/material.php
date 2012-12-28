<?php

/**
  * Represents a material of any type.
  * 
  * @param mixed[] $array Array structure to count the elements of.
  *
  * @return int Returns the number of elements.
  */
  
  class Material{
    
    // Determines when class is ready for use, i.e. it is fully populated.
    protected $ready = false;
    
    // Display name of this material
    protected $name = "";
    
    // Young's modulus of this material
    protected $E = 0;
    
    // Shear modulus of this material
    protected $G = 0;
    
    // Density
    // [weight/length^3]
    protected $density = 0;
    
    //Ultimate tensile strength
    protected $sigUlt = 0;
    
    // Stress-strain relationship
    // array of {stress,strain} arrays/objects
    protected $stressStrain = array();
    
    // Coefficient of thermal expansion
    protected $coefThermExpan = 0;
    
    // Poisson ratio
    protected $poissonRatio = 0;
    
    // allowed properties
    // these are the properties which can be set.
    protected $allowedProperties = array("name","E","G","density","sigUlt","stressStrain","coefThermExpan","poissonRatio");
    
      
    /**
      * Constructor
      * 
      * @param string $materialName Name of the library material to be represented. Else blank.
      * 
      */
    public function __construct( $materialName = "" ) {
    
      /*
       * No material specified, return blank class to be filled later.
       */
      if($materialName == "") {
      
        return $this;
      
      }
      
    }  
      
    /**
      * Check if this material is ready to be used. 
      * Should be internally checked prior to returning anything else.
      * 
      * @returns boolean Checks if this material is ready to be used.
      * 
      */
    public function isReady() {
        
      if($this->ready === true){
        return true;
      }
      else{
        return false;
      }
        
    }
    
   
      
  }
  
    
?>