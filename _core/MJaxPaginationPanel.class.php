<?php
class MJaxPaginationPanel extends MJaxPanel{
    protected $objCollection;

    public function __construct($objParentControl, $strControlId = null){
        parent::__construct($objParentControl, $strControlId);
        $this->strTemplate = __MJAX_CORE_VIEW__ . '/' . get_class($this) . '.tpl.php';
        $this->AddCssClass('pagination');
    }
    public function SetCollection(MLCBaseEntityCollection &$objCollection){
        $this->objCollection = $objCollection;
        for( $i = 0; $i < $objCollection->QueryPaginationLength(); $i++){
            $lnkPage  = new MJaxLinkButton($this);
            $lnkPage->Text = $i + 1;
            $lnkPage->ActionParameter = $i;
            $lnkPage->AddAction(
                $this,
                'lnkPage_click'
            );
        }
    }
    public function lnkPage_click($strFormId, $strControlId, $intPage){
        $this->objCollection->Limit(
            $this->objCollection->LimitCount,
            $intPage * $this->objCollection->LimitCount
        );
        $this->strActionParameter = $this->objCollection;
        $this->TriggerEvent('mjax-pagination-panel-page-change-event');
    }
    
}
class MJaxPaginationPanelPageChangeEvent extends MJaxEventBase{
    protected $strEventName = 'mjax-pagination-panel-page-change-event';
}