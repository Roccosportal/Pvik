<?php

class ModelArray extends ArrayObject
{

    protected $CurrentCompareFieldLists = null;
    protected $CurrentCompareField = null;
    protected $CurrentCompareForeignObject = null;

    public function CompareUp($Object1, $Object2)
    {
        $Field = $this->CurrentCompareField;
        if ($Field != null)
        {

            if ($Object1->$Field == $Object2->$Field)
            {
                return 0;
            }
            return ($Object1->$Field < $Object2->$Field) ? -1 : 1;
        }
        else
        {
            throw new Exception('Sort field can not be empty.');
        }
    }


    public function CompareDown($Object1, $Object2)
    {
        $Field = $this->CurrentCompareField;
        if ($Field != null)
        {

            if ($Object1->$Field == $Object2->$Field)
            {
                return 0;
            }
            return ($Object1->$Field > $Object2->$Field) ? -1 : 1;
        }
        else
        {
            throw new Exception('Sort field can not be empty.');
        }
    }

    public function SortUp($Field)
    {
        if (!empty($Field))
        {
            $this->CurrentCompareField = $Field;
            $this->uasort(array($this, 'CompareUp'));
        }
        else
        {
            throw new Exception('Sort field can not be empty.');
        }
        return $this;
    }

    public function SortDown($Field)
    {
        if (!empty($Field))
        {
            $this->CurrentCompareField = $Field;
            $this->uasort(array($this, 'CompareDown'));
        }
        else
        {
            throw new Exception('Sort field can not be empty.');
        }
        return $this;
    }

    public function Distinct($Field)
    {
        $KeyList = array();
        $List = new ModelArray();
        foreach ($this as $Object)
        {
            if (!in_array($Object->$Field, $KeyList))
            {
                array_push($KeyList, $Object->$Field);
                $List->append($Object);
            }
        }
        return $List;
    }

    public function FilterIn($Field, $Keys)
    {
        $List = new ModelArray();
        if ($Keys === null)
            return $List;


        if (!is_array($Keys) && !is_a($Keys, 'IteratorAggregate'))
            throw new Exception('The parameters keys must be an array.');


        foreach ($this as $Object)
        {
            foreach ($Keys as $Key)
            {
                if ($Object->$Field == $Key)
                {
                    $List->append($Object);
                    break;
                }
            }
        }
        return $List;
    }

    public function FilterEquals($Field, $Value)
    {
        $List = new ModelArray();
        foreach ($this as $Object)
        {
            if ($Object->$Field === $Value)
            {
                $List->append($Object);
            }
        }
        return $List;
    }

    public function GetList($Field)
    {
        $List = new ModelArray();
        foreach ($this as $Object)
        {
            $List->append($Object->$Field);
        }
        return $List;
    }
    
    public function Sort(){
        $Arguments = func_get_args();
        $FieldLists = array();
        foreach($Arguments as $Argument){
            array_push($FieldLists, $this->ConvertStringToFieldList($Argument));
        }
        $this->CurrentCompareFieldLists = $FieldLists;
         $this->uasort(array($this, 'Compare'));
         return $this;
    }
    
    protected function ConvertStringToFieldList($String){
        $Type = 'ASC';
        if($String[0]=='+'){
            $String =  substr($String, 1);
        }
        elseif($String[0]=='-'){
            $String =  substr($String, 1);
            $Type = 'DESC';
        }
        
        $Fields = explode('->', $String);
        
        $FieldList = array();
        $FieldList['Type'] = $Type;
        $FieldList['Fields'] = $Fields;
        return $FieldList;
    }
    
    
    public function Compare($Object1, $Object2){
        $FieldLists = $this->CurrentCompareFieldLists;
        foreach($FieldLists as $FieldList){
            $Result = $this->CompareFieldList($Object1, $Object2, $FieldList);
            if($Result!=0){
                return $Result;
            }
        }
        return 0;
    }
    

    protected function CompareFieldList($Object1, $Object2, $FieldList)
    {
        $Type = $FieldList['Type'];
        $Fields = $FieldList['Fields'];
        $CountFields = count($Fields);
        for ($Index = 0; $Index < $CountFields; $Index++)
        {
            $Field = $Fields[$Index];
            if ($Object1->$Field == null && $Object2->$Field == null)
            {
                return 0;
            }
            elseif ($Object1->$Field == null)
            {
                if ($Type == 'ASC')
                {
                    return -1;
                }
                return 1;
            }
            elseif ($Object2->$Field == null)
            {
                if ($Type == 'ASC')
                {
                    return 1;
                }
                return -1;
            }
            // if it is the last item in the field list
            elseif ($Index == ($CountFields - 1))
            {
                if ($Object1->$Field == $Object2->$Field)
                {
                    return 0;
                }
                elseif ($Object1->$Field < $Object2->$Field)
                {
                    if ($Type == 'ASC')
                    {
                        return -1;
                    }
                    return 1;
                }
                elseif ($Type == 'ASC')
                {
                    return 1;
                }
                return -1;
            }
            else
            {
                $Object1 = $Object1->$Field;
                $Object2 = $Object2->$Field;
            }
        }
    }
    
   public function Merge(ModelArray $ModelArray){
       $List = new ModelArray();
        foreach ($this as $Object)
        {
           
                $List->append($Object);
             
        }
        foreach ($ModelArray as $Object)
        {
           
                $List->append($Object);
             
        }
        return $List;
   }
   
   public function HasValue($Field, $Value){
       foreach($this as $Object){
           if($Object->$Field===$Value){
               return true;
           }
       }
       return false;
   }
   
   public function HasNotValue($Field, $Value){
      return !$this->HasValue($Field, $Value);
   }

}

?>