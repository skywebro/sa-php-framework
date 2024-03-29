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
	const ACTIONS_VAR_NAME = 'do';
	const ACTIONS_SEPARATOR = '-';
	const NOCACHE_VAR_NAME = 'nocache';
	const DEFAULT_PAGE = 'index';
	const SESSION_NAME = 'SASESSID';
	const SECRET = 'alAb4laPor70cAla';

	protected $request = null;
	protected $response = null;
	protected $currentPage = null;
	protected $appDir = null;
	protected $pagesDir = null;
	protected $layoutsDir = null;
	protected $templatesDir = null;
	protected $compileDir = null;
	protected $cacheDir = null;
	protected $pagePluginsDir = null;
	protected $pagePlugins = array();
	protected $noCache = false;
	protected static $instance = null;

	public function __construct($appDir) {
		parent::__construct();
		$this->useCache((USE_CACHE === true) ? true : ((USE_CACHE === false) ? false : ((isset($_REQUEST[self::NOCACHE_VAR_NAME]) || preg_match('/(\/' . self::NOCACHE_VAR_NAME . '\/1){1}/i', $_SERVER['PATH_INFO'])) ? false : true)));
		$this->setApplicationDir($appDir);
		self::$instance = &$this;

		try {
			$this->request = new SA_Request();
			$this->response = new SA_Response();
		} catch (Exception $e) {
			$this->error($e);
		}

		session_name(self::SESSION_NAME);
		if ($sid = $this->request->r(self::SESSION_NAME)) session_id($sid);
		session_start();
	}

	public function __destruct() {
		print '<p style="font:.75em normal verdana,helvetica,serif;">&copy; 2008 Petre Tr&icirc;nculescu &middot; <a href="http://skyweb.ro/" target="_blank">skyweb development</a></p>';
	}

	public function useCache($cache = null) {
		$this->noCache = is_null($cache) ? $this->noCache : !$cache;
		return !$this->noCache;
	}

	/**
	 * Fetch the file system structure of the pages directory in a DOMDocument
	 * The XML will be used by SA_Request in order to detect the page name
	 * The contents will be read from cache only if $_REQUEST[self::NOCACHE_VAR_NAME] is not set
	 * USE_CACHE overrides the request parameter
	 *
	 * @see SA_Request::detectGetParameters()
	 * @return DOMDocument
	 */

	public function getXMLPageMap() {
		static $xml;

		if (isset($xml)) return $xml;
		$cache = SA_SimpleCache::singleton('__XML_PAGES_MAP__');
		if ($xmlString = $cache->load()) {
			$xml = new SimpleXMLElement($xmlString);
		} else {
			$xml = new SimpleXMLElement('<?xml version="1.0" standalone="yes"?><pages/>');
			$this->xmlFileSystem($this->getPagesDir(), $xml);
			$cache->save($xml->asXML());
		}
		return $xml;
	}

	public function &getCurrentPage() {
		if (!is_a($this->currentPage, 'SA_Page')) throw new SA_NoPage_Exception('Could not determine current page!');
		return $this->currentPage;
	}

	public static function &getInstance() {
		if (!is_a(self::$instance, 'SA_Application')) throw new SA_NoApplication_Exception('Application not instantiated!');
		return self::$instance;
	}

	public function &request() {
		return $this->request;
	}

	public function &response() {
		return $this->response;
	}

	public function &setApplicationDir($appDir) {
		if (is_readable($appDir)) {
			$this->appDir = $appDir;
			$this->setCacheDir($appDir . 'cache/');
			$this->setPagesDir($appDir . 'pages/');
			$this->setLayoutsDir($appDir . 'layouts/');
			$this->setTemplatesDir($appDir . 'templates/');
			$this->setPagePluginsDir($appDir . 'plugins/');
			$this->setCompileDir($appDir . 'templates_c/');
		} else {
			throw new SA_DirNotFound_Exception('Application directory not found or not readable!');
		}
		return $this;
	}

	public function getApplicationDir() {
		return $this->appDir;
	}

	public function setPagesDir($pagesDir) {
		$this->pagesDir = $pagesDir;
	}

	public function getPagesDir() {
		return $this->pagesDir;
	}

	public function setLayoutsDir($layoutsDir) {
		$this->layoutsDir = $layoutsDir;
	}

	public function getLayoutsDir() {
		return $this->layoutsDir;
	}

	public function setTemplatesDir($templatesDir) {
		$this->templatesDir = $templatesDir;
	}

	public function getTemplatesDir() {
		return $this->templatesDir;
	}

	public function setCompileDir($compileDir) {
		$this->compileDir = $compileDir;
	}

	public function getCompileDir() {
		return $this->compileDir;
	}

	public function setCacheDir($cacheDir) {
		$this->cacheDir = $cacheDir;
	}

	public function getCacheDir() {
		return $this->cacheDir;
	}

	public function setPagePluginsDir($pagePluginsDir) {
		$this->pagePluginsDir = $pagePluginsDir;
	}

	public function getPagePluginsDir() {
		return $this->pagePluginsDir;
	}

	/**
	 * Registers a page level plugin
	 *
	 * @param string $pluginClass
	 * @param string $pageExp can be any valid regular expression string
	 * @return SA_Application
	 */

	public function &registerPagePlugin($pluginClass, $pageExp) {
		$pluginFileName = $this->getPagePluginsDir() . "$pluginClass.php";
		require_once $pluginFileName;
		$plugin = new $pluginClass($this->request, $this->response, $pageExp);
		if (!in_array('SA_IPagePlugin', class_implements($plugin))) {
			throw new SA_PageInterface_Exception("Class $pluginClass must implement SA_IPagePlugin interface!");
		}
		$this->pagePlugins[$pageExp][md5("{$pluginClass}{$pageExp}")] = $plugin;
		return $this;
	}

	public function unregisterPagePlugin($pluginClass, $pageExp) {
		unset($this->pagePlugins[$pageExp][md5("{$pluginClass}{$pageExp}")]);
	}

	public function &pageFactory($pageName = null) {
		$p = $this->request->r(self::PAGE_VAR_NAME);
		$p = empty($pageName) ?  (empty($p) ? self::DEFAULT_PAGE : $p) : $pageName;
		$pageName = trim(preg_replace('/[^a-z0-9_\/]/i', '_', basename($p)));
		$pagePath = (($dir = dirname($p)) == '.') ? '' : $dir . '/';
		$pagesDir = $this->getPagesDir();
		$pageFileName = "{$pagesDir}{$p}.php";
		require_once $pageFileName;
		$className = "Page_$pageName";
		$this->currentPage = new $className($this->request, $this->response);
		if (!in_array('SA_IPage', class_implements($this->currentPage))) {
			throw new SA_PageInterface_Exception("Class $className must implement SA_IPage interface!");
		}
		$this->currentPage->setPagePath($pagePath);
		$this->currentPage->setPageName($pageName);
		return $this->currentPage;
	}

	public function &layoutFactory($layoutName) {
		$layout = null;
		$layoutPath = $this->getLayoutsDir();
		$layoutFileName = "{$layoutPath}{$layoutName}.php";
		require_once $layoutFileName;
		$className = "Layout_$layoutName";
		$layout = new $className($this->request, $this->response);
		if (!in_array('SA_IPage', class_implements($layout))) {
			throw new SA_PageInterface_Exception("Layout $className must implement SA_IPage interface!");
		}
		$layout->setPageName($layoutName);
		return $layout;
	}

	public function error(Exception $e) {
		throw $e;
	}

	public function run($sendHeaders = true) {
		try {
			ob_start();
			$this->pagePluginsWalk($this->request->r(self::PAGE_VAR_NAME), 'beforeCreation');
			$page = $this->pageFactory();
			$pageName = $page->getPagePath() . $page->getPageName();
			$this->pagePluginsWalk($pageName, 'afterCreation');
			$page->init();
			$this->pagePluginsWalk($pageName, 'beforeProcess');
			if (is_array($actions = $this->request->r(self::ACTIONS_VAR_NAME))) {
				foreach($actions as $action) {
					$action = preg_replace('/[^a-z0-9_]/i', '_', $action);
					$method = 'do' . ucfirst(strtolower($action));
					if (method_exists($page, $method)) $page->$method();
				}
			}
			if ($this->request->isGet()) {
				$page->get();
			} elseif ($this->request->isPost()) {
				$page->post();
			}
			$this->pagePluginsWalk($pageName, 'afterProcess');
			$page->cleanup();
			$output = ob_get_contents();
			ob_end_clean();
			$output = $page->content($output);
			$this->response->body($output);
			$this->pagePluginsWalk($pageName, 'beforeDisplay');
			$this->response->send($sendHeaders);
			$this->pagePluginsWalk($pageName, 'afterDisplay');
		} catch(Exception $e) {
			$this->error($e);
		}
	}

	protected function pagePluginsWalk($page, $event) {
		array_walk($this->pagePlugins, array(&$this, 'runPagePlugins'), array($page, $event));
	}

	protected function runPagePlugins($plugins, $pageExp, $args) {
		list($page, $event) = $args;
		if (preg_match('/' . str_replace('/', '\/', $pageExp) . '/', $page)) array_walk($plugins, array(&$this, 'runPagePluginEvent'), $event);
	}

	protected function runPagePluginEvent(SA_IPagePlugin $plugin, $key, $event) {
		if ($plugin->isValidEvent($event)) $plugin->$event();
	}

	protected function xmlFileSystem($dir, SimpleXMLElement $xml) {
		try {
			$dirIterator = new DirectoryIterator($dir);
			foreach ($dirIterator as $entry) {
				$entryName = $entry->getFilename();
				if ($entry->isDir()) {
					if (!$entry->isDot()) {
						$node = $xml->addChild('dir');
						$node->addAttribute('name', $entryName);
						$this->xmlFileSystem($dir . '/' . $entryName, $node);
					}
				} else {
					$node = $xml->addChild('file');
					$node->addAttribute('name', $entryName);
				}
			}
		} catch (Exception $e) {}
	}
}