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

class SA_SimpleCache extends SA_Object {
	protected static $instances = array();

	public static function singleton($id) {
		$md5Id = md5($id);
		if (!isset(self::$instances[$md5Id])) {
			self::$instances[$md5Id] = new SA_DiskCache($id);
		}
		return self::$instances[$md5Id];
	}
}

class SA_DiskCache extends SA_Object {
	protected $id;
	protected $data;

	public function __construct($id) {
		$this->id = $id;
		$this->fileName = SA_Application::singleton()->getCacheDir() . md5(SA_Application::SECRET . $id . SA_Application::SECRET);
	}

	public function getData() {
		return $this->data;
	}

	public function setData($data) {
		$this->data = $data;
	}

	public function load() {
		return $this->data = @file_get_contents($this->fileName);
	}

	public function save($data = null) {
		$this->data = is_null($data) ? $this->data : $data;
		return @file_put_contents($this->fileName, $this->data, LOCK_EX);
	}
}