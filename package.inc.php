<?php
 define("__MJAX__", dirname(dirname(__FILE__ ).'/MJax'));
define("__MJAX_CORE__", __MJAX__ . "/_core");
define("__MJAX_CORE_VIEW__", __MJAX_CORE__ . '/view');
define("__MJAX_CORE_ASSET_URL__", MLCApplication::GetAssetUrl('', 'MJax'));
		
MLCApplicationBase::$arrClassFiles['MJaxApplication'] = __MJAX_CORE__ . '/MJaxApplication.class.php';
MLCApplicationBase::$arrClassFiles['MJaxFormBase'] = __MJAX_CORE__ . '/MJaxFormBase.class.php';
MLCApplicationBase::$arrClassFiles['MJaxForm'] = __MJAX_CORE__ . '/MJaxForm.class.php';
MLCApplicationBase::$arrClassFiles['MJaxControl'] = __MJAX_CORE__ . '/MJaxControl.class.php';
MLCApplicationBase::$arrClassFiles['MJaxControlBase'] = __MJAX_CORE__ . '/MJaxControlBase.class.php';
MLCApplicationBase::$arrClassFiles['MJaxCssClass'] = __MJAX_CORE__ . '/MJaxCssClass.class.php';
MLCApplicationBase::$arrClassFiles['MJaxControlStyle'] = __MJAX_CORE__ . '/MJaxControlStyle.class.php';
MLCApplicationBase::$arrClassFiles['MJaxTextBox'] = __MJAX_CORE__ . '/MJaxTextBox.class.php';
MLCApplicationBase::$arrClassFiles['MJaxButton'] = __MJAX_CORE__ . '/MJaxButton.class.php';
MLCApplicationBase::$arrClassFiles['MJaxImageButton'] = __MJAX_CORE__ . '/MJaxImageButton.class.php';
MLCApplicationBase::$arrClassFiles['MJaxListBox'] = __MJAX_CORE__ . '/MJaxListBox.class.php';
MLCApplicationBase::$arrClassFiles['MJaxListItem'] = __MJAX_CORE__ . '/MJaxListItem.class.php';
MLCApplicationBase::$arrClassFiles['MJaxPanel'] = __MJAX_CORE__ . '/MJaxPanel.class.php';
MLCApplicationBase::$arrClassFiles['MJaxAutoCompleteTextBox'] = __MJAX_CORE__ . '/MJaxAutoCompleteTextBox.class.php';
MLCApplicationBase::$arrClassFiles['MJaxDialogBox'] = __MJAX_CORE__ . '/MJaxDialogBox.class.php';
MLCApplicationBase::$arrClassFiles['MJaxNivoSlideShow'] = __MJAX_CORE__ . '/MJaxNivoSlideShow.class.php';
MLCApplicationBase::$arrClassFiles['MJaxJqFancySlideShow'] = __MJAX_CORE__ . '/MJaxJqFancySlideShow.class.php';
MLCApplicationBase::$arrClassFiles['MJaxLinkButton'] = __MJAX_CORE__ . '/MJaxLinkButton.class.php';
MLCApplicationBase::$arrClassFiles['MJaxUploadPanel'] = __MJAX_CORE__ . '/MJaxUploadPanel.class.php';
MLCApplicationBase::$arrClassFiles['MJaxControlProxy'] = __MJAX_CORE__ . '/MJaxControlProxy.class.php';
MLCApplicationBase::$arrClassFiles['MJaxCheckBox'] = __MJAX_CORE__ . '/MJaxCheckBox.class.php';
MLCApplicationBase::$arrClassFiles['MJaxFormStateHandler'] = __MJAX_CORE__ . '/MJaxFormStateHandler.class.php';
MLCApplicationBase::$arrClassFiles['MJaxTable'] = __MJAX_CORE__ . '/MJaxTable.class.php';
MLCApplicationBase::$arrClassFiles['MJaxTableRow'] = __MJAX_CORE__ . '/MJaxTableRow.class.php';
MLCApplicationBase::$arrClassFiles['MJaxRadioBox'] = __MJAX_CORE__ . '/MJaxRadioBox.class.php';
MLCApplicationBase::$arrClassFiles['MJaxUploadBox'] = __MJAX_CORE__ . '/MJaxUploadBox.class.php';


//header_asset
MLCApplicationBase::$arrClassFiles['MJaxHeaderAsset'] = __MJAX_CORE__ . '/header_assets/MJaxHeaderAsset.class.php';
MLCApplicationBase::$arrClassFiles['MJaxCssHeaderAsset'] = __MJAX_CORE__ . '/header_assets/MJaxCssHeaderAsset.class.php';
MLCApplicationBase::$arrClassFiles['MJaxJSHeaderAsset'] = __MJAX_CORE__ . '/header_assets/MJaxJSHeaderAsset.class.php';
MLCApplicationBase::$arrClassFiles['MJaxMetaHeaderAsset'] = __MJAX_CORE__ . '/header_assets/MJaxMetaHeaderAsset.class.php';
//plugins
MLCApplicationBase::$arrClassFiles['MJaxPluginBase'] = __MJAX_CORE__ . '/plugins/MJaxPluginBase.class.php';
MLCApplicationBase::$arrClassFiles['MJaxSelectMenuPlugin'] = __MJAX_CORE__ . '/plugins/MJaxSelectMenuPlugin.class.php';
MLCApplicationBase::$arrClassFiles['MJaxAnimatePlugin'] = __MJAX_CORE__ . '/plugins/MJaxAnimatePlugin.class.php';
MLCApplicationBase::$arrClassFiles['MJaxQTipPlugin'] = __MJAX_CORE__ . '/plugins/MJaxQTipPlugin.class.php';
MLCApplicationBase::$arrClassFiles['MJaxNivoSlideShowPlugin'] = __MJAX_CORE__ . '/plugins/MJaxNivoSlideShowPlugin.class.php';
MLCApplicationBase::$arrClassFiles['MJaxJqFancySlideShowPlugin'] = __MJAX_CORE__ . '/plugins/MJaxJqFancySlideShowPlugin.class.php';
MLCApplicationBase::$arrClassFiles['MJaxAutoCompletePlugin'] = __MJAX_CORE__ . '/plugins/MJaxAutoCompletePlugin.class.php';
MLCApplicationBase::$arrClassFiles['MJaxScrollToPlugin'] = __MJAX_CORE__ . '/plugins/MJaxScrollToPlugin.class.php';
MLCApplicationBase::$arrClassFiles['MJaxFancyboxPlugin'] = __MJAX_CORE__ . '/plugins/MJaxFancyboxPlugin.class.php';
MLCApplicationBase::$arrClassFiles['MJaxDatePickerPlugin'] = __MJAX_CORE__ . '/plugins/MJaxDatePickerPlugin.class.php';
MLCApplicationBase::$arrClassFiles['MJaxLimitPlugin'] = __MJAX_CORE__ . '/plugins/MJaxLimitPlugin.class.php';
//Extension
MLCApplicationBase::$arrClassFiles['MJaxExtensionBase'] = __MJAX_CORE__ . '/extension/MJaxExtensionBase.class.php';

require_once(__MJAX_CORE__ .  "/_enum.php");
require_once(__MJAX_CORE__ .  "/_actions.php");
require_once(__MJAX_CORE__ .  "/_events.php");
require_once(__MJAX_CORE__ .  "/_functions.inc.php");
require_once(__MJAX_CORE__ .  "/_exceptions.inc.php");
