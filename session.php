<?php
/**
 * Resession - Session Manager
 *
 * Manages all session data and interactions
 * 
 * @author 		Miles Johnson - www.milesj.me
 * @copyright	Copyright 2006-2009, Miles Johnson, Inc.
 * @license 	http://www.opensource.org/licenses/mit-license.php - Licensed under The MIT License
 * @link		www.milesj.me/resources/script/session-manager
 */

class Session {

	/**
	 * Current version: www.milesj.me/resources/logs/session-manager
	 *
	 * @access public
	 * @var string
	 */
	public $version = '1.9';

	/**
	 * Dis, dis, disabled!
	 *
	 * @access private
	 * @return void
	 */
	private function __construct() { }
	
	/**
	 * Updates the configuration settings.
	 *
	 * @access public
	 * @param string $key
	 * @param mixed $key
	 * @return boolean 
	 * @static
	 */
	public static function config($key, $value = '') {
		Resession::getInstance()->config($key, $value);
	}

	/**
	 * Adds a value to the session.
	 *
	 * @access public
	 * @param string $key
	 * @param string $value
	 * @return void
	 * @static
	 */
	public static function set($key, $value) { 
		Resession::getInstance()->set($key, $value);
	}
	
	/**
	 * Get a value from the session.
	 *
	 * @access public
	 * @param string $key
	 * @return mixed
	 * @static
	 */
	public static function get($key) {
		return Resession::getInstance()->get($key);
	}
	
	/**
	 * Removes an array of values or a single value.
	 *
	 * @access public
	 * @param array/string $keys
	 * @return void
	 * @static
	 */
	public static function clear($keys) {
		Resession::getInstance()->clear($keys);
	}
	
	/**
	 * Destroys a session.
	 *
	 * @access public
	 * @return void
	 * @static
	 */
	public static function destroy() {
		Resession::getInstance()->destroy();
	}
	
	/**
	 * Redirects the user to another page.
	 *
	 * @access public
	 * @param string $path
	 * @return void
	 * @static
	 */
	public static function redirect($path) {
		Resession::getInstance()->redirect($path);
	}
	
	/**
	 * Return the session id of the user.
	 *
	 * @access public
	 * @return string
	 * @static
	 */
	public static function getId() {
		return Resession::getInstance()->getId();
	}
	
	/**
	 * Regenerates and sets the new session id.
	 *
	 * @access public
	 * @param boolean $delete
	 * @return int
	 * @static
	 */
	public static function regenerate($delete = true) {
		return Resession::getInstance()->regenerate($delete);
	}
	
	/**
	 * Hardcode sets a cookie.
	 *
	 * @access public
	 * @param string $name
	 * @param string $value
	 * @param string $maxAge
	 * @param string $path
	 * @param string $domain
	 * @param boolean $secure
	 * @param boolean $HTTPOnly
	 * @return void
	 * @static
	 */
	public static function setCookie($name, $value = '', $maxAge = 0, $path = '', $domain = '', $secure = false, $HTTPOnly = false) {
		$cookie = rawurlencode($name) .'='. rawurlencode($value);
		
		if (!empty($maxAge))	$cookie .= '; Max-Age=' . $maxAge;
		if (!empty($path)) 		$cookie .= '; path=' . $path;
		if (!empty($domain))	$cookie .= '; domain=' . $domain;
		if (!empty($secure))	$cookie .= '; secure';
		if (!empty($HTTPOnly)) 	$cookie .= '; HttpOnly';
		
  		header('Set-Cookie: '. $cookie, false);
	}
	
}

class Resession {
	
	/**
	 * Holds the user agent / browser of the user.
	 *
	 * @access private
	 * @var string
	 */
	private $__agent;
	
	/**
	 * Configuration.
	 *
	 * @access private
	 * @var array
	 */
	private $__config = array(
		'security' 	=> 'medium',
		'name'		=> 'RESESSION',
		'cookies'	=> true
	);
	
	/**
	 * Holds the session id of the user.
	 *
	 * @access private
	 * @var string
	 */
	private $__id;

	/**
	 * Holds the session instance.
	 *
	 * @access private
	 * @var instance|object
	 * @static
	 */
	private static $__instance;
	
	/**
	 * Starts the session and returns the session id.
	 *
	 * @access private
	 * @return void
	 */
	private function __construct() {
		$uri = (isset($_SERVER['SCRIPT_URI'])) ? $_SERVER['SCRIPT_URI'] : '';
		if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (mb_strpos($uri, 'https://') !== false)) {
			ini_set('session.cookie_secure', 1);
		}
		
