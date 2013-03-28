<?php 
class ListSection{
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
		$strReturn = '<li data-role="list-divider" role="heading">' . $this->strHeader . '</li>';
		foreach($this->arrItems as $intIndex=>$objItem){
			$strReturn .= $objItem->Render(false);
		}
		if($blnPrint){
			echo $strReturn;
		}else{
			return $strReturn;
		}
	}
}
?>


