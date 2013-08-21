<?php
class MJaxTextArea extends MJaxTextBox{

    public function __construct($objParentControl,$strControlId = null, $arrAttrs = null) {
        parent::__construct($objParentControl,$strControlId, $arrAttrs);
        $this->strTextMode = MJaxTextMode::MultiLine;
    }
}