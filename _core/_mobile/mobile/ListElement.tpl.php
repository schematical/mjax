<?php 
class ListElement{
	protected $strPageName = null;
	protected $strAction = null;
	protected $strValue = null;
	protected $blnUseAjax = null;
	protected $strText = null;
	
	
	public function __construct($strText, $strPageName = null, $strAction = null, $strValue = null){
		$this->strText = $strText;
		$this->strPageName = $strPageName;
		if(!is_bool($strAction)){
			$this->strAction = $strAction;
			$this->strValue = $strValue;
		}else{
			$this->blnUseAjax = $strAction;			
		}		
	}
	public function UseAjax($blnUseAjax = true){
		$this->blnUseAjax = $blnUseAjax;
	}
	public function Render($blnPrint = true){
		if($this->blnUseAjax){
			$strPageLink = Appliacation::RenderActionURL($this->strPageName, $this->strAction, $this->strValue);
		}else{
			$strPageLink = '#' . $this->strPageName;
		}
		$strReturn = '<li data-theme="c">
			<a href="' . $strPageLink . '" data-transition="slide">' .
				$strText .
			'</a>
		</li>';
		if($blnPrint){
			echo $strReturn;
		}else{
			return $strReturn;
		}
	}
}
?>
