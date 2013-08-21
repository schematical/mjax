<?php
/* 
 * This is a simple button class for use with the mjax framework
 */
class MJaxLinkButton extends MJaxControl{
    
    public function __construct($objParentControl,$strControlId = null) {
        parent::__construct($objParentControl,$strControlId);

        $this->AddCssClass(MJaxApplication::CssClass('MJaxLinkButton'));
        $this->Attr('href', '#');
    }
    public function Render($blnPrint = true, $blnRenderAsAjax = false){
        //Render Actions first if applicable
        $strRendered = parent::Render();
    	if(!$blnRenderAsAjax){
            $strText = $this->strText;
        }else{
            $strText = MLCApplication::XmlEscape(trim($this->strText));
        }
        $strRendered .= sprintf("<a id='%s' name='%s' %s>%s</a>", $this->strControlId, $this->strControlId,  $this->GetAttrString(), $strText);
        if($blnPrint){
            echo($strRendered);
        }else{
            return $strRendered;
        }
    }
    /////////////////////////
    // Public Properties: GET
    /////////////////////////
    public function __get($strName) {
        switch ($strName) {
            case "Href": return $this->Attr('href');
            default:
                return parent::__get($strName);
               
        }
    }

    /////////////////////////
    // Public Properties: SET
    /////////////////////////
    public function __set($strName, $mixValue) {
        switch ($strName) {
            case "Href":
                return ($this->Attr('href', $mixValue));                
            default:
                return parent::__set($strName, $mixValue);
                
        }
    }
    public function SetValue($mixValue){
        return false;
    }
    public function GetValue(){
        return null;
    }

}
?>
