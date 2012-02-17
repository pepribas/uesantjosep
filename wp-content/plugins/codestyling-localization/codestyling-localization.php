<?php
/*
Plugin Name: CodeStyling Localization
Plugin URI: http://www.code-styling.de/english/development/wordpress-plugin-codestyling-localization-en
Description: Now you can freely manage, edit and modify your WordPress language translation files (*.po / *.mo) as usual. You won't need any additional editor have been installed. Also supports WPMU plugins, if WPMU versions has been detected.
Version: 1.99.16
Author: Heiko Rabe
Author URI: http://www.code-styling.de/english/
Text Domain: codestyling-localization
Domain Path: /languages


 License:
 ==============================================================================
 Copyright 2008 Heiko Rabe  (email : info@code-styling.de)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

   Requirements:
 ==============================================================================
 This plugin requires WordPress >= 2.5 and PHP Interpreter >= 4.4.2
 Since version 1.90 the PHP module "Tokenizer" is required.
 
  Version History:
 ==============================================================================
 Since WordPress 2.7 version history will be available by Context Help System newly introduced.
 
 */
 
//////////////////////////////////////////////////////////////////////////////////////////
//	constant definition
//////////////////////////////////////////////////////////////////////////////////////////

//Enable this only for debugging reasons. 
//Attention: the strict logging may prevent WP from proper working because of many not handled issues.
//error_reporting(E_ALL|E_STRICT);

if (function_exists('add_action')) {
	if ( !defined('WP_CONTENT_URL') )
	    define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
	if ( !defined('WP_CONTENT_DIR') )
	    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
	if ( !defined('WP_PLUGIN_URL') ) 
		define('WP_PLUGIN_URL', WP_CONTENT_URL.'/plugins');
	if ( !defined('WP_PLUGIN_DIR') ) 
		define('WP_PLUGIN_DIR', WP_CONTENT_DIR.'/plugins');
	if ( !defined('PLUGINDIR') )
		define( 'PLUGINDIR', 'wp-content/plugins' ); // Relative to ABSPATH.  For back compat.
		
	if ( !defined('WP_LANG_DIR') )
		define('WP_LANG_DIR', WP_CONTENT_DIR . '/languages');
		
	//WPMU definitions
	if ( !defined('WPMU_PLUGIN_DIR') )
		define( 'WPMU_PLUGIN_DIR', WP_CONTENT_DIR . '/mu-plugins' ); // full path, no trailing slash
	if ( !defined('WPMU_PLUGIN_URL') )
		define( 'WPMU_PLUGIN_URL', WP_CONTENT_URL . '/mu-plugins' ); // full url, no trailing slash
	if( defined( 'MUPLUGINDIR' ) == false ) 
		define( 'MUPLUGINDIR', 'wp-content/mu-plugins' ); // Relative to ABSPATH.  For back compat.

	define("CSP_PO_PLUGINPATH", "/" . plugin_basename( dirname(__FILE__) ));

    define('CSP_PO_TEXTDOMAIN', 'codestyling-localization');
    define('CSP_PO_BASE_URL', WP_PLUGIN_URL . CSP_PO_PLUGINPATH);
		
	//Bugfix: ensure valid JSON requests at IDN locations!
	//Attention: Google Chrome and Safari behave in different way (shared WebKit issue or all other are wrong?)!
	if (
		stripos($_SERVER['HTTP_USER_AGENT'], 'chrome') !== false 
		|| 
		stripos($_SERVER['HTTP_USER_AGENT'], 'safari') !== false
		||
		version_compare(phpversion(), '5.2.1', '<') //IDNA class requires PHP 5.2.1 or higher
	) {
		if (function_exists("admin_url")) {
			define('CSP_PO_ADMIN_URL', rtrim(strtolower(admin_url()), '/'));
		}else{
			define('CSP_PO_ADMIN_URL', rtrim(strtolower(get_option('siteurl')).'/wp-admin/', '/'));
		}
	}
	else{
		if (!class_exists('idna_convert'))
			require_once('includes/idna_convert.class.php');
		$idn = new idna_convert();
		if (function_exists("admin_url")) {
			define('CSP_PO_ADMIN_URL', $idn->decode(rtrim( strtolower(admin_url()) , '/'), 'utf8'));
		}else{
			define('CSP_PO_ADMIN_URL', $idn->decode(rtrim(strtolower(get_option('siteurl')).'/wp-admin/', '/'),'utf8'));
		}
	}
	
    define('CSP_PO_BASE_PATH', WP_PLUGIN_DIR . CSP_PO_PLUGINPATH);
	
	define('CSP_PO_MIN_REQUIRED_WP_VERSION', '2.5');
	define('CSP_PO_MIN_REQUIRED_PHP_VERSION', '4.4.2');
		
	register_activation_hook(__FILE__, 'csp_po_install_plugin');
}

function csp_is_multisite() {
	return (
		isset($GLOBALS['wpmu_version'])
		||
		(function_exists('is_multisite') && is_multisite())
		||
		(function_exists('wp_get_mu_plugins') && count(wp_get_mu_plugins()) > 0)
	);
}

if (function_exists('csp_po_install_plugin')) {
	//rewrite and extend the error messages displayed at failed activation
	//fall trough, if it's a real code bug forcing the activation error to get the appropriated message instead
	if (isset($_GET['action']) && isset($_GET['plugin']) && ($_GET['action'] == 'error_scrape') && ($_GET['plugin'] == plugin_basename(__FILE__) )) {
		if (
			(!version_compare($wp_version, CSP_PO_MIN_REQUIRED_WP_VERSION, '>=')) 
			|| 
			(!version_compare(phpversion(), CSP_PO_MIN_REQUIRED_PHP_VERSION, '>='))
			||
			!function_exists('token_get_all')
		) {
			load_plugin_textdomain(CSP_PO_TEXTDOMAIN, PLUGINDIR.'/codestyling-localization/languages','codestyling-localization/languages');
			echo "<table>";
			echo "<tr style=\"font-size: 12px;\"><td><strong style=\"border-bottom: 1px solid #000;\">Codestyling Localization</strong></td><td> | ".__('required', CSP_PO_TEXTDOMAIN)."</td><td> | ".__('actual', CSP_PO_TEXTDOMAIN)."</td></tr>";			
			if (!version_compare($wp_version, CSP_PO_MIN_REQUIRED_WP_VERSION, '>=')) {
				echo "<tr style=\"font-size: 12px;\"><td>WordPress Blog Version:</td><td align=\"center\"> &gt;= <strong>".CSP_PO_MIN_REQUIRED_WP_VERSION."</strong></td><td align=\"center\"><span style=\"color:#f00;\">".$wp_version."</span></td></tr>";
			}
			if (!version_compare(phpversion(), CSP_PO_MIN_REQUIRED_PHP_VERSION, '>=')) {
				echo "<tr style=\"font-size: 12px;\"><td>PHP Interpreter Version:</td><td align=\"center\"> &gt;= <strong>".CSP_PO_MIN_REQUIRED_PHP_VERSION."</strong></td><td align=\"center\"><span style=\"color:#f00;\">".phpversion()."</span></td></tr>";
			}
			if (!function_exists('token_get_all')) {
				echo "<tr style=\"font-size: 12px;\"><td>PHP Tokenizer Module:</td><td align=\"center\"><strong>active</strong></td><td align=\"center\"><span style=\"color:#f00;\">not installed</span></td></tr>";			
			}
			echo "</table>";
		}
	}
}


function csp_po_install_plugin(){
	global $wp_version;
	if (
		(!version_compare($wp_version, CSP_PO_MIN_REQUIRED_WP_VERSION, '>=')) 
		|| 
		(!version_compare(phpversion(), CSP_PO_MIN_REQUIRED_PHP_VERSION, '>='))
		|| 
		!function_exists('token_get_all')
	){
		$current = get_option('active_plugins');
		array_splice($current, array_search( plugin_basename(__FILE__), $current), 1 );
		update_option('active_plugins', $current);
		exit;
	}
}


//////////////////////////////////////////////////////////////////////////////////////////
//	general purpose methods
//////////////////////////////////////////////////////////////////////////////////////////

if (!function_exists('_n')) {
	function _n() {
		$args = func_get_args();
		return call_user_func_array('__ngettext', $args);
	}
}

if (!function_exists('_n_noop')) {
	function _n_noop() {
		$args = func_get_args();
		return call_user_func_array('__ngettext_noop', $args);
	}
}

if (!function_exists('_x')) {
	function _x() {
		$args = func_get_args();
		$what = array_shift($args); 
		$args[0] = $what.'|'.$args[0];
		return call_user_func_array('_c', $args);
	}
}

if (!function_exists('esc_js')) {
	function esc_js() {
		$args = func_get_args();
		return call_user_func_array('js_escape', $args);
	}
}

if (!function_exists('file_get_contents')) {
	function file_get_contents($filename, $incpath = false, $resource_context = null) {
		if (false === $fh = fopen($filename, 'rb', $incpath)) {
			user_error('file_get_contents() failed to open stream: No such file or directory', E_USER_WARNING);
			return false;
		}
		
		clearstatcache();
		if ($fsize = @filesize($filename)) {
			$data = fread($fh, $fsize);
		} else {
			$data = '';
			while (!feof($fh)) {
				$data .= fread($fh, 8192);
			}
		}
		
		fclose($fh);
		return $data;
	}	
}

if (!function_exists('scandir')) {
	function scandir($dir) {
		$files = array();
		$dh  = @opendir($dir);
		while (false !== ($filename = @readdir($dh))) {
		    $files[] = $filename;
		}
		@closedir($dh);
		return $files;
	}
}

function has_subdirs($base='') {
  if (!is_dir($base)) return $false;
  $array = array_diff(scandir($base), array('.', '..'));
  foreach($array as $value) : 
    if (is_dir($base.$value)) return true; 
  endforeach;
  return false;
}

function lscandir($base='', $reg='', &$data) {
  if (!is_dir($base)) return $data;
  $array = array_diff(scandir($base), array('.', '..')); 
  foreach($array as $value) : 
		if (is_file($base.$value) && preg_match($reg, $value) ) : 
			$data[] = str_replace("\\","/",$base.$value); 
		endif;
  endforeach;  
  return $data; 
}

function rscandir($base='', $reg='', &$data) {
  if (!is_dir($base)) return $data;
  $array = array_diff(scandir($base), array('.', '..')); 
  foreach($array as $value) : 
    if (is_dir($base.$value)) : 
      $data = rscandir($base.$value.'/', $reg, $data); 
    elseif (is_file($base.$value) && preg_match($reg, $value) ) : 
      $data[] = str_replace("\\","/",$base.$value); 
    endif;
  endforeach;
  return $data; 
}		

function rscanpath($base='', &$data) {
  if (!is_dir($base)) return $data;
  $array = array_diff(scandir($base), array('.', '..')); 
  foreach($array as $value) : 
    if (is_dir($base.$value)) : 
	  $data[] = str_replace("\\","/",$base.$value);
      $data = rscanpath($base.$value.'/', $data); 
    endif;
  endforeach;
  return $data; 
}		


function rscandir_php($base='', &$exclude_dirs, &$data) {
  if (!is_dir($base)) return $data;
  $array = array_diff(scandir($base), array('.', '..')); 
  foreach($array as $value) : 
    if (is_dir($base.$value)) : 
      if (!in_array($base.$value, $exclude_dirs)) : $data = rscandir_php($base.$value.'/', $exclude_dirs, $data); endif; 
    elseif (is_file($base.$value) && preg_match('/\.(php|phtml)$/', $value) ) : 
      $data[] = str_replace("\\","/",$base.$value); 
    endif;
  endforeach;
  return $data; 
}		

function file_permissions($filename) {
	static $R = array("---","--x","-w-","-wx","r--","r-x","rw-","rwx");
	$perm_o	= substr(decoct(fileperms( $filename )),3);
	return "[".$R[(int)$perm_o[0]] . '|' . $R[(int)$perm_o[1]] . '|' . $R[(int)$perm_o[2]]."]";
}

function csp_fetch_remote_content($url) {
	global $wp_version;
	$res = null;
	
	if(file_exists(ABSPATH . 'wp-includes/class-snoopy.php') && version_compare($wp_version, '3.0', '<')) {
		require_once( ABSPATH . 'wp-includes/class-snoopy.php');
		$s = new Snoopy();
		$s->fetch($url);	
		if($s->status == 200) {
			$res = $s->results;	
		}
	} else {
		$res = wp_remote_fopen($url);	
	}
	return $res;	
}

function csp_po_check_security() {
	if (!is_user_logged_in() || !current_user_can('manage_options')) {
		wp_die(__('You do not have permission to manage translation files.', CSP_PO_TEXTDOMAIN));
	}
}

function csp_find_translation_template(&$files) {
	$result = null;
	foreach($files as $tt) {
		if (preg_match('/\.pot$/',$tt)) {
			$result = $tt;
		}
	}
	return $result;
}

function csp_po_get_wordpress_capabilities() {
	$data = array();
	$data['dev-hints'] = null;
	$data['deny_scanning'] = false;
	$data['locale'] = get_locale();
	$data['type'] = 'wordpress';
	$data['img_type'] = 'wordpress';
	if (csp_is_multisite()) $data['img_type'] .= "_mu";
	$data['type-desc'] = __('WordPress',CSP_PO_TEXTDOMAIN);
	$data['name'] = "WordPress";
	$data['author'] = "<a href=\"http://codex.wordpress.org/WordPress_in_Your_Language\">WordPress.org</a>";
	$data['version'] = $GLOBALS['wp_version'];
	if (csp_is_multisite()) $data['version'] .= " | ".(isset($GLOBALS['wpmu_version']) ? $GLOBALS['wpmu_version'] : $GLOBALS['wp_version']);
	$data['description'] = "WordPress is a state-of-the-art publishing platform with a focus on aesthetics, web standards, and usability. WordPress is both free and priceless at the same time.<br />More simply, WordPress is what you use when you want to work with your blogging software, not fight it.";
	$data['status'] =  __("activated",CSP_PO_TEXTDOMAIN);
	$data['base_path'] = str_replace("\\","/", ABSPATH);
	$data['special_path'] = '';
	$data['filename'] = str_replace(str_replace("\\","/",ABSPATH), '', str_replace("\\","/",WP_LANG_DIR));
	$data['is-simple'] = false;
	$data['simple-filename'] = '';
	$data['textdomain'] = array('identifier' => 'default', 'is_const' => false );
	$data['languages'] = array();
	$data['is-path-unclear'] = false;
	$data['gettext_ready'] = true;
	$data['translation_template'] = null;
	$tmp = array();
	$data['is_US_Version'] = !is_dir(WP_LANG_DIR);
	if (!$data['is_US_Version']) {
		$files = rscandir(str_replace("\\","/",WP_LANG_DIR).'/', "/(.\mo|\.po|\.pot)$/", $tmp);
		$data['translation_template'] = csp_find_translation_template($files);
		foreach($files as $filename) {
			$file = str_replace(str_replace("\\","/",WP_LANG_DIR).'/', '', $filename);
			preg_match("/^([a-z][a-z]_[A-Z][A-Z]).(mo|po)$/", $file, $hits);
			if (empty($hits[1]) === false) {
				$data['languages'][$hits[1]][$hits[2]] = array(
					'class' => "-".(is_readable($filename) ? 'r' : '').(is_writable($filename) ? 'w' : ''),
					'stamp' => date(__('m/d/Y H:i:s',CSP_PO_TEXTDOMAIN), filemtime($filename))." ".file_permissions($filename)
				);
				$data['special_path'] = '';
			}
		}

		$data['base_file'] = (empty($data['special_path']) ? '' : $data['special_path'].'/') . $data['filename'].'/';
	}
	return $data;
}

function csp_po_get_plugin_capabilities($plug, $values) {
	$data = array();
	$data['dev-hints'] 		= null;
	$data['dev-security'] 	= null;
	$data['deny_scanning'] 	= false;
	$data['locale'] = get_locale();
	$data['type'] = 'plugins';	
	$data['img_type'] = 'plugins';	
	$data['type-desc'] = __('Plugin',CSP_PO_TEXTDOMAIN);	
	$data['name'] = $values['Name'];
	if (isset($values['AuthorURI'])) {
		$data['author'] = "<a href='".$values['AuthorURI']."'>".$values['Author']."</a>";
	}else{
		$data['author'] = $values['Author'];
	}
	$data['version'] = $values['Version'];
	$data['description'] = $values['Description'];
	$data['status'] = is_plugin_active($plug) ? __("activated",CSP_PO_TEXTDOMAIN) : __("deactivated",CSP_PO_TEXTDOMAIN);
	$data['base_path'] = str_replace("\\","/", WP_PLUGIN_DIR.'/'.dirname($plug).'/');
	$data['special_path'] = '';
	$data['filename'] = "";
	$data['is-simple'] = (dirname($plug) == '.');
	$data['simple-filename'] = '';
	$data['is-path-unclear'] = false;
	$data['gettext_ready'] = false;
	$data['translation_template'] = null;
	if ($data['is-simple']) {
		$files = array(WP_PLUGIN_DIR.'/'.$plug);
		$data['simple-filename'] = str_replace("\\","/",WP_PLUGIN_DIR.'/'.$plug);
		$data['base_path'] = str_replace("\\","/", WP_PLUGIN_DIR.'/');
	}
	else{
		$tmp = array();
		$files = rscandir(str_replace("\\","/",WP_PLUGIN_DIR).'/'.dirname($plug)."/", "/.(php|phtml)$/", $tmp);
	}
	$const_list = array();
	foreach($files as $file) {	
		$content = file_get_contents($file);
		if (preg_match("/[^_^!]load_(|plugin_)textdomain\s*\(\s*(\'|\"|)([\w\d\-_]+|[A-Z\d\-_]+)(\'|\"|)\s*(,|\))\s*([^;]+)\)/", $content, $hits)) {
			$data['textdomain'] = array('identifier' => $hits[3], 'is_const' => empty($hits[2]) );
			$data['gettext_ready'] = true;
			$data['php-path-string'] = $hits[6];
		}
		else if(preg_match("/[^_^!]load_(|plugin_)textdomain\s*\(/", $content, $hits)) {
			//ATTENTION: it is gettext ready but we don't realy know the textdomain name! Assume it's equal to plugin's name.
			//TODO: let's think about it in future to find a better solution.
			$data['textdomain'] = array('identifier' => substr(basename($plug),0,-4), 'is_const' => false );
			$data['gettext_ready'] = true;
			$data['php-path-string'] = '';	
		}
		if (isset($hits[1]) && $hits[1] != 'plugin_') 	$data['dev-hints'] = __("<strong>Loading Issue: </strong>Author is using <em>load_textdomain</em> instead of <em>load_plugin_textdomain</em> function. This may break behavior of WordPress, because some filters and actions won't be executed anymore. Please contact the Author about that.",CSP_PO_TEXTDOMAIN);
		if($data['gettext_ready'] && !$data['textdomain']['is_const']) break; //make it short :-)
		if (preg_match_all("/define\s*\(([^\)]+)\)/" , $content, $hits)) {
			$const_list = array_merge($const_list, $hits[1]);
		}
	}
	if ($data['gettext_ready']) {
		
		if ($data['textdomain']['is_const']) {
			foreach($const_list as $e) {
				$a = split(',', $e);
				$c = trim($a[0], "\"' \t");
				if ($c == $data['textdomain']['identifier']) {
					$data['textdomain']['is_const'] = $data['textdomain']['identifier'];
					$data['textdomain']['identifier'] = trim($a[1], "\"' \t");
				}
			}
		}
		$data['filename'] = $data['textdomain']['identifier'];
		//check if const contains brackets, mostly by functional defined const
		if(preg_match("/(\(|\))/", $data['textdomain']['identifier'])) {
			$data['filename'] = str_replace('.php', '', basename($plug));
			$data['textdomain']['is_const'] = false;
			$data['textdomain']['identifier'] = str_replace('.php', '', basename($plug));
			//var_dump(str_replace('.php', '', basename($plug)));
		}
	}		
	
	if (!$data['gettext_ready']) {
		//lets check, if the plugin is a encrypted one could be translated or an unknow but with defined textdomain
		//ATTENTION: mark encrypted plugins as a high security risk!!!
		if (isset($values['TextDomain']) && !empty($values['TextDomain'])) {
			$data['textdomain'] = array('identifier' => $values['TextDomain'], 'is_const' => false );
			$data['gettext_ready'] = true;
			$data['filename'] = $data['textdomain']['identifier'];
			
			$inside = token_get_all(file_get_contents(WP_PLUGIN_DIR."/".$plug));
			$encrypted = false;
			foreach($inside as $token) {
				if (is_array($token)) {
					list($id, $text) = $token;
					if (T_EVAL == $id) {
						$encrypted =true;
						break;
					}
				}
			}
			if($encrypted) {
				$data['img_type'] = 'plugins_encrypted';
				$data['dev-security'] .= __("<strong>Full Encryped PHP Code: </strong>This plugin consists out of encryped code will be <strong>eval</strong>'d at runtime! It can't be checked against exploitable code pieces. That's why it will become potential target of hidden intrusion.",CSP_PO_TEXTDOMAIN);
				$data['deny_scanning'] = true;
			}
			else {
				$data['img_type'] = 'plugins_maybe';
				$data['dev-hints'] .= __("<strong>Textdomain definition: </strong>This plugin provides a textdomain definition at plugin header fields but seems not to load any translation file. If it doesn't show your translation, please contact the plugin Author.",CSP_PO_TEXTDOMAIN);
			}
		}
	}
	
	$data['languages'] = array();
	if($data['gettext_ready']){
		if ($data['is-simple']) { $tmp = array(); $files = lscandir(str_replace("\\","/",dirname(WP_PLUGIN_DIR.'/'.$plug)).'/', "/(\.mo|\.po|\.pot)$/", $tmp); }
		else { 	$tmp = array(); $files = rscandir(str_replace("\\","/",dirname(WP_PLUGIN_DIR.'/'.$plug)).'/', "/(\.mo|\.po|\.pot)$/", $tmp); }
		$data['translation_template'] = csp_find_translation_template($files);
		foreach($files as $filename) {
			if ($data['is-simple']) {
				$file = str_replace(str_replace("\\","/",WP_PLUGIN_DIR).'/'.dirname($plug), '', $filename);
				preg_match("/".$data['filename']."-([a-z][a-z]_[A-Z][A-Z])\.(mo|po)$/", $file, $hits);		
				if (empty($hits[2]) === false) {				
					$data['languages'][$hits[1]][$hits[2]] = array(
						'class' => "-".(is_readable($filename) ? 'r' : '').(is_writable($filename) ? 'w' : ''),
						'stamp' => date(__('m/d/Y H:i:s',CSP_PO_TEXTDOMAIN), filemtime($filename))." ".file_permissions($filename)
					);
					$data['special_path'] = '';
				}
				else{
					//try to re-construct from real file.
					preg_match("/([\/a-z0-9\-_]+)-([a-z][a-z]_[A-Z][A-Z])\.(mo|po)$/", $file, $hits);
					if (empty($hits[2]) === false) {				
						$data['filename'] = $hits[1];
						$data['textdomain']['identifier'] = $hits[1];
						$data['img_type'] = 'plugins_maybe';
						$data['dev-hints'] .= __("<strong>Textdomain definition: </strong>There are problems to find the used textdomain. It has been taken from existing translation files. If it doesn't work with your install, please contact the Author of this plugin.",CSP_PO_TEXTDOMAIN);
						
						$data['languages'][$hits[2]][$hits[3]] = array(
							'class' => "-".(is_readable($filename) ? 'r' : '').(is_writable($filename) ? 'w' : ''),
							'stamp' => date(__('m/d/Y H:i:s',CSP_PO_TEXTDOMAIN), filemtime($filename))." ".file_permissions($filename)
						);
						$data['special_path'] = '';
					}
				}
			}else{
				$file = str_replace(str_replace("\\","/",WP_PLUGIN_DIR).'/'.dirname($plug), '', $filename);
				preg_match("/([\/a-z0-9\-_]*)\/".$data['filename']."-([a-z][a-z]_[A-Z][A-Z])\.(mo|po)$/", $file, $hits);
				if (empty($hits[2]) === false) {
					$data['languages'][$hits[2]][$hits[3]] = array(
						'class' => "-".(is_readable($filename) ? 'r' : '').(is_writable($filename) ? 'w' : ''),
						'stamp' => date(__('m/d/Y H:i:s',CSP_PO_TEXTDOMAIN), filemtime($filename))." ".file_permissions($filename)
					);
					$data['special_path'] = ltrim($hits[1], "/");
				}
				else{
					//try to re-construct from real file.
					preg_match("/([\/a-z0-9\-_]*)\/([\/a-z0-9\-_]+)-([a-z][a-z]_[A-Z][A-Z])\.(mo|po)$/", $file, $hits);
					if (empty($hits[3]) === false) {
						$data['filename'] = $hits[2];
						$data['textdomain']['identifier'] = $hits[2];
						$data['img_type'] = 'plugins_maybe';
						$data['dev-hints'] .= __("<strong>Textdomain definition: </strong>There are problems to find the used textdomain. It has been taken from existing translation files. If it doesn't work with your install, please contact the Author of this plugin.",CSP_PO_TEXTDOMAIN);

						$data['languages'][$hits[3]][$hits[4]] = array(
							'class' => "-".(is_readable($filename) ? 'r' : '').(is_writable($filename) ? 'w' : ''),
							'stamp' => date(__('m/d/Y H:i:s',CSP_PO_TEXTDOMAIN), filemtime($filename))." ".file_permissions($filename)
						);
						$data['special_path'] = ltrim($hits[1], "/");
					}
				}
			}
		}
		if (!$data['is-simple'] && ($data['special_path'] == '') && (count($data['languages']) == 0)) {
			$data['is-path-unclear'] = has_subdirs(str_replace("\\","/",dirname(WP_PLUGIN_DIR.'/'.$plug)).'/');
			if ($data['is-path-unclear'] && (count($files) > 0)) {
				$file = str_replace(str_replace("\\","/",WP_PLUGIN_DIR).'/'.dirname($plug), '', $files[0]);
				preg_match("/^\/([\/a-z0-9\-_]*)\//", $file, $hits);
				$data['is-path-unclear'] = false;
				if (empty($hits[1]) === false) { $data['special_path'] = $hits[1]; }
			}
		}
		
		//DEBUG:  $data['php-path-string']  will contain real path part like: "false,'codestyling-localization'" | "'wp-content/plugins/' . NGGFOLDER . '/lang'" | "GENGO_LANGUAGES_DIR" | "$moFile"
		//this may be part of later excessive parsing to find correct lang file path even if no lang files exist as hint or implementation of directory selector, if 0 languages contained
		//if any lang files may be contained the qualified sub path will be extracted out of
		//will be handled in case of  $data['is-path-unclear'] == true by display of treeview at file creation dialog 
		//var_dump($data['php-path-string']);

	}
	$data['base_file'] = (empty($data['special_path']) ? $data['filename'] : $data['special_path']."/".$data['filename']).'-';	
	return $data;
}

