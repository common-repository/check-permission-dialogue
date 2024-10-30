<?php
global $require_wordpress;
//set this to TRUE when using this as a part of wordpress, to prevent direct script execution
//set it to FALSE when using this on a stand-alone php site that doesn't use wordpress
$require_wordpress=true;

//on wordpress, this file should not be executed directly
if($require_wordpress===true){
	defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
}

//checks the current permission state for a given access control (by default this is cookies, specifically tracking cookies)
//arguments:
//	$perm_key: the permission to check the value of, by default this is "cookie"
//return:
//	true if permission is allowed, false if not
function chk_perm_get_perm_for($perm_key='cookie'){
	$perm_val=false;
	if(isset($_COOKIE['chk_perm_'.$perm_key])){
		$perm_val=($_COOKIE['chk_perm_'.$perm_key]==='allow');
	}
	return $perm_val;
}

//get the css class that's specific to the browser the user is using:  e.g. firefox
//arguments:
//	none
//return:
//	a string which should be applied as a css class to browser-specific displays
function chk_perm_get_browser_css_class(){
	//chrome is the default as it's the most widely-used desktop browser
	$browser='chrome';
//	$browser='';
	
	$user_agent=$_SERVER['HTTP_USER_AGENT'];

	//check for the WebKit and Blink rendering engines (chrome, safari)
	if((strpos($user_agent,'AppleWebKit')!==false) || (strpos($user_agent,'Chrome')!==false)){
		$browser='chrome';
//		$browser='chrome dark'; //my theme on chromium fits better with the dark setting...
	//check for the Gecko rendering engine, which firefox uses
	//NOTE: the order of these checks is important as chrome user agents can include "like Gecko"
	}elseif(strpos($user_agent,'Gecko')!==false){
		$browser='firefox';
	//check for the Trident rendering engine, which is used in IE
	}elseif(strpos($user_agent,'Trident')!==false){
		//IE is not supported; default to chrome
	//check for the EdgeHTML rendering engine, which is used in M$ Edge
	}elseif(strpos($user_agent,'Edge')!==false){
		//Edge is not supported; default to chrome
	}
	
	//check for iOS and mobile android
	if((strpos($user_agent,'iPhone')!==false)||(strpos($user_agent,'iPad')!==false)){
		$browser=' ios'; //NOTE: this does NOT apply the chrome styling, as iOS styling over-rides that
	}
	if(strpos($user_agent,'Android')!==false){
		$browser.=' android';
	}
	
	//NOTE: mobile vs desktop displays will be detected using media-query in css
	//so we don't detect mobile here
	//but we do detect mobile browsers to style accordingly

	return $browser;
}

//helper function for chk_perm_show_widget; output the <input type="hidden"> displays
//for previously-set GET data, so that new requests also include old GET data
//arguments:
//	$exclusions: an array of keys to NOT output in this way
//return:
//	none
//side-effects:
//	outputs a series of hidden <input> tags which pass through all existing $_GET data
//	for use with a form
function chk_perm_include_get_data($exclusions) {
	//include any existing GET data as hidden fields here
	//so that this works correctly on admin pages
	if(count(array_keys($_GET))>0){
		$get_vars=array_keys($_GET);
		foreach($get_vars as $get_var){
			//if we're not supposed to output for this variable
			//then skip it and check the next one
			if(in_array($get_var,$exclusions)){
				continue;
			}
			?>
			<input type='hidden' style='display:none !important;' name='<?php echo str_replace('\'','&apos;',$get_var); ?>' value='<?php echo str_replace('\'','&apos;',$_GET[$get_var]); ?>'>
			<?php
		}
	}
}

