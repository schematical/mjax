<?php
class MJaxRoute{
    protected $arrMethod = array();
    protected $strExtension = null;
    protected $strFunction = null;
    protected $objListener = null;
    public function __construct($mixMethod, $strExtension, $strFunction, $objListener){
        if(is_string($mixMethod)){
            $mixMethod = array($mixMethod);
        }
        $this->arrMethod = $mixMethod;
        $this->strExtension = $strExtension;
        $this->strFunction = $strFunction;
        $this->objListener = $objListener;
    }
    public function Trigger(){
        echo $this->objListener->{$this->strFunction}($this);
    }
    public function Matches($strTestMethod, $strTestExtension){
        $blnMethodPass = false;

        foreach($this->arrMethod as $intIndex => $strMethod){
            $strMethod = strtoupper($strMethod);
            if($strTestMethod == $strMethod){
                $blnMethodPass = true;
            }
        }
        //die($strTestExtension . '/' .  $this->strExtension);
        if(
            $blnMethodPass &&
            ($strTestExtension == $this->strExtension)
        ){
            return true;
        }
        return false;
    }
    /////////////////////////
    // Public Properties: GET
    /////////////////////////
    public function __get($strName) {
        switch ($strName) {
            case "Method": return $this->strMethod;
            case "Extension": return $this->strExtension;
            default:
                throw new MLCMissingPropertyException($this, $strName);

        }
    }

    /////////////////////////
    // Public Properties: SET
    /////////////////////////
    public function __set($strName, $mixValue) {
        $this->blnModified = true;
        switch ($strName) {
            case "Method":
                return ($this->strMethod = $mixValue);
            case "Value":
                return ($this->strExtension = $mixValue);
            default:
               throw new MLCMissingPropertyException($this, $strName);
        }
    }
}