function csp_po_get_plugin_mu_capabilities($plug, $values){
	$data = array();
	$data['dev-hints'] = null;
	$data['deny_scanning'] = false;
	$data['locale'] = get_locale();
	$data['type'] = 'plugins_mu';	
	$data['img_type'] = 'plugins_mu';	
	$data['type-desc'] = __('μ Plugin',CSP_PO_TEXTDOMAIN);	
	$data['name'] = $values['Name'];
	if (isset($values['AuthorURI'])) {
		$data['author'] = "<a href='".$values['AuthorURI']."'>".$values['Author']."</a>";
	}else{
		$data['author'] = $values['Author'];
	}
	$data['version'] = $values['Version'];
	$data['description'] = $values['Description'];
	$data['status'] = __("activated",CSP_PO_TEXTDOMAIN);
	$data['base_path'] = str_replace("\\","/", WPMU_PLUGIN_DIR.'/');
	$data['special_path'] = '';
	$data['filename'] = "";
	$data['is-simple'] = true;
	$data['simple-filename'] = str_replace("\\","/",WPMU_PLUGIN_DIR.'/'.$plug); 
	$data['is-path-unclear'] = false;
	$data['gettext_ready'] = false;
	$data['translation_template'] = null;
	$file = WPMU_PLUGIN_DIR.'/'.$plug;

	$const_list = array();
	$content = file_get_contents($file);
	if (preg_match("/[^_^!]load_(|plugin_|muplugin_)textdomain\s*\(\s*(\'|\"|)([\w\d\-_]+|[A-Z\d\-_]+)(\'|\"|)\s*(,|\))\s*([^;]+)\)/", $content, $hits)) {
		$data['textdomain'] = array('identifier' => $hits[3], 'is_const' => empty($hits[2]) );
		$data['gettext_ready'] = true;
		$data['php-path-string'] = $hits[6];
	}
	else if(preg_match("/[^_^!]load_(|plugin_|muplugin_)textdomain\s*\(/", $content, $hits)) {
		//ATTENTION: it is gettext ready but we don't realy know the textdomain name! Assume it's equal to plugin's name.
		//TODO: let's think about it in future to find a better solution.
		$data['textdomain'] = array('identifier' => substr(basename($plug),0,-4), 'is_const' => false );
		$data['gettext_ready'] = true;
		$data['php-path-string'] = '';			
	}
	if (!($data['gettext_ready'] && !$data['textdomain']['is_const'])) {
		if (preg_match_all("/define\s*\(([^\)]+)\)/" , $content, $hits)) {
			$const_list = array_merge($const_list, $hits[1]);
		}
	}

	if ($data['gettext_ready']) {
		
		if ($data['textdomain']['is_const']) {
			foreach($const_list as $e) {
				$a = split(',', $e);
				$c = trim($a[0], "\"' \t");
				if ($c == $data['textdomain']['identifier']) {
					$data['textdomain']['is_const'] = $data['textdomain']['identifier'];
					$data['textdomain']['identifier'] = trim($a[1], "\"' \t");
				}
			}
		}
		$data['filename'] = $data['textdomain']['identifier'];
	}		
	
	$data['languages'] = array();
	if($data['gettext_ready']){
		$tmp = array(); $files = lscandir(str_replace("\\","/",dirname(WPMU_PLUGIN_DIR.'/'.$plug)).'/', "/(\.mo|\.po|\.pot)$/", $tmp); 		
		$data['translation_template'] = csp_find_translation_template($files);
		foreach($files as $filename) {
			$file = str_replace(str_replace("\\","/",WPMU_PLUGIN_DIR).'/'.dirname($plug), '', $filename);
			preg_match("/".$data['filename']."-([a-z][a-z]_[A-Z][A-Z]).(mo|po)$/", $file, $hits);		
			if (empty($hits[2]) === false) {				
				$data['languages'][$hits[1]][$hits[2]] = array(
					'class' => "-".(is_readable($filename) ? 'r' : '').(is_writable($filename) ? 'w' : ''),
					'stamp' => date(__('m/d/Y H:i:s',CSP_PO_TEXTDOMAIN), filemtime($filename))." ".file_permissions($filename)
				);
				$data['special_path'] = '';
			}
		}
	}
	$data['base_file'] = (empty($data['special_path']) ? $data['filename'] : $data['special_path']."/".$data['filename']).'-';		
	return $data;
}

function csp_po_get_theme_capabilities($theme, $values, $active) {
	$data = array();
	$data['dev-hints'] = null;
	$data['deny_scanning'] = false;
	
	//let's first check the whether we have a child or base theme
	$data['base_path'] = str_replace("\\","/", WP_CONTENT_DIR.str_replace('wp-content', '', dirname($values['Template Files'][0])).'/');
	if (file_exists($values['Template Files'][0])){
		$data['base_path'] = dirname(str_replace("\\","/",$values['Template Files'][0])).'/';
	}
		
	$folder_filesys = end(explode('/',rtrim($data['base_path'], '/')));
	$folder_data = $values['Template']; 
	$is_child_theme = $folder_filesys != $folder_data;
	
	$data['locale'] = get_locale();
	$data['type'] = 'themes';
	$data['img_type'] = ($is_child_theme ? 'childthemes' : 'themes');	
	$data['type-desc'] = ($is_child_theme ? __('Childtheme',CSP_PO_TEXTDOMAIN) : __('Theme',CSP_PO_TEXTDOMAIN));	
	$data['name'] = $values['Name'];
	$data['author'] = $values['Author'];
	$data['version'] = $values['Version'];
	$data['description'] = $values['Description'];
	$data['status'] = $theme == $active->name ? __("activated",CSP_PO_TEXTDOMAIN) : __("deactivated",CSP_PO_TEXTDOMAIN);
	if ($is_child_theme) {
		$data['status'] .= ' / <b></i>'.__('child theme of',CSP_PO_TEXTDOMAIN).' '.$values['Parent Theme'].'</i></b>';
	}
	$data['special-path'] = '';
	$data['is-path-unclear'] = false;
	$data['gettext_ready'] = false;
	$data['translation_template'] = null;
	$data['is-simple'] = false;
	$data['simple-filename'] = '';
	
	//now scanning the child's own files
	$parent_files = array();
	$files = array();
	$const_list = array();
	$tmp = array();
	$files = rscandir($data["base_path"], "/\.(php|phtml)$/", $tmp);
	foreach($files as $themefile) {
		$main = file_get_contents($themefile);
		if (
			preg_match("/[^_^!]load_(child_theme_|theme_|)textdomain\s*\(\s*(\'|\"|)([\w\d\-_]+|[A-Z\d\-_]+)(\'|\"|)\s*(,|\))/", $main, $hits)
			||
			preg_match("/[^_^!]load_(child_theme_|theme_|)textdomain\s*\(\s*/", $main, $hits)			
		) {
			if (isset($hits[1]) && $hits[1] != 'child_theme_' && $hits[1] != 'theme_') 	$data['dev-hints'] = __("<strong>Loading Issue: </strong>Author is using <em>load_textdomain</em> instead of <em>load_theme_textdomain</em> or <em>load_child_theme_textdomain</em> function. This may break behavior of WordPress, because some filters and actions won't be executed anymore. Please contact the Author about that.",CSP_PO_TEXTDOMAIN);
		
			//fallback for variable names used to load textdomain, assumes theme name
			if(isset($hits[3]) && strpos($hits[3], '$') !== false) {
				unset($hits[3]);
				if (isset($data['dev-hints'])) $data['dev-hints'] .= "<br/><br/>";
				$data['dev-hints'] = __("<strong>Textdomain Naming Issue: </strong>Author uses a variable to load the textdomain. It will be assumed to be equal to theme name now.",CSP_PO_TEXTDOMAIN);
			}			
			//make it short
			$data['gettext_ready'] = true;
			if ($data['gettext_ready']) {
				if (!isset($hits[3])) {
					$data['textdomain'] = array('identifier' => $values['Template'], 'is_const' => false );
				}else {
					$data['textdomain'] = array('identifier' => $hits[3], 'is_const' => empty($hits[2]) );
				}
				$data['languages'] = array();
			}

			$dn = $data["base_path"];
			$tmp = array();
			$lng_files = rscandir($dn, "/(\.mo|\.po|\.pot)$/", $tmp);
			$data['translation_template'] = csp_find_translation_template($lng_files);
			$sub_dirs = array();
			$naming_convention_error = false;
			foreach($lng_files as $filename) {
				//somebody did place buddypress themes at sub folder hierarchy like:  themes/buddypress/bp-default
				//results at $values['Template'] to 'buddypress/bp-default' which damages the preg_match
				$theme_langfile_check =  end(explode('/',$values['Template']));
				preg_match("/\/(|".preg_quote($theme_langfile_check)."\-)([a-z][a-z]_[A-Z][A-Z])\.(mo|po)$/", $filename, $hits);
				if (empty($hits[1]) === false) {
					$naming_convention_error = true;

					$data['filename'] = '';
					$sd = dirname(str_replace($dn, '', $filename));
					if ($sd == '.') $sd = '';
					if (!in_array($sd, $sub_dirs)) $sub_dirs[] = $sd;
					
				}elseif (empty($hits[2]) === false) {
					$data['languages'][$hits[2]][$hits[3]] = array(
						'class' => "-".(is_readable($filename) ? 'r' : '').(is_writable($filename) ? 'w' : ''),
						'stamp' => date(__('m/d/Y H:i:s',CSP_PO_TEXTDOMAIN), filemtime($filename))." ".file_permissions($filename)
					);
					$data['filename'] = '';
					$sd = dirname(str_replace($dn, '', $filename));
					if ($sd == '.') $sd = '';
					if (!in_array($sd, $sub_dirs)) $sub_dirs[] = $sd;
				}
			}
			if($naming_convention_error && count($data['languages']) == 0) {
				if (isset($data['dev-hints'])) $data['dev-hints'] .= "<br/><br/>";
				$data['dev-hints'] .= sprintf(__("<strong>Naming Issue: </strong>Author uses unsupported language file naming convention! Instead of example <em>de_DE.po</em> the non theme standard version <em>%s</em> has been used. If you translate this Theme, only renamed language files will be working!",CSP_PO_TEXTDOMAIN), $values['Template'].'-de_DE.po');
			}
			
			//completely other directories can be defined WP if >= 2.7.0
			global $wp_version;
			if (version_compare($wp_version, '2.7', '>=')) {
				if (count($data['languages']) == 0) {
					$data['is-path-unclear'] = has_subdirs($dn);
					if ($data['is-path-unclear'] && (count($lng_files) > 0)) {
						foreach($lng_files as $file) {
							$f = str_replace($dn, '', $file);
							if (
								preg_match("/^([a-z][a-z]_[A-Z][A-Z])\.(mo|po|pot)$/", basename($f))
								||
								preg_match("/\.po(t|)$/", basename($f))
							) {
								$data['special_path'] = (dirname($f) == '.' ? '' : dirname($f));
								$data['is-path-unclear'] = false;
								break;
							}
						}
					}
				}
				else{
					if ($sub_dirs[0] != '') {
						$data['special_path'] = ltrim($sub_dirs[0], "/");
					}
				}
			}

		}
		if($data['gettext_ready'] && !$data['textdomain']['is_const']) break; //make it short :-)
		if (preg_match_all("/define\s*\(([^\)]+)\)/" , $main, $hits)) {
			$const_list = array_merge($const_list, $hits[1]);
		}
	}
	$data['base_file'] = (empty($data['special_path']) ? '' : $data['special_path']."/");

	if ($data['gettext_ready']) {	
		if ($data['textdomain']['is_const']) {
			foreach($const_list as $e) {
				$a = split(',', $e);
				$c = trim($a[0], "\"' \t");
				if ($c == $data['textdomain']['identifier']) {
					$data['textdomain']['is_const'] = $data['textdomain']['identifier'];
					$data['textdomain']['identifier'] = trim($a[1], "\"' \t");
				}
			}
		}
		
		//fallback for constants defined by variables! assume the theme name instead
		if(strpos($data['textdomain']['identifier'], '$') !== false) {
			$data['textdomain']['identifier'] = $values['Template'];
			if (isset($data['dev-hints'])) $data['dev-hints'] .= "<br/><br/>";
			$data['dev-hints'] = __("<strong>Textdomain Naming Issue: </strong>Author uses a variable to define the textdomain constant. It will be assumed to be equal to theme name now.",CSP_PO_TEXTDOMAIN);
		}			

	}		
	//check now known issues for themes
	if(isset($data['textdomain']['identifier']) && $data['textdomain']['identifier'] == 'woothemes') {
		if (isset($data['dev-hints'])) $data['dev-hints'] .= "<br/><br/>";
		$data['dev-hints'] .= __("<strong>WooThemes Issue: </strong>The Author is known for not supporting a translatable backend. Please expect only translations for frontend or contact the Author for support!",CSP_PO_TEXTDOMAIN);
	}
	
	
	return $data;
}

function csp_po_get_buddypress_capabilities($plug, $values) {
	$data = array();
	$data['dev-hints'] = null;
	$data['deny_scanning'] = false;
	$data['locale'] = get_locale();
	$data['type'] = 'plugins';	
	$data['img_type'] = 'buddypress';	
	$data['type-desc'] = __('BuddyPress',CSP_PO_TEXTDOMAIN);	
	$data['name'] = $values['Name'];
	if (isset($values['AuthorURI'])) {
		$data['author'] = "<a href='".$values['AuthorURI']."'>".$values['Author']."</a>";
	}else{
		$data['author'] = $values['Author'];
	}
	$data['version'] = $values['Version'];
	$data['description'] = $values['Description'];
	$data['status'] = is_plugin_active($plug) ? __("activated",CSP_PO_TEXTDOMAIN) : __("deactivated",CSP_PO_TEXTDOMAIN);
	$data['base_path'] = str_replace("\\","/", WP_PLUGIN_DIR.'/'.dirname($plug).'/');
	$data['special_path'] = '';
	$data['filename'] = "buddypress";
	$data['is-simple'] = false;
	$data['simple-filename'] = '';
	$data['is-path-unclear'] = false;
	$data['gettext_ready'] = true;	
	$data['translation_template'] = null;
	$data['textdomain'] = array('identifier' => 'buddypress', 'is_const' => false );
	$data['special_path'] = 'bp-languages';
	$data['languages'] = array();
	$tmp = array(); 
	$files = lscandir(str_replace("\\","/",dirname(WP_PLUGIN_DIR.'/'.$plug)).'/bp-languages/', "/(\.mo|\.po|\.pot)$/", $tmp); 
	$data['translation_template'] = csp_find_translation_template($files);
	foreach($files as $filename) {
		$file = str_replace(str_replace("\\","/",WP_PLUGIN_DIR).'/'.dirname($plug), '', $filename);
		preg_match("/".$data['filename']."-([a-z][a-z]_[A-Z][A-Z]).(mo|po)$/", $file, $hits);		
		if (empty($hits[2]) === false) {				
			$data['languages'][$hits[1]][$hits[2]] = array(
				'class' => "-".(is_readable($filename) ? 'r' : '').(is_writable($filename) ? 'w' : ''),
				'stamp' => date(__('m/d/Y H:i:s',CSP_PO_TEXTDOMAIN), filemtime($filename))." ".file_permissions($filename)
			);
		}
	}
	$data['base_file'] = (empty($data['special_path']) ? $data['filename'] : $data['special_path']."/".$data['filename']).'-';	
	return $data;
}

function csp_po_get_bbpress_on_buddypress_capabilities($plug, $values) {
	$data = array();
	$data['dev-hints'] = null;
	$data['deny_scanning'] = false;
	$data['locale'] = get_locale();
	$data['type'] = 'plugins';	
	$data['img_type'] = 'buddypress-bbpress';	
	$data['type-desc'] = __('bbPress',CSP_PO_TEXTDOMAIN);	
	$data['name'] = "bbPress";
	$data['author'] = "<a href='http://bbpress.org/'>bbPress.org</a>";
	$data['version'] = '-n.a.-';
	$data['description'] = "bbPress is forum software with a twist from the creators of WordPress.";
	$data['status'] = is_plugin_active($plug) ? __("activated",CSP_PO_TEXTDOMAIN) : __("deactivated",CSP_PO_TEXTDOMAIN);
	$data['base_path'] = str_replace("\\","/", WP_PLUGIN_DIR.'/'.dirname($plug).'/bp-forums/bbpress/');
	if (!is_dir($data['base_path'])) return false;
	$data['special_path'] = '';
	$data['filename'] = "";
	$data['is-simple'] = false;
	$data['simple-filename'] = '';
	$data['is-path-unclear'] = false;
	$data['gettext_ready'] = true;	
	$data['translation_template'] = null;
	$data['textdomain'] = array('identifier' => 'default', 'is_const' => false );
	$data['special_path'] = 'my-languages';
	$data['languages'] = array();
	$data['is_US_Version'] = !is_dir(str_replace("\\","/",dirname(WP_PLUGIN_DIR.'/'.$plug)).'/bp-forums/bbpress/my-languages');
	if (!$data['is_US_Version']) {	
		$tmp = array(); 	
		$files = lscandir(str_replace("\\","/",dirname(WP_PLUGIN_DIR.'/'.$plug)).'/bp-forums/bbpress/my-languages/', "/(\.mo|\.po|\.pot)$/", $tmp); 
		$data['translation_template'] = csp_find_translation_template($files);
		foreach($files as $filename) {
			$file = str_replace(str_replace("\\","/",WP_PLUGIN_DIR).'/'.dirname($plug), '', $filename);
			preg_match("/([a-z][a-z]_[A-Z][A-Z]).(mo|po)$/", $file, $hits);		
			if (empty($hits[2]) === false) {				
				$data['languages'][$hits[1]][$hits[2]] = array(
					'class' => "-".(is_readable($filename) ? 'r' : '').(is_writable($filename) ? 'w' : ''),
					'stamp' => date(__('m/d/Y H:i:s',CSP_PO_TEXTDOMAIN), filemtime($filename))." ".file_permissions($filename)
				);
			}
		}
	}
	$data['base_file'] = (empty($data['special_path']) ? $data['filename'] : $data['special_path']."/");	
	return $data;
}