//output the html necessary for the check permission dialogue, when a saved setting is not detected
//arguments:
//	$perm_key: the permission to request; defaults to "cookie" (for tracking cookies); this must not contain spaces!
//	$browser_class: the browser-specific class to apply (when null or nullstring this is detected through chk_perm_get-browser_css_class())
//	$other_classes: other css classes to apply to the widget to alter its display in pre-defined ways
//return:
//	none
//side-effects:
//	outputs the html for the check permission dialogue widget
function chk_perm_show_widget($perm_key='cookie',$bg_class='', $browser_class='',$other_classes='fixed',$img_base_path='img/',$admin_preview=false){
	//for admin preview, skip the actual permission check
	if($admin_preview===false){
		//if a setting is already saved for this permission, then the dialogue isn't needed and we can return without any output
		if(isset($_COOKIE['chk_perm_'.$perm_key])){
			return;
		}
	}
	
	//when no explicit browser class is given
	if(is_null($browser_class) || (strlen($browser_class)===0)){
		//detect it from the user agent
		$browser_class=chk_perm_get_browser_css_class();
	}
	
	//if we got here and didn't return then a saved setting doesn't yet exist for this permission and we need to prompt the user to set one
	
	//if no explicit background class was given and this is on iOS
	//then make mobile adjustments as this is probably an iPad
	if((is_null($bg_class) || (strlen($bg_class)===0)) && (strpos($browser_class,' ios')!==false)){
		$bg_class='mobile';
	}

	$icon_inline="<img class='chk-p-inline-icon' alt='Cookie Icon' src='".$img_base_path."floppy-o.svg' height='16' width='16'>";
	$icon_block="<img class='chk-p-icon' alt='Cookie Icon' src='".$img_base_path."floppy-o.svg' height='24' width='24'>";
	$perm_desc='Use cookies to track your activity';
	switch($perm_key){
		case 'third_party_scripts':
			$icon_inline="<img class='chk-p-inline-icon' alt='Script Icon' src='".$img_base_path."file-o.svg' height='16' width='16'>";
			$icon_block="<img class='chk-p-icon' alt='Script Icon' src='".$img_base_path."file-o.svg' height='24' width='24'>";
			$perm_desc='Run third-party scripts';
			break;

		//the default case was already set at variable initialization so do nothing here
		case 'cookie':
		default:
			break;
	}
?>
	<div
		class='chk-perm-widget-bg <?php echo $bg_class; ?>'
		id='<?php echo $admin_preview?'chk-perm-widget-bg-preview':'chk-perm-widget-bg'; ?>'
	>
		<div
			class='chk-perm-widget <?php echo $browser_class.' '.$other_classes; ?>'
			id='<?php echo $admin_preview?'chk-perm-widget-preview':'chk-perm-widget'; ?>'
		>
			<div class='chk-p-icon-cont'>
				<?php echo $icon_block; ?>
			</div>
			<div class='chk-p-text'>
				<div class='chk-p-hostname'>
					<?php echo $_SERVER['HTTP_HOST']; ?>
				</div>
				<div class='chk-p-permissions'>
					<?php echo $icon_inline; ?>
					<?php echo $perm_desc; ?>
				</div>
				<br class='chk-p-learn-link-separator'>
				<a class='chk-p-learn-link' target='_blank' rel='noopener' href='https://support.mozilla.org/en-US/kb/cookies-information-websites-store-on-your-computer'>Learn more...</a>
			</div>
			<div class='chk-p-button-list'>
	<!--
				<a class='chk-p-button block' href='javascript:;'></a>
				<a class='chk-p-button allow' href='javascript:;'></a>
	-->
				<!-- Cookie storage is handled server-side via getback to provide functionality for users who have javascript disabled -->
				<form method="get" action="" class='chk-p-button-form'>
					<?php chk_perm_include_get_data($exclusions=['chk_perm_'.$perm_key]); ?>
					<input type='hidden' name='chk_perm_<?php echo $perm_key; ?>' value='block'>
					<button type='submit' class='chk-p-button block'></button>
				</form>
				<form method="get" action="" class='chk-p-button-form'>
					<?php chk_perm_include_get_data($exclusions=['chk_perm_'.$perm_key]); ?>
					<input type='hidden' name='chk_perm_<?php echo $perm_key; ?>' value='allow'>
					<button type='submit' class='chk-p-button allow'></button>
				</form>
			</div>
	<!--
			<a class='chk-p-close-button' href='javascript:;'>
				&times;
			</a>
	-->
			<form method="get" action="" class='chk-p-close-button-form'>
				<?php chk_perm_include_get_data($exclusions=['chk_perm_'.$perm_key]); ?>
				<input type='hidden' name='chk_perm_<?php echo $perm_key; ?>' value='block'>
				<button type='submit' class='chk-p-close-button'>&times;</button>
			</form>
		</div>
	</div>
<?php
}

