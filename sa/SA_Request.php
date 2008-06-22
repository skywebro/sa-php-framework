<?php
/**
 * © 2008 Petre Trînculescu <petre@skyweb.ro>
 * @author Petre Trînculescu
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files
 * (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge,
 * publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE
 * FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * $Id$
 */

class SA_Request extends SA_Object {
	const REQUEST_METHOD_GET = 'GET';
	const REQUEST_METHOD_POST = 'POST';
	const REQUEST_AJAX = 'XMLHTTPREQUEST';

	protected $get = null;
	protected $post = null;
	protected $request = null;
	protected $cookies = null;
	protected $server = null;
	protected $env = null;

	public function __construct() {
		parent::__construct();
		$this->cookies = new ArrayObject($_COOKIE);
		$this->server = new ArrayObject($_SERVER);
		$this->env = new ArrayObject($_ENV);
		$this->post = new ArrayObject($_POST);
		$this->detectGetParameters();
		$this->get = new ArrayObject($_GET);
		$this->request = new ArrayObject($_REQUEST);
	}

	public function &g($key = null) {
		return is_null($key) ? $this->get : $this->get[$key];
	}

	public function &p($key = null) {
		return is_null($key) ? $this->post : $this->post[$key];
	}

	public function &r($key = null) {
		return is_null($key) ? $this->request : $this->request[$key];
	}

	public function &c($key = null) {
		return is_null($key) ? $this->cookies : $this->cookies[$key];
	}

	public function &s($key = null) {
		return is_null($key) ? $this->server : $this->server[$key];
	}

	public function &e($key = null) {
		return is_null($key) ? $this->env : $this->env[$key];
	}

	public function isGet() {
		return strcasecmp($this->s('REQUEST_METHOD'), self::REQUEST_METHOD_GET) == 0;
	}

	public function isPost() {
		return strcasecmp($this->s('REQUEST_METHOD'), self::REQUEST_METHOD_POST) == 0;
	}

	public function isAjax() {
		//this check works for jQuery style Ajax requests
		$ajaxHeader = $this->s('HTTP_X_REQUESTED_WITH');
		if (empty($ajaxHeader) && function_exists('apache_request_headers')) {
			$apacheHeaders = array_change_key_case(apache_request_headers(), CASE_LOWER);
			$ajaxHeader = $apacheHeaders['x-requested-with'];
		}
		return strcasecmp($ajaxHeader, self::REQUEST_AJAX) == 0;
	}

	public function detectGetParameters() {
		$pathInfo = rawurldecode(substr($pathInfoString = $this->s('PATH_INFO'), strpos($pathInfoString, '/') + 1));
		$pathInfoStack = $pathInfoArray = explode('/', preg_replace('/[^a-z0-9_\/]/i', '_', $pathInfo));
		$pageName = $partialPathInfo = null;

		function pageXPath($pathInfo, $type) {
			if (!is_array($pathInfo)) return null;
			$pathInfo = array_filter($pathInfo, create_function('$value', 'return trim($value) !== "";'));
			$xpath = array('//pages');
			if ($type == 'file') $fileName = array_pop($pathInfo);
			foreach($pathInfo as $value) {
				$xpath[] = "dir[@name='$value']";
			}
			if ($type == 'dir') {
				$xpath[] = "file[@name='" . SA_Application::DEFAULT_PAGE . ".php']";
			} elseif ($type == 'file') {
				$xpath[] = "file[@name='{$fileName}.php']";
			}

			return implode('/', $xpath);
		}

		$xPath = new DOMXPath(SA_Application::singleton()->getDOMPageMap());
		for($i = 0; $i < count($pathInfoArray); $i++) {
			if (strlen($partialPathInfo = implode('/', $pathInfoStack))) {
				if ((substr($partialPathInfo, -1) == '/') && $xPath->query(pageXPath($pathInfoStack, 'dir'))->length) {
					$pageName = $partialPathInfo . SA_Application::DEFAULT_PAGE;
				} elseif ($xPath->query(pageXPath($pathInfoStack, 'file'))->length) {
					$pageName = trim($partialPathInfo, "\t /");
				}
				if ($pageName) break;
			}
			array_pop($pathInfoStack);
		}
		$params = array_filter(explode('/', trim(substr($pathInfo, strlen($pageName)), "\t /")), create_function('$value', 'return trim($value) !== "";'));
		if (count($params) % 2) throw new SA_NoPage_Exception('Page not found');
		for($i = 0; $i < count($params); $i += 2) {
			$key = trim($params[$i]);
			if (empty($key)) continue;
			$value = trim($params[$i + 1]);
			if (strcasecmp($value, SA_Url::NULL) == 0) $value = null;
			elseif (is_array($possiblyEncoded = @unserialize(base64_decode($value))) || is_object($possiblyEncoded)) $value = $possiblyEncoded;
			else $value = str_replace(SA_Url::SLASH, '/', $value);
			$_REQUEST[$key] = $_GET[$key] = $value;
		}
		$_REQUEST[SA_Application::ACTIONS_VAR_NAME] = $_GET[SA_Application::ACTIONS_VAR_NAME] = isset($_REQUEST[SA_Application::ACTIONS_VAR_NAME]) ? explode(SA_Application::ACTIONS_SEPARATOR, $_REQUEST[SA_Application::ACTIONS_VAR_NAME]) : array();
		$_REQUEST[SA_Application::PAGE_VAR_NAME] = $_GET[SA_Application::PAGE_VAR_NAME] = empty($pageName) ? SA_Application::DEFAULT_PAGE : $pageName;
		//print_r($_GET);
	}
}