function csp_po_collect_by_type($type){
	$res = array();
	$do_compat_filter = ($type == 'compat');
	$do_security_filter = ($type == 'security');
	if ($do_compat_filter || $do_security_filter) $type = '';
	if (empty($type) || ($type == 'wordpress')) {
		if (!$do_compat_filter && !$do_security_filter)
			$res[] = csp_po_get_wordpress_capabilities();
	}
	if (empty($type) || ($type == 'plugins')) {
		//WARNING: Plugin handling is not well coded by WordPress core
		$err = error_reporting(0);
		$plugs = get_plugins(); 
		error_reporting($err);
		$textdomains = array();
		foreach($plugs as $key => $value) { 
			$data = null;
			if (dirname($key) == 'buddypress') {
				if ($do_compat_filter || $do_security_filter) continue;
				$data = csp_po_get_buddypress_capabilities($key, $value);
				$res[] = $data;
				$data = csp_po_get_bbpress_on_buddypress_capabilities($key, $value);
				if($data !== false) $res[] = $data;
			}else {
				$data = csp_po_get_plugin_capabilities($key, $value);
				if (!$data['gettext_ready']) continue;
				if (in_array($data['textdomain'], $textdomains)) {
					for ($i=0; $i<count($res); $i++) {
						if ($data['textdomain'] == $res[$i]['textdomain']) {
							$res[$i]['child-plugins'][] = $data;
							break;
						}
					}
				}
				else{
					if ($do_compat_filter && !isset($data['dev-hints'])) continue;
					elseif ($do_security_filter && !isset($data['dev-security'])) continue;
					array_push($textdomains, $data['textdomain']);
					$res[] = $data;
				}
			}
		}
	}
	if (csp_is_multisite()) {
		if (empty($type) || ($type == 'plugins_mu')) {
			$plugs = array();
			$textdomains = array();
			if( is_dir( WPMU_PLUGIN_DIR ) ) {
				if( $dh = opendir( WPMU_PLUGIN_DIR ) ) {
					while( ( $plugin = readdir( $dh ) ) !== false ) {
						if( substr( $plugin, -4 ) == '.php' ) {
							$plugs[$plugin] = get_plugin_data( WPMU_PLUGIN_DIR . '/' . $plugin );
						}
					}
				}
			}		
			foreach($plugs as $key => $value) { 
				$data = csp_po_get_plugin_mu_capabilities($key, $value);
				if (!$data['gettext_ready']) continue;
				if ($do_compat_filter && !isset($data['dev-hints'])) continue;
				elseif ($do_security_filter && !isset($data['dev-security'])) continue;
				if (in_array($data['textdomain'], $textdomains)) {
					for ($i=0; $i<count($res); $i++) {
						if ($data['textdomain'] == $res[$i]['textdomain']) {
							$res[$i]['child-plugins'][] = $data;
							break;
						}
					}
				}
				else{
					if ($do_compat_filter && !isset($data['dev-hints'])) continue;
					elseif ($do_security_filter && !isset($data['dev-security'])) continue;
					array_push($textdomains, $data['textdomain']);
					$res[] = $data;
				}
			}
		}
	}
	if (empty($type) || ($type == 'themes')) {
		$themes = get_themes();
		//WARNING: Theme handling is not well coded by WordPress core
		$err = error_reporting(0);
		$ct = current_theme_info();
		error_reporting($err);
		foreach($themes as $key => $value) { 
			$data = csp_po_get_theme_capabilities($key, $value, $ct);
			if (!$data['gettext_ready']) continue;
			if ($do_compat_filter && !isset($data['dev-hints'])) continue;
			elseif ($do_security_filter && !isset($data['dev-security'])) continue;
			$res[] = $data;
		}	
	}
	return $res;
}

//////////////////////////////////////////////////////////////////////////////////////////
//	Admin Ajax Handler
//////////////////////////////////////////////////////////////////////////////////////////

if (function_exists('add_action')) {
	add_action('wp_ajax_csp_po_dlg_new', 'csp_po_ajax_handle_dlg_new');
	add_action('wp_ajax_csp_po_dlg_delete', 'csp_po_ajax_handle_dlg_delete');
	add_action('wp_ajax_csp_po_dlg_rescan', 'csp_po_ajax_handle_dlg_rescan');
	add_action('wp_ajax_csp_po_dlg_show_source', 'csp_po_ajax_handle_dlg_show_source');
	
	add_action('wp_ajax_csp_po_create', 'csp_po_ajax_handle_create');
	add_action('wp_ajax_csp_po_destroy', 'csp_po_ajax_handle_destroy');
	add_action('wp_ajax_csp_po_scan_source_file', 'csp_po_ajax_handle_scan_source_file');	
	add_action('wp_ajax_csp_po_change_low_memory_mode', 'csp_po_ajax_csp_po_change_low_memory_mode');
	add_action('wp_ajax_csp_po_change_permission', 'csp_po_ajax_handle_change_permission');
	add_action('wp_ajax_csp_po_launch_editor', 'csp_po_ajax_handle_launch_editor');
	add_action('wp_ajax_csp_po_translate_by_google', 'csp_po_ajax_handle_translate_by_google');
	add_action('wp_ajax_csp_po_save_catalog_entry', 'csp_po_ajax_handle_save_catalog_entry');
	add_action('wp_ajax_csp_po_generate_mo_file', 'csp_po_ajax_handle_generate_mo_file');
	add_action('wp_ajax_csp_po_create_language_path', 'csp_po_ajax_handle_create_language_path');
	add_action('wp_ajax_csp_po_create_pot_indicator', 'csp_po_ajax_handle_create_pot_indicator');
	//WP 2.7 help extensions
	add_filter('screen_meta_screen', 'csp_po_filter_screen_meta_screen');
	add_filter('contextual_help_list', 'csp_po_filter_help_list_filter');
}

//WP 2.7 help extensions
//TODO: doesn't work as expected beginning at WP 3.0 (object now!) and never gets called while already object skipps filtering!
function csp_po_filter_screen_meta_screen($screen) {
	if (preg_match('/codestyling-localization$/', $screen)) return "codestyling-localization";
	return $screen;
}

//WP 2.7 help extensions
function csp_po_filter_help_list_filter($_wp_contextual_help) {

	global $wp_version;
	if (version_compare($wp_version, '3', '<')) {

		require_once(ABSPATH.'/wp-includes/rss.php');
		$rss = fetch_rss('http://www.code-styling.de/online-help/plugins.php?type=config&locale='.get_locale().'&plug=codestyling-localization');	
		if ( $rss ) {
			$_wp_contextual_help['codestyling-localization'] = '';
			foreach ($rss->items as $item ) {
				if ($item['category'] == 'thickbox') {
					$_wp_contextual_help['codestyling-localization'] .= '<a href="'. $item['link'] . '&amp;TB_iframe=true" class="thickbox" name="<strong>'. $item['title'] . '</strong>">'. $item['title'] . '</a> | ';
				} else {
					$_wp_contextual_help['codestyling-localization'] .= '<a target="_blank" href="'. $item['link'] . '" >'. $item['title'] . '</a> | ';
				}
			}
		}
		
	} else {
	
		//TODO: WP 3.0 introduces only accepts the new classes without depreciate, furthermore the screen key is handled different now (see function above!)
		require_once(ABSPATH.'/wp-includes/feed.php');
		$rss = fetch_feed('http://www.code-styling.de/online-help/plugins.php?type=config&locale='.get_locale().'&plug=codestyling-localization');
		if ( $rss && !is_wp_error($rss)) {
			$_wp_contextual_help['tools_page_codestyling-localization/codestyling-localization'] = '';
			foreach ($rss->get_items(0, 9999) as $item ) {		
				$cat = $item->get_category();
				if ($cat->get_term() == 'thickbox') {
					$_wp_contextual_help['tools_page_codestyling-localization/codestyling-localization'] .= '<a href="'. $item->get_link() . '&amp;TB_iframe=true" class="thickbox" name="<strong>'. $item->get_title() . '</strong>">'. $item->get_title() . '</a> | ';
				} else {
					$_wp_contextual_help['tools_page_codestyling-localization/codestyling-localization'] .= '<a target="_blank" href="'. $item->get_link() . '" >'. $item->get_title() . '</a> | ';
				}
			}
		}
		
	}
	return $_wp_contextual_help;
}

function csp_po_ajax_handle_dlg_new() {
	csp_po_check_security();
	load_plugin_textdomain(CSP_PO_TEXTDOMAIN, PLUGINDIR.'/codestyling-localization/languages','codestyling-localization/languages');
	require_once('includes/locale-definitions.php');
?>
	<table class="widefat" cellspacing="2px">
		<tr>
			<td nowrap="nowrap"><strong><?php _e('Project-Id-Version',CSP_PO_TEXTDOMAIN); ?>:</strong></td>
			<td><?php echo rawurldecode($_POST['name']); ?><input type="hidden" id="csp-dialog-name" value="<?php echo rawurldecode($_POST['name']); ?>" /></td>
		</tr>
		<tr>
			<td><strong><?php _e('Creation-Date',CSP_PO_TEXTDOMAIN); ?>:</strong></td>
			<td><?php echo date("Y-m-d H:iO"); ?><input type="hidden" id="csp-dialog-timestamp" value="<?php echo date("Y-m-d H:iO"); ?>" /></td>
		</tr>
		<tr>
			<td style="vertical-align:middle;"><strong><?php _e('Last-Translator',CSP_PO_TEXTDOMAIN); ?>:</strong></td>
			<td><input style="width:330px;" type="text" id="csp-dialog-translator" value="<?php $myself = wp_get_current_user(); echo "$myself->user_nicename &lt;$myself->user_email&gt;"; ?>" /></td>
		</tr>
		<tr>
			<td valign="top"><strong><?php echo $csp_l10n_login_label[substr(get_locale(),0,2)]?>:</strong></td>
			<td>
				<div style="width:332px;height:300px; overflow:scroll;border:solid 1px #54585B;overflow-x:hidden;">
					<?php $existing = explode('|', ltrim($_POST['existing'],'|')); if(strlen($existing[0]) == 0) $existing=array(); ?>
					<input type="hidden" id="csp-dialog-row" value="<?php echo $_POST['row']; ?>" />
					<input type="hidden" id="csp-dialog-numlangs" value="<?php echo count($existing)+1; ?>" />
					<input type="hidden" id="csp-dialog-language" value="" />
					<input type="hidden" id="csp-dialog-path" value="<?php echo $_POST['path']; ?>" />
					<input type="hidden" id="csp-dialog-subpath" value="<?php echo $_POST['subpath']; ?>" />
					<input type="hidden" id="csp-dialog-simplefilename" value="<?php echo $_POST['simplefilename']; ?>" />			
					<input type="hidden" id="csp-dialog-transtemplate" value="<?php echo $_POST['transtemplate']; ?>" />					
					<input type="hidden" id="csp-dialog-textdomain" value="<?php echo $_POST['textdomain']; ?>" />					
					<input type="hidden" id="csp-dialog-denyscan" value="<?php echo ($_POST['denyscan'] ? "true" : "false"); ?>" />					
					<table style="font-family:monospace;">
					<?php
						$total = array_keys($csp_l10n_sys_locales);
						foreach($total as $key) {
							if (in_array($key, $existing)) continue;
							$values = $csp_l10n_sys_locales[$key];
							if (get_locale() == $key) { $selected = '" selected="selected'; } else { $selected=""; };
							?>
							<tr>
								<td><input type="radio" name="mo-locale" value="<?php echo $key; ?><?php echo $selected; ?>" onclick="$('submit_language').enable();$('csp-dialog-language').value = this.value;" /></td>
								<td><img alt="" title="locale: <?php echo $key ?>" src="<?php echo CSP_PO_BASE_URL."/images/flags/".$csp_l10n_sys_locales[$key]['country-www'].".gif\""; ?>" /></td>
								<td><?php echo $key; ?></td>
								<td style="padding-left: 5px;border-left: 1px solid #aaa;"><?php echo $values['lang-native']."<br />"; ?></td>
							</tr>
							<?php
						}
					?>
					</table>
				</div>
			</td>
		</tr>
	</table>
	<div style="text-align:center; padding-top: 10px"><input class="button" id="submit_language" type="submit" disabled="disabled" value="<?php _e('create po-file',CSP_PO_TEXTDOMAIN); ?>" onclick="return csp_create_new_pofile(this,<?php echo "'".$_POST['type']."'"; ?>);"/></div>
<?php
exit();
}

function csp_po_ajax_handle_dlg_delete() {
	csp_po_check_security();
	load_plugin_textdomain(CSP_PO_TEXTDOMAIN, PLUGINDIR.'/codestyling-localization/languages','codestyling-localization/languages');
	require_once('includes/locale-definitions.php');
	$lang = isset($csp_l10n_sys_locales[$_POST['language']]) ? $csp_l10n_sys_locales[$_POST['language']]['lang-native'] : $_POST['language'];
?>
	<p style="text-align:center;"><?php echo sprintf(__('You are about to delete <strong>%s</strong> from "<strong>%s</strong>" permanently.<br/>Are you sure you wish to delete these files?', CSP_PO_TEXTDOMAIN), $lang, rawurldecode($_POST['name'])); ?></p>
	<div style="text-align:center; padding-top: 10px"><input class="button" id="submit_language" type="submit" value="<?php _e('delete files',CSP_PO_TEXTDOMAIN); ?>" onclick="csp_destroy_files(this,'<?php echo str_replace("'", "\\'", rawurldecode($_POST['name']))."','".$_POST['row']."','".$_POST['path']."','".$_POST['subpath']."','".$_POST['language']."','".$_POST['numlangs'];?>');" /></div>
<?php
	exit();
}

function csp_po_ajax_handle_dlg_rescan() {
	csp_po_check_security();
	load_plugin_textdomain(CSP_PO_TEXTDOMAIN, PLUGINDIR.'/codestyling-localization/languages','codestyling-localization/languages');
	require_once('includes/locale-definitions.php');	
	global $wp_version;
	if ($_POST['type'] == 'wordpress') {	
		$abs_root = rtrim(str_replace('\\', '/', ABSPATH), '/');
		$excludes = array();
		$files = array(
			$abs_root.'/wp-activate.php',
			$abs_root.'/wp-app.php',
			$abs_root.'/wp-atom.php',
			$abs_root.'/wp-blog-header.php',
			$abs_root.'/wp-comments-post.php',
			$abs_root.'/wp-commentsrss2.php',
			$abs_root.'/wp-cron.php',
			$abs_root.'/wp-feed.php',
			$abs_root.'/wp-links-opml.php',
			$abs_root.'/wp-load.php',
			$abs_root.'/wp-login.php',
			$abs_root.'/wp-mail.php',
			$abs_root.'/wp-pass.php',
			$abs_root.'/wp-rdf.php',
			$abs_root.'/wp-register.php',
			$abs_root.'/wp-rss.php',
			$abs_root.'/wp-rss2.php',
			$abs_root.'/wp-settings.php',
			$abs_root.'/wp-signup.php',
			$abs_root.'/wp-trackback.php',
			$abs_root.'/xmlrpc.php',
			str_replace("\\", "/", WP_PLUGIN_DIR).'/akismet/akismet.php'
		);
		rscandir_php($abs_root.'/wp-admin/', $excludes, $files);
		rscandir_php($abs_root.'/wp-includes/', $excludes, $files);
		//do not longer rescan old themes prior hosted the the main localization file starting from WP 3.0!
		if (version_compare($wp_version, '3', '<')) {
			rscandir_php(str_replace("\\","/",WP_CONTENT_DIR)."/themes/default/", $excludes, $files);
			rscandir_php(str_replace("\\","/",WP_CONTENT_DIR)."/themes/classic/", $excludes, $files);
		}	
	}
	elseif ($_POST['type'] == 'plugins_mu') {
		$files[] = $_POST['simplefilename'];
	}
	elseif ($_POST['textdomain'] == 'buddypress') {
		$files = array();
		$excludes = array($_POST['path'].'bp-forums/bbpress');
		rscandir_php($_POST['path'], $excludes, $files);
	}
	else{
		$files = array();
		$excludes = array();
		if (isset($_POST['simplefilename']) && !empty($_POST['simplefilename'])) { $files[] = $_POST['simplefilename']; }
		else { rscandir_php($_POST['path'], $excludes, $files); }
	}
	$country_www = isset($csp_l10n_sys_locales[$_POST['language']]) ? $csp_l10n_sys_locales[$_POST['language']]['country-www'] : 'unknown';
	$lang_native = isset($csp_l10n_sys_locales[$_POST['language']]) ? $csp_l10n_sys_locales[$_POST['language']]['lang-native'] : $_POST['language'];
	$filename = $_POST['path'].$_POST['subpath'].$_POST['language'].".po";
?>	
	<input id="csp-dialog-source-file-json" type="hidden" value="{ <?php 
		echo "row: '".$_POST['row']."',";
		echo "language: '".$_POST['language']."',";
		echo "textdomain: '".$_POST['textdomain']."',";
		echo "next : 0,";
		echo "path : '".$_POST['path']."',";
		echo "pofile : '".$_POST['path'].$_POST['subpath'].$_POST['language'].".po',";
		echo "type : '".$_POST['type']."',";
		echo "files : ['".implode("','",$files)."']"
	?>}" />
	<table class="widefat" cellspacing="2px">
		<tr>
			<td nowrap="nowrap"><strong><?php _e('Project-Id-Version',CSP_PO_TEXTDOMAIN); ?>:</strong></td>
			<td colspan="2"><?php echo rawurldecode($_POST['name']); ?><input type="hidden" name="name" value="<?php echo rawurldecode($_POST['name']); ?>" /></td>
		</tr>
		<tr>
			<td nowrap="nowrap"><strong><?php _e('Language Target',CSP_PO_TEXTDOMAIN); ?>:</strong></td>
			<td><img alt="" title="locale: <?php echo $_POST['language']; ?>" src="<?php echo CSP_PO_BASE_URL."/images/flags/".$country_www.".gif\""; ?>" /></td>			
			<td><?php echo $lang_native; ?></td>
		</tr>	
		<tr>
			<td nowrap="nowrap"><strong><?php _e('Affected Total Files',CSP_PO_TEXTDOMAIN); ?>:</strong></td>
			<td nowrap="nowrap" align="right"><?php echo count($files); ?></td>
			<td><em><?php echo "/".str_replace(str_replace("\\",'/',ABSPATH), '', $_POST['path']); ?></em></td>
		</tr>
		<tr>
			<td nowrap="nowrap" valign="top"><strong><?php _e('Scanning Progress',CSP_PO_TEXTDOMAIN); ?>:</strong></td>
			<td id="csp-dialog-progressvalue" nowrap="nowrap" valign="top" align="right">0</td>
			<td>
				<div style="height:13px;width:290px;border:solid 1px #333;"><div id="csp-dialog-progressbar" style="height: 13px;width:0%; background-color:#0073D9"></div></div>
				<div id="csp-dialog-progressfile" style="width:290px;white-space:nowrap;overflow:hidden;font-size:8px;font-family:monospace;padding-top:3px;">&nbsp;</div>
			</td>
		<tr>
	</table>
	<div style="text-align:center; padding-top: 10px"><input class="button" id="csp-dialog-rescan" type="submit" value="<?php _e('scan now',CSP_PO_TEXTDOMAIN); ?>" onclick="csp_scan_source_files(this);"/><span id="csp-dialog-scan-info" style="display:none"><?php _e('Please standby, files presently being scanned ...',CSP_PO_TEXTDOMAIN); ?></span></div>
<?php
	exit();
}

function csp_po_convert_js_input_for_source($str) {
	$search = array('\\\\\"','\\\\n', '\\\\t', '\\\\$','\\0', "\\'", '\\\\');
	$replace = array('"', "\n", "\\t", "\\$", "\0", "'", "\\");
	$str = str_replace( $search, $replace, $str );
	return $str;
}

