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

require_once 'SA_Object.php';

class SA extends SA_Object {
	public static function autoload($className) {
		if (preg_match('/^SA_/', $className)) {
			$classFileName = SA_LIB_DIR . "{$className}.php";
			require_once $classFileName;
		}
	}

	public static function prettyDump($var) {
		print '<div style="font-family:arial,helvetica,serif;font-size:12px;background-color:black;color:lime;padding:5px;border:3px solid red;">' . nl2br($var) . '</div>';
	}
}