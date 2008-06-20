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

abstract class SA_Application extends SA_Object {
	const PAGE_VAR_NAME = '__SA_PAGE__';
	const DEFAULT_PAGE = 'index';
	const SESSION_NAME = 'SASESSID';

	protected $request = null;
	protected $response = null;
	protected $appDir = null;
	protected $pagesDir = null;
	protected static $instance = null;

	public function __construct($appDir) {
		parent::__construct();

		$this->setApplicationDir($appDir);
		self::$instance = &$this;
		$this->request = new SA_Request();
		$this->response = new SA_Response();
		session_name(self::SESSION_NAME);
		session_start();
	}

	public static function &singleton() {
		if (is_null(self::$instance)) {
			throw new SA_NoApplication_Exception('Application not instantiated!');
		}
		return self::$instance;
	}

	public function &request() {
		return $this->request;
	}

	public function &response() {
		return $this->response;
	}

	public function setApplicationDir($appDir) {
		if (is_dir($appDir) && is_readable($appDir)) {
			$this->appDir = $appDir;
			$this->setPagesDir($appDir . 'pages/');
		} else {
			throw new SA_DirNotFound_Exception('Application directory not found or not readable!');
		}
		return $this;
	}

	public function getApplicationDir() {
		return $this->appDir;
	}

	public function setPagesDir($pagesDir) {
		if (is_dir($pagesDir) && is_readable($pagesDir)) {
			$this->pagesDir = $pagesDir;
		} else {
			throw new SA_DirNotFound_Exception('Pages directory not found or not readable!');
		}
	}

	public function getPagesDir() {
		return $this->pagesDir;
	}

	public function &pageFactory($pageName = null) {
		$pagesDir = $this->getPagesDir();
		$p = $this->request->get(self::PAGE_VAR_NAME);
		$p = empty($pageName) ?  (empty($p) ? self::DEFAULT_PAGE : $p) : $pageName;
		$pageName = basename($p);
		$pagePath = dirname($p);
		$pageFileName = "{$pagesDir}{$p}.php";
		if (!is_file($pageFileName) || !is_readable($pageFileName)) {
			throw new SA_FileNotFound_Exception("File $pageFileName not found!");
		}
		require_once $pageFileName;
		$className = 'Page_' . ucfirst($pageName);
		if (!class_exists($className)) {
			throw new SA_PageInterface_Exception("Class $className does not exist!");
		}
		if (!in_array('SA_IPage', class_implements($this->page = new $className($this->request, $this->response)))) {
			throw new SA_PageInterface_Exception("Class $className must implement SA_IPage interface!");
		}
		$this->page->setPageName($pageName);
		return $this->page;
	}

	public function error(Exception $e) {
		throw $e;
	}

	public function run($sendHeaders = true) {
		try {
			$page = $this->pageFactory($this->request->get(self::PAGE_VAR_NAME));
			$page->init();
			if ($this->request->isGet()) {
				$page->get();
			} elseif ($this->request->isPost()) {
				$page->post();
			}
			$this->response->body($page->content());
			$this->response->send($sendHeaders);
			$page->cleanup();
		} catch(Exception $e) {
			$this->error($e);
		}
	}
}