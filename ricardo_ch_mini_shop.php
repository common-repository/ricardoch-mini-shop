<?php
/*
Plugin Name: ricardo.ch Mini-Shop
Plugin URI: http://www.ricardo.ch/mini-shop
Version: 1.0
*/
?>
<?

class ricardo_ch_mini_shop extends WP_Widget {
function ricardo_ch_mini_shop() {
	parent::WP_Widget(false, $name = 'ricardo.ch Mini-Shop');
}

function widget($args, $instance) {
	extract( $args );
	if($instance["api_id"]!="0" && $instance["api_id"]!="") {
?>
<div style='width:100%;height:450px;text-align:center;'><iframe frameBorder="0" border="0" style="border:none;background:none;" allowTransparency="true" scrolling="no" width="320" height="415" src="http://widget.ricardo.ch/widget/#/user/<?=$instance["api_id"]?>/<?=$instance["api_lang"]?>"></iframe><br /><span style='text-align:center;color:#999;font-size:10px;'>(c) by <a style='color:#999;' href='http://www.ricardo.ch' target='popup'>ricardo.ch</a></span></div>
<?php
	}
}

function update($new_instance, $old_instance) {
	// Check for username/passw:
        $aUI=$this->CheckForUpdate($new_instance);
	$sErr="";
	$sStatus=$aUI["status"];
	if(IntVal($aUI["id"])<=0 || $sStatus=="") {
		$sErr=__('The username or password are not valid');
	} else {
		switch($sStatus) {
			case "Active":
			case "ActiveWithMoneyBookers":
			case "Debited":
				// ok
			break;
			case "Closed":
				$sErr=__("The account is closed");
			break;
			case "NotValidAliasPwd":
				$sErr=__("Username or password are not valid");
			break;
			case "NotValidIdentifier":
				$sErr=__("The identifier is not valid");
			break;
			case "NotSetSAC":
				$sErr=__("The account activation code has not yet been registered");
			break;
			case "NotSetAddressPhone":
				$sErr=__("The address or phone are not set for this account");
			break;
			case "Banned":
				$sErr=__("The account is banned");
			break;
			case "NotValidEmail":
				$sErr=__("The email is not valid");
			break;
			case "BlacklistedIP":
				$sErr=__("The API member connexion IP is banished");
			break;
			default:
				$sErr=__("Unknown error status code").":".$sStatus;
			break;
		}
	}
	if($sErr!="") {
		$new_instance["api_last_error"]=$sErr;
		$new_instance["api_id"]=0;
	} else {
		$new_instance["api_last_error"]="";
		$new_instance["api_id"]=$aUI["id"];
	}
	return $new_instance;
}

function CheckForUpdate(&$aVars) {
	$sUsername=$aVars["api_username"];
	$lang_id=1;
	$ap_conf = array('identifier'=>'deiner', 'apiurl'=>array('accountService'=>'https://ws.qxlricardo.com/qxlricardowebservices/accountService.asmx?WSDL',
'updateService'=>'https://ws.qxlricardo.com/qxlricardowebservices/updateService.asmx?WSDL'),
'apiurlbeta'=>array('accountService'=>'https://ws.betaqxl.com/qxlricardowebservices/accountService.asmx?WSDL',
'updateService'=>'https://ws.betaqxl.com/qxlricardowebservices/updateService.asmx?WSDL'),
'soap.wsdl_cache' => 1,
'soap.wsdl_cache_enabled' => 1,
'CountryNr'       => 2,
'PartnerId'       => 2,
'PartnerIdfr'           => 200000
);
 
$aQuery = array('userCountryNr' => $ap_conf['CountryNr'],
'PartnerNr' => $ap_conf['PartnerId'],
'LanguageNr' => $lang_id,
'userNick' => $aVars["api_username"],
'userIP' => '0.0.0.0.',
'userPassword' => $aVars["api_passw"],
'Identifier' => $ap_conf['identifier']
);               
           
$s = new SoapClient($ap_conf['apiurl']['accountService'], array('trace' => 1, 'exceptions'=> 1, 'soap_version' => SOAP_1_2));  // Open The SOAP
$Xml = $s->CheckAccount($aQuery);  // Start The Function
$iID=IntVal($Xml->CheckAccountResult->ID);
$sStatus=$Xml->CheckAccountResult->AccountStatus;
	return array("id"=>$iID, "status"=>$sStatus);
}

function form($instance) {
$sApiUsername=esc_attr($instance['api_username']);
$sApiPassw=esc_attr($instance['api_passw']);
$sApiLang=esc_attr($instance['api_lang']);
$sApiErr=esc_attr($instance['api_last_error']);
if($sApiErr!="") {
	echo '<div style="color:#ff0000">'.stripslashes($sApiErr).'</div><br>';
}
?>
<p>
<label for="<?php echo $this->get_field_id('api_username'); ?>">Username:
<input class="widefat" id="<?php echo $this->get_field_id('api_username'); ?>" name="<?php echo $this->get_field_name('api_username'); ?>" type="text" value="<?php echo htmlspecialchars(stripslashes($sApiUsername)); ?>" />
</label>
</p>
<p>
<label for="<?php echo $this->get_field_id('api_passw'); ?>">Password:
<input class="widefat" id="<?php echo $this->get_field_id('api_passw'); ?>" name="<?php echo $this->get_field_name('api_passw'); ?>" type="password" value="<?php echo htmlspecialchars(stripslashes($sApiPassw)); ?>" />
</label>
</p>
<p>
<label for="<?php echo $this->get_field_id('api_lang'); ?>">Language:
<select class="widefat" id="<?php echo $this->get_field_id('api_lang'); ?>" name="<?php echo $this->get_field_name('api_lang'); ?>">
<option value="de"<?php if($sApiLang=="de") echo " SELECTED"; ?>>Deutsch</option>
<option value="fr"<?php if($sApiLang=="fr") echo " SELECTED"; ?>>Fran&ccedil;ais</option>
</select>
</label>
</p>
<?php
}

}

function ricardo_ch_mini_shop_install() {
}

register_activation_hook(__FILE__,'ricardo_ch_mini_shop_install');

add_action('widgets_init', create_function('', 'return register_widget("ricardo_ch_mini_shop");'));

?>