<?php
abstract class BaseApplication{
	public static $strTplName = null;
	public static $mixResponse = null;
	public static $arrIncludes = null;
	public static $objDataBase = null;
	public static $blnDBConnected = null;
	public static $strRenderMode = null;
	public static $arrFormData = array();
	public static $objFacebook = null;
	public static $strMiscJS = '';
	public static function Init(){
		Application::$strRenderMode = RenderMode::MOBILE_HTML;
		
	}
	public static function Run($strTplName = 'index'){
		Application::Init();
		if(
			(array_key_exists(QS::FORM_DATA, $_POST))
		){
			
			//Application::$strRenderMode = RenderMode::MOBILE_AJAX_HTML;
			
			// Cleanup from FormState Base64 Encoding
			$strSerializedForm = $_POST[QS::FORM_DATA];
			$strSerializedForm = str_replace('-', '+', $strSerializedForm);
			$strSerializedForm = str_replace('_', '/', $strSerializedForm);
			
			$strSerializedForm = base64_decode($strSerializedForm);
			Application::$arrFormData = unserialize($strSerializedForm);
			//die(print_r(Application::$arrFormData));
		}
		
		
		
		if(array_key_exists(QS::ACTION,$_REQUEST)){		
			$strAction = $_REQUEST[QS::ACTION];
		}else{		
			$strAction = Action::DISP_DEFAULT;
		}
		if(array_key_exists(QS::VALUE,$_REQUEST)){	
			$mixData = $_REQUEST[QS::VALUE];
		}else{
			$mixData = null;
		}
		if(defined('TRACK_EVENTS') && strtolower(TRACK_EVENTS) == 'true'){
			
			$arrData = array();
			$arrData['action'] = $strAction;
			$arrData['action_param'] = $mixData;
			$arrData['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
			MLCEventTrackingDriver::Track('RUN_' . $strAction, $arrData);
		}
		
		
		Application::RunAction($strAction, $mixData);
	
		Application::RenderApp();
		
		Application::CleanUp();
	
	}
	public static function FormData($strName, $mixValue = null){
		
		if(!is_null($mixValue)){
			Application::$arrFormData[$strName] = $mixValue;
		}
		
		if(!array_key_exists($strName, Application::$arrFormData)){
			return null;
		}else{
			return Application::$arrFormData[$strName];
		}
		
	}
	
	
	/*------------------------------------------------------------------------------
	 *
	* Actions
	*
	* -----------------------------------------------------------------------------*/
	
	public static function DisplayDefault(){
		//HOME PAGE
		if(array_key_exists(QS::TPL, $_REQUEST)){
			$strPageName = $_REQUEST[QS::TPL];
		}else{
			$strPageName = PageTPL::HOME;
		}
		
		return Application::RenderPage($strPageName, $strPageName);
		
		 
	}
	/*------------------------------------------------------------------------------
	 * 
	 * Render Views
	 * 
	 * -----------------------------------------------------------------------------*/
	public static function RenderActionURL($strPageName, $strAction = null, $strValue = null){
		$strPageLink = '?tpl=' . $strPageName;
		if(!is_null($strAction)){
			$strPageLink .= '&' . QS::ACTION . '=' . $strAction;
		}
		if(!is_null($strValue)){
			$strPageLink .= '&' . QS::VALUE . '=' . $strValue;
		}
		return $strPageLink;
	}
	public static function RenderApp(){
		
		
		
		switch(Application::$strRenderMode){
			case(RenderMode::JSON):
				echo(json_encode(Application::$mixResponse));
			break;
			case(RenderMode::MOBILE_HTML):
				require(__TPL_ASSETS__ . '/app.tpl.php');
			break;
			case(RenderMode::MOBILE_AJAX_HTML):
				echo Application::$mixResponse;
			break;
			case(RenderMode::DESKTOP_HTML):
				require(__TPL_ASSETS__ . '/desktop.tpl.php');
			break;
		}
	}
	public static function RenderPage($strPageName, $strTemplateName, $arrTplData = null){
		if(is_null($arrTplData)){
			$arrTplData = Application::$arrFormData;
		}
		$arrTplData['_PAGE_NAME'] = $strPageName;
		$arrTplData['_PAGE_TPL'] = __TPL_ASSETS__ . '/' . $strTemplateName . '.tpl.php';
		
		return Application::EvaluateTemplate(__TPL_ASSETS__ . '/misc/page.tpl.php', $arrTplData);
	}
	public static function RenderDesktopPage($strPageName, $strTemplateName, $arrTplData = null){
		if(is_null($arrTplData)){
			$arrTplData = Application::$arrFormData;
		}
		$arrTplData['_PAGE_NAME'] = $strPageName;
		$strPageTpl = __TPL_ASSETS__ . '/' . $strTemplateName . '.tpl.php';
		
		return Application::EvaluateTemplate($strPageTpl, $arrTplData);
	}
	public static function RenderInputPage($strPageName, $strTemplateName, $intNextInput, $strInputDesc, $strInputValue ='', $strHeaderText = ''){
		$arrTplData = array();
		$arrTplData['_ACTION_URL'] = Appliacation::RenderActionURL(PageTPL::BLANK, Action::DISP_INPUT, $strNextInput);		
		$arrTplData['_INPUT_VALUE'] = $strInputValue;
		$arrTplData['_HEADER_TEXT'] = $strHeaderText;
		$arrTplData['_INPUT_DESC'] = $strInputDesc;
		return Application::RenderPage($strTemplateName, $strPageName, $arrTplData);
	}
	public static function RenderListPage($strPageName, $objListColl, $strHeaderText = '', $strTemplateName = PageTPL::LIST_NAV){
		$arrTplData = array();
		$arrTplData['_LIST_COLL'] = $objListColl;
		$arrTplData['_HEADER_TEXT'] = $strHeaderText;
		return Application::RenderPage($strTemplateName, $strPageName, $arrTplData);
	}
	
	public static function EvaluateTemplate($strTemplate, $arrTplData = array()){
	
		if ($strTemplate) {
			/*foreach(Application::$arrGlobalTemplateData as $strKey=>$mixValue){
			 $$strKey = $mixValue;
			}*/
	
			foreach($arrTplData as $strKey=>$mixValue){
				$$strKey = $mixValue;
			}
			// Store the Output Buffer locally
			$strAlreadyRendered = ob_get_contents();
			ob_clean();
	
			// Evaluate the new template
			$blnStart = ob_start('__ObHandler');
			require($strTemplate);
			$strTemplateEvaluated = ob_get_contents();
	
			ob_end_clean();
	
			// Restore the output buffer and return evaluated template
			print($strAlreadyRendered);
	
	
			return $strTemplateEvaluated;
		} else {
			return null;
		}
	
	}
	public static function InitDB($strDataBaseConnection){
		if(!Application::$blnDBConnected){
			Application::$blnDBConnected = true;
			$arrDBInfo = unserialize($strDataBaseConnection);
	
			Application::$objDataBase = new MySqlDataConnection($arrDBInfo['host'], $arrDBInfo['db_name'], $arrDBInfo['user'], $arrDBInfo['password']);
			
			Application::$objDataBase->Connect();
			
			MLCDBDriver::AddDataConnection(Application::$objDataBase);
		}
	}
	public static function RenderFormData(){
		// Don't Encrypt the FormState -- Simply Base64 Encode it
		$strFormState = serialize(Application::$arrFormData);
		$strFormState = base64_encode($strFormState);

		// Cleanup FormState Base64 Encoding
		$strFormState = str_replace('+', '-', $strFormState);
		$strFormState = str_replace('/', '_', $strFormState);
		echo $strFormState;
	}
	public static function RenderFormDataDebug(){
		echo '<ul data-role="listview" data-divider-theme="b" data-inset="true">';
		foreach(Application::$arrFormData as $strKey => $mixData){
			echo '<h3>' . $strKey . '</h3>';
			echo print_r($mixData);
		}
		echo '</ul>';
	}
	public static function CleanUp(){}
	
	public static function RunRedirect(){
	
		//Track Event
		$arrData = array();
		$arrData['action'] = 'REDIRECT';
		$arrData['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		$arrData['request_data'] = $_REQUEST;
			//Get User Data
		$objUser = Application::User();
		if(!is_null($objUser)){
			$arrData['id_user'] = $objUser->idUser;
		}
		MLCEventTrackingDriver::Track($arrData['action'] , $arrData);
		$strUrl = Application::QS(QS::URL);
		if(is_null($strUrl)){
			
			$strUrl = Application::RedirectURLOverride($_REQUEST);
		}
		//Redirect
		if(is_null($strUrl)){
			$strUrl = __APP_URL__;
		}
		die(header('location: ' . $strUrl));
	}
	public static function GetRedirectUrl($mixData){
		$strUrl = __APP_URL__ . '/redirect.php?';
		if(is_string($mixData)){
			$strUrl .= 'url=' . urlencode($mixData);
		}elseif(is_array($mixData)){
			foreach($arrData as $strName => $strKey){
				$strUrl .= sprintf('%s=%s&', $strName, urlencode($strKey));
			}
		}else{
			throw new Exception(__FUNCTION__ . ":: First parameter must be either a string url or an array");
		}
		return $strUrl;
	}
	public static function RedirectURLOverride($arrData){
		return null;
	}
	public static function GetRemoteAddr(){
		if(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)){
			$strIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}else{
			$strIp = $_SERVER['REMOTE_ADDR'];
		}
		if(strpos($strIp,',') !== false) {
			$strIp = substr($strIp,0,strpos($strIp,','));
		}
		return $strIp;
	}
	public static function In($strUrl, $arrData = null){
		$ch = curl_init();
		$strUrl = 'https://api.instagram.com/v1' . $strUrl . '?';
		//curl_setopt($ch, CURLOPT_POST, true);
		if(!is_null($arrData)){
			$arrData['client_id'] = I_APP_ID;
			//curl_setopt($ch, CURLOPT_POSTFIELDS, $arrData);
		}
		foreach($arrData as $strName => $strKey){
			$strUrl .= sprintf('%s=%s&', $strName, urlencode($strKey));
		}
		MLCEventTrackingDriver::UpdateLastEventData('instagram_api_url', $strUrl);
		
		curl_setopt($ch, CURLOPT_URL, $strUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_REFERER, 'mattleaconsulting.com');
		
		$body = curl_exec($ch);
		//error_log(curl_getinfo($ch, CURLINFO_HTTP_CODE));
		curl_close($ch);
		
		// now, process the JSON string
		$json = json_decode($body, true);
		return $json;
	}

	

	public static function ParseRSS($strRss){
		//http://www.kickstarter.com/projects/feed.atom
		
		// sendRequest
		// note how referer is set manually
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $strRss);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_REFERER, 'mattleaconsulting.com');
		$body = curl_exec($ch);
		curl_close($ch);
		
