<?php
/**
 * Resession - Manages and manipulates session data with built in security.
 * 
 * @author 		Miles Johnson - http://milesj.me
 * @copyright	Copyright 2006-2011, Miles Johnson, Inc.
 * @license 	http://opensource.org/licenses/mit-license.php - Licensed under The MIT License
 * @link		http://milesj.me/resources/script/session-manager
 */

class Resession {

	/**
	 * Security level constants.
	 */
	const SECURITY_LOW = 0;
	const SECURITY_MEDIUM = 1;
	const SECURITY_HIGH = 3;

	/**
	 * Current version: http://milesj.me/resources/logs/session-manager
	 *
	 * @access public
	 * @var string
	 */
	public $version = '2.0';

	/**
	 * Holds the user agent of the client.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_agent;

	/**
	 * Configuration.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_config = array(
		'name'		=> 'RESESSION',
		'cookies'	=> true,
		'security'	=> self::SECURITY_MEDIUM
	);

	/**
	 * Holds the session id of the client.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_id;

	/**
	 * Starts the session and set any security restrictions.
	 *
	 * @access private
	 * @param array $config
	 * @return void
	 */
	public function __construct(array $config = array()) {
		$this->_config = $config + $this->_config;

		if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on') {
			ini_set('session.cookie_secure', true);
		}

		ini_set('session.name', $this->_config['name']);
		ini_set('session.use_trans_sid', false);
		ini_set('url_rewriter.tags', '');

		if ($this->_config['cookies']) {
			ini_set('session.use_cookies', true);
			ini_set('session.use_only_cookies', true);
			ini_set('session.cookie_domain', $_SERVER['HTTP_HOST']);
		}

		switch ($this->_config['security']) {
			case self::SECURITY_HIGH:
			case self::SECURITY_MEDIUM:
			default:
				$timeout = 180;

				if ($this->_config['security'] == self::SECURITY_HIGH) {
					$lifetime = $timeout * 5;
				} else {
					$lifetime = $timeout * 15;
				}

				ini_set('session.referer_check', $_SERVER['HTTP_HOST']);
				ini_set('session.cookie_lifetime', $lifetime);
				ini_set('session.cookie_httponly', true);
			break;
			case self::SECURITY_LOW:
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

		$this->_id = session_id();
		$this->_agent = md5($_SERVER['HTTP_USER_AGENT']);
	}

	/**
	 * Removes an array of values or a single value.
	 *
	 * @access public
	 * @param array|string $keys
	 * @return this
	 */
	public function clear($key) {
		$this->validate();

		if (strpos($key, '.')) {
			$keys = explode('.', $key);
			unset($_SESSION[$keys[0]][$keys[1]]);

		} else if (is_array($key)){
			foreach ($key as $k) {
				$this->clear($k);
			}

		} else {
			unset($_SESSION[$key]);
		}

		return $this;
	}

	/**
	 * Destroys a session.
	 *
	 * @access public
	 * @return void
	 */
	public function destroy() {
		$_SESSION = array();
		$this->_id = null;

		if (isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time() - 42000, '/');
		}

		session_destroy();
	}

	/**
	 * Get a value from the session.
	 *
	 * @access public
	 * @param string $key
	 * @return mixed
	 */
	public function get($key = null) {
		$this->validate();

		if (empty($key)) {
			return $_SESSION;

		} else if (strpos($key, '.')) {
			$keys = explode('.', $key);

			if (isset($_SESSION[$keys[0]][$keys[1]])) {
				return $_SESSION[$keys[0]][$keys[1]];
			}
			
		} else if (isset($_SESSION[$key])) {
			return $_SESSION[$key];
		}

		return null;
	}

	/**
	 * Return the session id of the user.
	 *
	 * @access public
	 * @return int
	 */
	public function id() {
		$this->validate();

		if ($this->_id) {
			return $this->_id;
		}

		return $this->regenerate(true);
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

		$this->_id = session_id();

		return $this->_id;
	}

	/**
	 * Adds a value to the session.
	 *
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 * @return this
	 */
	public function set($key, $value) {
		$this->validate();

		if (strpos($key, '.')) {
			$keys = explode('.', $key);

			if (!isset($_SESSION[$keys[0]])) {
				$this->set($keys[0], array());
			}

			$_SESSION[$keys[0]][$keys[1]] = $value;
		} else {
			$_SESSION[$key] = $value;
		}

		return $this;
	}

	/**
	 * Validate that the current session is legitimate.
	 *
	 * @access public
	 * @return void
	 */
	public function validate() {
		if ($this->_config['security'] == self::SECURITY_HIGH && $this->_agent != md5($_SERVER['HTTP_USER_AGENT'])) {
			$this->destroy();
			$this->regenerate(true);
		}
	}

}