function csp_po_ajax_handle_dlg_show_source() {
	csp_po_check_security();
	load_plugin_textdomain(CSP_PO_TEXTDOMAIN, PLUGINDIR.'/codestyling-localization/languages','codestyling-localization/languages');
	list($file, $match_line) = explode(':', $_POST['file']);
	$l = filesize($_POST['path'].$file);
	$handle = fopen($_POST['path'].$file,'rb');
	$content = str_replace(array("\r","\\$"),array('','$'), fread($handle, $l));
	fclose($handle);

	$msgid = $_POST['msgid'];
	$msgid = csp_po_convert_js_input_for_source($msgid);	
	if (strlen($msgid) > 0) {
		if (strpos($msgid, "\00") > 0)
			$msgid = explode("\00", $msgid);
		else
			$msgid = explode("\01", $msgid); //opera fix
		foreach($msgid as $item) {	
			if (strpos($content, $item) === false) {
				//difficult to separate between real \n notation and LF brocken strings also \t 
				$test = str_replace("\n", '\n', $item);
				if (strpos($content, $test) === false) {
					$test2 = str_replace('\t', "\t", $item);
					if (strpos($content, $test2) === false) {
						$test2 = str_replace('\t', "\t", $test);
						if (strpos($content, $test2) === true) {
							$item = $test2;
						}
					}else{
						$item = $test2;
					}
				}else {
					$item = $test;
				}
			}
			$content = str_replace($item, "\1".$item."\2", $content);
		}
	}
	$tmp = htmlentities($content, ENT_COMPAT, 'UTF-8');
	if (empty($tmp)) $tmp = htmlentities($content, ENT_COMPAT);
	$content = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;",$tmp);
	$content = preg_split("/\n/", $content);
	$c=0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head>
<body style="margin:0; padding:0;font-family:monospace;font-size:13px;">
	<table id="php_source" cellspacing="0" width="100%" style="padding:0; margin:0;">
<?php	
	$open = 0;
	$closed = 0;
	foreach($content as $line) {
		$c++;
		$style = $c % 2 == 1 ? "#fff" : "#eee";
		
		$open += substr_count($line,"\1");
		$closed += substr_count($line,"\2");
		$contained = preg_match("/(\1|\2)/", $line) || ($c == $match_line) || ($open != $closed);
		if ($contained) $style="#FFEF3F";
		
		if (!preg_match("/(\1|\2)/", $line) && $contained) $line = "<span style='background-color:#f00; color:#fff;padding:0 3px;'>".$line."</span>";
		if((substr_count($line,"\1") < substr_count($line,"\2")) && ($open == $closed)) $line = "<span style='background-color:#f00; color:#fff;padding:0 3px;'>".$line;
		if(substr_count($line,"\1") > substr_count($line,"\2")) $line .= "</span>";
		$line = str_replace("\1", "<span style='background-color:#f00; color:#fff;padding:0 3px;'>", $line);
		$line = str_replace("\2", "</span>", $line);
		
		echo "<tr id=\"l-$c\" style=\"background-color:$style;\"><td align=\"right\" style=\"background-color:#888;padding-right:5px;\">$c</td><td nowrap=\"nowrap\" style=\"padding-left:5px;\">$line</td></tr>\n";
	}
?>
	</table>
	<script type="text/javascript">
	/* <![CDATA[ */
function init() {
	try{
		window.scrollTo(0,document.getElementById('l-'+<?php echo max($match_line-15,1); ?>).offsetTop);
	}catch(e) {
		//silently kill errors if *.po files line numbers comes out of an outdated file and exceed the line range
	}
}
	
if (typeof Event == 'undefined') Event = new Object();
Event.domReady = {
	add: function(fn) {
		//-----------------------------------------------------------
		// Already loaded?
		//-----------------------------------------------------------
		if (Event.domReady.loaded) return fn();

		//-----------------------------------------------------------
		// Observers
		//-----------------------------------------------------------
	
		var observers = Event.domReady.observers;
		if (!observers) observers = Event.domReady.observers = [];
		// Array#push is not supported by Mac IE 5
		observers[observers.length] = fn;
 
		//-----------------------------------------------------------
		// domReady function
		//-----------------------------------------------------------
		if (Event.domReady.callback) return;
		Event.domReady.callback = function() {
			if (Event.domReady.loaded) return;
			Event.domReady.loaded = true;
			if (Event.domReady.timer) {
				clearInterval(Event.domReady.timer);
				Event.domReady.timer = null;
			}

			var observers = Event.domReady.observers;
			for (var i = 0, length = observers.length; i < length; i++) {
				var fn = observers[i];
				observers[i] = null;
				fn(); // make 'this' as window
			}
			Event.domReady.callback = Event.domReady.observers = null;
		};

		//-----------------------------------------------------------
		// Emulates 'onDOMContentLoaded'
		//-----------------------------------------------------------
		var ie = !!(window.attachEvent && !window.opera);
		var webkit = navigator.userAgent.indexOf('AppleWebKit/') > -1;
 
		if (document.readyState && webkit) {
 
			// Apple WebKit (Safari, OmniWeb, ...)
			Event.domReady.timer = setInterval(function() {
				var state = document.readyState;
				if (state == 'loaded' || state == 'complete') {
					Event.domReady.callback();
				}
			}, 50);
 
		} else if (document.readyState && ie) {
 
			// Windows IE
			var src = (window.location.protocol == 'https:') ? '://0' : 'javascript:void(0)';
			document.write(
				'<script type="text/javascript" defer="defer" src="' + src + '" ' +
				'onreadystatechange="if (this.readyState == \'complete\') Event.domReady.callback();"' +
				'><\/script>');
 
		} else {
 
			if (window.addEventListener) {
				// for Mozilla browsers, Opera 9
				document.addEventListener("DOMContentLoaded", Event.domReady.callback, false);
				// Fail safe
				window.addEventListener("load", Event.domReady.callback, false);
			} else if (window.attachEvent) {
				window.attachEvent('onload', Event.domReady.callback);
			} else {
				// Legacy browsers (e.g. Mac IE 5)
				var fn = window.onload;
				window.onload = function() {
					Event.domReady.callback();
					if (fn) fn();
				}
			}
		}
	}
}	
	Event.domReady.add(init);
	/* ]]> */
	</script>	
</body>
</html>
<?php
	exit();
}

function csp_po_ajax_handle_create() {
	csp_po_check_security();
	load_plugin_textdomain(CSP_PO_TEXTDOMAIN, PLUGINDIR.'/codestyling-localization/languages','codestyling-localization/languages');
	require_once('includes/locale-definitions.php');
	require_once('includes/class-translationfile.php');
	$pofile = new CspTranslationFile();
	$filename = $_POST['path'].$_POST['subpath'].$_POST['language'].'.po';
	
	$ok = $pofile->read_pofile($_POST['transtemplate']);
	if ($ok) 
		$ok = $pofile->write_pofile($filename, false, $_POST['textdomain']);
	if (!$ok)
		$ok = $pofile->create_pofile(
		$filename, 
		$_POST['subpath'],
		$_POST['name'], 
		$_POST['timestamp'], 
		$_POST['translator'], 
		$csp_l10n_plurals[substr($_POST['language'],0,2)], 
		$csp_l10n_sys_locales[$_POST['language']]['lang'], 
		$csp_l10n_sys_locales[$_POST['language']]['country']
		);
	
	if(!$ok) {
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		echo sprintf(__("You do not have the permission to create the file '%s'.", CSP_PO_TEXTDOMAIN), $filename);
	}
	else{	
		header('Content-Type: application/json');
?>
{
		name: '<?php echo rawurldecode($_POST['name']); ?>',
		row : '<?php echo $_POST['row']; ?>',
		head: '<?php echo sprintf(_n('<strong>%d</strong> Language', '<strong>%d</strong> Languages',$_POST['numlangs'],CSP_PO_TEXTDOMAIN), $_POST['numlangs']); ?>',
		path: '<?php echo $_POST['path']; ?>',
		subpath: '<?php echo $_POST['subpath']; ?>',
		language: '<?php echo $_POST['language']; ?>',
		lang_native: '<?php echo $csp_l10n_sys_locales[$_POST['language']]['lang-native']; ?>',
		image: '<?php echo CSP_PO_BASE_URL."/images/flags/".$csp_l10n_sys_locales[$_POST['language']]['country-www'].".gif";?>',
		type: '<?php echo $_POST['type']; ?>',
		simplefilename: '<?php echo $_POST['simplefilename']; ?>',
		transtemplate: '<?php echo $_POST['transtemplate']; ?>',
		permissions: '<?php echo date(__('m/d/Y H:i:s',CSP_PO_TEXTDOMAIN), filemtime($filename))." ".file_permissions($filename); ?>',
		denyscan: <?php echo $_POST['denyscan']; ?>
}
<?php		
	}
	exit();
}

function csp_po_ajax_handle_destroy() {
	csp_po_check_security();
	load_plugin_textdomain(CSP_PO_TEXTDOMAIN, PLUGINDIR.'/codestyling-localization/languages','codestyling-localization/languages');
	$pofile = $_POST['path'].$_POST['subpath'].$_POST['language'].'.po';
	$mofile = $_POST['path'].$_POST['subpath'].$_POST['language'].'.mo';
	$error = false;
	if (file_exists($pofile)) if (!@unlink($pofile)) $error = sprintf(__("You do not have the permission to delete the file '%s'.", CSP_PO_TEXTDOMAIN), $pofile);
	if (file_exists($mofile)) if (!@unlink($mofile)) $error = sprintf(__("You do not have the permission to delete the file '%s'.", CSP_PO_TEXTDOMAIN), $mofile);
	if ($error) {
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		echo $error;
		exit();
	}
	$num = $_POST['numlangs'] - 1;
	header('Content-Type: application/json');
?>
{
	row : '<?php echo $_POST['row']; ?>',
	head: '<?php echo sprintf(_n('<strong>%d</strong> Language', '<strong>%d</strong> Languages',$num,CSP_PO_TEXTDOMAIN), $num); ?>',
	language: '<?php echo $_POST['language']; ?>'
}
<?php	
	exit();
}
function csp_po_ajax_csp_po_change_low_memory_mode() {
	csp_po_check_security();
	update_option('codestyling-localization.low-memory', ($_POST['mode'] == 'true' ? true : false));
	exit();
}

function csp_po_ajax_handle_scan_source_file() {
	csp_po_check_security();

	$low_mem_scanning = (bool)get_option('codestyling-localization.low-memory', false);
	
	require_once('includes/class-translationfile.php');
	require_once('includes/locale-definitions.php');
	$textdomain = $_POST['textdomain'];
	//TODO: give the domain into translation file as default domain
	$pofile = new CspTranslationFile($_POST['type']);
	//BUGFIX: 1.90 - may be, we have only the mo but no po, so we dump it out as base po file first
	if (!file_exists($_POST['pofile'])) {
		//try implicite convert first and reopen as po second
		if($pofile->read_mofile(substr($_POST['pofile'],0,-2)."mo", $csp_l10n_plurals, false, $textdomain)) {
			$pofile->write_pofile($_POST['pofile']);
		}
	}		
	if ($pofile->read_pofile($_POST['pofile'])) {
		if ((int)$_POST['num'] == 0) { $pofile->parsing_init(); }
		
		$php_files = explode("|", $_POST['php']);
		$s = (int)$_POST['num'];
		$e = min($s + (int)$_POST['cnt'], count($php_files));
		$last = ($e >= count($php_files));
		for ($i=$s; $i<$e; $i++) {
			if ($low_mem_scanning) {
				$options = array(
					'type' => $_POST['type'],
					'path' => $_POST['path'],
					'textdomain' => $_POST['textdomain'],
					'file' => $php_files[$i]
				);
				$r = wp_remote_post(CSP_PO_BASE_URL.'/includes/low-memory-parsing.php', array('body' => $options));
				$data = unserialize(base64_decode($r['body']));
				$pofile->add_messages($data);
			}else{
				$pofile->parsing_add_messages($_POST['path'], $php_files[$i], $textdomain);
			}
		}	
		if ($last) { $pofile->parsing_finalize($textdomain); }
		if ($pofile->write_pofile($_POST['pofile'], $last)) {
			header('Content-Type: application/json');
			echo '{ title: "'.date(__('m/d/Y H:i:s',CSP_PO_TEXTDOMAIN), filemtime($_POST['pofile']))." ".file_permissions($_POST['pofile']).'" }';
		}
		else{
			header('Status: 404 Not Found');
			header('HTTP/1.1 404 Not Found');
			echo sprintf(__("You do not have the permission to write to the file '%s'.", CSP_PO_TEXTDOMAIN), $_POST['pofile']);
		}
	}
	else{
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		echo sprintf(__("You do not have the permission to read the file '%s'.", CSP_PO_TEXTDOMAIN), $_POST['pofile']);
	}
	exit();
}

function csp_po_ajax_handle_change_permission() {
	csp_po_check_security();
	load_plugin_textdomain(CSP_PO_TEXTDOMAIN, PLUGINDIR.'/codestyling-localization/languages','codestyling-localization/languages');
	$filename = $_POST['file'];
	$error = false;
	if (file_exists($filename)) {
		@chmod($filename, 0644);
		if(!is_writable($filename)) {
			@chmod($filename, 0664);
			if (!is_writable($filename)) {
				@chmod($filename, 0666);
			}
			if (!is_writable($filename)) $error = __('Server Restrictions: Changing file rights is not permitted.', CSP_PO_TEXTDOMAIN);
		}
	}
	else $error = sprintf(__("You do not have the permission to modify the file rights for a not existing file '%s'.", CSP_PO_TEXTDOMAIN), $filename);
	if ($error) {
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		echo $error;			
	}
	else{
		header('Content-Type: application/json');
		echo '{ title: "'.date(__('m/d/Y H:i:s',CSP_PO_TEXTDOMAIN), filemtime($filename))." ".file_permissions($filename).'" }';
	}
	exit();
}

function csp_po_ajax_handle_launch_editor() {
	csp_po_check_security();
	load_plugin_textdomain(CSP_PO_TEXTDOMAIN, PLUGINDIR.'/codestyling-localization/languages','codestyling-localization/languages');
	require_once('includes/locale-definitions.php');
	require_once('includes/class-translationfile.php');
	$f = new CspTranslationFile();
	if (!file_exists($_POST['basepath'].$_POST['file'])) {
		//try implicite convert first
		if($f->read_mofile(substr($_POST['basepath'].$_POST['file'],0,-2)."mo", $csp_l10n_plurals, $_POST['file'], $_POST['textdomain'])) {
			$f->write_pofile($_POST['basepath'].$_POST['file']);
		}
	}
	else{
		$f->read_pofile($_POST['basepath'].$_POST['file'], $csp_l10n_plurals, $_POST['file']);
	}
	if ($f->supports_textdomain_extension()){
		$f->echo_as_json($_POST['basepath'], $_POST['file'], $csp_l10n_sys_locales);
	}else {
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		_e("Your translation file doesn't support the <em>multiple textdomains in one translation file</em> extension.<br/>Please re-scan the related source files at the overview page to enable this feature.",CSP_PO_TEXTDOMAIN);
	}
	exit();
}

function csp_po_ajax_handle_translate_by_google() {
	csp_po_check_security();
	// reference documentation: http://code.google.com/intl/de-DE/apis/ajaxlanguage/documentation/reference.html
	// example 'http://ajax.googleapis.com/ajax/services/language/translate?v=1.0&q=hello%20world&langpair=en%7Cit'
	$msgid = $_POST['msgid'];
	$search = array('\\\\\\\"', '\\\\\"','\\\\n', '\\\\r', '\\\\t', '\\\\$','\\0', "\\'", '\\\\');
	$replace = array('\"', '"', "\n", "\r", "\\t", "\\$", "\0", "'", "\\");
	$msgid = str_replace( $search, $replace, $msgid );
	$res = csp_fetch_remote_content("http://ajax.googleapis.com/ajax/services/language/translate?v=1.0&format=html&q=".urlencode($msgid)."&langpair=en%7C".$_POST['destlang']);
	if ($res) {
		header('Content-Type: application/json');
		echo $res;
	}
	else{
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		load_plugin_textdomain(CSP_PO_TEXTDOMAIN, PLUGINDIR.'/codestyling-localization/languages','codestyling-localization/languages');
		_e("Sorry, Google Translation is not available.", CSP_PO_TEXTDOMAIN);		
	}
	exit();
}

function csp_po_ajax_handle_save_catalog_entry() {
	csp_po_check_security();
	load_plugin_textdomain(CSP_PO_TEXTDOMAIN, PLUGINDIR.'/codestyling-localization/languages','codestyling-localization/languages');
	require_once('includes/class-translationfile.php');
	$f = new CspTranslationFile();
	//opera bugfix: replace embedded \1 with \0 because Opera can't send embeded 0
	$_POST['msgid'] = str_replace("\1", "\0", $_POST['msgid']);
	$_POST['msgstr'] = str_replace("\1", "\0", $_POST['msgstr']);
	if ($f->read_pofile($_POST['path'].$_POST['file'])) {
		if (!$f->update_entry($_POST['msgid'], $_POST['msgstr'])) {
			header('Status: 404 Not Found');
			header('HTTP/1.1 404 Not Found');
			echo sprintf(__("You do not have the permission to write to the file '%s'.", CSP_PO_TEXTDOMAIN), $_POST['file']);
		}
		else{
			$f->write_pofile($_POST['path'].$_POST['file']);
			header('Status: 200 Ok');
			header('HTTP/1.1 200 Ok');
			header('Content-Length: 1');	
			echo "0";
		}
	}
	else{
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		echo sprintf(__("You do not have the permission to read the file '%s'.", CSP_PO_TEXTDOMAIN), $_POST['file']);
	}
	exit();
}

function csp_po_ajax_handle_generate_mo_file(){
	csp_po_check_security();
	load_plugin_textdomain(CSP_PO_TEXTDOMAIN, PLUGINDIR.'/codestyling-localization/languages','codestyling-localization/languages');
	require_once('includes/class-translationfile.php');
	$pofile = (string)$_POST['pofile'];
	$textdomain = (string)$_POST['textdomain'];
	$f = new CspTranslationFile();
	if (!$f->read_pofile($pofile)) {
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		echo sprintf(__("You do not have the permission to read the file '%s'.", CSP_PO_TEXTDOMAIN), $pofile);
		exit();
	}
	//lets detected, what we are about to be writing:
	$mo = substr($pofile,0,-2).'mo';

	$wp_dir = str_replace("\\","/",WP_LANG_DIR);
	$pl_dir = str_replace("\\","/",WP_PLUGIN_DIR);
	$plm_dir = str_replace("\\","/",WPMU_PLUGIN_DIR);
	$parts = pathinfo($mo);
	//dirname|basename|extension
	if (preg_match("|^".$wp_dir."|", $mo)) {
		//we are WordPress itself
		if ($textdomain != 'default') {
			$mo	= $parts['dirname'].'/'.$textdomain.'-'.$parts['basename'];
		}
	}elseif(preg_match("|^".$pl_dir."|", $mo)|| preg_match("|^".$plm_dir."|", $mo)) {
		//we are a normal or wpmu plugin
		if ((strpos($parts['basename'], $textdomain) === false) && ($textdomain != 'default')) {
			preg_match("/([a-z][a-z]_[A-Z][A-Z]\.mo)$/", $parts['basename'], $h);
			if (!empty($textdomain)) {
				$mo	= $parts['dirname'].'/'.$textdomain.'-'.$h[1];
			}else {
				$mo	= $parts['dirname'].'/'.$h[1];
			}
		}
	}else{
		//we are a theme plugin, could be tested but skipped for now.
	}
	
	if ($f->is_illegal_empty_mofile($textdomain)) {
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		_e("You are trying to create an empty mo-file without any translations. This is not possible, please translate at least one entry.", CSP_PO_TEXTDOMAIN);
		exit();
	}
	
	if (!$f->write_mofile($mo,$textdomain)) {
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		echo sprintf(__("You do not have the permission to write to the file '%s'.", CSP_PO_TEXTDOMAIN), $mo);
		exit();
	}

	header('Content-Type: application/json');
?>
{
	filetime: '<?php echo date (__('m/d/Y H:i:s',CSP_PO_TEXTDOMAIN), filemtime($mo)); ?>'
}
<?php		
	exit();
}

function csp_po_ajax_handle_create_language_path() {
	csp_po_check_security();
	load_plugin_textdomain(CSP_PO_TEXTDOMAIN, PLUGINDIR.'/codestyling-localization/languages','codestyling-localization/languages');
	$path = $_POST['path'];
	if (!mkdir($path)) {
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		_e("You do not have the permission to create a new Language File Path.<br/>Please create the appropriated path using your FTP access.", CSP_PO_TEXTDOMAIN);
	}
	else{
			header('Status: 200 ok');
			header('HTTP/1.1 200 ok');
			header('Content-Length: 1');	
			print 0;
	}
	exit();
}

function csp_po_ajax_handle_create_pot_indicator() {
	csp_po_check_security();
	load_plugin_textdomain(CSP_PO_TEXTDOMAIN, PLUGINDIR.'/codestyling-localization/languages','codestyling-localization/languages');
	
	$handle = @fopen($_POST['potfile'], "w");
	
	if ($handle === false) {
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
		_e("You do not have the permission to choose the translation file directory<br/>Please upload at least one language file (*.mo|*.po) or an empty template file (*.pot) at the appropriated folder using FTP.", CSP_PO_TEXTDOMAIN);
	}
	else{
		@fwrite($handle, 
			'MIME-Version: 1.0\n'.
			'Content-Type: text/plain; charset=UTF-8\n'.
			'Content-Transfer-Encoding: 8bit'
		);
		@fclose($handle);
		header('Status: 200 ok');
		header('HTTP/1.1 200 ok');
		header('Content-Length: 1');	
		print 0;
	}
	exit();
}

//////////////////////////////////////////////////////////////////////////////////////////
//	Admin Initialization ad Page Handler
//////////////////////////////////////////////////////////////////////////////////////////
if (function_exists('add_action')) {
	if (is_admin() && !defined('DOING_AJAX')) {
		add_action('admin_init', 'csp_po_init');
		add_action('admin_head', 'csp_po_admin_head');
		add_action('admin_menu', 'csp_po_admin_menu');
		require_once('includes/locale-definitions.php');
	}
}

function csp_po_init() {
	//currently not used, subject of later extension
	$low_mem_mode = (bool)get_option('codestyling-localization.low-memory', false);
	define('CSL_LOW_MEMORY', $low_mem_mode);	
}

function csp_load_po_edit_admin_page(){
	wp_enqueue_script( 'thickbox' );
	wp_enqueue_script('prototype');
	wp_enqueue_script('scriptaculous-effects');
	if (function_exists('wp_enqueue_style')) {
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_style('codestyling-localization', CSP_PO_BASE_URL.'/codestyling-localization.php?css=default&amp;dir='.((function_exists('is_rtl') && is_rtl()) ? 'rtl' : 'ltr'));
	}
	//prevent WP E-Commerce scripts from removing protoype at my pages!
	$wpec = false;
	$wpec |= remove_action( 'admin_head', 'wpsc_admin_include_css_and_js' );
	$wpec |= remove_action( 'admin_head', 'wpsc_admin_include_css_and_js_refac' );
	$wpec |= remove_action( 'admin_enqueue_scripts', 'wpsc_admin_include_css_and_js_refac' );
	
	define('CSL_WPEC_PATCH', $wpec);
	
}

function csp_po_admin_head() {
	if (!function_exists('wp_enqueue_style') 
		&& 
		preg_match("/^codestyling\-localization\/codestyling\-localization\.php/", $_GET['page'])
	) {
		print '<link rel="stylesheet" href="'.get_option('siteurl')."/wp-includes/js/thickbox/thickbox.css".'" type="text/css" media="screen"/>';
		print '<link rel="stylesheet" href="'.CSP_PO_BASE_URL.'/codestyling-localization.php?css=default'.'" type="text/css" media="screen"/>';
	}
}