//provide a link which creates a request to clear the current setting for a given permission type
//arguments:
//	$perm_key: the permission to clear
//	$link_text: the text of the ink to display to the user
//return:
//	none
//side-effects:
//	outputs the html for a post-based form link which will clear the setting for the given permission
function chk_perm_clear_link($perm_key='cookie',$link_text='Clear Permissions'){
?>
	<form method="get" action="" class="chk-perm-clear-link-form">
		<?php chk_perm_include_get_data($exclusions=['chk_perm_'.$perm_key]); ?>
		<input type='hidden' name='chk_perm_<?php echo $perm_key; ?>' value='clear'>
		<button type='submit' class='chk-perm-clear-link'><?php echo $link_text; ?></button>
	</form>
<?php
}

//handle a getback (like postback but for GET data) call by saving or clearing settings
//this should be called prior to any output
//arguments:
//	$perm_key: the permission to store or clear, defaults to "cookie" for tracking cookies
//return:
//	true if the request was handled by this function, false otherwise (for getbacks that were not related to the check permission dialogue)
//side-effects:
//	saves or clears the permission setting included in the $_GET data
function chk_perm_handle_getback($perm_key='cookie'){
	//if we have a setting
	if(isset($_GET['chk_perm_'.$perm_key])){
		if($_GET['chk_perm_'.$perm_key]==='clear'){
			//remove the existing cookie, if there is one (set it expired)
			//1 corresponds to 1970 in unix EPOCH time plus one second
			//this is more reliable and simpler than subtracting a value from time()
			setcookie('chk_perm_'.$perm_key,'',1,'/');
			
			//clear the cookie for the remainder of the current request
			//without this it would only be cleared on the following request
			unset($_COOKIE['chk_perm_'.$perm_key]);
		}else{
			$allow=($_GET['chk_perm_'.$perm_key]==='allow');
			
			//remember what the user's preference was for all subsequent page loads
			//NOTE: these cookies expire at the end of the session
			setcookie('chk_perm_'.$perm_key,($allow)?'allow':'block',0,'/');
			
			//treat the cookie as set for the remainder of the current request as well, so as not to re-show the dialogue
			//without this it would only be visible on the following request
			$_COOKIE['chk_perm_'.$perm_key]=($allow)?'allow':'block';
		}
		return true;
	}
	return false;
}

