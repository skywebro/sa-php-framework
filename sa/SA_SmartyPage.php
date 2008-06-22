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
	protected $smarty = null;
	protected $template = '';
	protected $layout = null;
	protected $hasTemplate = true;

	public function __construct(SA_Request $request, SA_Response $response) {
		parent::__construct($request, $response);
		$this->smarty = new Smarty;
		$this->smarty->use_sub_dirs = true;
		$app = SA_Application::singleton();
		$this->smarty->plugins_dir = array_merge($this->smarty->plugins_dir, array(SA_LIB_DIR . 'smarty_plugins'));
		$this->smarty->template_dir = $app->getTemplatesDir();
		$this->smarty->compile_dir = $app->getCompileDir();
		$this->smarty->compile_id = md5($this->smarty->template_dir);
		$this->smarty->assign_by_ref('__PAGE__', $this);
	}

	public function &getSmarty() {
		return $this->smarty;
	}

	public function assign($key, $value = null) {
		$this->smarty->assign($key, $value);
	}

	public function hasTemplate() {
		return ($this->hasTemplate == true) && (!is_null($this->template));
	}

	public function setPageName($name) {
		parent::setPageName($name);
		$this->setTemplate($this->hasTemplate() ? "$name.tpl" : null);
	}

	public function setPagePath($path) {
		parent::setPagePath($path);
		$this->smarty->template_dir = SA_Application::singleton()->getTemplatesDir() . $path;
		$this->smarty->compile_id = md5($this->smarty->template_dir);
	}

	public function setTemplate($template = null) {
		$this->template = is_null($template) ? null : $template;
	}

	public function getTemplate($template) {
		return $this->template;
	}

	public function setLayout($layoutName = null) {
		return $this->layout = is_null($layoutName) ? null : SA_Application::singleton()->layoutFactory($layoutName);
	}

	public function hasLayout() {
		return !is_null($this->layout) && is_a($this->layout, 'SA_Layout');
	}

	public function &getLayout() {
		return $this->layout;
	}

	public function fetch($template = null) {
		$template = is_null($template) ? $this->template : $template;
		if (!$this->smarty->template_exists($template)) throw new SA_FileNotFound_Exception($this->smarty->template_dir . $template . ' does not exist.');
		return $this->smarty->fetch($template);
	}

	public function &content($content = null) {
		$content = $this->hasTemplate() ? $this->fetch() : null;
		if ($this->hasLayout()) {
			$this->layout->assign('__CONTENT_FOR_LAYOUT__', $content);
			$content = $this->layout->fetch();
		}
		return parent::content($content);
	}
}