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

abstract class SA_Page extends SA_Object implements SA_IPage {
	protected $request = null;
	protected $response = null;
	protected $name = null;
	protected $path = null;
	protected $content = null;

	public function __construct(SA_Request $request, SA_Response $response) {
		parent::__construct();
		$this->name = $name;
		$this->request = $request;
		$this->response = $response;
	}

	public function setPageName($name) {
		$this->name = $name;
	}

	public function getPageName() {
		return $this->name;
	}

	public function setPagePath($path) {
		$this->path = $path;
	}

	public function getPagePath() {
		return $this->path;
	}

	public function &headers($key = null, $value = null) {
		return $this->response->headers($key, $value);
	}

	public function init() {}

	public function get() {}

	public function post() {}

	public function cleanup() {}

	public function &content($content = null) {
		if (!is_null($content)) $this->content = $content;
		return $this->content;
	}

	public function display() {
		print $this->content();
	}
}