//filter out html elements from the given DOM tree to enforce the cookie permission
//arguments:
//	$DOMtree: the DOM version of the html to filter
//	$scripts: a list of <script> and <noscript> tags within the $DOMtree
//	$iframes: a list of <iframe> and <img> tags within the $DOMtree
//return:
//	returns an ordered array consisting of
//		whether or not there were blacklisted nodes found (true/false)
//		the updated $DOMtree with the blacklisted items removed
//side-effects:
//	deletes blacklisted tracking cookies
function chk_perm_sanitize_html_cookie($DOMtree,$scripts,$iframes){
	//the following domains are blacklisted for scripts, iframes, and images
	$domain_blacklist=[
		//remove google tag manager scripts
		//remove google analytics iframes
		'googletagmanager.com',

		//remove google analytics scripts
		'g.doubleclick.net',
		'analytics.js',
		'analytics.min.js',
		'doubleclick',
		'ga.js', //legacy google analytics

		//remove facebook analytics scripts
		'connect.facebook.net',
		//remove facebook analytics iframes
		'facebook.com/tr',

		//remove crazyegg tracking scripts
		'crazyegg.com',
	];

	//NOTE: because google cookie names include the google analytics ID and we don't know what that is at the plugin level
	//we have to consider the cookie blacklist as a PREFIX blacklist
	//it's possible that this could cause unexpected side-effects but it's an unforunate result of google's decisions
	
	$cookie_prefix_blacklist=[
		//delete (expire) google analytics cookies
		'_ga',
		'_gid',
		
		//delete (expire) google tag manager cookies
		'_gat',
		'__utm',
		
		//delete (expire) facebook cookies
		'_js_reg_fb',
		'_js_datr',
		'fr',
		'_fbp',
		'_fb',
		
		//delete (expire) crazyegg cookies
		//cookie names can be cross-referenced with https://www.crazyegg.com/cookies
		'_crazyegg_session',
		'ce_login',
		'ce_signup_flow',
		'ce2ab',
		'ceac',
		'cean',
		'cehc',
		'celi',
		'_ceir',
		'_CEFT',
		'_ceg.',
		'cer.',
	];

	//go through all scripts and iframes in the document and remove any that include a blacklisted string
	//in either the value or the src attribute
	$blacklisted_nodes=[];
	foreach([$scripts,$iframes] as $tag_array){
		for($n=0;$n<count($tag_array);$n++){
			$value=$tag_array[$n]->nodeValue;
			$attribs=$tag_array[$n]->attributes;
			if($attribs!==null){
				$src=$attribs->getNamedItem('src');
				if($src!==null){
					$src=$src->nodeValue;
				}
			}
			foreach([$value,$src] as $val){
				//null attributes can't be blacklisted so just skip them
				if($val===null){
					continue;
				}
				//check if this attribute is blacklisted
				foreach($domain_blacklist as $domain){
					if(strpos($val,$domain)){
						//if so, add it to a list of nodes to remove
						array_push($blacklisted_nodes,$tag_array[$n]);
					}
				}
			}
		}
	}
	
	//if no blacklisted nodes were found, then note that as part of this function's return value
	//and use that to hide the display in that case, since there's no point asking for a permission that's not needed and would have no effect
	$dialog_req=true;
	if(count($blacklisted_nodes)===0){
		$dialog_req=false;
	}
	
	//remove all nodes that we've detected shouldn't be there
	foreach($blacklisted_nodes as $node){
		if($node->parentNode!==null){
			$node->parentNode->removeChild($node);
		}else{
			try{
				$DOMtree->removeChild($node);
			}catch(DOMException $e){
//				print($e);
				
				//we can't remove something that doesn't have a parent
				//due to limitations of the HTML DOM
				//so this is a silent error/failure; SORRY!
			}
		}
	}

	//get a complete list of all domains up to the top-level domain
	//so that we can remove cookies even if they are defined for a higher-level domain than the full domain name
	$full_domain=$_SERVER['HTTP_HOST'];
	$domain_levels=explode('.',$full_domain);
	$domains=[$full_domain];
	for($n=1;$n<count($domain_levels);$n++){
		$domain=$domain_levels[$n];
		for($lvl_idx=$n+1;$lvl_idx<count($domain_levels);$lvl_idx++){
			$domain.='.'.$domain_levels[$lvl_idx];
		}
		array_push($domains,$domain);
	}
	foreach($domains as $domain){
		foreach($_COOKIE as $ck_name=>$ck_val){
			foreach($cookie_prefix_blacklist as $prefix){
				//NOTE: blacklisted cookies are defined by PREFIX, meaning that a cookie with a partially matching name still gets re-set
				if(strpos($ck_name,$prefix)===0){
					//TODO: test that this deletion works properly in as many cases as possible
					setcookie($ck_name,'',1,'/',$domain);
				}
			}
		}
	}

	return array($dialog_req,$DOMtree);
}