function csp_po_admin_menu() {
	load_plugin_textdomain(CSP_PO_TEXTDOMAIN, PLUGINDIR.'/codestyling-localization/languages', 'codestyling-localization/languages');
	$hook = add_management_page(__('WordPress Localization',CSP_PO_TEXTDOMAIN), __('Localization', CSP_PO_TEXTDOMAIN), 'manage_options', __FILE__, 'csp_po_main_page');
	add_action('load-'.$hook, 'csp_load_po_edit_admin_page'); //only load the scripts and stylesheets by hook, if this admin page will be shown
}

function csp_po_main_page() {
	csp_po_check_security();
	$mo_list_counter = 0;
	global $csp_l10n_sys_locales, $wp_version;
	$csp_wp_main_page = (version_compare($wp_version, '2.7 ', '>=') ? "tools" : "edit");
?>
<div id="csp-wrap-main" class="wrap">
<div class="icon32" id="icon-tools"><br/></div>
<h2><?php _e('Manage Language Files', CSP_PO_TEXTDOMAIN); ?></h2>
<p>
	<input id= "enable_low_memory_mode" type="checkbox" name="enable_low_memory_mode" value="1" <?php if (CSL_LOW_MEMORY) echo 'checked="checked"'; ?>> <label for="enable_low_memory_mode"><?php _e('enable low memory mode', CSP_PO_TEXTDOMAIN); ?></label> <img id="enable_low_memory_mode_indicator" style="display:none;" alt="" src="<?php echo CSP_PO_BASE_URL."/images/loading-small.gif"?>" /><br />
	<small><?php _e('If your Installation is running under low remaining memory conditions, you will face the memory limit error during scan process or opening catalog content. If you hitting your limit, you can enable this special mode. This will try to perform the actions in a slightly different way but that will lead to a considerably slower response times but nevertheless gives no warranty, that it will solve your memory related problems at all cases.', CSP_PO_TEXTDOMAIN); ?></small>
</p>
<?php if (CSL_WPEC_PATCH) : ?>
<p>
	<small><strong><?php _e('Attention:', CSP_PO_TEXTDOMAIN); ?></strong>&nbsp;<?php _e("You have a running version of WP e-Commerce and it has been programmed to deactivate the javascript library prototype.js at each WordPress backend page! I did a work arround that, in case of issues read my article: <a href=\"http://www.code-styling.de/english/wp-e-commerce-breaks-intentionally-other-plugins-or-themes\">WP e-Commerce breaks intentionally other Plugins or Themes</a>", CSP_PO_TEXTDOMAIN); ?></small>
</p>
<?php endif; ?>
<ul class="subsubsub">
<li>
	<a<?php if(!isset($_GET['type'])) echo " class=\"current\""; ?> href="<?php echo $csp_wp_main_page ?>.php?page=codestyling-localization/codestyling-localization.php"><?php  _e('All Translations', CSP_PO_TEXTDOMAIN); ?>
	</a> | </li>
<li>
	<a<?php if(isset($_GET['type']) && $_GET['type'] == 'wordpress') echo " class=\"current\""; ?> href="<?php echo $csp_wp_main_page ?>.php?page=codestyling-localization/codestyling-localization.php&amp;type=wordpress"><?php _e('WordPress', CSP_PO_TEXTDOMAIN); ?>
	</a> | </li>
<?php if (csp_is_multisite()) { ?>
<li>
	<a<?php if(isset($_GET['type']) && $_GET['type'] == 'plugins_mu') echo " class=\"current\""; ?> href="<?php echo $csp_wp_main_page ?>.php?page=codestyling-localization/codestyling-localization.php&amp;type=plugins_mu"><?php _e('μ Plugins', CSP_PO_TEXTDOMAIN); ?>
	</a> | </li>
<?php } ?>
<li>
	<a<?php if(isset($_GET['type']) && $_GET['type'] == 'plugins') echo " class=\"current\""; ?> href="<?php echo $csp_wp_main_page ?>.php?page=codestyling-localization/codestyling-localization.php&amp;type=plugins"><?php _e('Plugins', CSP_PO_TEXTDOMAIN); ?>
	</a> | </li>
<li>
	<a<?php if(isset($_GET['type']) && $_GET['type'] == 'themes') echo " class=\"current\""; ?> href="<?php echo $csp_wp_main_page ?>.php?page=codestyling-localization/codestyling-localization.php&amp;type=themes"><?php _e('Themes', CSP_PO_TEXTDOMAIN); ?>
	</a> | </li>
<li>
	<a<?php if(isset($_GET['type']) && $_GET['type'] == 'compat') echo " class=\"current\""; ?> href="<?php echo $csp_wp_main_page ?>.php?page=codestyling-localization/codestyling-localization.php&amp;type=compat"><?php _e('Compatibility', CSP_PO_TEXTDOMAIN); ?>
	</a> | </li>
<li>
	<a<?php if(isset($_GET['type']) && $_GET['type'] == 'security') echo " class=\"current\""; ?> href="<?php echo $csp_wp_main_page ?>.php?page=codestyling-localization/codestyling-localization.php&amp;type=security"><?php _e('Security Risk', CSP_PO_TEXTDOMAIN); ?>
	</a></li>
</ul>
<div style="float:<?php if (function_exists('is_rtl') && is_rtl()) echo 'left'; else echo 'right'; ?>;">
<small><em><?php _e('You like it?', CSP_PO_TEXTDOMAIN); ?></em></small>
<form style="float:right;" method="post" action="https://www.paypal.com/cgi-bin/webscr">
<input type="hidden" value="" name="amount">
<input type="hidden" value="_xclick" name="cmd">
<input type="hidden" value="donate@code-styling.de" name="business">
<input type="hidden" value="Donation www.code-styling.de - Plugin: Codestyling Localization" name="item_name">
<input type="hidden" value="1" name="no_shipping">
<input type="hidden" value="http://www.code-styling.de/" name="return">
<input type="hidden" value="http://www.code-styling.de/" name="cancel_return">
<input type="hidden" value="USD" name="currency_code">
<input type="hidden" value="0" name="tax">
<input type="hidden" value="PP-DonationsBF" name="bn">
<?php $loc = get_locale(); if ($loc == 'de_DE' || $loc == 'de') { $loc = 'de_DE'; } else { $loc = 'en_US'; } ?>
<input border="0" type="image" alt="Make payments with PayPal - it's fast, free and secure!" name="submit" src="https://www.paypal.com/<?php echo $loc ?>/i/btn/btn_donate_SM.gif">
</form>
<br/>
</div>
<table class="widefat clear" style="cursor:default;" cellspacing="0">
<thead>
  <tr>
    <th scope="col"><?php _e('Type',CSP_PO_TEXTDOMAIN); ?></th>
    <th scope="col"><?php _e('Description',CSP_PO_TEXTDOMAIN); ?></th>
	<th scope="col"><?php _e('Languages',CSP_PO_TEXTDOMAIN); ?></th>
  </tr>
</thead>
<tbody class="list" id="the-gettext-list">
<?php 
	$rows = csp_po_collect_by_type(isset($_GET['type']) ? $_GET['type'] : ''); 
	if (isset($_GET['type']) && $_GET['type'] == 'compat') $_GET['type'] = '';
	foreach($rows as $data) : 
?>
<!-- <tr<?php if ($data['status'] == __("activated",CSP_PO_TEXTDOMAIN)) echo " class=\"csp-active\""; ?>> -->
<tr<?php if (preg_match("/^".__("activated",CSP_PO_TEXTDOMAIN)."/", $data['status'])) echo " class=\"csp-active\""; ?>>
	<td align="center"><img alt="" src="<?php echo CSP_PO_BASE_URL."/images/".$data['img_type'].".gif"; ?>" /><div><strong><?php echo $data['type-desc']; ?></strong></div></td>
	<td>
		<h3 class="csp-type-name"><?php echo $data['name']; ?><span style="font-weight:normal;">&nbsp;&nbsp;&copy;&nbsp;</span><sup><em><?php echo $data['author']; ?></em></sup></h3>
		<table class="csp-type-info" border="0" width="100%">
			<tr>
				<td width="140px"><strong><?php _e('Textdomain',CSP_PO_TEXTDOMAIN); ?>:</strong></td>
				<td class="csp-info-value"><?php echo $data['textdomain']['identifier']; ?><?php if ($data['textdomain']['is_const']) echo " (".__('defined by constant',CSP_PO_TEXTDOMAIN).")"; ?></td>
			</tr>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td><strong><?php _e('Version',CSP_PO_TEXTDOMAIN); ?>:</strong></td>
				<td class="csp-info-value"><?php echo $data['version']; ?></td>
			</tr>
			<tr>
				<td><strong><?php _e('State',CSP_PO_TEXTDOMAIN); ?>:</strong></td>
				<td class="csp-info-value"><?php echo $data['status']; ?></td>
			</tr>
			<tr>
				<td><strong><?php _e('Description',CSP_PO_TEXTDOMAIN); ?>:</strong></td>
				<td class="csp-info-value"><?php echo $data['description'];?></td>
			</tr>
			<?php if (isset($data['dev-hints'])) : ?>
			<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
			<tr>
				<td><strong style="color: #f00;"><?php _e('Compatibility',CSP_PO_TEXTDOMAIN); ?>:</strong></td>
				<td class="csp-info-value"><?php echo $data['dev-hints'];?></td>
			</tr>
			<?php endif; ?>
			<?php if (isset($data['dev-security'])) : ?>
			<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
			<tr>
				<td><strong style="color: #f00;"><?php _e('Security Risk',CSP_PO_TEXTDOMAIN); ?>:</strong></td>
				<td class="csp-info-value"><?php echo $data['dev-security'];?></td>
			</tr>
			<?php endif; ?>
			<?php  if ($data['type'] == 'wordpress-xxx') : ?>
			<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
			<tr>
				<td><strong style="color: #f00;"><?php _e('Memory Warning',CSP_PO_TEXTDOMAIN); ?>:</strong></td>
				<td class="csp-info-value"><?php _e('Since WordPress 3.x version it may require at least <strong>58MB</strong> PHP memory_limit! The reason is still unclear but it doesn\'t freeze anymore. Instead a error message will be shown and the scanning process aborts while reaching your limits.',CSP_PO_TEXTDOMAIN); ?></td>
			<tr>
			<?php endif; ?>
			<?php if ($data['is-path-unclear']) : ?>
			<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
			<tr>
				<td><strong style="color: #f00;"><?php _e('Language Folder',CSP_PO_TEXTDOMAIN); ?>:</strong></td>
				<td class="csp-info-value"><?php _e('The translation file folder is ambiguous, please select by clicking the appropriated language file folder or ask the Author about!',CSP_PO_TEXTDOMAIN); ?></td>
			<tr>
			<?php endif; ?>
		</table>
		<?php if (isset($data['child-plugins'])) { foreach($data['child-plugins'] as $child) { ?>
		<div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #ccc;">
			<h3 class="csp-type-name"><?php echo $child['name']; ?> <small><em><?php _e('by',CSP_PO_TEXTDOMAIN); ?> <?php echo $child['author']; ?></em></small></h3>
			<table class="csp-type-info" border="0">
				<tr>
					<td><strong><?php _e('Version',CSP_PO_TEXTDOMAIN); ?>:</strong></td>
					<td width="100%" class="csp-info-value"><?php echo $child['version']; ?></td>
				</tr>
				<tr>
					<td><strong><?php _e('State',CSP_PO_TEXTDOMAIN); ?>:</strong></td>
					<td class="csp-info-value"><?php echo $child['status']; ?></td>
				</tr>
				<tr>
					<td><strong><?php _e('Description',CSP_PO_TEXTDOMAIN); ?>:</strong></td>
					<td class="csp-info-value"><?php echo $child['description'];?></td>
				</tr>
			</table>
		</div>
		<?php } } ?>
	</td>
	<td>
		<?php  if ($data['type'] == 'wordpress' && $data['is_US_Version'] ) {?>
			<div style="color:#f00;"><?php _e("The original US version doesn't contain the language directory.",CSP_PO_TEXTDOMAIN); ?></div>
			<br/>
			<div><a class="clickable button" onclick="csp_create_languange_path(this, '<?php echo str_replace("\\", '/', WP_CONTENT_DIR)."/languages" ?>');"><?php _e('try to create the WordPress language directory',CSP_PO_TEXTDOMAIN); ?></a></div>
			<br/>
			<div>
				<?php _e('or create the missing directory using FTP Access as:',CSP_PO_TEXTDOMAIN); ?>
				<br/><br/>
				<?php echo str_replace("\\", '/', WP_CONTENT_DIR)."/"; ?><strong style="color:#f00;">languages</strong>			
			</div>
		<?php } elseif($data['is-path-unclear']) { ?>
			<strong style="border-bottom: 1px solid #ccc;"><?php _e('Available Directories:',CSP_PO_TEXTDOMAIN) ?></strong><br/><br/>
			<?php 
				$tmp = array(); 
				$dirs = rscanpath($data['base_path'], $tmp);
				$dir = $data['base_path'];
				echo '<a class="clickable pot-folder" onclick="csp_create_pot_indicator(this,\''.$dir.$data['base_file'].'xx_XX.pot\');">'. str_replace(str_replace("\\","/",WP_PLUGIN_DIR), '', $dir)."</a><br/>";
				foreach($dirs as $dir) { 
					echo '<a class="clickable pot-folder" onclick="csp_create_pot_indicator(this,\''.$dir.'/'.$data['base_file'].'xx_XX.pot\');">'. str_replace(str_replace("\\","/",WP_PLUGIN_DIR), '', $dir)."</a><br/>";
				} 
			?>
		<?php } elseif($data['name'] == 'bbPress' && isset($data['is_US_Version']) && $data['is_US_Version']) { ?>	
			<div style="color:#f00;"><?php _e("The original bbPress component doesn't contain a language directory.",CSP_PO_TEXTDOMAIN); ?></div>
			<br/>
			<div><a class="clickable button" onclick="csp_create_languange_path(this, '<?php echo $data['base_path']."my-languages"; ?>');"><?php _e('try to create the bbPress language directory',CSP_PO_TEXTDOMAIN); ?></a></div>
			<br/>
			<div>
				<?php _e('or create the missing directory using FTP Access as:',CSP_PO_TEXTDOMAIN); ?>
				<br/><br/>
				<?php echo $data['base_path']; ?><strong style="color:#f00;">my-languages</strong>			
			</div>			
		<?php	} else { ?>
		<table width="100%" cellspacing="0" class="mo-list" id="mo-list-<?php echo ++$mo_list_counter; ?>" summary="<?php echo $data['textdomain']['identifier'].'|'.$data['type']; ?>">
			<tr class="mo-list-head">
				<td colspan="2" nowrap="nowrap">
					<img alt="GNU GetText" class="alignleft" src="<?php echo CSP_PO_BASE_URL; ?>/images/gettext.gif" />
					&nbsp;<a rel="<?php echo implode('|', array_keys($data['languages']));?>" class="clickable mofile" onclick="csp_add_language(this,'<?php echo $data['type']; ?>','<?php echo rawurlencode($data['name'])." v".$data['version']."','mo-list-".$mo_list_counter."','".$data['base_path']."','".$data['base_file']."',this.rel,'".$data['type']."','".$data['simple-filename']."','".$data['translation_template']."','".$data['textdomain']['identifier']."',".($data['deny_scanning'] ? '1' : '0') ?>);"><?php _e("Add New Language", CSP_PO_TEXTDOMAIN); ?></a>
				</td>
				<td nowrap="nowrap" class="csp-ta-right"><?php echo sprintf(_n('<strong>%d</strong> Language', '<strong>%d</strong> Languages',count($data['languages']),CSP_PO_TEXTDOMAIN), count($data['languages'])); ?></td>
			</tr>
			<tr class="mo-list-desc">
				<td nowrap="nowrap" align="center"><?php _e('Language',CSP_PO_TEXTDOMAIN);?></td>
				<td nowrap="nowrap" align="center"><?php _e('Permissions',CSP_PO_TEXTDOMAIN);?></td>
				<td nowrap="nowrap" align="center"><?php _e('Actions',CSP_PO_TEXTDOMAIN);?></td>
			</tr>
			<?php 
				foreach($data['languages'] as $lang => $gtf) : 
					$country_www = isset($csp_l10n_sys_locales[$lang]) ? $csp_l10n_sys_locales[$lang]['country-www'] : 'unknown';
					$lang_native = isset($csp_l10n_sys_locales[$lang]) ? $csp_l10n_sys_locales[$lang]['lang-native'] : '<em>locale: </em>'.$lang;
			?>
			<tr class="mo-file" lang="<?php echo $lang; ?>">
				<td nowrap="nowrap" width="100%"><img title="<?php _e('Locale',CSP_PO_TEXTDOMAIN); ?>: <?php echo $lang ?>" alt="(locale: <?php echo $lang; ?>)" src="<?php echo CSP_PO_BASE_URL."/images/flags/".$country_www.".gif"; ?>" /><?php if (get_locale() == $lang) echo "<strong>"; ?>&nbsp;<?php echo $lang_native; ?><?php if (get_locale() == $lang) echo "</strong>"; ?></td>
				<td nowrap="nowrap" align="center">
					<div style="width:44px">
						<?php if (array_key_exists('po', $gtf)) {
							echo "<a class=\"csp-filetype-po".$gtf['po']['class']."\" title=\"".$gtf['po']['stamp'].($gtf['po']['class'] == '-r' ? '" onclick="csp_make_writable(this,\''.$data['base_path'].$data['base_file'].$lang.".po".'\',\'csp-filetype-po-rw\');' : '')."\">&nbsp;</a>";
						} else { ?>
						<a class="csp-filetype-po" title="<?php _e('-n.a.-',CSP_PO_TEXTDOMAIN); ?> [---|---|---]">&nbsp;</a>
						<?php } ?>
						<?php if (array_key_exists('mo', $gtf)) {
							echo "<a class=\"csp-filetype-mo".$gtf['mo']['class']."\" title=\"".$gtf['mo']['stamp'].($gtf['mo']['class'] == '-r' ? '" onclick="csp_make_writable(this,\''.$data['base_path'].$data['base_file'].$lang.".mo".'\',\'csp-filetype-mo-rw\');' : '')."\">&nbsp;</a>";
						} else { ?>
						<a class="csp-filetype-mo" title="<?php _e('-n.a.-',CSP_PO_TEXTDOMAIN); ?> [---|---|---]">&nbsp;</a>
						<?php } ?>
					</div>
				</td>
				<td nowrap="nowrap" style="padding-right: 5px;">
					<a class="clickable" onclick="csp_launch_editor(this, '<?php echo $data['base_file'].$lang.".po" ;?>', '<?php echo $data['base_path']; ?>','<?php echo $data['textdomain']['identifier']; ?>');"><?php _e('Edit',CSP_PO_TEXTDOMAIN); ?></a>
					<span> | </span>
					<?php if (!$data['deny_scanning']) : ?>
					<a class="clickable" onclick="csp_rescan_language(this,'<?php echo rawurlencode($data['name'])." v".$data['version']."','mo-list-".$mo_list_counter."','".$data['base_path']."','".$data['base_file']."','".$lang."','".$data['type']."','".$data['simple-filename']."'"; ?>)"><?php _e('Rescan',CSP_PO_TEXTDOMAIN); ?></a>
					<span> | </span>
					<?php else: ?>
					<span style="text-decoration: line-through;"><?php _e('Rescan',CSP_PO_TEXTDOMAIN); ?></span>
					<span> | </span>
					<?php endif; ?>
					<a class="clickable" onclick="csp_remove_language(this,'<?php echo rawurlencode($data['name'])." v".$data['version']."','mo-list-".$mo_list_counter."','".$data['base_path']."','".$data['base_file']."','".$lang."'"; ?>)"><?php _e('Delete',CSP_PO_TEXTDOMAIN); ?></a>
				</td>
			</tr>
			<?php endforeach; ?>		
		</table>
		<?php } ?>
	</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div><!-- csp-wrap-main closed -->
<div id="csp-wrap-editor" class="wrap" style="display:none">
	<div class="icon32" id="icon-tools"><br/></div>
	<h2><?php _e('Translate Language File', CSP_PO_TEXTDOMAIN); ?>&nbsp;&nbsp;&nbsp;<a class="clickable button" onclick="window.location.reload()"><?php _e('back to overview page &raquo;', CSP_PO_TEXTDOMAIN) ?></a></h2>
	<div id="csp-json-header">
		<div class="po-header-toggle"><strong><?php _e('File:', CSP_PO_TEXTDOMAIN); ?></strong> <a onclick="csp_toggle_header(this,'po-hdr');"><?php _e('unknown', CSP_PO_TEXTDOMAIN); ?></a></div>
	</div>
	<div class="action-bar">
		<p>
			<small>
			<?php _e('<b>Hint:</b> The extended feature for textdomain separation shows at dropdown box <i>Textdomain</i> the pre-selected primary textdomain.',CSP_PO_TEXTDOMAIN); ?><br/>
			<?php _e('All other additional contained textdomains occur at the source but will not be used, if not explicitely supported by this component!',CSP_PO_TEXTDOMAIN); ?><br/>
			<?php _e('Please contact the author, if some of the non primary textdomain based phrases will not show up translated at the required position!',CSP_PO_TEXTDOMAIN); ?><br/>
			<?php _e('The Textdomain <i><b>default</b></i> always stands for the WordPress main language file, this could be either intentionally or accidentally!',CSP_PO_TEXTDOMAIN); ?><br/>
			</small>
		</p>
		<div class="alignleft"id="csp-mo-textdomain"><span><b><?php _e('Textdomain:',CSP_PO_TEXTDOMAIN); ?></b><span>&nbsp;&nbsp;<select id="csp-mo-textdomain-val" onchange="csp_change_textdomain_view(this.value);"></select></div>
		<div class="alignleft">&nbsp;&nbsp;<input id="csp-write-mo-file" class="button button-secondary" style="display:none" type="submit" value="<?php _e('generate mo-file', CSP_PO_TEXTDOMAIN); ?>" onclick="csp_generate_mofile(this);" /></div>
		<div class="alignleft" style="margin-left:10px;font-size:11px;padding-top:3px;"><?php _e('last written:',CSP_PO_TEXTDOMAIN);?>&nbsp;&nbsp;<span id="catalog-last-saved" ><?php _e('unknown',CSP_PO_TEXTDOMAIN); ?></span></div>
		<br class="clear" />
	</div>
	<ul class="subsubsub">
		<li><a id="csp-filter-all" class="csp-filter current" onclick="csp_filter_result(this, csp_idx.total)"><?php _e('Total', CSP_PO_TEXTDOMAIN); ?> ( <span class="csp-flt-cnt">0</span> )</a> | </li>
		<li><a id="csp-filter-plurals" class="csp-filter" onclick="csp_filter_result(this, csp_idx.plurals)"><?php _e('Plural', CSP_PO_TEXTDOMAIN); ?> ( <span class="csp-flt-cnt">0</span> )</a> | </li>
		<li><a id="csp-filter-ctx" class="csp-filter" onclick="csp_filter_result(this, csp_idx.ctx)"><?php _e('Context', CSP_PO_TEXTDOMAIN); ?> ( <span class="csp-flt-cnt">0</span> )</a> | </li>
		<li><a id="csp-filter-open" class="csp-filter" onclick="csp_filter_result(this, csp_idx.open)"><?php _e('Not translated', CSP_PO_TEXTDOMAIN); ?> ( <span class="csp-flt-cnt">0</span> )</a> | </li>
		<li><a id="csp-filter-rem" class="csp-filter" onclick="csp_filter_result(this, csp_idx.rem)"><?php _e('Comments', CSP_PO_TEXTDOMAIN); ?> ( <span class="csp-flt-cnt">0</span> )</a> | </li>
		<li><a id="csp-filter-code" class="csp-filter" onclick="csp_filter_result(this, csp_idx.code)"><?php _e('Code Hint', CSP_PO_TEXTDOMAIN); ?> ( <span class="csp-flt-cnt">0</span> )</a> | </li>
		<li><a id="csp-filter-trail" class="csp-filter" onclick="csp_filter_result(this, csp_idx.trail)"><?php _e('Trailing Space', CSP_PO_TEXTDOMAIN); ?> ( <span class="csp-flt-cnt">0</span> )</a></li>
		<li style="display:none;"> | <span id="csp-filter-search" class="current"><?php _e('Search Result', CSP_PO_TEXTDOMAIN); ?>  ( <span class="csp-flt-cnt">0</span> )</span></li>
		<li style="display:none;"> | <span id="csp-filter-regexp" class="current"><?php _e('Expression Result', CSP_PO_TEXTDOMAIN); ?>  ( <span class="csp-flt-cnt">0</span> )</span></li>
	</ul>
	<div class="tablenav">
		<div class="alignleft">
			<div class="alignleft" style="padding-top: 5px;font-size:11px;"><strong><?php _e('Page Size', CSP_PO_TEXTDOMAIN); ?>:&nbsp;</strong></div>
			<select id="catalog-pagesize" name="catalog-pagesize" onchange="csp_change_pagesize(this.value);" class="alignleft" style="font-size:11px;" autocomplete="off">
				<option value="10">10</option>
				<option value="25">25</option>
				<option value="50">50</option>
				<option value="75">75</option>
				<option value="100" selected="selected">100</option>
				<option value="150">150</option>
				<option value="200">200</option>
			</select>
		</div>
		<div id="catalog-pages-top" class="tablenav-pages alignright">
			<a href="#" class="prev page-numbers"><?php _e('&laquo; Previous', CSP_PO_TEXTDOMAIN); ?></a>
			<a href="#" class="page-numbers">1</a>
			<a href="#" class="page-numbers">2</a>
			<a href="#" class="page-numbers">3</a>
			<span class="page-numbers current">4</span>
			<a href="#" class="next page-numbers"><?php _e('Next &raquo;', CSP_PO_TEXTDOMAIN); ?></a>
		</div>
		<br class="clear" />
	</div>
	<br class="clear" />
	<table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<th nowrap="nowrap"><span><?php _e('Infos',CSP_PO_TEXTDOMAIN); ?></span></th>
				<th width="50%">
					<table>
						<tr>
							<th style="background:transparent;border-bottom:0px;padding:0px;"><?php _e('Original:',CSP_PO_TEXTDOMAIN); ?></th>
							<th style="background:transparent;border-bottom:0px;padding:0px;vertical-align:top;">
								<input id="s_original" name="s_original" type="text" size="16" value="" onkeyup="csp_search_result(this)" style="margin-bottom:3px;" autocomplete="off" />
								<br/>
								<input id="ignorecase_key" name="ignorecase_key" type="checkbox" value="" onclick="csp_search_key('s_original')" /><label for="ignorecase_key" style="font-weight:normal;margin-top:-2px;"> <?php _e('non case-sensitive', CSP_PO_TEXTDOMAIN) ?></label>
							</th>
							<th style="background:transparent;border-bottom:0px;padding:0px;vertical-align:top;">
								<a class="clickable regexp" onclick="csp_search_regexp('s_original')"></a>
							</th>
						</tr>
					</table>
				</th>
				<th width="50%">
					<table>
						<tr>
							<th style="background:transparent;border-bottom:0px;padding:0px;"><?php _e('Translation:',CSP_PO_TEXTDOMAIN); ?></th>
							<th style="background:transparent;border-bottom:0px;padding:0px;vertical-align:top;">
								<input id="s_translation" name="s_translation" type="text" size="16" value="" onkeyup="csp_search_result(this)" style="margin-bottom:3px;" autocomplete="off" />
								<br/>
								<input id="ignorecase_val" name="ignorecase_val" type="checkbox" value="" onclick="csp_search_val('s_translation')" /><label for="ignorecase_val" style="font-weight:normal;margin-top:-2px;"> <?php _e('non case-sensitive', CSP_PO_TEXTDOMAIN) ?></label>
							</th>
							<th style="background:transparent;border-bottom:0px;padding:0px;vertical-align:top;">
								<a class="clickable regexp" onclick="csp_search_regexp('s_translation')"></a>
							</th>
						</tr>
					</table>
				</th>
				<th nowrap="nowrap"><span><?php _e('Actions',CSP_PO_TEXTDOMAIN); ?></span></th>
			</tr>
		</thead>
		<tbody id="catalog-body">
			<tr><td colspan="4" align="center"><img alt="" src="<?php echo CSP_PO_BASE_URL."/images/loading.gif"?>" /><br /><span style="color:#328AB2;"><?php _e('Please wait, file content presently being loaded ...',CSP_PO_TEXTDOMAIN); ?></span></td></tr>
		</tbody>
	</table>	
	<div class="tablenav">
		<a class="alignright button" href="javascript:void(0);" onclick="window.scrollTo(0,0);" style="margin:3px 0 0 30px;"><?php _e('scroll to top', CSP_PO_TEXTDOMAIN); ?></a>
		<div id="catalog-pages-bottom" class="tablenav-pages">
			<a href="#" class="prev page-numbers"><?php _e('&laquo; Previous', CSP_PO_TEXTDOMAIN); ?></a>
			<a href="#" class="page-numbers">1</a>
			<a href="#" class="page-numbers">2</a>
			<a href="#" class="page-numbers">3</a>
			<span class="page-numbers current">4</span>
			<a href="#" class="next page-numbers"><?php _e('Next &raquo;', CSP_PO_TEXTDOMAIN); ?></a>
		</div>
		<br class="clear" />
	</div>
	<br class="clear" />
</div><!-- csp-wrap-editor closed -->
<div id="csp-dialog-container" style="display:none;">
	<div>
		<h3 id="csp-dialog-header">
			<img alt="" id="csp-dialog-icon" class="alignleft" src="<?php echo CSP_PO_BASE_URL; ?>/images/gettext.gif" />
			<span id="csp-dialog-caption" class="alignleft"><?php _e('Edit Catalog Entry',CSP_PO_TEXTDOMAIN); ?></span>
			<img alt="" id="csp-dialog-cancel" class="alignright clickable" title="<?php _e('close', CSP_PO_TEXTDOMAIN); ?>" src="<?php echo CSP_PO_BASE_URL."/images/close.gif"; ?>" onclick="csp_cancel_dialog();" />
			<br class="clear" />
		</h3>	
		<div id="csp-dialog-body"></div>
		<div style="text-align:center;"><img id="csp-dialog-saving" src="<?php echo CSP_PO_BASE_URL; ?>/images/saving.gif" style="margin-top:20%;display:none;" /></div>
	</div>
</div><!-- csp-dialog-container closed -->
<br />
<script type="text/javascript">
/* <![CDATA[ */

Object.extend(Array.prototype, {
  intersect: function(array){
    return this.findAll( function(token){ return array.include(token) } );
  }
});

//--- management based functions ---
function csp_make_writable(elem, file, success_class) {
	elem = $(elem);
	elem.blur();
	new Ajax.Request('<?php echo CSP_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'csp_po_change_permission',
				file: file
			},
			onSuccess: function(transport) {		
				elem.className=success_class;
				elem.title=transport.responseJSON.title;
				elem.onclick = null;
			},
			onFailure: function(transport) {
				csp_show_error(transport.responseText);
			}
		}
	);
	return false;	
}

