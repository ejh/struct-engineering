<?php

/**
  * Represents a cross section of any type.
  * 
  * @param mixed[] $array Array structure to count the elements of.
  *
  * @return int Returns the number of elements.
  */
  
  class CrossSection{
      
    private $ready = false;
    
    //parts holds all the shapes that make up the cross-section
    private $parts = array();
    
    /*
     * Sectional properties
     *  Each sectional property has two properties
     *  The first holds the actual property
     *  The second states the validity of that property.
     *
     */
    //Ixx
    private $Ixx = 0;
    private $ixxValid = false;
    //Iyy
    private $Iyy = 0;
    private $iyyValid = false;
    //A
    private $A = 0;
    private $aValid = false;
    //Centroid y coord
    private $Yc = 0;
    private $ycValid = false;
    //Centroid x coord
    private $Xc = 0;
    private $xcValid = false;
    
     
    
    private static $xDiv = 1000;
    private static $yDiv = 1000;
    
    
      
    public function __construct( $input = "" ) {
        
      if($input === ""){
        return $this;
      }
      
      /*
       * Input is an array containing an array comprising 'part's 
       * that together make up the cross section:
       *  - {type:rectangle,width:x,height:y,[bottom:x,][left:y]}
       *  - {type:circle,x:x,y:y,radius:r}
       *  - {type:points,points:[{x,y},etc]}
       *
       * Any 'part' can have the void:true option set, this creates a void.
       *
       */
        
    }
     
     
    /**
      * Add a number of parts
      * 
      * @param array The parts to add.
      * 
      * @return boolean Returns a boolean for whether the action was entirely successful.
      * 
      */
    public function addParts($parts) {
        
        $success = true;
        
        if(count($parts)>0){
            $this->invalidate();
        }
        
        foreach($parts as $part){
            
            switch($part['type']) {
                
                case 'rectangle':
                    $success = ($this->addRectangle() !== true ? false : $success);
                    break;
                    
                case 'circle':
                    $success = ($this->addCircle() !== true ? false : $success);
                    break;
                    
                case 'points':
                    $success = ($this->addPoints() !== true ? false : $success);
                    break;
                
            }
            
        }
        
        return $success;
        
    }
    
    /**
      * Add a rectangle
      * 
      * @param array The rectangle to add.
      * 
      * @return boolean Returns a boolean for whether the action was entirely successful.
      * 
      */
    public function addRectangle($rect) {
        /* A rectangle must have:
         *  - width
         *  - height
         *
         * It must have two of:
         *  - bottom, top, left, right, midX, midY (default is midX=midY=0)
         *
         */
         
        $hasWidth = (isset($rect['width']) && $rect['width']>0 ? true : false);
        $hasHeight = (isset($rect['height']) && $rect['height']>0 ? true : false);
        
        $hasMidY = (isset($rect['midY']) && is_numeric($rect['midY']) ? true : false);
        $hasBottom = (isset($rect['bottom']) && is_numeric($rect['bottom']) ? true : false);
        $hasTop = (isset($rect['top']) && is_numeric($rect['top']) ? true : false);
        $hasYCoord = $hasBottom || $hasTop || $hasMidY;
        
        $hasMidX = (isset($rect['midX']) && is_numeric($rect['midX']) ? true : false);
        $hasLeft = (isset($rect['left']) && is_numeric($rect['left']) ? true : false);
        $hasRight = (isset($rect['right']) && is_numeric($rect['right']) ? true : false);
        $hasXCoord = $hasLeft || $hasRight || $hasMidX;
        
        if(!($hasWidth && $hasHeight && $hasYCoord && $hasXCoord)){
            //either doesn't have width of height
            return false;
        }
        
        //Definitely have width and height, grab them:
        $width = (float) $rect['width'];
        $height = (float) $rect['height'];
        
        //we have enough info
        //find values of midX and midY, we'll use those
        if($hasMidX){
            $midX = (float) $rect['midX'];
        }elseif($hasLeft){
            $midX = $rect['left'] + ($width/2);
        }elseif($hasRight){
            $midX = $rect['right'] - ($width/2);
        }else{
            return false;
        }
        
        if($hasMidY){
            $midY = (float) $rect['midY'];
        }elseif($hasTop){
            $midY = $rect['top'] - ($height/2);
        }elseif($hasBottom){
            $midY = $rect['bottom'] + ($height/2);
        }else{
            return false;
        }
        
        //{type:rectangle,width:x,height:y,[bottom:x,][left:y]}
        $this->parts[] = array(
            "type"=>"rectangle",
            "width"=>$width,
            "height"=>$height,
            "midX"=>$midX,
            "midY"=>$midY
        );
        
    }
    
    /**
      * Add a Circle
      * 
      * @param array The circle to add.
      * 
      * @return boolean Returns a boolean for whether the action was entirely successful.
      * 
      */
    public function addCircle($circ) {
        
        /* A circle must have x,y,r
         *  (x and y represent the midpoint)
         *  (other options could be added here)
         */
         
        $hasX = (isset($circ['x']) && is_numeric($circ['x']) ? true : false); 
        $hasY = (isset($circ['y']) && is_numeric($circ['y']) ? true : false); 
        $hasR = (isset($circ['r']) && is_numeric($circ['r']) ? true : false);
        
        if(!($hasX && $hasY && $hasR)){
            return false;
        }
        
        $this->parts[] = array(
            "type"=>"circle",
            "x"=> (float) $circ['x'],
            "y"=> (float) $circ['y'],
            "r"=> (float) $circ['r'],
        );
        
    }
    
    /**
      * Add a shape based on some Points
      * 
      * @param array The points to add.
      * 
      * @return boolean Returns a boolean for whether the action was entirely successful.
      * 
      */
    public function addPoints($points) {
        
    }
    
    private function invalidate() {
        
        $this->iyyValid = false;
        $this->ixxValid = false;
        $this->aValid = false;
        $this->ycValid = false;
        $this->xcValid = false;
        
        return;
        
    }
     
    /**
      * Calculate section properties.
      * This is all done at once because it is more efficient.
      * 
      */
    private function calcStuff() {
        
        $xDiv = $this::xDiv;
        $yDiv = $this::yDiv;
        
        /*
         * Ixx = integral( A * y^2 ).dy
         *
         * Need to find the size of the cross section
         */
         
        /*
         * Yc = integral(A * y).dy / A
         *
         * Need to find the size of the cross section
         */
         
        //The following values used to loop accurately
        $bounds = $this->getbounds();
        $height = $bounds['n'] - $bounds['s'];
        $width = $bounds['e'] - $bounds['w'];
        
        $xSlice = $width / $xDiv;
        $ySlice = $height / $yDiv;
        
        $startX = $bounds['w'] + ($xSlice/2);
        $endX = $bounds['e'] - ($xSlice/2);
        $startY = $bounds['s'] + ($ySlice/2);
        $endY = $bounds['n'] - ($ySlice/2);
        
        //The following values used in calculation
        $A = 0;
        $Ay = 0;
        $Ax = 0;
        $dA = $xSlice * $ySlice;
        $Ayy = 0;
        $Axx = 0;
        
        for($x = $startX; $x <= $endX; $x = $x + $xSlice){
            
            for($y = $startY; $y <= $endY; $y = $y + $ySlice){
                
                if($this->materialAtPoint()){
                    //There is material at this point, add it to calcs
                    
                    //First, add to area
                    $A = $A + $dA;
                    
                    //Then add to A*y and A*x
                    $Ay = $Ay + ($dA * $y);
                    $Ax = $Ax + ($dA * $x);
                    
                    //Then add to A*y^2
                    $Ayy = $Ayy + ($dA * $y * $y);
                    $Axx = $Axx + ($dA * $x * $x);
                }
                
            }
            
        }
        
        if($A==0){
            $this->invalidate();
            return false;
        }
        
        $yc = $Ay/$A;
        $xc = $Ax/$A;
        
        $this->Ixx = $Ayy - ($A * $yc * $yc);
        $this->Iyy = $Axx - ($A * $xc * $xc);
        $this->A = $A;
        $this->Yc = $yc;
        $this->Xc = $xc;
        
        $this->ixxValid = true;
        $this->iyyValid = true;
        $this->aValid = true;
        $this->ycValid = true;
        $this->xcValid = true;
        
        return true;
        
    }
    
    
    /**
      * Get second moment of area about the X-X axis
      * 
      * @return float The second moment of area about the X-X axis. Returns false if cannot calculate it.
      * 
      */
    public function getIxx($axis = 'neutral') {
        
        if($this->ixxValid !== true){
            $this->calcStuff();
        }
        
        if($this->ixxValid === true){
            if($axis === 'neutral'){
                //Ixx is stored relative to the neutral axis
                return $this->Ixx;
            }elseif($axis === 'local'){
                //Convert Ixx to be relative to the loca axis
                return $this->Ixx + ($this>-A * $this->Yc * $this->Yc);
            }else{
                return false;
            }
        }else{
            return false;
        }
        
        
        
    }
    
    /**
      * Get second moment of area about the Y-Y axis
      * 
      * @return float The second moment of area about the Y-Y axis
      * 
      */
    public function getIyy() {
        
        if($this->iyyValid !== true){
            $this->calcStuff();
        }
        
        if($this->iyyValid === true){
            if($axis === 'neutral'){
                //Iyy is stored relative to the neutral axis
                return $this->Iyy;
            }elseif($axis === 'local'){
                //Convert Iyy to be relative to the loca axis
                return $this->Iyy + ($this->A * $this->Xc * $this->Xc);
            }else{
                return false;
            }
        }else{
            return false;
        }
    
    }
    
    /**
      * Get all the parts that make up this cross-section
      * 
      * @return array Get all the parts that make up this cross-section
      * 
      */
    public function getParts() {
        
        return $this->parts;
      
    }
     
     
    /**
      * Determine whether there is material at a particular point
      * 
      * @param //TODO
      * 
      * @return array Determine whether there is material at a particular point
      * 
      */
    private function materialAtPoint($x = false, $y = false) {
        
        $isMaterial = false;
        
        foreach($this->getParts() as $part){
            
            switch($part['type']) {
                
                case 'rectangle':
                    if($this->squareIsThere($part, $x, $y)){
                        $success = true;
                    }
                    break;
                    
                case 'circle':
                    if($this->circleIsThere($x, $y)){
                        $success = true;
                    }
                    break;
                    
                case 'points':
                    if($this->pointsIsThere($x, $y)){
                        $success = true;
                    }
                    break;
                
            }
            
            if($success === true){
                //we've found some material, call off the search
                break;
            }
            
        }
        
        return $success;
        
    }
    
    private function squareIsThere($part = false, $x = false, $y = false) {
        
        if(!$part){
            return false;
        }
        
        /*
         *  We have:
         *   - width, height, midX, midY
         *
         */
         
        if($part['midX'] + ($part['width']/2) >= $x
            && $part['midX'] - ($part['width']/2) <= $x
            && $part['midY'] + ($part['height']/2) >= $y
            && $part['midY'] - ($part['height']/2) <= $y){
            
            return true;
            
        }else{
            return false;
        }
        
    }
    
    private function circleIsThere($part = false, $x = false, $y = false) {
        
        if(!($part && $x && $y)){
            return false;
        }
        
        /*
         *  We have:
         *   - x, y, r
         *
         *  Circle equation is:
         *   ((x-a)^2) + ((y-b)^2) = r^2
         *
         */
         
        $a = $part['x'];
        $b = $part['y'];
        $r = $part['r'];
        
        $lhs = (($x - $a)^2) + (($y - $b)^2);
        
        if($lhs <= ($r^2)){
            return true;
        }
        else{
            return false;
        }
        
    }
    
    public function getBounds() {
        
        //get the bounds of all parts
        
        $n = -INF;
        $s = INF;
        $e = -INF;
        $w = INF;
        
        foreach($this->parts as $part) {
            
            switch($part['type']) {
                
                case 'rectangle':
                    $b = $this->rectBounds($part);
                    $n = ( $b['n'] > $n ? $b['n'] : $n );
                    $s = ( $b['s'] < $s ? $b['s'] : $s );
                    $e = ( $b['e'] > $e ? $b['e'] : $e );
                    $w = ( $b['w'] < $w ? $b['w'] : $w );
                    break;
                    
                case 'circle':
                    $b = $this->circBounds($part);
                    $n = ( $b['n'] > $n ? $b['n'] : $n );
                    $s = ( $b['s'] < $s ? $b['s'] : $s );
                    $e = ( $b['e'] > $e ? $b['e'] : $e );
                    $w = ( $b['w'] < $w ? $b['w'] : $w );
                    break;
                    
                case 'points':
                    $b = $this->pointsBounds($part);
                    $n = ( $b['n'] > $n ? $b['n'] : $n );
                    $s = ( $b['s'] < $s ? $b['s'] : $s );
                    $e = ( $b['e'] > $e ? $b['e'] : $e );
                    $w = ( $b['w'] < $w ? $b['w'] : $w );
                    break;
                
            }
            
        }
        
        return array(
            "n"=>$n,  
            "s"=>$s,
            "e"=>$e,
            "w"=>$w
        );
        
    }
    
    private function rectBounds($part) {
        
        return array(
            "n"=>$part['midY'] + ($part['height']/2),
            "s"=>$part['midY'] - ($part['height']/2),
            "e"=>$part['midX'] + ($part['width']/2),
            "w"=>$part['midX'] - ($part['width']/2)
        );
        
    }
    
    private function circBounds($part) {
        
        return array(
            "n"=>$part['y'] + $part['r'],
            "s"=>$part['y'] - $part['r'],
            "e"=>$part['x'] + $part['r'],
            "w"=>$part['x'] - $part['r']
        );
        
    }
      
      
  }
  
  $rect = array(
      "type"=>"rectangle",
      "width"=>10,
      "height"=>10,
      "left"=>-5,
      "top"=>5
      );
  
  $cs = new CrossSection($rect);
  
  echo $cs->getParts();
  
  ?>