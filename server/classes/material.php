<?php

/**
  * Represents a material of any type.
  * 
  * @param mixed[] $array Array structure to count the elements of.
  *
  * @return int Returns the number of elements.
  */
  
  abstract class Materials{
    
    // Determines when class is ready for use, i.e. it is fully populated.
    private $ready = false;
    
    // Display name of this material
    private $name = "";
    
    // Young's modulus of this material
    private $E = 0;
    
    // Shear modulus of this material
    private $G = 0;
    
    // Density
    // [weight/length^3]
    private $density = 0;
    
    // Yield stress
    // 0 for brittle (non-ductile) material
    private $sigY = 0;
    
    //Ultimate tensile strength
    private $sigUlt = 0;
    
    // Stress-strain relationship
    // array of {stress,strain} arrays/objects
    private $stressStrain = array();
    
    
    
    
      
      
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
      
  }