function csp_add_language(elem, type, name, row, path, subpath, existing, type, simplefilename, transtemplate, textdomain, denyscan) {
	elem = $(elem);
	elem.blur();
	new Ajax.Request('<?php echo CSP_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'csp_po_dlg_new',
				type: type,
				name: name,
				row: row,
				path: path,
				subpath: subpath,
				existing: existing,
				type: type,
				simplefilename: simplefilename,
				transtemplate: transtemplate,
				textdomain: textdomain,
				denyscan: denyscan
			},
			onSuccess: function(transport) {
				$('csp-dialog-caption').update("<?php _e('Add New Language',CSP_PO_TEXTDOMAIN); ?>");
				$("csp-dialog-body").update(transport.responseText).setStyle({'padding' : '10px'});
				tb_show(null,"#TB_inline?height=530&width=500&inlineId=csp-dialog-container&modal=true",false);
			}
		}
	); 	
	return false;
}

function csp_create_new_pofile(elem, type){
	elem = $(elem);
	elem.blur();
	
	new Ajax.Request('<?php echo CSP_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'csp_po_create',
				name: $('csp-dialog-name').value,
				timestamp: $('csp-dialog-timestamp').value,
				translator: $('csp-dialog-translator').value,
				path: $('csp-dialog-path').value,
				subpath: $('csp-dialog-subpath').value,
				language: $('csp-dialog-language').value,
				row : $('csp-dialog-row').value,
				numlangs: $('csp-dialog-numlangs').value,
				type: type,
				simplefilename: $('csp-dialog-simplefilename').value,
				transtemplate: $('csp-dialog-transtemplate').value,
				textdomain: $('csp-dialog-textdomain').value,
				denyscan: $('csp-dialog-denyscan').value
			},
			onSuccess: function(transport) {	
				$$('#'+transport.responseJSON.row+' .mo-list-head').first().down(3).update(transport.responseJSON.head);
				rel = $$('#'+transport.responseJSON.row+' .mo-list-head').first().down(2).rel;
				$$('#'+transport.responseJSON.row+' .mo-list-head').first().down(2).rel += ((rel.empty() ? '' : "|" ) + transport.responseJSON.language);
				elem_after = null;
								
				content = "<tr class=\"mo-file\" lang=\""+transport.responseJSON.language+"\">"+
					"<td nowrap=\"nowrap\" width=\"100%\">"+
						"<img title=\"<?php _e('Locale',CSP_PO_TEXTDOMAIN); ?>: "+transport.responseJSON.language+"\" alt=\"(locale: "+transport.responseJSON.language+")\" src=\""+transport.responseJSON.image+"\" />" +
						("<?php echo get_locale(); ?>" == transport.responseJSON.language ? "<strong>" : "") + 
						"&nbsp;" + transport.responseJSON.lang_native +
						("<?php echo get_locale(); ?>" == transport.responseJSON.language ? "</strong>" : "") + 
					"</td>"+
					"<td align=\"center\">"+
						"<div style=\"width:44px\">"+
						"<a class=\"csp-filetype-po-rw\" title=\""+transport.responseJSON.permissions+"\">&nbsp;</a>"+
						"<a class=\"csp-filetype-mo\" title=\"<?php _e('-n.a.-',CSP_PO_TEXTDOMAIN); ?> [---|---|---]\">&nbsp;</a>"+
						"</div>"+
					"</td>"+
					"<td nowrap=\"nowrap\">"+
						"<a class=\"clickable\" onclick=\"csp_launch_editor(this, '"+transport.responseJSON.subpath+transport.responseJSON.language+".po"+"', '"+transport.responseJSON.path+"','"+transport.responseJSON.textdomain+"');\"><?php _e('Edit',CSP_PO_TEXTDOMAIN); ?></a>"+
						"<span> | </span>"+(transport.responseJSON.denyscan == false ? 
						"<a class=\"clickable\" onclick=\"csp_rescan_language(this,'"+escape(transport.responseJSON.name)+"','"+transport.responseJSON.row+"','"+transport.responseJSON.path+"','"+transport.responseJSON.subpath+"','"+transport.responseJSON.language+"','"+transport.responseJSON.type+"','"+transport.responseJSON.simplefilename+"')\"><?php _e('Rescan',CSP_PO_TEXTDOMAIN); ?></a>"+
						"<span> | </span>" 
						: 
						"<span style=\"text-decoration: line-through;\"><?php _e('Rescan',CSP_PO_TEXTDOMAIN); ?></span>"+
						"<span> | </span>" 
						) +
						"<a class=\"clickable\" onclick=\"csp_remove_language(this,'"+escape(transport.responseJSON.name)+"','"+transport.responseJSON.row+"','"+transport.responseJSON.path+"','"+transport.responseJSON.subpath+"','"+transport.responseJSON.language+"');\"><?php _e('Delete',CSP_PO_TEXTDOMAIN); ?></a>"+
					"</td>"+
					"</tr>";			
				$$('#'+transport.responseJSON.row+' .mo-file').each(function(tr) {
					if ((tr.lang > transport.responseJSON.language) && !Object.isElement(elem_after)) {	elem_after = tr; }
				});
				ne = null;
				if (Object.isElement(elem_after)) { ne = elem_after.insert({ 'before' : content }).previous(); }
				else { ne = $$('#'+transport.responseJSON.row+' tbody').first().insert(content).childElements().last(); }
				new Effect.Highlight(ne, { startcolor: '#25FF00', endcolor: '#FFFFCF' });
			},
			onFailure: function(transport) {
				csp_show_error(transport.responseText);
			}
		}
	); 	
	csp_cancel_dialog();
	return false;
}

function csp_remove_language(elem, name, row, path, subpath, language) {
	elem = $(elem);
	elem.blur();
	new Ajax.Request('<?php echo CSP_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'csp_po_dlg_delete',
				name: name,
				row: row,
				path: path,
				subpath: subpath,
				language: language,
				numlangs: $$('#'+row+' .mo-list-head').first().down(2).rel.split('|').size()
			},
			onSuccess: function(transport) {
				$('csp-dialog-caption').update("<?php _e('Confirm Delete Language',CSP_PO_TEXTDOMAIN); ?>");
				$("csp-dialog-body").update(transport.responseText).setStyle({'padding' : '10px'});
				tb_show.defer(null,"#TB_inline?height=180&width=300&inlineId=csp-dialog-container&modal=true",false);
			}
		}
	); 	
	return false;
}

function csp_destroy_files(elem, name, row, path, subpath, language, numlangs){
	elem = $(elem);
	elem.blur();
	csp_cancel_dialog();
	new Ajax.Request('<?php echo CSP_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'csp_po_destroy',
				name: name,
				row: row,
				path: path,
				subpath: subpath,
				language: language,
				numlangs: numlangs
			},
			onSuccess: function(transport) {
				$$('#'+transport.responseJSON.row+' .mo-file').each(function(tr) {
					if (tr.lang == transport.responseJSON.language) { 
						new Effect.Highlight(tr, { 
							startcolor: '#FF7A0F', 
							endcolor: '#FFFFCF', 
							duration: 1,
							afterFinish: function(obj) { 
								$$('#'+transport.responseJSON.row+' .mo-list-head').first().down(3).update(transport.responseJSON.head);
								a = $$('#'+transport.responseJSON.row+' .mo-list-head').first().down(2).rel.split('|').without(transport.responseJSON.language);
								$$('#'+transport.responseJSON.row+' .mo-list-head').first().down(2).rel = a.join('|');
								obj.element.remove(); 
							}
						});
					}
				});
			},
			onFailure: function(transport) {
				csp_show_error(transport.responseText);
			}
		}
	); 	
	return false;	
}

function csp_rescan_language(elem, name, row, path, subpath, language, type, simplefilename) {
	elem = $(elem);
	elem.blur();
	var a = elem.up('table').summary.split('|');
	actual_domain = a[0];
	new Ajax.Request('<?php echo CSP_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'csp_po_dlg_rescan',
				name: name,
				row: row,
				path: path,
				subpath: subpath,
				language: language,
				numlangs: $$('#'+row+' .mo-list-head').first().down(2).rel.split('|').size(),
				type: type,
				textdomain: actual_domain,
				simplefilename: simplefilename
			},
			onSuccess: function(transport) {
				$('csp-dialog-caption').update("<?php _e('Rescanning PHP Source Files',CSP_PO_TEXTDOMAIN); ?>");
				$("csp-dialog-body").update(transport.responseText).setStyle({'padding' : '10px'});
				tb_show.defer(null,"#TB_inline?height=230&width=510&inlineId=csp-dialog-container&modal=true",false);
			}
		}
	); 		
	return false;
}

var csp_php_source_json = 0;
var csp_chuck_size = <?php echo (CSL_LOW_MEMORY ? 1 : 20); ?>;

function csp_scan_source_files() {
	if (csp_php_source_json == 0) {
		$('csp-dialog-rescan').hide();
		$('csp-dialog-cancel').hide();
		$('csp-dialog-scan-info').show();
		csp_php_source_json = $('csp-dialog-source-file-json').value.evalJSON();
	}
	if (csp_php_source_json.next >= csp_php_source_json.files.size()) {
		if ($('csp-dialog-cancel').visible()) {
			csp_cancel_dialog();
			csp_php_source_json = 0;
			return false;
		}
		$('csp-dialog-scan-info').hide();
		$('csp-dialog-rescan').show().writeAttribute({'value' : '<?php _e('finished', CSP_PO_TEXTDOMAIN); ?>' });
		$('csp-dialog-cancel').show();
		$('csp-dialog-progressfile').update('&nbsp;');
		elem = $$("#"+csp_php_source_json.row+" .mo-file[lang=\""+csp_php_source_json.language+"\"] div a").first();
		elem.className = "csp-filetype-po-rw";
		elem.title = csp_php_source_json.title;
		return false;
	}
	new Ajax.Request('<?php echo CSP_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'csp_po_scan_source_file',
				type: csp_php_source_json.type,
				pofile: csp_php_source_json.pofile,
				textdomain: csp_php_source_json.textdomain,
				num: csp_php_source_json.next,
				cnt: csp_chuck_size,
				path: csp_php_source_json.path,
				php: csp_php_source_json.files.join("|")
			},
			onSuccess: function(transport) {
				try{
					csp_php_source_json.title = transport.responseJSON.title;
				}catch(e) {
					$('csp-dialog-scan-info').hide();
					$('csp-dialog-rescan').show().writeAttribute({'value' : '<?php _e('finished', CSP_PO_TEXTDOMAIN); ?>' });
					$('csp-dialog-cancel').show();
					csp_php_source_json = 0;
					var mem_reg = /Allowed memory size of (\d+) bytes exhausted/;
					mem_reg.exec(transport.responseText);
					error_text = "<?php _e('You are trying to rescan files which expands above your PHP Memory Limit at %s MB during the analysis.<br/>Please enable the <em>low memory mode</em> for scanning this component.',CSP_PO_TEXTDOMAIN); ?>";
					csp_show_error(error_text.replace('%s', RegExp.$1 / 1024.0 / 1024.0));
				}
				csp_php_source_json.next += csp_chuck_size;
				var perc = Math.min(Math.round(csp_php_source_json.next*1000.0/csp_php_source_json.files.size())/10.0, 100.00);
				$('csp-dialog-progressvalue').update(Math.min(csp_php_source_json.next, csp_php_source_json.files.size()));
				$('csp-dialog-progressbar').setStyle({'width' : ''+perc+'%'});
				if (csp_php_source_json.files[csp_php_source_json.next-csp_chuck_size]) $('csp-dialog-progressfile').update("<?php _e('File:', CSP_PO_TEXTDOMAIN); ?>&nbsp;"+csp_php_source_json.files[csp_php_source_json.next-csp_chuck_size].replace(csp_php_source_json.path,""));
				csp_scan_source_files().delay(0.1);
			},
			onFailure: function(transport) {
				$('csp-dialog-scan-info').hide();
				$('csp-dialog-rescan').show().writeAttribute({'value' : '<?php _e('finished', CSP_PO_TEXTDOMAIN); ?>' });
				$('csp-dialog-cancel').show();
				csp_php_source_json = 0;
				csp_show_error(transport.responseText);
			}
		}
	); 	
	return false;
}

//--- editor based functions ---
var csp_pagesize = 100;
var csp_pagenum = 1;
var csp_search_timer = null;
var csp_search_interval = Prototype.Browser.IE ? 0.3 : 0.1;

var csp_destlang = 'de';
var csp_path = '';
var csp_file = '';
var csp_num_plurals = 2;
var csp_func_plurals = '';
var csp_idx = {	'total' : [], 'plurals' : [], 'open' : [], 'rem' : [], 'code' : [], 'ctx' : [], 'cur' : [] , 'ltd' : [] , 'trail' : [] }
var csp_searchbase = [];
var csp_pofile = [];
var csp_textdomains = [];
var csp_actual_type = '';

