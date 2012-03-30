<?php

class ModelArray extends ArrayObject
{

    protected $SortFieldLists = null;
    protected $ModelTable;

    public function SetModelTable(ModelTable $ModelTable = null)
    {
        $this->ModelTable = $ModelTable;
    }

    public function GetModelTable()
    {
        return $this->ModelTable;
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
        $List->SetModelTable($this->GetModelTable());
        if ($Keys === null)
            return $List;


        if (!is_array($Keys) && !($Keys instanceof IteratorAggregate))
            throw new Exception('The parameters keys must be an array.');


        foreach ($this as $Object)
        {
            foreach ($Keys as $Key)
            {
                if ($Object->$Field === $Key)
                {
                    $List->append($Object);
                    break;
                }
            }
        }
        return $List;
    }

    public function FilterNotIn($Field, $Keys)
    {
        $List = new ModelArray();
        $List->SetModelTable($this->GetModelTable());
        if ($Keys === null)
            return $List;


        if (!is_array($Keys) && !($Keys instanceof IteratorAggregate))
            throw new Exception('The parameters keys must be an array.');


        foreach ($this as $Object)
        {
            $HasKey = false;
            foreach ($Keys as $Key)
            {
                if ($Object->$Field == $Key)
                {
                    $HasKey = true;
                    break;
                }
            }
            if ($HasKey == false)
            {
                $List->append($Object);
            }
        }
        return $List;
    }

    public function FilterEquals($Field, $Value)
    {
        $List = new ModelArray();
        $List->SetModelTable($this->GetModelTable());
        foreach ($this as $Object)
        {
            if ($Object->$Field === $Value)
            {
                $List->append($Object);
            }
        }
        return $List;
    }
    
    public function FilterHeigher($Field, $Value)
    {
        $List = new ModelArray();
        $List->SetModelTable($this->GetModelTable());
        foreach ($this as $Object)
        {
            if ($Object->$Field > $Value)
            {
                $List->append($Object);
            }
        }
        return $List;
    }
    
    public function FilterHeigherEquals($Field, $Value)
    {
        $List = new ModelArray();
        $List->SetModelTable($this->GetModelTable());
        foreach ($this as $Object)
        {
            if ($Object->$Field >= $Value)
            {
                $List->append($Object);
            }
        }
        return $List;
    }
    
    public function FilterLower($Field, $Value)
    {
        $List = new ModelArray();
        $List->SetModelTable($this->GetModelTable());
        foreach ($this as $Object)
        {
            if ($Object->$Field < $Value)
            {
                $List->append($Object);
            }
        }
        return $List;
    }
    
    public function FilterLowerEquals($Field, $Value)
    {
        $List = new ModelArray();
        $List->SetModelTable($this->GetModelTable());
        foreach ($this as $Object)
        {
            if ($Object->$Field <= $Value)
            {
                $List->append($Object);
            }
        }
        return $List;
    }

    public function FilterNotEquals($Field, $Value)
    {
        $List = new ModelArray();
        $List->SetModelTable($this->GetModelTable());
        foreach ($this as $Object)
        {
            if ($Object->$Field !== $Value)
            {
                $List->append($Object);
            }
        }
        return $List;
    }
    
    public function FilterStartsWith($Field, $Value)
    {
        $List = new ModelArray();
        $List->SetModelTable($this->GetModelTable());
        foreach ($this as $Object)
        {
            if ($Object->$Field!=null&&strpos($Object->$Field, $Value)===0)
            {
                $List->append($Object);
            }
        }
        return $List;
    }
    
    public function FilterEndsWith($Field, $Value)
    {
        $List = new ModelArray();
        $List->SetModelTable($this->GetModelTable());
        foreach ($this as $Object)
        {
            if ($Object->$Field!=null){
                $LengthField = strlen($Object->$Field);
                $LengthValue = strlen($Value);
                if($LengthField>=$LengthValue){
                    if(strpos($Object->$Field, $Value)===($LengthField - $LengthValue)){
                        $List->append($Object);
                    }
                }
            }
        }
        return $List;
    }
    
    public function FilterContains($Field, $Value)
    {
        $List = new ModelArray();
        $List->SetModelTable($this->GetModelTable());
        foreach ($this as $Object)
        {
            if (strpos($Object->$Field, $Value)!==false)
            {
                $List->append($Object);
            }
        }
        return $List;
    }

