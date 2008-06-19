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

abstract class SA_Application extends SA_Object {
	const PAGE_VAR_NAME = 'p';
	const DEFAULT_PAGE = 'index';
	protected $request = null;
	protected $response = null;
	protected $appDir = null;

	public function __construct() {
		parent::__construct();
		$this->request = new SA_Request();
		$this->response = new SA_Response();
	}

	public function &getResponse() {
		return $this->response;
	}

	public function &getRequest() {
		return $this->request;
	}

	public function setApplicationDir($appDir) {
		if (is_dir($appDir) && is_readable($appDir)) {
			$this->appDir = $appDir;
		} else {
			throw new Exception('Application directory not found or not readable!');
		}
		return $this;
	}

	public function &getApplicationDir() {
		return $this->appDir;
	}

	public function &pageFactory($pageName = null) {
		$pagesDir = $this->getApplicationDir() . 'pages/';
		if (!is_dir($pagesDir) || !is_readable($pagesDir)) {
			throw new Exception('Pages directory not found or not readable!');
		}
		$p = $this->request->get(self::PAGE_VAR_NAME);
		$pageName = strtolower(is_null($pageName) ?  (empty($p) ? self::DEFAULT_PAGE : $p) : $pageName);
		$pageFileName = "{$pagesDir}{$pageName}.php";
		if (!is_file($pageFileName) || !is_readable($pageFileName)) {
			throw new Exception("File $pageFileName not found!");
		}
		require_once $pageFileName;
		$className = "Page_$pageName";
		$page = new $className($this->request, $this->response);
		if (!is_a($page, 'SA_IPage')) {
			throw new Exception("Class $className must implement SA_IPage interface!");
		}
		return $page;
	}

	public function run($sendHeaders = true) {
		$page = &$this->pageFactory();
		$this->response->body($page->content());
		$this->response->send($sendHeaders);
	}
}