		// now, process the JSON string
		$json = json_decode($body);
		return $json;
	}
	
	
	public static function User(){
		return MLCLiteAuthDriver::User();
	}
	
	
	public static function QS($strName){
		if(array_key_exists($strName, $_POST)){
			return $_POST[$strName];
		}elseif(array_key_exists($strName, $_GET)){
			return $_GET[$strName];
		}else{
			return null;
		}
	}
	
	public static function ParseFBSignedRequest($signed_request = null){
		if(is_null($signed_request)){
			$signed_request = $_REQUEST["signed_request"];
		}
	    list($encoded_sig, $payload) = explode('.', $signed_request, 2); 
	
	    $arrFBData = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
	    
	    return $arrFBData;
	}
	public static function FB($strCall, $arrParams = null){
		$arrResults = null;
		if(is_null(Application::$objFacebook)){
			Application::$objFacebook = new Facebook(array(
				  'appId'  => FB_APP_ID,
				  'secret' => FB_APP_SECRET,
				  'cookie' => true,
			));
		}
	
		$objSession = Application::$objFacebook->getSession();
		// Session based API call.
		//if ($objSession) {
			error_log("Call:" . $strCall);
		try {
			if(is_null($arrParams)){
				$arrResults = Application::$objFacebook->api($strCall);
			}else{
				$arrResults = Application::$objFacebook->api($strCall, 'POST', $arrParams);
			}
		} catch (FacebookApiException $e) {
			throw new Exception($e);
			//self::Dlg($e->getMessage());//"An error connecting to Facebook has occured");
			return $arrResults;
		}
		/*}else{
		 throw new Exception("No facebook session available");
		}*/
		//echo ("Calling: " . $strCall . "<br>");
		return $arrResults;
	}
	public static function ParseFBAuthResponse($mixData){
		if(array_key_exists('authResponse', $mixData)){
			$strFbuid = $mixData['authResponse']['userID'];
			$objUser = User::LoadByFbuid($strFbuid);
			$objDBUser = MLCLiteAuthDriver::User();
			if(is_null($objUser)){
				$objUser = $objDBUser;
				//Set up fb data
				/*
				 Array
				(
				    [id] => 184702085
				    [name] => Matt Lea
				    [first_name] => Matt
				    [last_name] => Lea
				    [username] => 3villabs
				    [location] => Array
				        (
				            [id] => 107572595931951
				            [name] => Madison, Wisconsin
				        )
				
				    [gender] => male
				    [email] => live_die@yahoo.com
				    [locale] => en_US
				)
				1
				 * */
				$arrFBData = self::FB('/' . $strFbuid);
				
				$objUser->fbuid = $arrFBData['id'];
				$objUser->username = $arrFBData['name'];
				$objUser->email = $arrFBData['email'];
				if(count($arrFBData['location']) > 0){
					$objUser->defaultLocation = $arrFBData['location'][0];
				}
				$objUser->save();
			}else{
				
				if(!is_null($objDBUser)){
					if($objDBUser->idUser != $objUser->idUser){
						$objDBUser->markDeleted();
					}
				}
			}
			return $objUser;
			
		}
		return null;
	}

	public static function GetInstagramAuthUrl(){
		return sprintf('https://instagram.com/oauth/authorize/?client_id=%s&redirect_uri=%s&response_type=code',
			 I_APP_ID,
			 urlencode(__APP_URL__)
		);
	}
	public static function GetDistance($lat1, $lng1, $lat2, $lng2){
		
		$lngLatDist = ($lat1 - $lat2);
		$lngLngDist = ($lng1 - $lng2);
		$lngDist = sqrt(($lngLatDist * $lngLatDist) + ($lngLngDist * $lngLngDist));
		$lngMileDist = ($lngDist *  69.11);
		return (round($lngMileDist, 1));
	}
	public static function Alert($strHtml, $strTitle){
		Application::$strMiscJS .= sprintf(' window.setTimeout(function(){ App.Alert("%s", "%s") }, 1000);', $strHtml, $strTitle);
	}
	public static function IsFacebookApp(){
		return array_key_exists('signed_request', $_REQUEST);
	}
	public static function RenderFriends($arrFriends, $intCount){
		$_USERS = $arrFriends;
		$_COUNT = $intCount;
		require(__TPL_ASSETS__ . '/misc/friendList.tpl.php');
	}
}