//filter out tracking scripts from html (enforce the chk_perm_cookie permission block setting)
//arguments:
//	$perm_key: the permission to enforce
//	$html: the html to search for offending scripts
//return:
//	updated html with the offending scripts removed
//side-effects:
//	enforces the given permission; for $perm_key 'cookie' this clears known tracking cookies
function chk_perm_sanitize_html($perm_key='cookie',$html=''){
	//if permission is allowed then do no filtering
	$allowed=chk_perm_get_perm_for($perm_key);
	if($allowed===true){
		return $html;
	}

	//parse the document as html to check for specific tag contents
	$DOMtree=new DOMDocument();
	
	//suppress internal non-fatal errors because we don't control the html of the site
	$error_level=libxml_use_internal_errors(true);

	$DOMtree->loadHTML($html);

	//restore normal error handling in case they use those errors for something else
	libxml_use_internal_errors($error_level);

	//NOTE: DOM nodes are "late-bound" so we need to eagerly evaluate them now by use of a loop
	//so they can be removed without error later
	$scripts=[];
	foreach($DOMtree->getElementsByTagName('script') as $script){
		array_push($scripts,$script);
	}
	foreach($DOMtree->getElementsByTagName('noscript') as $script){
		array_push($scripts,$script);
	}
	
	$iframes=[];
	foreach($DOMtree->getElementsByTagName('iframe') as $iframe){
		array_push($iframes,$iframe);
	}
	foreach($DOMtree->getElementsByTagName('img') as $img){
		array_push($iframes,$img);
	}
	
	if($perm_key==='cookie'){
		//cookie permission handling is done in a separate function which takes the DOM nodes parsed above as arguments
		$sanitization_result=chk_perm_sanitize_html_cookie($DOMtree,$scripts,$iframes);
		$dialog_req=$sanitization_result[0];
		$DOMtree=$sanitization_result[1];
	}
	
	//save the DOMtree back to a string for output
	$html=$DOMtree->saveHTML();

	//if we're on wordpress, then strip trailing </body></html> tags
	//because the wordpress hooks don't allow this function to be called after those tags are closed
	//and the DOM parser auto-adds the closing tags
	if(defined('ABSPATH')){
		$dangling_tags='</body></html>';
		$html=trim($html);
		$html_ending=substr($html,strlen($html)-strlen($dangling_tags),strlen($html));
		if($html_ending===$dangling_tags){
			$html=substr($html,0,strlen($html)-strlen($dangling_tags));
			
			//if the widget dialogue isn't required at all then hide it for users with javascript enabled
			//NOTE: while I would like to do this without javascript, because of execution order I can't
			//so users who don't have javascript enabled will still have a functioning permission setup,
			//they will just see this dialog in cases where the site administrator has configured it but doesn't need it
			if($dialog_req===false){
				$html.='<script type="text/javascript">(function () { var chk_perm_widget_bg=document.getElementById("chk-perm-widget-bg"); if(chk_perm_widget_bg!==null){ chk_perm_widget_bg.style.display="none"; } }());</script>';
			}
		}
	}
	
	return $html;
}