    public function GetList($Field)
    {
        $List = new ModelArray();
        if ($this->ModelTable != null)
        {
            $List->SetModelTable($this->ModelTable->GetFieldModelTable($Field));
        }
        foreach ($this as $Object)
        {
            $List->append($Object->$Field);
        }
        return $List;
    }

    public function SortUp($Fields)
    {
        return $this->Sort($Fields);
    }

    public function SortDown($Fields)
    {
        return $this->Sort('-' . $Fields);
    }

    public function Sort()
    {
        $Arguments = func_get_args();
        $FieldLists = array();
        foreach ($Arguments as $Argument)
        {
            array_push($FieldLists, $this->ConvertStringToFieldList($Argument, true));
        }
        $this->SortFieldLists = $FieldLists;
        $this->uasort(array($this, 'Compare'));
        return $this;
    }

    protected function ConvertStringToFieldList($String, $IsSortable = false)
    {
        if ($IsSortable)
        {
            $Sort = 'ASC';
            if ($String[0] == '+')
            {
                $String = substr($String, 1);
            }
            elseif ($String[0] == '-')
            {
                $String = substr($String, 1);
                $Sort = 'DESC';
            }
        }

        $Fields = explode('->', $String);

        $FieldList = array();
        if ($IsSortable)
            $FieldList['Sort'] = $Sort;
        $FieldList['Fields'] = $Fields;
        return $FieldList;
    }

    public function Compare($Object1, $Object2)
    {
        $FieldLists = $this->SortFieldLists;
        foreach ($FieldLists as $FieldList)
        {
            $Result = $this->CompareFieldList($Object1, $Object2, $FieldList);
            if ($Result != 0)
            {
                return $Result;
            }
        }
        return 0;
    }

    protected function CompareFieldList($Object1, $Object2, $FieldList)
    {
        $Sort = $FieldList['Sort'];
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
                if ($Sort == 'ASC')
                {
                    return -1;
                }
                return 1;
            }
            elseif ($Object2->$Field == null)
            {
                if ($Sort == 'ASC')
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
                    if ($Sort == 'ASC')
                    {
                        return -1;
                    }
                    return 1;
                }
                elseif ($Sort == 'ASC')
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

    public function Merge(ModelArray $ModelArray)
    {
        $List = new ModelArray();
        // set model table if both arrays have the same model table
        if ($this->GetModelTable() == $ModelArray->GetModelTable())
        {
            $List->SetModelTable($this->GetModelTable());
        }
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

    public function HasValue($Field, $Value)
    {
        foreach ($this as $Object)
        {
            if ($Object->$Field === $Value)
            {
                return true;
            }
        }
        return false;
    }

    public function HasNotValue($Field, $Value)
    {
        return!$this->HasValue($Field, $Value);
    }

    public function Load($Fields)
    {
        if ($this->ModelTable == null)
        {
            throw new Exception('ModelTable must be set to use this function.');
        }
        $FieldList = $this->ConvertStringToFieldList($Fields);
        $ModelTable = $this->ModelTable;
        $List = $this;
        foreach ($FieldList['Fields'] as $Field)
        {
            // load definition for current field
            $DataDefinition = $ModelTable->GetDataDefinition();

            if (!isset($DataDefinition[$Field]))
            {
                throw new Exception('Field ' . $Field . ' must be in data defintion');
            }
            $FieldDefintion = $DataDefinition[$Field];
            switch ($FieldDefintion['Type'])
            {
                case 'ForeignObject':
                    $ForeignKey = $FieldDefintion['ForeignKey'];
                    $ModelTableName = $DataDefinition[$ForeignKey]['ModelTable'];
                    $ModelTable = ModelTable::Get($ModelTableName);
                    $Keys = array();
                    foreach ($List as $Object)
                    {
                        if ($Object != null && $Object->$ForeignKey !== null)
                        {
                            array_push($Keys, $Object->$ForeignKey);
                        }
                    }
                    $List = $ModelTable->Load($Keys);
                    break;
                case 'ManyForeignObjects':
                    $ModelTableName = $FieldDefintion['ModelTable'];
                    $ModelTable = ModelTable::Get($ModelTableName);
                    $Keys = array();
                    foreach ($List as $Object)
                    {
                        if ($Object != null)
                        {
                            $Keys = array_merge($Keys, $Object->GetKeys($Field));
                        }
                    }
                    $List = $ModelTable->Load($Keys);
                    break;
                default:
                    return null;
                    break;
            }
        }
        return $List;
    }

}

?>