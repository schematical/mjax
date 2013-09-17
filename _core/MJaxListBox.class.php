<?php
/* 
 * This will contain the code needed to generate a select box
 */
class MJaxListBox extends MJaxControl{
    protected $arrListItems = array();
    public function Render($blnPrint = true, $blnRenderAsAjax = false){
        $strRendered = parent::Render();
        if(!$blnRenderAsAjax){
            $strTag = 'select';
        }else{
            $strTag = 'control';
        }
        $strRendered .= sprintf("<%s id='%s' name='%s' %s>\n", $strTag, $this->strControlId, $this->strControlId, $this->GetAttrString());

        $strChildren  = '';
         foreach($this->arrListItems as $objListItem){
            //render list items
             $strChildren .= $objListItem->__toString();
         }
        if($blnRenderAsAjax){
            $strChildren = MLCApplication::XmlEscape($strChildren);
        }
        $strRendered .= $strChildren;
        $strRendered .= sprintf("</%s>\n", $strTag);

        if($blnPrint){
            echo($strRendered);
        }else{
            return $strRendered;
        }
    }
    public function ParsePostData() {
			// Check to see if this Control's Value was passed in via the POST data
			if (array_key_exists($this->strControlId, $_POST)) {
                //$this->blnModified = true;
				// It was -- update this Control's value with the new value passed in via the POST arguments
				$strValue = $_POST[$this->strControlId];

                foreach($this->arrListItems as $objListItem){
                    if($objListItem->Value == $strValue){
                        $objListItem->Selected = true;
                    }else{
                    	 $objListItem->Selected = false;
                    }
                }
            }
    }
    public function AddItem($strText, $strValue = null, $blnSelected = false){
        if($strText instanceof MJaxListItem){
            $this->arrListItems[] = $strText;
        }else{
            $this->arrListItems[] = new MJaxListItem($strText, $strValue, $blnSelected);
        }
    }
	public function RemoveAllItems(){
		$this->arrListItems = array();
	}
    /////////////////////////
    // Public Properties: GET
    /////////////////////////
    public function __get($strName) {
        switch ($strName) {
            case "SelectedItem":
                $arrSelected = array();
                 foreach($this->arrListItems as $objListItem){
                    if($objListItem->Selected){
                        $arrSelected[] = $objListItem;
                    }
                }              
                if(count($arrSelected) == 0){
                    return null;
                }else{
                    return $arrSelected[0];
                }
            case "SelectedValue":
            	$objItem = $this->__get('SelectedItem');
            	if(!is_null($objItem)){
            		return $objItem->Value;
            	}else{
            		return null;
            	}
            case "SelectedItems":
                $arrSelected = array();
                 foreach($this->arrListItems as $objListItem){
                    if($objListItem->Selected){
                        $arrSelected[] = $objListItem;
                    }
                }
                
                return $arrSelected;
                
            default:
				//throw new Exception("Not porperty exists with name '" . $strName . "' in class " . __CLASS__);
               return parent::__get($strName);
               
        }
    }

    /////////////////////////
    // Public Properties: SET
    /////////////////////////
    public function __set($strName, $mixValue) {
        switch ($strName) {
            /*
            case "Selected":
                try {
                    return ($this->blnSelected = QType::Cast($mixValue, QType::Boolean));
                } catch (QCallerException $objExc) {
                    $objExc->IncrementOffset();
                    throw $objExc;
                }
             */
            default:
				//throw new Exception("Not porperty exists with name '" . $strName . "' in class " . __CLASS__);
                return parent::__set($strName, $mixValue);
               
        }
    }
    public function SetValue($mixValue){
        foreach($this->arrListItems as $objListItem){
            if($objListItem->Value == $mixValue){
                $objListItem->Selected = true;
            }else{
                $objListItem->Selected = false;
            }
        }
        $this->blnModified = true;
        return true;
    }
    public function GetValue(){
        return $this->SelectedValue;
    }
}
?>