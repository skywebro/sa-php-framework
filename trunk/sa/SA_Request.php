<?php
/**
 * © 2008 Petre Trînculescu <andi@skyweb.ro>
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
	protected $cookies = null;

	public function __construct() {
		$this->get = new ArrayObject($_GET);
		$this->post = new ArrayObject($_POST);
		$this->cookies = new ArrayObject($_COOKIE);
	}

	public function &get($name = null) {
		return is_null($name) ? $this->get : $this->get[$name];
	}

	public function &post($name = null) {
		return is_null($name) ? $this->post : $this->post[$name];
	}

	public function &cookie($name = null) {
		return is_null($name) ? $this->cookies : $this->cookies[$name];
	}

	public function isGet() {
		return strcasecmp($_SERVER['REQUEST_METHOD'], self::REQUEST_METHOD_GET) == 0;
	}

	public function isPost() {
		return strcasecmp($_SERVER['REQUEST_METHOD'], self::REQUEST_METHOD_POST) == 0;
	}

	public function isAjax() {
		//this implementation works for jQuery style Ajax requests
		$ajaxHeader = $_SERVER['HTTP_X_REQUESTED_WITH'];
		if (empty($ajaxHeader) && function_exists('apache_request_headers')) {
			$apacheHeaders = array_change_key_case(apache_request_headers(), CASE_LOWER);
			$ajaxHeader = $apacheHeaders['x-requested-with'];
		}
		return strcasecmp($ajaxHeader, self::REQUEST_AJAX) == 0;
	}
}