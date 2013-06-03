<?php
class MJaxTableRow extends MJaxControl{
	 protected $arrData = array();
	 public function AddData($mixData, $strPropName = null){
	 	if(!is_null($strPropName)){
			$this->arrData[$strPropName] = $mixData;	
		}else{
			$this->arrData[] = $mixData;
		}	
	 }
	 public function GetData($strPropName){
	 	
	 	return $this->arrData[$strPropName];
	 }
	 public function Render($blnPrint = true, $blnRenderAsAjax = false){
	 	//After acomidating for the base data we need to acomidate for  the controls as well
	 	if($blnRenderAsAjax){
            $strElementOverride = 'control';
            $this->Attr('transition', $this->strTransition);
        }else{
            $strElementOverride = 'tr';
        }
        $strRendered = parent::Render();
        $strHeader = sprintf("<%s id='%s' name='%s' %s>\n", $strElementOverride, $this->strControlId, $this->strControlId, $this->GetAttrString());
		foreach($this->objParentControl->GetColumnTitles() as $strTitle => $mixData){
			if(array_key_exists($strTitle, $this->arrData)){

				$mixData = $this->arrData[$strTitle];

				if(
					(is_object($mixData)) &&
					($mixData instanceof MJaxControl)
				){
					$strHtml = $mixData->Render(false);
					$strRendered .= '<td>' . $strHtml . '</td>';
				}/*elseif(is_callable($mixData)){
                    $strHtml = $mixData($this);
                    $strRendered .= '<td>' . $strHtml . '</td>';
                }*/else{
					$strRendered .= '<td>' . $mixData . '</td>';
				}
			}else{
				$strRendered .= '<td>&nbsp;</td>';
			}
		}
		$strFooter = sprintf("</%s>", $strElementOverride);
        if(!$blnRenderAsAjax){
            $strRendered = $strHeader . $strRendered . $strFooter;
        }else{
            $strRendered = $strHeader . MLCApplication::XmlEscape(trim($strRendered)) . $strFooter;
        }
		$this->blnModified = false;
		if($blnPrint){
            echo($strRendered);
        }else{
            return $strRendered;
        }
	 }
}
