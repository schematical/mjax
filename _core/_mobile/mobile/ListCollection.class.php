<?php 
class ListCollection{
	protected $strHeader = null;
	protected $arrItems = null;
	public function __construct($strHeader, $arrItems = array()){
		$this->strHeader = $strHeader;
		$this->arrItems = $arrItems;
	}
	public function AddItem($objItem){
		$this->arrItems[] = $objItem;
	}
	public function Render($blnPrint = true){
		$strReturn = '<ul data-role="listview" data-divider-theme="a" data-inset="true">';
		foreach($this->arrItems as $intIndex=>$objItem){
			$strReturn .= $objItem->Render(false);
		}
		$strReturn .= '</ul>';
		if($blnPrint){
			echo $strReturn;
		}else{
			return $strReturn;
		}
	}
}
?>


