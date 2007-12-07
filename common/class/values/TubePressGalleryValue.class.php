<?php
final class TubePressGalleryValue extends TubePressAbstractValue {
   
    public function __construct($theName, $theDefault) {
        
        if (!is_a($theDefault, "TubePressGallery")) {
            throw new Exception("Gallery value can only take on a TubePressGallery");
        }
        
        $this->setName($theName);
        $this->setCurrentValue($theDefault);
        
    }
    
    public function printForOptionsPage() {
        
    }
    
    public function updateFromOptionsPage(array $postVars) {
        
    }
    
    public function updateManually($newValue) {
        
    }
}
?>