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

require_once SMARTY_DIR . 'Smarty.class.php';

abstract class SA_SmartyPage extends SA_Page {
	const TEMPLATE_EXTENSION = '.tpl';

	protected $smarty = null;
	protected $template = null;
	protected $layout = null;
	protected $useTemplate = true;

	public function __construct(SA_Request $request, SA_Response $response) {
		parent::__construct($request, $response);
		$this->smarty = new Smarty;
		$this->smarty->use_sub_dirs = true;
		$app = SA_Application::getInstance();
		$this->smarty->plugins_dir = array_merge($this->smarty->plugins_dir, array(SA_LIB_DIR . 'smarty_plugins'));
		$this->smarty->template_dir = $app->getTemplatesDir();
		$this->smarty->compile_dir = $app->getCompileDir();
		$this->smarty->compile_id = md5($this->smarty->template_dir);
		$this->smarty->assign_by_ref('__PAGE__', $this);
	}

	public function &getTemplateObj() {
		return $this->smarty;
	}

	public function assign($key, $value = null) {
		$this->smarty->assign($key, $value);
	}

	public function hasTemplate() {
		return ($this->useTemplate == true) && (!empty($this->template));
	}

	public function setPageName($name) {
		parent::setPageName($name);
		$this->setTemplate($this->useTemplate == true ? $this->getPagePath() . $name . self::TEMPLATE_EXTENSION : null);
	}

	public function setTemplate($template = null) {
		$this->template = $template;
		$this->useTemplate = !empty($this->template);
	}

	public function getTemplate($template) {
		return $this->template;
	}

	public function setLayout($layoutName = null) {
		return $this->layout = empty($layoutName) ? null : SA_Application::getInstance()->layoutFactory($layoutName);
	}

	public function hasLayout() {
		return !empty($this->layout) && is_a($this->layout, 'SA_IPage');
	}

	public function &getLayout() {
		return $this->layout;
	}

	public function fetch($template = null) {
		$template = empty($template) ? $this->template : $template;
		if (!$this->smarty->template_exists($template)) throw new SA_FileNotFound_Exception($this->smarty->template_dir . $template . ' does not exist.');
		return $this->smarty->fetch($template);
	}

	public function &content($content = null) {
		$content .= $this->hasTemplate() ? $this->fetch() : null;
		if (is_string($this->layout)) $this->setLayout($this->layout);
		if ($this->hasLayout()) {
			$this->layout->assign('__CONTENT_FOR_LAYOUT__', $content);
			$this->layout->init();
			if ($this->request->isGet()) {
				$this->layout->get();
			} elseif ($this->request->isPost()) {
				$this->layout->post();
			}
			$this->layout->cleanup();
			$content = $this->layout->fetch();
		}
		return parent::content($content);
	}
}