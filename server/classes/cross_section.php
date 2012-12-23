<?php

/**
  * Represents a cross section of any type.
  * 
  * Abstract class.
  *
  * @param mixed[] $array Array structure to count the elements of.
  *
  * @return int Returns the number of elements.
  */
  
  abstract class CrossSection{
      
    /**
      * Get second moment of area about the X-X axis
      * 
      */
    abstract protected function getIxx();
    
    
    /**
      * Get second moment of area about the Y-Y axis
      * 
      */
    abstract protected function getIyy();
      
      
      
      
      
      
  }
  
  error_log("hi");
  
  ?>