//loads javascript (progressive enhancement; still works without javascript)
//arguments:
//	$perm_key: the name of the permission to load javascript for (by default 'cookie')
//return:
//	none
//side-effects:
//	outputs javascript that runs on page load and takes over check perm widget forms
//	to avoid get data showing up in urls unnecessarily
function chk_perm_load_js($perm_key='cookie'){
?>
	<!-- Check Permission Dialogue -->
	<script type='text/javascript'>
	
	//progressive enhancement: optional javascript for browsers that support it
	//this takes over allow, block, and clear actions and executes instead of the php that would normally be run
	//it might seem pointless because you still have to reload the page but because we're using GET data on the php calls
	//using js allows us to keep the URL from changing
	//which is definitely an enhancement
	//it also ensures compatibility with cookie cutter GDPR auto-deny; https://cookie-cutter.sourceforge.io/
	
	window.addEventListener('DOMContentLoaded',function () {
		function chk_perm_dialog_close(){
			let dialogs=Array.from(document.querySelectorAll('.chk-perm-widget-bg'));
			console.log('dialogs=',dialogs);
			for(let idx=0;idx<dialogs.length;idx++){
				dialogs[idx].style.display='none';
			}
			
			//NOTE: in order for permissions changes to take effect a refresh is required
			//even though the cookie has already been set by the time this function is called
			setTimeout(function () {
				window.location.href=window.location.href;
			},600);
		}
		
		//make all form widget buttons work by setting the appropriate cookie and then refreshing the page only after the cookie is set
		let q_prefix='.chk-perm-widget-bg .chk-perm-widget ';
		let block_btns=Array.from(document.querySelectorAll(q_prefix+' .chk-p-button-list .chk-p-button.block'));
		let cancel_btns=Array.from(document.querySelectorAll(q_prefix+' .chk-p-close-button'));
		let allow_btns=Array.from(document.querySelectorAll(q_prefix+' .chk-p-button-list .chk-p-button.allow'));
		let all_btns=block_btns.concat(cancel_btns).concat(allow_btns);
		for(let idx=0;idx<all_btns.length;idx++){
			all_btns[idx].addEventListener('click',function (event){
				event.stopPropagation();
				event.preventDefault();
				
				//both block/close and allow buttons are in this all_btns list
				//so we determine what's what based on the presence or absence of the 'allow' CSS class on the button
				if(event.target.classList.contains('allow')){
					document.cookie='chk_perm_<?php echo $perm_key; ?>=allow;path=/;';
				}else{
					document.cookie='chk_perm_<?php echo $perm_key; ?>=block;path=/;';
				}
				
				chk_perm_dialog_close();
				
				return false;
			});
		}

		//for clear links make them work as intended by deleting the stored cookie and then refreshing the page
		let clear_link_forms=Array.from(document.querySelectorAll('.chk-perm-clear-link-form'));
		for(let idx=0;idx<clear_link_forms.length;idx++){
			clear_link_forms[idx].addEventListener('submit',function (event){
				event.stopPropagation();
				event.preventDefault();
				
				//in this case just delete the cookie if any existed
				//yes, in javascript you delete a cookie by setting it to be expired; there's no better standard way
				document.cookie='chk_perm_<?php echo $perm_key; ?>=;path=/;expires=Thu, 01 Jan 1970 00:00:01 GMT';
				
				chk_perm_dialog_close();
				
				return false;
			});
		}
	});
	</script>

	<?php
}

//initialize a page load that enfoces the chk_perm settings
//arguments:
//	none
//return:
//	none
//side-effects:
//	re-directs the output from stdout to a temporary buffer until chk_perm_load_end is called
function chk_perm_load_start($perm_keys=['cookie']){
	ob_start(NULL,0,(PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE));
}

//complete a page load that enfoces the chk_perm settings
//arguments:
//	$perm_keys: a list of permissions to enforce, defaults to ['cookies']
//return:
//	none
//side-effects:
//	applies filters for the given perm_keys and pipes the output buffer to stdout, then re-sets stdout to be the default output location for the remainder of the load
//	note: no filtering will be applied on output which is generated AFTER this function call completes
function chk_perm_load_end($perm_keys=['cookie']){
	$html=ob_get_clean();
	if($html!==false){
		foreach($perm_keys as $perm_key){
			$html=chk_perm_sanitize_html($perm_key,$html);
		}

		print($html);
	}
}

//NOTE: in chk-perm-dialog.php we link chk_perm_load_start and chk_perm_load_end to wordpress actions (wp_loaded and wp_footer respectively)