function csp_init_editor(actual_domain, actual_type) {
	//list all contained text domains
	opt_list = '';
	csp_actual_type = actual_type;
	for (i=0; i<csp_textdomains.size(); i++) {
		opt_list += '<option value="'+csp_textdomains[i]+'"'+(csp_textdomains[i] == actual_domain ? ' selected="selected"' : '')+'>'+(csp_textdomains[i].empty() ? 'default' : csp_textdomains[i])+'</option>';
	}
	initial_domain = $('csp-mo-textdomain-val').update(opt_list).value;
	
	//setup all indizee register
	for (i=0; i<csp_pofile.size(); i++) {
		csp_idx.total.push(i);
		if (Object.isArray(csp_pofile[i].key)) {
			if (csp_pofile[i].key[0].match(/\s+$/g) || csp_pofile[i].key[1].match(/\s+$/g)) {
				csp_idx.trail.push(i);
			}

			if (!Object.isArray(csp_pofile[i].val)) {
				if(csp_pofile[i].val.blank()) csp_idx.open.push(i);
			}
			else{
				if(csp_pofile[i].val.join('').blank()) csp_idx.open.push(i);
			}
			csp_idx.plurals.push(i);
		}else{
			if (csp_pofile[i].key.match(/\s+$/g)) {
				csp_idx.trail.push(i);
			}
			
			if(csp_pofile[i].val.empty()) {
				csp_idx.open.push(i);
			}
		}
		if(!csp_pofile[i].rem.empty()) csp_idx.rem.push(i);
		if(csp_pofile[i].ctx) csp_idx.ctx.push(i);
		if(csp_pofile[i].code) csp_idx.code.push(i);
		if(csp_pofile[i].ltd.indexOf(initial_domain) != -1) csp_idx.ltd.push(i);
	}
//$	csp_idx.cur = csp_idx.total;
	csp_idx.cur = csp_idx.ltd.intersect(csp_idx.total);
	csp_searchbase = csp_idx.cur;
/*
	if(csp_textdomains[0] != '{php-code}'){
		$('csp-write-mo-file').show();
	}else{
		$('csp-write-mo-file').hide();
	}
*/	
	csp_change_pagesize(100);
	window.scrollTo(0,0);
	$('s_original').value="";
	$('s_original').autoComplete="off";
	$('s_translation').value="";
	$('s_translation').autoComplete="off";	
	csp_change_textdomain_view(initial_domain);
}

function csp_change_textdomain_view(textdomain) {
	csp_idx.ltd = [];
	for (i=0; i<csp_pofile.size(); i++) {
		if (csp_pofile[i].ltd.indexOf(textdomain) != -1) csp_idx.ltd.push(i);
	}
	csp_idx.cur = csp_idx.ltd.intersect(csp_idx.total);
	csp_searchbase = csp_idx.cur;
	$$("a.csp-filter").each(function(e) { e.removeClassName('current')});
	$('csp-filter-all').addClassName('current');
	hide = false;
	if (textdomain == '{php-code}') { hide = true; }
	else if(textdomain == 'default') {
		hide = true;
		//special bbPress on BuddyPress test because of default domain too
		reg = /\/bp-forums\/bbpress\/$/;
		if ((csp_actual_type == 'wordpress')||reg.test(csp_path)) { hide = false; }
	}
	if (hide) {
		$('csp-write-mo-file').hide();
	}
	else {
		$('csp-write-mo-file').show();
	}
	csp_filter_result('csp-filter-all', csp_idx.total);
}

function csp_show_error(message) {
	error = "<div style=\"text-align:center\"><img src=\"<?php echo CSP_PO_BASE_URL."/images/error.gif"; ?>\" align=\"left\" />"+message+
			"<p style=\"margin:15px 0 0 0;text-align:center; padding-top: 5px;border-top: solid 1px #aaa;\">"+
			"<input class=\"button\" type=\"submit\" onclick=\"return csp_cancel_dialog();\" value=\"  Ok  \"/>"+
			"</p>"+
			"</div>";
	$('csp-dialog-caption').update("CodeStyling Localization - <?php _e('Access Error',CSP_PO_TEXTDOMAIN); ?>");
	$("csp-dialog-body").update(error).setStyle({'padding' : '10px'});
	if ($('csp-dialog-saving')) $('csp-dialog-saving').hide();
	tb_show.defer(null,"#TB_inline?height=140&width=510&inlineId=csp-dialog-container&modal=true",false);
}

function csp_cancel_dialog(){
	tb_remove();
	$('csp-dialog-body').update("");
	$$('.highlight-editing').each(function(e) {
		e.removeClassName('highlight-editing');
	});
}

function csp_launch_editor(elem, file, path, textdomain) {
	var a = $(elem).up('table').summary.split('|');
	$('csp-wrap-main').hide();
	$('csp-wrap-editor').show();
	new Ajax.Request('<?php echo CSP_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'csp_po_launch_editor',
				basepath: path,
				file: file,
				textdomain: textdomain
			},
			onSuccess: function(transport) {
				//switch to editor now
				try{
					$('csp-json-header').insert(transport.responseJSON.header);
				}catch(e) {
					var mem_reg = /Allowed memory size of (\d+) bytes exhausted/;
					mem_reg.exec(transport.responseText);
					error_text = "<?php _e('You are trying to open a translation catalog which expands above your PHP Memory Limit at %s MB during read.<br/>Please enable the <em>low memory mode</em> for opening this components catalog.',CSP_PO_TEXTDOMAIN); ?>";
					$('catalog-body').update('<tr><td colspan="4" align="center" style="color:#f00;">'+error_text.replace('%s', RegExp.$1 / 1024.0 / 1024.0)+'</td></tr>');
				}				
				$('catalog-last-saved').update(transport.responseJSON.last_saved);
				$$('#csp-json-header a')[0].update(transport.responseJSON.file);
				csp_destlang = transport.responseJSON.destlang;
				csp_path = transport.responseJSON.path;
				csp_file = transport.responseJSON.file;
				csp_num_plurals = transport.responseJSON.plurals_num;
				csp_func_plurals = transport.responseJSON.plurals_func;
				csp_idx = transport.responseJSON.index;
				csp_pofile = transport.responseJSON.content;
				csp_textdomains = transport.responseJSON.textdomains;
				csp_init_editor(a[0], a[1]);
			},
			onFailure: function(transport) {
				$('catalog-body').update('<tr><td colspan="4" align="center" style="color:#f00;">'+transport.responseText+'</td></tr>');
			}
		}
	); 
	return false;	
}

function csp_toggle_header(host, elem) {
	$(host).up().toggleClassName('po-header-collapse');
	$(elem).toggle();
}

function csp_change_pagesize(newsize) {
	csp_pagesize = parseInt(newsize);
	csp_change_pagenum(1);
}

function csp_change_pagenum(newpage) {
	csp_pagenum = newpage;
	var cp = $('catalog-pages-top');
	var cb = $('catalog-body')
	
	var inner = '';
	
	var cnt = Math.round(csp_idx.cur.size() * 1.0 / csp_pagesize + 0.499);
	if (cnt > 1) {
		
		if (csp_pagenum > 1) { inner += "<a class=\"next page-numbers\" onclick=\"csp_change_pagenum("+(csp_pagenum-1)+")\"><?php _e('&laquo; Previous', CSP_PO_TEXTDOMAIN); ?></a>"; }
		var low = Math.max(csp_pagenum - 5,1);
		if (low > 1) inner += "<span>&nbsp;...&nbsp;</span>"; 
		for (i=low; i<=Math.min(low+10,cnt); i++) {
			inner += "<a class=\"page-numbers"+(i==csp_pagenum ? ' current' : '')+"\" onclick=\"csp_change_pagenum("+i+")\">"+i+"</a>";
		}
		if (Math.min(low+10,cnt) < cnt) inner += "<span>&nbsp;...&nbsp;</span>"; 
		if (csp_pagenum < cnt) { inner += "<a class=\"next page-numbers\" onclick=\"csp_change_pagenum("+(csp_pagenum+1)+")\"><?php _e('Next &raquo;', CSP_PO_TEXTDOMAIN); ?></a>"; }
	}
	cp.update(inner);
	$('catalog-pages-bottom').update(inner);
	
	inner = '';

	for (var i=(csp_pagenum-1)*csp_pagesize; i<Math.min(csp_pagenum * csp_pagesize, csp_idx.cur.size());i++) {
		inner += "<tr"+(i % 2 == 0 ? '' : ' class="odd"')+" id=\"msg-row-"+csp_idx.cur[i]+"\">";
		var tooltip = [];
		if (!csp_pofile[csp_idx.cur[i]].rem.empty()) tooltip.push(String.fromCharCode(3)+"<?php _e('Comment',CSP_PO_TEXTDOMAIN); ?>"+String.fromCharCode(4)+csp_pofile[csp_idx.cur[i]].rem);
		if (csp_pofile[csp_idx.cur[i]].code) tooltip.push(String.fromCharCode(3)+"<?php _e('Code Hint',CSP_PO_TEXTDOMAIN); ?>"+String.fromCharCode(4)+csp_pofile[csp_idx.cur[i]].code);
		if (tooltip.size() > 0) {
			tooltip = tooltip.join(String.fromCharCode(1)).replace("\n", String.fromCharCode(1)).escapeHTML();
			tooltip = tooltip.replace(/\1/g, '<br/>').replace(/\3/g, '<strong>').replace(/\4/g, '</strong>');
		}
		else { tooltip = '' };
		inner += "<td nowrap=\"nowrap\">";
		if(csp_pofile[csp_idx.cur[i]].ref.size() > 0) {
			inner += "<a class=\"csp-msg-tip\"><img alt=\"\" src=\"<?php echo CSP_PO_BASE_URL;?>/images/php.gif\" /><span><strong><?php _e('Files:',CSP_PO_TEXTDOMAIN); ?></strong>";
			csp_pofile[csp_idx.cur[i]].ref.each(function(r) {
				inner += "<em onclick=\"csp_view_phpfile(this, '"+r+"', "+csp_idx.cur[i]+")\">"+r+"</em><br />";
			});
			inner += "</span></a>";
		}		
		inner += (tooltip.empty() ? '' : "<a class=\"csp-msg-tip\"><img alt=\"\" src=\"<?php echo CSP_PO_BASE_URL;?>/images/comment.gif\" /><span>"+tooltip+"</span></a>");
		inner += "</td>";
		ctx_str = '';
		if (csp_pofile[csp_idx.cur[i]].ctx) {
			ctx_str = "<div><b style=\"border-bottom: 1px dotted #000;\"><?php _e('Context',CSP_PO_TEXTDOMAIN); ?>:</b>&nbsp;<span style=\"color:#f00;\">"+csp_pofile[csp_idx.cur[i]].ctx+"</span></div>";
		}
		if (Object.isArray(csp_pofile[csp_idx.cur[i]].key)) {
			inner += 
				"<td>"+ctx_str+"<div><span class=\"csp-pl-form\"><?php _e('Singular:',CSP_PO_TEXTDOMAIN); ?> </span>"+csp_pofile[csp_idx.cur[i]].key[0].escapeHTML().replace(/\s+$/g,'<span style="border: solid 1px #FF8080;">&nbsp;</span>')+"</div><div><span class=\"csp-pl-form\"><?php _e('Plural:',CSP_PO_TEXTDOMAIN); ?> </span>"+csp_pofile[csp_idx.cur[i]].key[1].escapeHTML().replace(/\s+$/g,'<span style="border: solid 1px #FF8080;">&nbsp;</span>')+"</div></td>"+
				"<td>"+ctx_str;
			for (pl=0;pl<csp_num_plurals; pl++) {
				if (csp_num_plurals == 1) {
					inner += "<div><span class=\"csp-pl-form\"><?php _e('Plural Index Result =',CSP_PO_TEXTDOMAIN); ?> "+pl+" </span>"+(!csp_pofile[csp_idx.cur[i]].val.empty() ? csp_pofile[csp_idx.cur[i]].val.escapeHTML().replace(/\s+$/g,'<span style="border: solid 1px #FF8080;">&nbsp;</span>') : '&nbsp;')+"</div>"
				}
				else{
					inner += "<div><span class=\"csp-pl-form\"><?php _e('Plural Index Result =',CSP_PO_TEXTDOMAIN); ?> "+pl+" </span>"+(!csp_pofile[csp_idx.cur[i]].val[pl].empty() ? csp_pofile[csp_idx.cur[i]].val[pl].escapeHTML().replace(/\s+$/g,'<span style="border: solid 1px #FF8080;">&nbsp;</span>') : '&nbsp;')+"</div>"
				}
			}
			inner += "</td>";
		}
		else{			
			inner += 
				"<td>"+ctx_str+csp_pofile[csp_idx.cur[i]].key.escapeHTML().replace(/\s+$/g,'<span style="border: solid 1px #FF8080;">&nbsp;</span>')+"</td>"+
				"<td>"+ctx_str+(csp_pofile[csp_idx.cur[i]].val.empty() ? '&nbsp;' : csp_pofile[csp_idx.cur[i]].val.escapeHTML().replace(/\s+$/g,'<span style="border: solid 1px #FF8080;">&nbsp;</span>'))+"</td>";
		}
		inner += 
			"<td nowrap=\"nowrap\">"+
			  "<a class=\"tr-edit-link\" onclick=\"return csp_edit_catalog(this);\"><?php _e('Edit',CSP_PO_TEXTDOMAIN); ?></a>&nbsp;|&nbsp;"+  
			  "<a onclick=\"return csp_copy_catalog(this);\"><?php _e('Copy',CSP_PO_TEXTDOMAIN); ?></a>"; // TODO: add here comment editing link
		inner += "</td></tr>";
	}	
	cb.replace("<tbody id=\"catalog-body\">"+inner+"</tbody>");
	
	$$("#csp-filter-all span").first().update(csp_idx.cur.size() + " / " + csp_idx.total.size());
	$$("#csp-filter-plurals span").first().update(csp_idx.plurals.size());
	$$("#csp-filter-open span").first().update(csp_idx.open.size());
	$$("#csp-filter-rem span").first().update(csp_idx.rem.size());
	$$("#csp-filter-code span").first().update(csp_idx.code.size());
	$$("#csp-filter-ctx span").first().update(csp_idx.ctx.size());
	$$("#csp-filter-trail span").first().update(csp_idx.trail.size());
	$$("#csp-filter-search span").first().update(csp_idx.cur.size());
	$$("#csp-filter-regexp span").first().update(csp_idx.cur.size());
}

function csp_filter_result(elem, set) {
	$$("a.csp-filter").each(function(e) { e.removeClassName('current')});
	$(elem).addClassName('current');
	$('s_original').clear();
	$('s_translation').clear();
	$('csp-filter-search').up().hide();
	$('csp-filter-regexp').up().hide();
//$	csp_idx.cur = set;
	csp_idx.cur = csp_idx.ltd.intersect(set);
	csp_searchbase = csp_idx.cur;
	csp_change_pagenum(1);
}

function csp_search_key(elem, expr) {
	var term = $(elem).value;
	var ignore_case = $('ignorecase_key').checked;
	var is_expr = (typeof(expr) == "object");
	if (is_expr) { 
		term = expr; ignore_case = false; 
		$('s_original').clear();
	}
	else { 
		if (ignore_case) term = term.toLowerCase(); 
	}
	$('s_translation').clear();
	$$("a.csp-filter").each(function(e) { e.removeClassName('current')});
	csp_idx.cur = [];
	try{
		for (i=0; i<csp_searchbase.size(); i++) {
			if (Object.isArray(csp_pofile[csp_searchbase[i]].key)) {
				if (csp_pofile[csp_searchbase[i]].key.find(function(s){ return (ignore_case ? s.toLowerCase().include(term) : s.match(term)); })) csp_idx.cur.push(csp_searchbase[i]);			
			}
			else{
				if ( (ignore_case ? csp_pofile[csp_searchbase[i]].key.toLowerCase().include(term) : csp_pofile[csp_searchbase[i]].key.match(term) ) ) csp_idx.cur.push(csp_searchbase[i]);
			}
		}
	}catch(e) {
		//in case of half ready typed regexp catch it silently
		csp_idx.cur = csp_idx.total;
	}
	$('csp-filter-search').up().hide();
	$('csp-filter-regexp').up().hide();
	if (term) {
		if (is_expr) $('csp-filter-regexp').up().show();
		else $('csp-filter-search').up().show();
		csp_change_pagenum(1);
	}
	else {
		csp_filter_result('csp-filter-all', csp_idx.total);
	}
}

function csp_search_val(elem, expr) {
	var term = $(elem).value;
	var ignore_case = $('ignorecase_val').checked;
	var is_expr = (typeof(expr) == "object");
	if (is_expr) { 
		term = expr; ignore_case = false; 
		$('s_translation').clear();
	}
	else { 
		if (ignore_case) term = term.toLowerCase(); 
	}
	$('s_original').clear();
	$$("a.csp-filter").each(function(e) { e.removeClassName('current')});
	csp_idx.cur = [];
	try{
		for (i=0; i<csp_searchbase.size(); i++) {
			if (Object.isArray(csp_pofile[csp_searchbase[i]].val)) {
				if (csp_pofile[csp_searchbase[i]].val.find(function(s){ return (ignore_case ? s.toLowerCase().include(term) : s.match(term)); })) csp_idx.cur.push(csp_searchbase[i]);
			}
			else{
				if ( (ignore_case ? csp_pofile[csp_searchbase[i]].val.toLowerCase().include(term) : csp_pofile[csp_searchbase[i]].val.match(term) ) ) csp_idx.cur.push(csp_searchbase[i]);
			}
		}
	}catch(e) {
		//in case of half ready typed regexp catch it silently
		csp_idx.cur = csp_idx.total;
	}
	$('csp-filter-search').up().hide();
	$('csp-filter-regexp').up().hide();
	if (term) {
		if (is_expr) $('csp-filter-regexp').up().show();
		else $('csp-filter-search').up().show();
		csp_change_pagenum(1);
	}
	else {
		csp_filter_result('csp-filter-all', csp_idx.total);
	}
}

function csp_search_result(elem) {
	window.clearTimeout(csp_search_timer);
	if ($(elem).id == "s_original") {
		csp_search_timer = this.csp_search_key.delay(csp_search_interval, elem);
	}else{
		csp_search_timer = this.csp_search_val.delay(csp_search_interval, elem);
	}
}

function csp_exec_expression(elem) {
	var s = $("csp-dialog-expression").value;
	var t = /^\/(.*)\/([gi]*)/;
	var a = t.exec(s);
	var r = (a != null ? RegExp(a[1], a[2]) : RegExp(s, ''));
	if (elem == "s_original") {
		csp_search_key(elem, r);
	}else{
		csp_search_val(elem, r);
	}
	csp_cancel_dialog();
}

function csp_search_regexp(elem) {
	$(elem).blur();
	$('csp-dialog-caption').update("<?php _e('Extended Expression Search',CSP_PO_TEXTDOMAIN); ?>");
	$("csp-dialog-body").update(
		"<div><strong><?php _e('Expression:',CSP_PO_TEXTDOMAIN); ?></strong></div>"+
		"<input type=\"text\" id=\"csp-dialog-expression\" style=\"width:98%;font-size:11px;line-height:normal;\" value=\"\"\>"+		
		"<div style=\"margin-top:10px; color:#888;\"><strong><?php _e('Examples: <small>Please refer to official Perl regular expression descriptions</small>',CSP_PO_TEXTDOMAIN); ?></strong></div>"+
		'<div style="height: 215px; overflow:scroll;">'+
		<?php require('includes/js-help-perlreg.php'); ?>
		'</div>'+
		"<p style=\"margin:5px 0 0 0;text-align:center; padding-top: 5px;border-top: solid 1px #aaa;\">"+
		"<input class=\"button\" type=\"submit\" onclick=\"return csp_exec_expression('"+elem+"');\" value=\"  <?php echo _e('Search', CSP_PO_TEXTDOMAIN); ?>  \"/>"+
		"</p>"
	).setStyle({'padding' : '10px'});		
	tb_show(null,"#TB_inline?height=385&width=600&inlineId=csp-dialog-container&modal=true",false);	
	$("csp-dialog-expression").focus();
}

function csp_translate_google(elem, source, dest) {
	$(elem).blur();
	$(elem).down().show();
	//resulting {"responseData": {"translatedText":"Kann nicht öffnen zu schreiben!"}, "responseDetails": null, "responseStatus": 200}
	//TODO: can't handle google errors by own error dialog, because Thickbox is not multi instance ready (modal over modal) !!!
	new Ajax.Request('<?php echo CSP_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{
			parameters: {
				action: 'csp_po_translate_by_google',
				msgid: $(source).value,
				destlang: csp_destlang
			},
			onSuccess: function(transport) {
				if (transport.responseJSON.responseStatus == 200 && !transport.responseJSON.responseData.translatedText.empty()) {
					$(dest).value = transport.responseJSON.responseData.translatedText;
				}else{
					alert(transport.responseJSON.responseDetails);
				}
				$(elem).down().hide();
			},
			onFailure: function(transport) {
				$(elem).down().hide();
				alert(transport.responseText); 
			}
		}
	);
}

function csp_save_translation(elem, isplural, additional_action){
	$(elem).blur();
	
	msgid = $('csp-dialog-msgid').value;
	msgstr = '';
	
	glue = (Prototype.Browser.Opera ? '\1' : '\0'); //opera bug: can't send embedded 0 in strings!
	
	if (isplural) {
		msgid = [$('csp-dialog-msgid').value, $('csp-dialog-msgid-plural').value].join(glue);
		msgstr = [];
		if (csp_num_plurals == 1){
			msgstr = $('csp-dialog-msgstr-0').value;
		}
		else {
			for (pl=0;pl<csp_num_plurals; pl++) {
				msgstr.push($('csp-dialog-msgstr-'+pl).value);
			}
			msgstr = msgstr.join(glue);
		}
	}
	else{
		msgstr = $('csp-dialog-msgstr').value;
	}
	idx = parseInt($('csp-dialog-msg-idx').value);
	if (additional_action != 'close') {
		$('csp-dialog-body').hide();
		$('csp-dialog-saving').show();
	}
	//add the context in front of again
	if (csp_pofile[idx].ctx) msgid = csp_pofile[idx].ctx+ String.fromCharCode(4) + msgid;
	new Ajax.Request('<?php echo CSP_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'csp_po_save_catalog_entry',
				path: csp_path,
				file: csp_file,
				isplural: isplural,
				msgid: msgid,
				msgstr: msgstr,
				msgidx: idx
			},
			onSuccess: function(transport) {
				if (isplural && (csp_num_plurals != 1)) {
					csp_pofile[idx].val = msgstr.split(glue);
				}
				else{
					csp_pofile[idx].val = msgstr;
				}
				//TODO: check also erasing fields !!!!
				if (!msgstr.empty() && (csp_idx.open.indexOf(idx) != -1)) { 
					csp_idx.open = csp_idx.open.without(idx); 
//					csp_idx.cur = csp_idx.cur.without(idx); //TODO: only allowed if this is not total !!!
				}else if (msgstr.empty() && (csp_idx.open.indexOf(idx) == -1)) { 
					csp_idx.open.push(idx); 
				}
				csp_change_pagenum(csp_pagenum);
				if (additional_action != 'close') {
					var lin_idx = csp_idx.cur.indexOf(idx);
					if (additional_action == 'prev') {
						lin_idx--; 
					}
					if (additional_action == 'next') {
						lin_idx++; 
					}					
					if (Math.floor(lin_idx / csp_pagesize) != csp_pagenum -1) {
						csp_change_pagenum(Math.floor(lin_idx / csp_pagesize) + 1);
					}
					$('csp-dialog-saving').hide();
					$('csp-dialog-body').show();
					csp_edit_catalog($$("#msg-row-"+csp_idx.cur[lin_idx]+" a.tr-edit-link")[0]);
				}
				else {
					csp_cancel_dialog();
				}
			},
			onFailure: function(transport) {
				$('csp-dialog-saving').hide();
				$('csp-dialog-body').show();
				//opera bug: Opera has in case of error no valid responseText (always empty), even if server sends it! Ensure status text instead (dirty fallback)
				csp_show_error( (Prototype.Browser.Opera ? transport.statusText : transport.responseText));
			}
		}
	); 	
	return false;
}

