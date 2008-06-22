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
	const SLASH = '(:s:)';
	const NULL = '(:n:)';

	protected static $parts = null;

	public static function url($page = null, $params = array(), $port = 80, $secure = false) {
		SA_Url::init();
		$protocol = $secure ? 'https' : 'http';
		$page = trim($page);
		$page = empty($page) ? SA_Application::DEFAULT_PAGE : $page;
		$isAbsolute = strpos($page, '/') === 0;
		$isDir = substr($page, -1) == '/';
		$page = trim($page, '/');
		$page .= $isDir && $page ? '/' . SA_Application::DEFAULT_PAGE : '';
		$page = $isAbsolute ? $page : self::$parts->currentPage->getPagePath() . $page;
		$url = "$protocol://" . self::$parts->host . ($port == 80 ? '' : ":$port") . '/' . str_replace('%2F', '/', rawurlencode($page));

		if ($actions = $params['actions']) {
			if (!is_array($actions)) $actions = array($actions);
			$actions = array_map(create_function('$value', 'return str_replace("/", SA_Url::SLASH, trim($value));'), array_filter($actions, create_function('$value', 'return is_scalar($value) && strcmp($value, "") != 0;')));
			$actionsString = implode(SA_Application::ACTIONS_SEPARATOR, $actions);
			if (strlen($actionsString)) $url .= '/' . SA_Application::ACTIONS_VAR_NAME . '/' . rawurlencode($actionsString);
			unset($params['actions']);
		}

		if (is_array($params)) {
			$params = array_map(create_function('$value', 'return is_scalar($value) && strcmp($value, "") == 0 ? null : $value;'), $params);
			$pairs = array();
			foreach($params as $key => $value) {
				if (is_string($key) && strlen($key)) {
					if (is_null($value)) $value = self::NULL;
					elseif (is_array($value) || is_object($value)) $value = base64_encode(serialize($value));
					else $value = str_replace('/', self::SLASH, $value);
					$pairs[] = rawurlencode($key) . '/' . rawurlencode($value);
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