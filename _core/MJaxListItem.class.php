<?php
/* 
 * This acts as an entry for a list box
 */
class MJaxListItem{
    protected $strText = null;
    protected $strValue = null;
    protected $blnSelected = false;
    
    public function  __construct($strText, $strValue, $blnSelected = false) {
        $this->strText = $strText;
        $this->strValue = $strValue;
        $this->blnSelected = $blnSelected;
    }

    public function __toString(){
        if($this->blnSelected){
            $strAttributes = " selected='true'";
        }else{
            $strAttributes = "";
        }
        
        $strReturn = sprintf("<option value='%s'%s>%s</option>\n", $this->strValue, $strAttributes, $this->strText);
        return $strReturn;
    }
    /////////////////////////
    // Public Properties: GET
    /////////////////////////
    public function __get($strName) {
        switch ($strName) {
            case "Value": return $this->strValue;
            case "Selected": return $this->blnSelected;
            case "Text": return $this->strText;
            default:
                return parent::__get($strName);
               
        }
    }

    /////////////////////////
    // Public Properties: SET
    /////////////////////////
    public function __set($strName, $mixValue) {
        switch ($strName) {
            case "Value":
                return ($this->strValue = $mixValue);
                
            case "Text":
				return ($this->strText = $mixValue);
                
            case "Selected":
				return ($this->blnSelected = $mixValue);
               
            default:
				return parent::__set($strName, $mixValue);
                
        }
    }
}
?>
