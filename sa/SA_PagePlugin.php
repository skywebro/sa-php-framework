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

abstract class SA_PagePlugin implements SA_IPagePlugin {
	protected $request = null;
	protected $response = null;
	protected $reg = null;

	public function __construct(SA_Request $request, SA_Response $response, $pageExp) {
		$this->request = $request;
		$this->response = $response;
		$this->reg = '/' . str_replace('/', '\/', $pageExp) . '/';
	}
	public function pageMatch($page) {
		return preg_match($this->reg, $page);
	}
	public function isValidEvent($event) {
		return method_exists($this, $event);
	}
	public function beforeCreate() {}
	public function beforeProcess() {}
	public function beforeDisplay() {}
	public function afterCreate() {}
	public function afterProcess() {}
	public function afterDisplay() {}
}