//display the admin settings page for wordpress users
//arguments:
//	none
//return:
//	none
//side-effects:
//	displays the wordpress admin settings page
function chk_perm_dialog_settings_html(){
	//user must have 'administrator' capability
	if(!current_user_can('administrator')){
		return;
	}
	
	//TODO: when/if we have admin-configurable settings, handle it with $_GET['settings-updated'] data here
	
	?>
	<div class='chk-perm-admin-settings'>
		<h1>Check Permission Dialogue Settings</h1>
		<hr>
		<h2>Enable permission checking for...</h2>
		<form action='options.php' method='post'>
			<div>
				<input type='checkbox' id='chk-perm-cookies' name='chk-perm-cookies' checked='checked' disabled>
				<label for='chk-perm-cookies'><strong>Cookies</strong> - users will be able to opt-in to tracking cookies and tracking scripts from google, facebook, and crazyegg; until a user opts in all occurrances of these trackers before wp_footer() will be blocked (removed from the html prior to page load)</label>
			</div>
		</form>
		<hr>
		<h2>Preview</h2>
		<noscript>
			<p class='note'>
				Changing between preview displays requires javascript.  
			</p>
		</noscript>
		<p class='note mobile-only'>
			Because you are viewing the admin page on a mobile display, you will not be able to view the desktop previews properly as there is not room to display them.  
			Instead, you will see the mobile view of all widgets.  
		</p>
		<div class='chk-perm-preview-frame' id='chk-perm-preview-frame'>
			<select id='chk-perm-widget-preview-browser' name='chk-perm-widget-preview-browser' class='chk-perm-widget-preview-browser'
				onchange='(function (e) { chk_perm_preview_select(e); }(event))'
				value='chrome'
			>
				<option value=',,chrome'>Chrome on desktop (desktop default)</option>
				<option value=',,firefox'>Firefox on desktop</option>
				<option value=',,chrome dark'>Dark-theme Chrome on desktop</option>
				<option value=',,ios'>iOS (iPad)</option>
				<option value='mobile,,chrome android mobile'>Android (mobile default)</option>
				<option value='mobile,,firefox mobile'>Firefox on mobile</option>
				<option value='mobile,,ios'>Mobile iOS (iPhone)</option>
				<option value=',,'>Unstyled on desktop (users will never see this)</option>
				<option value='mobile,,'>Unstyled on mobile (users will never see this)</option>
			</select>

			<?php chk_perm_show_widget('cookie','','chrome','',plugin_dir_url(__FILE__).'img/',true); ?>

			<script type='text/javascript'>
			function chk_perm_preview_select(e){
				var preview_select_classes=document.getElementById("chk-perm-widget-preview-browser").value.split(',');
				var preview_frame_classes='chk-perm-preview-frame '+preview_select_classes[0];
				var widget_bg_classes='chk-perm-widget-bg '+preview_select_classes[1];
				var widget_classes='chk-perm-widget '+preview_select_classes[2];
				document.getElementById('chk-perm-preview-frame').className=preview_frame_classes;
				document.getElementById('chk-perm-widget-bg-preview').className=widget_bg_classes;
				document.getElementById('chk-perm-widget-preview').className=widget_classes;
			}
			//initialize the preview based on the select value
			chk_perm_preview_select(null);
			</script>
		</div>
		<hr>
		<h2>Clear your current permission settings</h2>
		<div>
			<strong>Cookie</strong>
			<div>
				Permission is currently	<?php
				if(isset($_COOKIE['chk_perm_cookie'])){
					$perm_val=($_COOKIE['chk_perm_cookie']==='allow');
					echo $perm_val?'ALLOWED':'DENIED';
				}else{
					echo 'UNSET (denied by default)';
				}
				?>
				<?php chk_perm_clear_link('cookie','Clear Coookie Setting'); ?>
			</div>
		</div>
	</div>
	<?php
}

//if GET data was provided, then handle it as a getback (postback but with GET)
if(chk_perm_handle_getback('cookie')){
	//we handled the getback, so the remainder of this request is a page refresh using the updated setting
}

?>
