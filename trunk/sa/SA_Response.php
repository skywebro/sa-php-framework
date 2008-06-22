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

class SA_Response extends SA_Object {
	protected $headers = null;
	protected $body = null;

	public function __construct() {
		parent::__construct();
		$this->headers = new ArrayObject();
		$this->headers['Content-type'] = 'text/html; charset=utf-8';
	}

	public function &headers($key = null, $value = null) {
		$result = null;
		if (is_null($key)) {
			$result = &$this->headers;
		} elseif (is_null($value)) {
			$result = &$this->headers[$key];
		} else {
			$result = &$this->headers[$key];
			$this->headers[$key] = $value;
		}
		return $result;
	}

	public function sendHeaders() {
		if (!headers_sent()) {
			for($i = $this->headers->getIterator(); $i->valid(); $i->next()) {
				header($i->key() . ': ' . $i->current());
			}
		}
	}

	public function &body($content = null) {
		if (!is_null($content)) $this->body = $content;
		return $this->body;
	}

	public function send($sendHeaders = true) {
		if ($sendHeaders) $this->sendHeaders();
		print $this->body();
	}
}