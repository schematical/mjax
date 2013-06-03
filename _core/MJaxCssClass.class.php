<?php
/* 
 * This class defines certain attributes of a css class for use with jQuery and the DOM
 */
class MJaxCssClass /*extends QBaseClass*/{
    protected $strClassName = null;
    protected $objStyle = null;
    protected $arrPlugins = array();
    protected $arrEvents = array();
    protected $blnModified = false;
    protected $blnRenderClearActions = false;
    public function  __construct($strClassName) {
        $this->strClassName = $strClassName;
        $this->objStyle = new MJaxControlStyle();
        //Automatically register with MJaxApplication
        $this->blnModified = true;
        MJaxApplication::RegisterCssClass($this);
    }
    public function AddAction($objEvent, $objAction){
        $objEvent->Init($this, $objAction);
        $this->arrEvents[] = $objEvent;
    }
    public function Animate($arrProperties){
        $this->ApplyPlugin(new MJaxAnimatePlugin($this->objForm, $arrProperties));
    }
    public function ApplyPlugin($objPlugin){
        $objPlugin->SetTargetClass($this);
        $this->arrPlugins[] = $objPlugin;
    }
	public function RemoveAllActions($mixEvent){
        if($mixEvent instanceof MJaxEventBase){
            $strEvent= $mixEvent->EventName;
        }elseif(is_string($mixEvent)){
            $strEvent = $mixEvent;
        }else{
            throw new QCallerException("RemoveAllActions method must take either a string or a MJaxEvent for the 1st parameter");
        }
        $this->blnModified = true;
        $this->arrEvents[$strEvent] = array();
        $this->blnRenderClearActions = true;
    }
    public function RenderJSCalls($blnAjaxFormating = false){
        $strDocumentReady = '';
        foreach($this->arrEvents as $arrEventType){
        	foreach($arrEventType as $intIndex=>$objEvent){
            	$strDocumentReady .= $objEvent->Render();
        	}
        }
        foreach($this->arrPlugins as $objPlugin){
            $strDocumentReady .= $objPlugin->Render(false);
        }
        if($this->blnModified && $blnAjaxFormating){
            //add any style changes
            $strRendered = "$('." . $this->strClassName . "').css(\n";
            $strRendered .= $this->objStyle->__toJason(true);
            $strRendered .= ");\n";
            $strDocumentReady .= $strRendered;
        }
        $strRendered = '';
        if((strlen($strDocumentReady) > 0) || ($this->blnRenderClearActions)){
            
            if(!$blnAjaxFormating){
                $strRendered .= "<script language='javascript'>";
            }
            $strRendered .= sprintf("$('docuent').ready(function(){%s});\n", $strDocumentReady);
            if(!$blnAjaxFormating){
                $strRendered .= "</script>";
            }
            $this->blnRenderClearActions = false;
        }
        return $strRendered;

    }
    public function RenderCss($blnPrint = true, $blnAjaxFormating = false){
        
        $strRendered = "." . $this->strClassName . "{\n";
        $strRendered .= $this->objStyle->__toCss(true);
        $strRendered .= "}\n";
        
        $this->blnModified = false;
        if($blnPrint){
            _p($strRendered, false);
        }else{
            return $strRendered;
        }

    }
     /////////////////////////
    // Public Properties: GET
    /////////////////////////
    public function __get($strName) {
        switch ($strName) {
            case "ClassName": return $this->strClassName;
            case "Style": return $this->objStyle;
            case "Modified": return $this->blnModified;
            default:
               throw new MLCMissingPropertyException($this, $strName);
                
        }
    }

    /////////////////////////
    // Public Properties: SET
    /////////////////////////
    public function __set($strName, $mixValue) {
        switch ($strName) {
            case "ClassName":

                return ($this->strClassName = $mixValue);
               
            case "Style":
                
                return ($this->objStyle = $mixValue);
               
            default:
                throw new MLCMissingPropertyException($this, $strName);
        }
    }
    
    
}
?>
