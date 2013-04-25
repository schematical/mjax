<?php
/* 
 * This handles most of the misc and core MJaxFunctionality
 */
abstract class MJaxApplication{
    public static $arrCssClasses = array();
    /**
     * This function should only be called from inside of the MJaxCssClass __construct method
     * @param <type> $objCssClass
     */
    public static function RegisterCssClass($objCssClass){
        if(key_exists($objCssClass->ClassName, self::$arrCssClasses)){
            throw new QCallerException("Class '" . $objCssClass->ClassName . "' already exists. Please use the 'CssClass' method to get the MJaxCssClass");
        }

        self::$arrCssClasses[$objCssClass->ClassName] = $objCssClass;
        
    }
    /**
     *
     * Call this function to select a css class for editing
     * @param <type> $strCssClassName
     * @return <type>
     */
    public static function CssClass($strCssClassName){
       if(key_exists($strCssClassName, self::$arrCssClasses)){
           return self::$arrCssClasses[$strCssClassName];
       }else{
           return new MJaxCssClass($strCssClassName);
       }
    }
    
    public static function ConvertArrayToJason($arrProperties){
    	$arrRepKeys = array();
		foreach($arrProperties as $strKey=>$strProperty){
			if(substr($strProperty, 0, 8) == 'function'){
				$strReplace = sprintf("@%s@", $strKey);
				$arrRepKeys[$strReplace] = $strProperty;
				$arrProperties[$strKey] = $strReplace;
			}
			
		}
        $strJson = json_encode($arrProperties);
        foreach($arrRepKeys as $strReplace=>$strProperty){
        	$strJson = str_replace('"' .$strReplace . '"', $strProperty, $strJson);
        }               
        return $strJson;
        /*
        $strProperties = "{";
        if(count($arrProperties) > 0){
            foreach($arrProperties as $strKey=>$strValue){
                if(is_array($strValue)){
                    $strValue = MJaxApplication::ConvertArrayToJason($strValue);
                }else{
                    if(!is_numeric($strValue) && (strpos($strValue, "'") === false)){
                        $strValue = "'" . $strValue . "'";
                    }
                }
                $strProperties .=  sprintf("'%s':%s,\n", $strKey, $strValue);
            }
            //Remove Trailing ','
            $strProperties = substr($strProperties, 0, strlen($strProperties) -2);
        }
        $strProperties .= "}\n";
        return $strProperties;
         */
    }
    public static function HasParent($mixClass, $mixParent){
        if(!is_string($mixParent)){
            $strParentName = get_class($mixParent);
        }else{
            $strParentName = $mixParent;
        }
        $arrParents = class_parents($mixClass, true);
        foreach($arrParents as $strParentNameTest){
            if($strParentName == $strParentNameTest){
                return true;
            }
        }
        return false;
    }
    const SPECIAL_JSON_ENCODE_START = '<<%%';
    const SPECIAL_JSON_ENCODE_END = '%%>>';
    public static function SpecialJSONEncode($arrData, $blnRenderAsString = true){
        foreach($arrData as $strKey => $mixData){
            if(is_object($mixData)){
                if(
                    ($mixData instanceof MJaxBaseAction)// ||
                    //($mixData instanceof MJaxEventBase)
                ){

                    $arrData[$strKey] = self::SPECIAL_JSON_ENCODE_START . $mixData->Render() . self::SPECIAL_JSON_ENCODE_END;
                }elseif(method_exists($mixData, '__toJson')){
                    $arrData[$strKey] = self::SPECIAL_JSON_ENCODE_START . $mixData->__toJson() . self::SPECIAL_JSON_ENCODE_END;
                }else{
                    throw new Exception("Failed to SpecialJSONEncode: " . get_class($mixData));
                }
            }elseif(is_array($arrData[$strKey])){
                $arrData[$strKey] = self::SpecialJSONEncode($arrData[$strKey], false);

            }
        }
        if(!$blnRenderAsString){
            return $arrData;
        }

        $strData = json_encode($arrData);
        //echo ("Incoming: " . $strData . "\n\n");
        $intStartPos = strpos($strData, self::SPECIAL_JSON_ENCODE_START);

        $strAfter = $strData;//Will get overwritten if it hits the loop
        $intEndPos = 0;
        $strReturn = substr($strData, 0, $intStartPos - 1);
        while($intStartPos !== false){


            $intEndPos = strpos($strData, self::SPECIAL_JSON_ENCODE_END, $intStartPos);

            $strPayload = substr(
                $strData,
                $intStartPos + strlen(self::SPECIAL_JSON_ENCODE_START),
                $intEndPos - ($intStartPos + (strlen(self::SPECIAL_JSON_ENCODE_END)))
            );

            //echo("Payload Presstrip:" . $strPayload . "\n\n");
            error_log("After:" . $strAfter);
            $strPayload =  stripslashes($strPayload);
            //echo("Payload Post strip:" . $strPayload . "\n\n");
            $strReturn .= $strPayload;


            $intStartPos = strpos($strData,self::SPECIAL_JSON_ENCODE_START, $intEndPos);
            if($intStartPos !== false){
                $strAfter = substr(
                    $strData,
                    $intEndPos + strlen(self::SPECIAL_JSON_ENCODE_END) + 1,
                    $intStartPos - ($intEndPos + strlen(self::SPECIAL_JSON_ENCODE_START) + 2)

                );
                $strReturn .= $strAfter;
            }

        }
        //echo("Strlen: " . $intEndPos . '/' . strlen($strData) . "\n\n");
        $strAfter = substr(
            $strData,
            $intEndPos + strlen(self::SPECIAL_JSON_ENCODE_END) + 1
        );
        //echo("After:" . $strAfter . "\n\n");
        $strReturn .= $strAfter;
        //echo("Finished:" . $strReturn . "\n\n");
        //echo("\n\n\n--------------------------------------------\n\n");
        /*if(defined('cccc')){
            //die($strReturn);
        }
        if(defined('bbbb')){
            //die($strData);
            define("cccc", '1');
        }else{
            if(defined('aaaa')){
                define("bbbb", '1');
            }
            if(!defined('aaaa')){
                define("aaaa", '1');
            }
        }*/
        return $strReturn;
    }

}
/*
class MJaxFancyboxCloseEvent extends MJaxEventBase{
    protected $strEventName = 'fancybox-close';
}
*/
?>