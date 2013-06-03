<?php
class MJaxHeaderAsset{
    protected $strSrc = null;
    protected $blnAlreadyRendered = false;
    public function RenderAsJSCall($blnPrint){
    	$strRendered = sprintf('$("head").append(\'%s\');', $this->__toString());
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
            case "AlreadyRendered": return $this->blnAlreadyRendered;
            case "Src": return $this->strSrc;
            default:
                return parent::__get($strName);
               
        }
    }

    /////////////////////////
    // Public Properties: SET
    /////////////////////////
    public function __set($strName, $mixValue) {
        switch ($strName) {
            case "AlreadyRendered":
                return ($this->blnAlreadyRendered = $mixValue);
                
            default:
                return parent::__set($strName, $mixValue);
		}
                
    }

}

?>
