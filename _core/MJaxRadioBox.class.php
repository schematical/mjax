<?php
/* 
 * This is a simple button class for use with the mjax framework
 */
class MJaxRadioBox extends MJaxControl{
	protected $strValue = null;
	protected $blnChecked = false;
    public function Render($blnPrint = true){
    	if($this->blnChecked){
    		$this->attr('checked', true);
    	}else{
    		unset($this->arrAttr['checked']);
    	}
        //Render Actions first if applicable
        $strRendered = parent::Render();        
        $strRendered .= sprintf("<input id='%s' name='%s' type='radio' value='%s' %s></input>", $this->strControlId, $this->strName, $this->strValue,  $this->GetAttrString());
        if($blnPrint){
            echo($strRendered);
        }else{
            return $strRendered;
        }
    }
    public function ParsePostData(){
    	if (array_key_exists($this->strControlId, $_POST)) {
			$this->blnChecked = ($_POST[$this->strControlId] == 'true');
		} else {
			$this->blnChecked = false;
		}
    }
/////////////////////////
    // Public Properties: GET
    /////////////////////////
    public function __get($strName) {
        switch ($strName) {
            case "Checked": return $this->blnChecked;      
			case "Value": return $this->strValue;            
            default:
                return parent::__get($strName);
               
        }
    }

    /////////////////////////
    // Public Properties: SET
    /////////////////////////
    public function __set($strName, $mixValue) {
        $this->blnModified = true;
        switch ($strName) {
            case "Checked":
                return ($this->blnChecked = $mixValue);
			 case "Value":
                return ($this->strValue = $mixValue);                   
            default:
                return parent::__set($strName, $mixValue);               
        }
    }
}
?>