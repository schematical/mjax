<?php
/* 
 * This is a basic dif for use with the MJax Framework
 */
class MJaxTable extends MJaxControl{
	protected $strFooterText = null;
    protected $arrColumnTitles = array();
	protected $arrDataEntites = array();
	public function GetColumnTitles(){
		return $this->arrColumnTitles;
	}
	public function AddRow($arrColumnData = null){
		$objRow = new MJaxTableRow($this);
		//TODO: Possibly allow user to set default row styling
		if(!is_null($arrColumnData)){
			
			foreach($arrColumnData as $strPropName => $strData){
				$objRow->AddData($strData, $strPropName);
			}
		}
		return $objRow;
	}
	public function SetDataEntites($arrDataEntites){
		//$this->strDataMode = MJaxTableDataMode::DATA_ENTITY;
		if(!is_array($arrDataEntites)){
			$this->arrDataEntites = $arrDataEntites->GetCollection();
		}else{
			$this->arrDataEntites = $arrDataEntites;
		}
		$arrColumnData = array();
		foreach($this->arrDataEntites as $objEntity){
        	foreach($this->arrColumnTitles as $strTitle => $mixData){

                if(is_string($mixData)){
        		    $arrColumnData[$strTitle] = $objEntity->$mixData;
                }
			}
            //_dp($arrColumnData);
			$objRow = $this->AddRow($arrColumnData);
			$objRow->ActionParameter = $objEntity->GetId();
			//_dp($objRow);
		}
		
		
	}
	public function AddColumn($strTitle, $mixColumn = null){
        if(is_string($mixColumn)){
            if(!is_null($mixColumn)){
                $this->arrColumnTitles[$strTitle] = $mixColumn;
            }else{
                $this->arrColumnTitles[$strTitle] = $strTitle;
            }
        }elseif(is_callable($mixColumn)){
            $this->arrColumnTitles[$strTitle] = $mixColumn;
        }else{
            throw MLCMLCWrongTypeException(__FUNCTION__, 'mixColumn');
        }
	}
    public function Render($blnPrint = true, $blnRenderAsAjax = false){
        if($blnRenderAsAjax){
            $strElementOverride = 'control';
            $this->Attr('transition', $this->strTransition);
        }else{
            $strElementOverride = 'table';
        }
        $strRendered = parent::Render();
        $strHeader = sprintf("<%s id='%s' name='%s' %s>\n", $strElementOverride, $this->strControlId, $this->strControlId, $this->GetAttrString());
        //If template is set render template
        if(!is_null($this->strTemplate)){
          $strRendered .= $this->RenderFromTempate();
        }else{
          $strRendered .= $this->RenderDefault();
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
	public function RenderFromTemplate(){
	  	if(!file_exists($this->strTemplate)){
            throw new Exception("Template file (" . $this->strTemplate .") does not exist");
        }
        global $_CONTROL;
        $objPrevControl = $_CONTROL;
        $_CONTROL = $this;
        $_FORM = $this->objForm;
        $strRendered = $this->objForm->EvaluateTemplate($this->strTemplate);
        $_CONTROL = $objPrevControl;
		return $strRendered;
	}
	public function RenderDefault(){
		$strRendered = '<thead>';
		$strRendered .= '<tr>';
		foreach($this->arrColumnTitles as $strTitle => $mixProp){
			$strRendered .= sprintf('<th scope="col" class="rounded-company">%s</th>', $strTitle);
		}
		
		$strRendered .= '</tr>';
		$strRendered .= '</thead>';
		$strRendered .= '<tbody>';
        foreach($this->arrChildControls as $objChildControl){
            $strRendered .= $objChildControl->Render(false);
        }
		
		$strRendered .= '<tbody>';
        $strRendered .= sprintf(
        				'<tfoot>
							<tr>
								<td colspan="%s" class="rounded-foot-left">%s</td>
							</tr>
						</tfoot>',
						count($this->arrColumnTitles),
						$this->strFooterText
		);
		return $strRendered;
	}
	/////////////////////////
    // Public Properties: GET
    /////////////////////////
    public function __get($strName) {
        switch ($strName) {
            case "Rows": return $this->arrChildControls;            
            default:
                try {
                    return parent::__get($strName);
                } catch (QCallerException $objExc) {
                    $objExc->IncrementOffset();
                    throw $objExc;
                }
        }
    }

    /////////////////////////
    // Public Properties: SET
    /////////////////////////
    public function __set($strName, $mixValue) {
        $this->blnModified = true;
        switch ($strName) {              
            default:
                try {
                    return parent::__set($strName, $mixValue);
                } catch (QCallerException $objExc) {
                    $objExc->IncrementOffset();
                    throw $objExc;
                }
        }
    }
}
?>