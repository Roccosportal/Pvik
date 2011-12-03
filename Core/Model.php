<?php
class Model {
    protected $ModelTableName;
    protected $Data = array();

    public function Fill($Data = array()){
        // fill this class with the data
        foreach($Data as $Key => $Value){
           
            $this->Data[$Key] =  $Value;
        }
        $ModelTable = $this->GetModelTable();
        $ModelTable->StoreInCache($this);
    }

    public function GetModelTable(){
        return ModelTable::get($this->ModelTableName);
    }

    public function  __get($Key) {
        $ModelTable  = $this->GetModelTable();
        $DataDefinition = $ModelTable->GetDataDefinition();

       
        if(isset($DataDefinition[$Key])){
             switch ($DataDefinition[$Key]['Type']) {
                case 'PrimaryKey':
                    return $this->GetObjectData($Key);
                    break;
                case 'Normal':
                    return $this->GetObjectData($Key);
                    break;
                case 'ForeignKey':
                    return $this->GetObjectData($Key);
                    break;
                case 'ForeignObject':
                    // get the foreign key definition and the data for the foreign key
                    $ForeignKeyDefinitionKey = $DataDefinition[$Key]['ForeignKey'];
                    $ForeignKeyDefinition = $DataDefinition[$ForeignKeyDefinitionKey];
                    
                    if(isset($this->Data[$ForeignKeyDefinitionKey])){
                        $ForeignKey =  $this->Data[$ForeignKeyDefinitionKey];
                    }
                    else {
                        return null;
                    }

                    // get the model definition name
                    $ModelTableName = $ForeignKeyDefinition['ModelTable'];

                    if(isset($ForeignKey)){
                        // try to get the foreign object
                        return ModelTable::Get($ModelTableName)->GetByPrimaryKey($ForeignKey);
                    }
                    else {
                        throw new Exception('Foreign key for '. $Key . ' not found.');
                    }

                    break;
                case 'ManyForeignObjects':
                    $Keys = $this->GetObjectData($Key);
                    if($Keys==null){
                        return new ModelArray();
                    }
                    if(isset($Keys)){
                        $List = new ModelArray();
                        $ModelTableName = $DataDefinition[$Key]['ModelTable'];
                        // convert to array
                        $ForeignKeys = explode(',', $Keys);
                        $LoadKeys = array();
                        foreach($ForeignKeys as $ForeignKey){
                            if(!empty($ForeignKey)){
                                // search in cache
                                $Item = ModelTable::Get($ModelTableName)->GetFromCacheByPrimaryKey($ForeignKey);
                                
                                // mark for loading later
                                if($Item==null){
                                    array_push($LoadKeys, $ForeignKey);
                                }
                                else {
                                    $List->append($Item);
                                }
                            }
                        }
                        if(!empty($LoadKeys)){
                            // now load every data we didn't find in the cache
                            $LoadedItems = ModelTable::Get($ModelTableName)->ByPrimaryKeys($LoadKeys);
                            foreach($LoadedItems as $LoadedItem){
                                 $List->append($LoadedItem);
                            }
                        }
                        return $List;
                    }
                    else {
                        throw new Exception('Foreign keys for '. $Key . ' not found.');
                    }
                    break;
            }
        }
        else {
            throw new Exception('Value ' . $Key . ' not found.');
        }
    }

    public function __set($Key, $Value) {
        

        $ModelTable  = $this->GetModelTable();
        $DataDefinition = $ModelTable->GetDataDefinition();

        if(isset($DataDefinition[$Key])){
             switch ($DataDefinition[$Key]['Type']) {
                case 'PrimaryKey':
                     throw new Exception('The primary key is only readable: '. $Key);
                     break;
                case 'Normal':
                     $this->Data[$Key] = $Value;
                     break;
                case 'ForeignKey':

                    $OldForeignKey = $this->GetObjectData($Key);
                    $this->UpdateObjectData($Key, $Value);
                    $PrimaryKey = $this->GetPrimaryKey();
                    if($PrimaryKey!=null){
                        $ForeignModelTable = ModelTable::Get($DataDefinition[$Key]['ModelTable']);
                        // if old object exist in cache we need to update the instance
                        $OldForeignObject = $ForeignModelTable->GetFromCacheByPrimaryKey($OldForeignKey);
                        if($OldForeignObject!=null){
                            foreach($ForeignModelTable->GetDataDefinition() as $ForeignDefinitionKey => $ForeignDefinition){
                                if($ForeignDefinition['Type']=='ManyForeignObjects'&&$ForeignDefinition['ForeignKey'] == $Key){
                                    $OldKeys = $OldForeignObject->GetObjectData($ForeignDefinitionKey);
                                    // delete from old keys
                                    $OldForeignObject->UpdateObjectData($ForeignDefinitionKey, str_replace($PrimaryKey, '', $OldKeys));
                                    break;
                                }
                            }
                        }

                        // if new object exist in cache we need to update the instance
                        $NewForeignObject = $ForeignModelTable->GetFromCacheByPrimaryKey($Value);
                        if($NewForeignObject!=null){
                            foreach($ForeignModelTable->GetDataDefinition() as $ForeignDefinitionKey => $ForeignDefinition){
                                if($ForeignDefinition['Type']=='ManyForeignObjects'&&$ForeignDefinition['ForeignKey'] == $Key){
                                    $OldKeys = $NewForeignObject->GetObjectData($ForeignDefinitionKey);
                                    // add to keys
                                    $NewForeignObject->UpdateObjectData($ForeignDefinitionKey, $OldKeys . ' ' . $Value);
                                    break;
                                }
                            }
                        }
                    }
                    
                    break;
                case 'ForeignObject':
                    throw new Exception('The object is only readable: '. $Key);
                    break;
                case 'ManyForeignObjects':
                    throw new Exception('The list is only readable: '. $Key);
                    break;
            }
        }
        else {
            throw new Exception('Value ' . $Key . ' not found.');
        }
    }

    public function GetPrimaryKey(){
        $ModelTable  = $this->GetModelTable();
        $PrimaryKeyName = $ModelTable->GetPrimaryKeyName();
        if($PrimaryKeyName!=null){
            if(isset($this->Data[$PrimaryKeyName])){
                return $this->Data[$PrimaryKeyName];
            }
            else {
                return null;
            }
        }
         else {
            throw new Exception('This model has no primary key.');
        }
    }

    public function Insert(){
        return $this->GetModelTable()->Insert($this);
    }
    
    public function Update(){
        return $this->GetModelTable()->Update($this);
    }
    
    public function Delete(){
        return $this->GetModelTable()->Delete($this);
    }

    public function UpdateObjectData($Key, $Value){
        $this->Data[$Key] = $Value;
    }

    public function GetObjectData($Key){
        if(array_key_exists($Key,$this->Data)){
            return $this->Data[$Key];
        }
        else{
            return null;
        }
    }
    
    public function GetKeys($Key){
        $ModelTable  = $this->GetModelTable();
        $DataDefinition = $ModelTable->GetDataDefinition();
        if(!isset($DataDefinition[$Key])||$DataDefinition[$Key]['Type']!='ManyForeignObjects'){
            throw new Exception('The field must have the type "ManyForeignObjects".');
        }
        $KeysString = $this->GetObjectData($Key);
        if($KeysString!=null){
            return explode(',', $KeysString);
        }else {
            return null;
        }
    }
}

?>