		ini_set('session.name', $this->__config['name']);
		ini_set('session.use_trans_sid', 0);
		ini_set('url_rewriter.tags', '');
		
		if ($this->__config['cookies'] === true) {
			ini_set('session.use_cookies', true);
			ini_set('session.use_only_cookies', true);
			ini_set('session.cookie_domain', $_SERVER['HTTP_HOST']);
		}
		
		switch ($this->__config['security']) {
			case 'high':
			case 'medium':
			default:
				$timeout = 180;
				if ($this->__config['security'] == 'high') {
					$lifetime = $timeout * 5;
				} else {
					$lifetime = $timeout * 15;
				}
				
				ini_set('session.referer_check', $_SERVER['HTTP_HOST']);
				ini_set('session.cookie_lifetime', $lifetime);
				ini_set('session.cookie_httponly', true);
			break;
			case 'low':
				ini_set('session.cookie_lifetime', 0);
			break;
		}
		
		session_write_close();
		
		if (headers_sent()) {
			if (empty($_SESSION)) {
				$_SESSION = array();
			}
			return false;
		} else {
			session_start();
		}
		
		$this->__id = session_id();
		$this->__agent = md5($_SERVER['HTTP_USER_AGENT']);
		
		return $this->__id;
	}
	
	/**
	 * Returns the current session instance.
	 *
	 * @access public
	 * @return instance
	 * @static
	 */
	public static function getInstance() {
		if (!isset(self::$__instance)){
			self::$__instance = new Resession();
		}
		
		$resession = self::$__instance;
		
		// Check user agent
		if ($resession->__config['security'] == 'high' && $resession->__agent != md5($_SERVER['HTTP_USER_AGENT'])) {
			$resession->destroy();
			$resession->regenerate(true);
		}
		
		return $resession;
	}
	
	/**
	 * Updates the configuration settings.
	 *
	 * @access public
	 * @param string $key
	 * @param mixed $key
	 * @return boolean
	 */
	public function config($key, $value = '') {
		if (is_array($key)) {
			foreach ($key as $k => $v) {
				self::config($k, $v);
			}
		} else {
			$this->__config[$key] = $value;
		}
		
		return true;
	}

	/**
	 * Adds a value to the session.
	 *
	 * @access public
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	public function set($key, $value) {
		if (mb_strpos($key, '.')) {
			$keys = explode('.', $key);
			
			if (!isset($_SESSION[$keys[0]])) {
				self::set($keys[0], array());
			}
			
			$_SESSION[$keys[0]][$keys[1]] = $value;
		} else {
			$_SESSION[$key] = $value;
		}
	}
	
	/**
	 * Get a value from the session.
	 *
	 * @access public
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		if (mb_strpos($key, '.')) {
			$keys = explode('.', $key);
			
			if (isset($_SESSION[$keys[0]][$keys[1]])) {
				return $_SESSION[$keys[0]][$keys[1]];
			} else {
				return null;
			}
		} else {
			if (isset($_SESSION[$key])) {
				return $_SESSION[$key];
			} else {
				return null;
			}
		}
	}
	
	/**
	 * Removes an array of values or a single value.
	 *
	 * @access public
	 * @param array/string $keys
	 * @return void
	 */
	public function clear($key) {
		if (mb_strpos($key, '.')) {
			$keys = explode('.', $key);
			unset($_SESSION[$keys[0]][$keys[1]]);
			
		} else if (is_array($key)){
			foreach ($key as $k) {
				self::clear($k);
			}
			
		} else {
			unset($_SESSION[$key]);
		}	
	}
	
	/**
	 * Destroys a session.
	 *
	 * @access public
	 * @return void
	 */
	public function destroy() {
		$_SESSION = array();
		$this->__id = null;
		
		if (isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time() - 42000, '/');
		}
		
		session_destroy();
	}
	
	/**
	 * Redirects the user to another page.
	 *
	 * @access public
	 * @param string $path
	 * @return void
	 */
	public function redirect($path) {
		header('Location: '. $path);
		exit();
	}
	
	/**
	 * Return the session id of the user.
	 *
	 * @access public
	 * @return int
	 */
	public function getId() {
		if ($this->__id) {
			return $this->__id;
		} else {
			return self::regenerate(true);
		}
	}
	
	/**
	 * Regenerates and sets the new session id.
	 *
	 * @access public
	 * @param boolean $delete
	 * @return int
	 */
	public function regenerate($delete) {
		session_regenerate_id($delete);
		$this->__id = session_id();
		return $this->__id;
	}
	
	/**
	 * Disabled clone from being used.
	 *
	 * @access private
	 * @return void
	 */
	private function __clone() {}
	
}
