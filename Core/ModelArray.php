<?php
class ModelArray extends ArrayObject{
    protected $CurrentCompareField = null;
    
    public function CompareUp($Object1,$Object2){
        $Field = $this->CurrentCompareField;
        if($Field!=null){
            
         if ($Object1->$Field == $Object2->$Field) {
                    return 0;
                }
                return ($Object1->$Field < $Object2->$Field) ? -1 : 1;
        }
        else {
            throw new Exception('Sort field can not be empty.');
        }
    }
    
     public function CompareDown($Object1,$Object2){
        $Field = $this->CurrentCompareField;
        if($Field!=null){
            
         if ($Object1->$Field == $Object2->$Field) {
                    return 0;
                }
                return ($Object1->$Field > $Object2->$Field) ? -1 : 1;
        }
        else {
            throw new Exception('Sort field can not be empty.');
        }
    }
    
    public function SortUp($Field){
        if(!empty($Field)){
            $this->CurrentCompareField = $Field;
            $this->uasort(array($this, 'CompareUp'));
        }
        else {
            throw new Exception('Sort field can not be empty.');
        }
        return $this;
    }
    
    public function SortDown($Field){
        if(!empty($Field)){
            $this->CurrentCompareField = $Field;
            $this->uasort(array($this, 'CompareDown'));
        }
        else {
            throw new Exception('Sort field can not be empty.');
        }
        return $this;
    }

}
?>
