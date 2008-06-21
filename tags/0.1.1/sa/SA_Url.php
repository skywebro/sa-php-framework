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

class SA_Url extends SA_Object {
	const MAX_LENGTH = 2083; //careful with this: maximum URL length is 2083 characters in Internet Explorer
	protected static $parts = null;

	public static function url($page = null, $actions = false, $params = array(), $port = 80, $secure = false) {
		SA_Url::init();
		$protocol = $secure ? 'https' : 'http';
		$page = trim(empty($page) ? SA_Application::DEFAULT_PAGE : $page, "\t ");
		$isAbsolute = strpos($page, '/') === 0;
		$isDir = substr($page, -1) == '/';
		$page = trim($page, '/');
		$page .= $isDir && $page ? '/' . SA_Application::DEFAULT_PAGE : '';
		$page = $isAbsolute ? $page : self::$parts->currentPage->getPagePath() . $page;
		$url = "$protocol://" . self::$parts->host . ($port == 80 ? '' : ":$port") . '/' . $page;

		if ($actions) {
			if (!is_array($actions)) $actions = array($actions);
			$actions = implode('-', $actions);
			$url .= '/' . SA_Application::ACTIONS_VAR_NAME . '/' . urlencode($actions);
		}

		if (is_array($params)) {
			$params = array_map(create_function('$value', 'return strcmp($value, "") == 0 ? null : $value;'), $params);
			$paramKeys = array_keys($params);
			$pairs = array();
			for($i = 0; $i < count($paramKeys); $i++) {
				if ($key = $paramKeys[$i]) {
					$value = $params[$key];
					if (is_null($value)) $value = '(:null:)';
					elseif (is_array($value) || is_object($value)) $value = base64_encode(serialize($value));
					$value = str_replace('/', '(:slash:)', $value);
					$pairs[] = urlencode($key) . '/' . urlencode($value);
				}
			}
			if ($pairsString = implode('/', $pairs)) $url .= "/$pairsString";
		}

		if (strlen($url) > self::MAX_LENGTH) throw new Exception('The URL exceeds maximum length of ' . self::MAX_LENGTH . ' characters!');

		return $url;
	}

	protected static function init() {
		if (is_null(self::$parts)) {
			self::$parts = new stdClass();
			$app = &SA_Application::singleton();
			self::$parts->app = $app;
			self::$parts->host = $app->request()->s('HTTP_HOST');
			self::$parts->currentPage = $app->getCurrentPage();
		}
	}
}