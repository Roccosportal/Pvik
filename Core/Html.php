<?php
class Html {
    public static function Link($Path, $Title, $HtmlAttributes = array()){
        $RelativePath = Core::RelativePath($Path);
        $LinkHtml = '<a';
        $HtmlAttributes['href'] = $RelativePath;
        $LinkHtml .= self::GenerateAttributes($HtmlAttributes);
        $LinkHtml .= '>' . $Title . '</a>';
        echo $LinkHtml;
    }

    public static function Out($Html){
        echo $Html;
    }

    public static function GenerateAttributes($HtmlAttributes){
        $Html = '';
        foreach ($HtmlAttributes as $Name => $Value){
            $Html .= ' ' .$Name . '="'. $Value . '"';
        }
        return $Html;
    }

    public static function StyleSheetLink($Path){
        $RelativePath = Core::RelativePath($Path);
        $Html = '<link rel="stylesheet" type="text/css" href="' . $RelativePath . '" ></link>';
        echo $Html;
    }

    public static function JavaScriptLink($Path){
        $RelativePath = Core::RelativePath($Path);
        $Html = '<script type="text/javascript" src="'. $RelativePath . '"></script>';
        echo $Html;
    }

    public static function FaviconLink($Path){
        $RelativePath = Core::RelativePath($Path);
        $Html = '<link rel="shortcut icon" href="'. $RelativePath . '" type="image/x-icon"></link>';
        $Html .= '<link rel="icon" href="'. $RelativePath . '" type="image/x-icon"></link>';
        echo $Html;
    }

    public static function Errorfield(ValidationState $ValidationState, $Field, $Class = 'errorfield'){
         if($ValidationState!=null){
             if($ValidationState->GetError($Field)!=null){
                     echo '<span class="' . $Class .'">'.$ValidationState->GetError($Field). '</span>';
             }
         }
    }

 
    public static function CheckboxPostCheck($PostField, $OtherField){
        if(Core::IsPOST($PostField)){
            if(Core::GetPOST($PostField)){
                echo 'checked="checked"';
            }
        }
        elseif($OtherField){
             echo 'checked="checked"';
        }
    }
}
?>