function csp_suppress_enter(event) {
	if(event.keyCode == Event.KEY_RETURN) Event.stop(event);
}

function csp_copy_catalog(elem) {
	elem = $(elem);
	elem.blur();
	var msg_idx = parseInt(elem.up().up().id.replace('msg-row-',''));
	msgid = csp_pofile[msg_idx].key;
	msgstr = csp_pofile[msg_idx].key;
	if(Object.isArray(csp_pofile[msg_idx].key)) {
		msgid = csp_pofile[msg_idx].key.join("\0");
		if (csp_num_plurals == 1) {
			msgstr = csp_pofile[msg_idx].key[0];
		}
		else{
			msgstr = msgid;
		}
	}
	new Ajax.Request('<?php echo CSP_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'csp_po_save_catalog_entry',
				path: csp_path,
				file: csp_file,
				isplural: Object.isArray(csp_pofile[msg_idx].key),
				msgid: msgid,
				msgstr: msgstr,
				msgidx: msg_idx
			},
			onSuccess: function(transport) {
				idx = msg_idx;
				if (Object.isArray(csp_pofile[msg_idx].key) && (csp_num_plurals != 1)) {
					csp_pofile[idx].val = msgstr.split("\0");
				}
				else{
					csp_pofile[idx].val = msgstr;
				}
				//TODO: check also erasing fields !!!!
				if (!msgstr.empty() && (csp_idx.open.indexOf(idx) != -1)) { 
					csp_idx.open = csp_idx.open.without(idx); 
				}
				csp_change_pagenum(csp_pagenum);
			},
			onFailure: function(transport) {
				csp_show_error(transport.responseText);
			}
		}
	); 	
	return false;	
}

function csp_edit_catalog(elem) {
	elem = $(elem);
	elem.blur();
	elem.up().up().addClassName('highlight-editing');
	var msg_idx = parseInt(elem.up().up().id.replace('msg-row-',''));
	$('csp-dialog-caption').update("<?php _e('Edit Catalog Entry',CSP_PO_TEXTDOMAIN); ?>");
	if (Object.isArray(csp_pofile[msg_idx].key)) {
		trans = '';
		for (pl=0;pl<csp_num_plurals; pl++) {
			if (!csp_destlang.empty()) {
				switch(pl){
					case 0:
						trans += "<div style=\"margin-top:10px;height:20px;\"><strong class=\"alignleft\"><?php _e('Plural Index Result =',CSP_PO_TEXTDOMAIN); ?> "+pl+"</strong><a class=\"alignright clickable google\" onclick=\"csp_translate_google(this, 'csp-dialog-msgid', 'csp-dialog-msgstr-0');\"><img style=\"display:none;\" src=\"<?php echo CSP_PO_BASE_URL; ?>/images/loading-small.gif\" />&nbsp;<?php _e('translate with Google API',CSP_PO_TEXTDOMAIN); ?></a><br class=\"clear\" /></div>";
					break;
					case 1:
						trans += "<div style=\"margin-top:10px;height:20px;\"><strong class=\"alignleft\"><?php _e('Plural Index Result =',CSP_PO_TEXTDOMAIN); ?> "+pl+"</strong><a class=\"alignright clickable google\" onclick=\"csp_translate_google(this, 'csp-dialog-msgid-plural', 'csp-dialog-msgstr-1');\"><img style=\"display:none;\" src=\"<?php echo CSP_PO_BASE_URL; ?>/images/loading-small.gif\" />&nbsp;<?php _e('translate with Google API',CSP_PO_TEXTDOMAIN); ?></a><br class=\"clear\" /></div>";
					break;
					default:
						trans += "<div style=\"margin-top:10px;height:20px;\"><strong><?php _e('Plural Index Result =',CSP_PO_TEXTDOMAIN); ?> "+pl+"</strong></div>";
					break;
				}
			}
			else{
				trans += "<div style=\"margin-top:10px;\"><strong><?php _e('Plural Index Result =',CSP_PO_TEXTDOMAIN); ?> "+pl+"</strong></div>";
			}
			if (csp_num_plurals == 1) {
				trans += "<textarea id=\"csp-dialog-msgstr-"+pl+"\" class=\"csp-area-multi\" cols=\"50\" rows=\"1\" style=\"width:98%;font-size:11px;line-height:normal;\">"+csp_pofile[msg_idx].val.escapeHTML()+"</textarea>";
			}
			else{
				trans += "<textarea id=\"csp-dialog-msgstr-"+pl+"\" class=\"csp-area-multi\" cols=\"50\" rows=\"1\" style=\"width:98%;font-size:11px;line-height:normal;\">"+csp_pofile[msg_idx].val[pl].escapeHTML()+"</textarea>";
			}
		}
	
		$("csp-dialog-body").update(	
			"<small style=\"display:block;text-align:right;\"><b><?php _e('Access Keys:',CSP_PO_TEXTDOMAIN); ?></b> <em>ALT</em> + <em>Shift</em> + [<b>p</b>]revious | [<b>s</b>]ave | [<b>n</b>]next</small>"+
			"<div><strong><?php _e('Singular:',CSP_PO_TEXTDOMAIN); ?></strong></div>"+
			"<textarea id=\"csp-dialog-msgid\" class=\"csp-area-multi\" cols=\"50\" rows=\"1\" style=\"width:98%;font-size:11px;line-height:normal;\" readonly=\"readonly\">"+csp_pofile[msg_idx].key[0].escapeHTML()+"</textarea>"+
			"<div style=\"margin-top:10px;\"><strong><?php _e('Plural:',CSP_PO_TEXTDOMAIN); ?></strong></div>"+
			"<textarea id=\"csp-dialog-msgid-plural\" class=\"csp-area-multi\" cols=\"50\" rows=\"1\" style=\"width:98%;font-size:11px;line-height:normal;\" readonly=\"readonly\">"+csp_pofile[msg_idx].key[1].escapeHTML()+"</textarea>"+
			"<div style=\"font-weight:bold;padding-top: 5px;border-bottom: dotted 1px #aaa;\"><?php _e("Plural Index Calculation:",CSP_PO_TEXTDOMAIN);?>&nbsp;&nbsp;&nbsp;<span style=\"color:#D54E21;\">"+csp_func_plurals+"</span></div>"+
			trans+
			"<p style=\"margin:5px 0 0 0;text-align:center; padding-top: 5px;border-top: solid 1px #aaa;\">"+
			"<input class=\"button\""+(csp_idx.cur.indexOf(msg_idx) > 0 ? "" : " disabled=\"disabled\"")+" type=\"submit\" onclick=\"return csp_save_translation(this, true, 'prev');\" value=\"  <?php echo _e('« Save & Previous',CSP_PO_TEXTDOMAIN); ?>  \" accesskey=\"p\"/>&nbsp;&nbsp;&nbsp;&nbsp;"+
			"<input class=\"button\" type=\"submit\" onclick=\"return csp_save_translation(this, true, 'close');\" value=\"  <?php echo _e('Save',CSP_PO_TEXTDOMAIN); ?>  \" accesskey=\"s\"/>"+
			"&nbsp;&nbsp;&nbsp;&nbsp;<input class=\"button\""+(csp_idx.cur.indexOf(msg_idx)+1 < csp_idx.cur.size() ? "" : " disabled=\"disabled\"")+" type=\"submit\" onclick=\"return csp_save_translation(this, true, 'next');\" value=\"  <?php echo _e('Save & Next »',CSP_PO_TEXTDOMAIN); ?>  \" accesskey=\"n\"/>"+
			"</p><input id=\"csp-dialog-msg-idx\" type=\"hidden\" value=\""+msg_idx+"\" />"
		).setStyle({'padding' : '10px'});		
	}else{
		$("csp-dialog-body").update(	
			"<small style=\"display:block;text-align:right;\"><b><?php _e('Access Keys:',CSP_PO_TEXTDOMAIN); ?></b> <em>ALT</em> + <em>Shift</em> + [p]revious | [s]ave | [n]next</small>"+
			"<div><strong><?php _e('Original:',CSP_PO_TEXTDOMAIN); ?></strong></div>"+
			"<textarea id=\"csp-dialog-msgid\" class=\"csp-area-single\" cols=\"50\" rows=\"7\" style=\"width:98%;font-size:11px;line-height:normal;\" readonly=\"readonly\">"+csp_pofile[msg_idx].key.escapeHTML()+"</textarea>"
			+ (csp_destlang.empty() ? 
			"<div style=\"margin-top:10px;\"><strong><?php _e('Translation:',CSP_PO_TEXTDOMAIN); ?></strong></div>"
			:
			 "<div style=\"margin-top:10px;height:20px;\"><strong class=\"alignleft\"><?php _e('Translation:',CSP_PO_TEXTDOMAIN); ?></strong><a class=\"alignright clickable google\" onclick=\"csp_translate_google(this, 'csp-dialog-msgid', 'csp-dialog-msgstr');\"><img style=\"display:none;\" align=\"left\" src=\"<?php echo CSP_PO_BASE_URL; ?>/images/loading-small.gif\" />&nbsp;<?php _e('translate with Google API',CSP_PO_TEXTDOMAIN); ?></a><br class=\"clear\" /></div>"
			 ) +
			"<textarea id=\"csp-dialog-msgstr\" class=\"csp-area-single\" cols=\"50\" rows=\"7\" style=\"width:98%;font-size:11px;line-height:normal;\">"+csp_pofile[msg_idx].val.escapeHTML()+"</textarea>"+
			"<p style=\"margin:5px 0 0 0;text-align:center; padding-top: 5px;border-top: solid 1px #aaa;\">"+
			"<input class=\"button\""+(csp_idx.cur.indexOf(msg_idx) > 0 ? "" : " disabled=\"disabled\"")+" type=\"submit\" onclick=\"return csp_save_translation(this, false, 'prev');\" value=\"  <?php echo _e('« Save & Previous',CSP_PO_TEXTDOMAIN); ?>  \" accesskey=\"p\"/>&nbsp;&nbsp;&nbsp;&nbsp;"+
			"<input class=\"button\" type=\"submit\" onclick=\"return csp_save_translation(this, false, 'close');\" value=\"  <?php echo _e('Save',CSP_PO_TEXTDOMAIN); ?>  \" accesskey=\"s\"/>"+
			"&nbsp;&nbsp;&nbsp;&nbsp;<input class=\"button\""+(csp_idx.cur.indexOf(msg_idx)+1 < csp_idx.cur.size() ? "" : " disabled=\"disabled\"")+" type=\"submit\" onclick=\"return csp_save_translation(this, false, 'next');\" value=\"  <?php echo _e('Save & Next »',CSP_PO_TEXTDOMAIN); ?>  \" accesskey=\"n\"/>"+
			"</p><input id=\"csp-dialog-msg-idx\" type=\"hidden\" value=\""+msg_idx+"\" />"
		).setStyle({'padding' : '10px'});
	}
	tb_show(null,"#TB_inline?height="+(csp_num_plurals > 2 && Object.isArray(csp_pofile[msg_idx].key) ? '520' : '385')+"&width=600&inlineId=csp-dialog-container&modal=true",false);
	$$('#csp-dialog-body textarea').each(function(e) {
		e.observe('keydown', csp_suppress_enter);
		e.observe('keypress', csp_suppress_enter);
		e.observe('keyup', csp_suppress_enter);
	});
	$("csp-dialog-msgstr", "csp-dialog-msgstr-0").each(function(e) {
		csp_focus_editor.defer(e);
	});
	return false;
}

function csp_focus_editor(e) {
	try{e.focus();}catch(a){};
}

function csp_view_phpfile(elem, phpfile, idx) {
	elem.blur();	
	glue = (Prototype.Browser.Opera ? '\1' : '\0'); //opera bug: can't send embedded 0 in strings!
	msgid = csp_pofile[idx].key;
	if (Object.isArray(msgid)) {
		msgid = msgid.join(glue);
	}
	new Ajax.Request('<?php echo CSP_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'csp_po_dlg_show_source',
				path: csp_path,
				file: phpfile,
				msgid: msgid
			},
			onSuccess: function(transport) {
				//own <iframe> creation, because of POST content filling into inline thickbox
				var iframe = null;
				$('csp-dialog-caption').update("<?php _e('File:', CSP_PO_TEXTDOMAIN); ?> "+phpfile.split(':')[0]);
				$('csp-dialog-body').insert(iframe = new Element('iframe', {'class' : 'csp-dialog-iframe', 'frameBorder' : '0'}).writeAttribute({'width' : '100%', 'height' : '570px', 'margin': '0'})).setStyle({'padding' : '0px'});
				tb_show(null,"#TB_inline?height=600&width=600&inlineId=csp-dialog-container&modal=true",false);
				iframe.contentWindow.document.open();
				iframe.contentWindow.document.write(transport.responseText);
				iframe.contentWindow.document.close();
			}
		}
	); 
	return false;	
}

function csp_generate_mofile(elem) {
	elem.blur();
	new Ajax.Request('<?php echo CSP_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'csp_po_generate_mo_file',
				pofile: csp_path + csp_file,
				textdomain: $('csp-mo-textdomain-val').value
			},
			onSuccess: function(transport) {
				new Effect.Highlight($('catalog-last-saved').update(transport.responseJSON.filetime), { startcolor: '#25FF00', endcolor: '#FFFFCF' });
			},
			onFailure: function(transport) {
				csp_show_error(transport.responseText);
			}
		}
	); 
	return false;
}

function csp_create_languange_path(elem, path) {
	elem.blur();
	new Ajax.Request('<?php echo CSP_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'csp_po_create_language_path',
				path: path
			},
			onSuccess: function(transport) {
				window.location.reload();
			},
			onFailure: function(transport) {
				csp_show_error(transport.responseText);
			}
		}
	); 
	return false;	
}

function csp_create_pot_indicator(elem, potfile) {
	elem.blur();
	new Ajax.Request('<?php echo CSP_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
		{  
			parameters: {
				action: 'csp_po_create_pot_indicator',
				potfile: potfile
			},
			onSuccess: function(transport) {
				window.location.reload();
			},
			onFailure: function(transport) {
				csp_show_error(transport.responseText);
			}
		}
	); 
	return false;	
}

jQuery(document).ready(function() { 
	jQuery('#enable_low_memory_mode').click(function(e) {
		jQuery('#enable_low_memory_mode_indicator').toggle();
		mode = jQuery(e.target).is(':checked');
		new Ajax.Request('<?php echo CSP_PO_ADMIN_URL.'/admin-ajax.php' ?>', 
			{  
				parameters: {
					action: 'csp_po_change_low_memory_mode',
					mode: mode
				},
				onSuccess: function(transport) {
					jQuery('#enable_low_memory_mode_indicator').toggle();
				}
			});		
		csp_chuck_size = (jQuery(e.target).is(':checked') ? 1 : 20);
	});
});

/* TODO: implement context sensitive help 
function csp_process_online_help(event) {
	if (event) {
		if (event.keyCode == 112) {
			Event.stop(event);
			//TODO: launch appropriated help ajax here for none IE
			return false;
		}
	}else{
		//TODO: launch appropriated help ajax here for IE
		return false;
	}
	return true;
}

function csp_term_help_key(event) {
	if(event.keyCode == 112) {
		Event.stop(event);
		return false;
	}
	return true;
}

if (Prototype.Browser.IE) {
	document.onhelp = csp_process_online_help;
}else{
	document.observe("keydown", csp_process_online_help);
}
document.observe("keyup", csp_term_help_key);
document.observe("keypress", csp_term_help_key);
*/

/* ]]> */
</script>
<?php	
}

//////////////////////////////////////////////////////////////////////////////////////////
//	stylesheet handling during direct plugin file call
//////////////////////////////////////////////////////////////////////////////////////////
if (isset($_GET['css']) && $_GET['css'] == 'default') {
	header("Content-Type: text/css");
?>
/* general usage */
.clickable { cursor: pointer; }
.regexp { display:block;width:16px;height:16px;background-image:url(images/regexp.gif); }
.regexp:hover { background-image:url(images/regexp-hover.gif); }
.pot-folder { padding-left: 20px; background: url(images/folder.gif) no-repeat 0 2px; }
.csp-filetype-po, .csp-filetype-po-r, .csp-filetype-po-rw, 
.csp-filetype-mo, .csp-filetype-mo-r, .csp-filetype-mo-rw { cursor: default; display:block; float: left; margin-top: 2px; height: 12px; width: 18px;}
.csp-filetype-po { background: url(images/po.gif) no-repeat 0 0; }
.csp-filetype-po-r { cursor: pointer !important; background: url(images/po.gif) no-repeat -18px 0; }
.csp-filetype-po-rw { background: url(images/po.gif) no-repeat -36px 0; }
.csp-filetype-mo { margin-left: 5px; background: url(images/mo.gif) no-repeat 0 0; } 
.csp-filetype-mo-r { cursor: pointer !important; margin-left: 5px; background: url(images/mo.gif) no-repeat -18px 0; }
.csp-filetype-mo-rw { margin-left: 5px; background: url(images/mo.gif) no-repeat -36px 0; }

/* overview page styles */
.csp-active { background-color: #E7F7D3; }
*:first-child + html tr.csp-active td{ background-color: #E7F7D3; }
.csp-type-name { 	margin: 0pt 10px 1em 0pt; }
.csp-type-info {}
table.csp-type-info td {	padding:0; border-bottom: 0px; }
table.csp-type-info td.csp-info-value { padding:0 5px; }
table.mo-list td { padding:3px 0 3px 5px;border-bottom: 0px !important; }
table.mo-list tr.mo-list-head td, table.mo-list tr.mo-list-desc td { border-bottom: 1px solid #aaa !important; }
.csp-ta-right { text-align: right; }
tr.mo-file td { border-bottom: 1px solid transparent !important; }
tr.mo-file:hover td { border-bottom: 1px dashed #666 !important; }

/* new ajax dialogs */
#TB_ajaxContent { background-color: #EAF3FA !important; width: auto !important; overflow: hidden !important; }
#TB_ajaxContent.TB_modal { padding: 0px; }
#csp-dialog-header { background-color:#222 !important; margin:0; padding:0px 2px; color:#D7D7D7; height:20px; font-size:13px; }
#csp-dialog-header img { width: 16px; height:16px; padding-top: 2px;}
#csp-dialog-caption { padding: 1px 0 0 5px; }
#TB_window a.google:hover { color: #D54E21 !important; }

/* catalog editor styles */
#catalog-body a { cursor: pointer; }
#catalog-body td { overflow: hidden; }
#catalog-body tr.odd { background-color: #eee; }
*:first-child + html #catalog-body tr.odd td { background-color: #eee; }
#catalog-body tr.highlight-editing { background-color: #FFF36F !important; }
*:first-child + html #catalog-body tr.highlight-editing td { background-color: #FFF36F !important; }
#catalog-body .csp-pl-form { padding-top: 5px; font-weight: bold; color:#aaa; display:block; border-bottom: 1px dotted #ccc; }
#csp-filter-search, #csp-filter-regexp { font-weight: bold; color: #FF0000; }
.page-numbers { cursor: pointer; }
#php-files a, .subsubsub a.csp-filter { cursor: pointer; }
#php-files { padding: 3px; border: 1px solid #ccc; overflow:auto; height: 100px;}

/* file and comment tooltip */
.csp-msg-tip span { display: none; }
.csp-msg-tip:hover span { display:block; position: absolute; z-index:50; margin-top: -5px; padding: 3px; background-color:#FFF79F; border: solid 1px #333; color:black; }
*:first-child + html .csp-msg-tip span { margin: 10px 0 0 -26px !important; }
.csp-msg-tip:hover span strong { margin-bottom: 3px; border-bottom: dotted 1px #333; display:block; cursor: default; }
.csp-msg-tip:hover span em { font-style: normal; color: #328AB2; }
.csp-msg-tip:hover span em:hover { color: #D54E21; }

#po-hdr { border-top: 1px dotted #ccc;}
<?php if ($_GET['dir'] == 'rtl') : ?>
.po-header-toggle { margin: 10px 0 0 0; padding-right:20px; cursor: pointer; background: transparent url('images/expand.gif') right 3px no-repeat; }
.po-header-collapse { background: transparent url('images/collapse.gif') right 3px no-repeat; margin-bottom: 3px;}
<?php else : ?>
.po-header-toggle { margin: 10px 0 0 0; padding-left:20px; cursor: pointer; background: transparent url('images/expand.gif') 0 3px no-repeat; }
.po-header-collapse { background: transparent url('images/collapse.gif') 0 3px no-repeat; margin-bottom: 3px;}
<?php endif; ?>
.po-hdr-key { font-family: monospace; font-size: 11px; font-weight:bold; }
.po-hdr-val { font-family: monospace; font-size: 11px; padding-left: 10px; }

.csp-area-single { height: 110px; }
.csp-area-multi { height: 24px; }

<?php
}

?>