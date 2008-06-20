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
	protected $withoutTemplate = false;

	public function __construct(SA_Request $request, SA_Response $response) {
		parent::__construct($request, $response);
		$this->smarty = new Smarty;
		$this->smarty->use_sub_dirs = true;
		$app = SA_Application::singleton();
		$this->smarty->template_dir = $app->getApplicationDir() . 'templates/';
		$this->smarty->compile_dir = $app->getApplicationDir() . 'templates_c/';
		$this->smarty->compile_id = md5($this->smarty->template_dir);
	}

	public function assign($key, $value = null) {
		$this->smarty->assign($key, $value);
	}

	public function isWithoutTemplate() {
		return ($this->withoutTemplate == true) || (is_null($this->template));
	}

	public function setPageName($name) {
		parent::setPageName($name);
		$this->setTemplate($this->isWithoutTemplate() ? null : "$name.tpl");
	}

	public function setPagePath($path) {
		parent::setPagePath($path);
		$this->smarty->template_dir = SA_Application::singleton()->getApplicationDir() . 'templates/' . $path;
		$this->smarty->compile_id = md5($this->smarty->template_dir);
	}

	public function setTemplate($template = null) {
		if (is_null($template)) {
			$this->template = null;
		} elseif ($this->smarty->template_exists($template)) {
			$this->template = $template;
		} else {
			throw new SA_FileNotFound_Exception($this->getPageName() . ' template does not exist.');
		}
	}

	public function getTemplate($template) {
		return $this->template;
	}

	public function &content($content = null) {
		$content = $this->isWithoutTemplate() ? null : $this->smarty->fetch($this->template);
		return parent::content($content);
	}
}