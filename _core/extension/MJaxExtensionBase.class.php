<?php
abstract class MJaxExtensionBase{
	protected $objControl = null;
	public function InitControl($objControl){
		$this->objControl = $objControl;
		
	}
	public function SetControl($objControl){
		$this->objControl = $objControl;
	}
	public function RunPreRender($objControl){
		$this->objControl = $objControl;
		
	}
}
