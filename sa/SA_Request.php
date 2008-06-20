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
		$this->detectParameters();
		$this->get = new ArrayObject($_GET);
		$this->post = new ArrayObject($_POST);
		$this->request = new ArrayObject($_REQUEST);
		$this->cookies = new ArrayObject($_COOKIE);
		$this->server = new ArrayObject($_SERVER);
		$this->env = new ArrayObject($_ENV);
	}

	public function &get($key = null) {
		return is_null($key) ? $this->get : $this->get[$key];
	}

	public function &post($key = null) {
		return is_null($key) ? $this->post : $this->post[$key];
	}

	public function &request($key = null) {
		return is_null($key) ? $this->request : $this->request[$key];
	}

	public function &cookie($key = null) {
		return is_null($key) ? $this->cookies : $this->cookies[$key];
	}

	public function &server($key = null) {
		return is_null($key) ? $this->server : $this->server[$key];
	}

	public function &env($key = null) {
		return is_null($key) ? $this->env : $this->env[$key];
	}

	public function isGet() {
		return strcasecmp($this->server('REQUEST_METHOD'), self::REQUEST_METHOD_GET) == 0;
	}

	public function isPost() {
		return strcasecmp($this->server('REQUEST_METHOD'), self::REQUEST_METHOD_POST) == 0;
	}

	public function detectParameters() {
		$pathInfo = substr($_SERVER['PATH_INFO'], strpos($_SERVER['PATH_INFO'], '/') + 1);
		$pathInfoStack = $pathInfoArray = explode('/', $pathInfo);
		$pageName = $partialPathInfo = null;

		function fileXPath($pathInfo, $type) {
			if (!is_array($pathInfo)) return null;
			$pathInfo = array_filter($pathInfo, create_function('$value', 'return trim(urldecode($value)) !== "";'));
			$xpath = array('//dirs');
			if ($type == 'file') $fileName = preg_replace('/[^a-z0-9_\/]/i', '_', array_pop($pathInfo));
			foreach($pathInfo as $value) {
				if (!empty($value)) {
					$value = preg_replace('/[^a-z0-9_\/]/i', '_', $value);
					$xpath[] = "dir[@name='$value']";
				}
			}
			if ($type == 'dir') {
				$xpath[] = "file[@name='" . SA_Application::DEFAULT_PAGE . ".php']";
			} elseif ($type == 'file') {
				$xpath[] = "file[@name='{$fileName}.php']";
			}
			$xPathString = implode('/', $xpath);
			return $xPathString;
		}

		$xml = SA_Application::singleton()->getXMLPageMap();
		for($i = 0; $i < count($pathInfoArray); $i++) {
			$partialPathInfo = implode('/', $pathInfoStack);
			if ((substr($xpath = $partialPathInfo, -1) == '/') && is_array($entries = $xml->xpath(fileXPath($pathInfoStack, 'dir'))) && count($entries)) {
				$pageName = $partialPathInfo . SA_Application::DEFAULT_PAGE;
			} elseif (is_array($entries = $xml->xpath(fileXPath($pathInfoStack, 'file'))) && count($entries)) {
				$pageName = trim($partialPathInfo, '/');
			}
			if ($pageName) break;
			array_pop($pathInfoStack);
		}
		$params = array_filter(explode('/', trim(substr($pathInfo, strlen($pageName)), '/')), create_function('$value', 'return trim(urldecode($value)) !== "";'));
		if (count($params) % 2) throw new SA_NoPage_Exception('Page not found');
		for($i = 0; $i < count($params); $i += 2) {
			$key = trim(urldecode($params[$i]));
			$value = trim(urldecode($params[$i + 1]));
			if (!empty($key)) $_REQUEST[$key] = $_GET[$key] = $value;
		}
		$_REQUEST[SA_Application::PAGE_VAR_NAME] = $_GET[SA_Application::PAGE_VAR_NAME] = preg_replace('/[^a-z0-9_\/]/i', '_', urldecode($pageName));
	}

	public function isAjax() {
		//this check works for jQuery style Ajax requests
		$ajaxHeader = $this->server('HTTP_X_REQUESTED_WITH');
		if (empty($ajaxHeader) && function_exists('apache_request_headers')) {
			$apacheHeaders = array_change_key_case(apache_request_headers(), CASE_LOWER);
			$ajaxHeader = $apacheHeaders['x-requested-with'];
		}
		return strcasecmp($ajaxHeader, self::REQUEST_AJAX) == 0;
	}
}