<?php (defined('COREPATH')) OR exit('No direct script access allowed');

/* load the MX core module class */
require dirname(__FILE__).'/Modules.php';

/**
 * Modular Extensions - HMVC
 *
 * Adapted from the CodeIgniter Core Classes
 * @link	http://codeigniter.com
 *
 * Description:
 * This library extends the CodeIgniter router class.
 *
 * Install this file as application/third_party/MX/Router.php
 *
 * @copyright	Copyright (c) 2015 Wiredesignz
 * @version 	5.5
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 **/
class MX_Router extends \CI_Router
{
	public $module;
	private $located = 0;
	private $controller;

	public function fetch_module()
	{
		return $this->module;
	}

	protected function _set_request($segments = [])
	{
		if ($this->translate_uri_dashes === true)
		{
			foreach(range(0, 2) as $v)
			{
				isset($segments[$v]) && $segments[$v] = str_replace('-', '_', $segments[$v]);
			}
		}
		
		$segments = $this->locate($segments);

		if($this->located == -1)
		{
			$this->_set_404override_controller();
			return;
		}

		if(empty($segments))
		{
			$this->_set_default_controller();
			return;
		}

		$this->set_class($segments[0]);
		
		if (isset($segments[1]))
		{
			$this->set_method($segments[1]);
		}
		else
		{
			$segments[1] = 'index';
		}
       
		array_unshift($segments, null);
		unset($segments[0]);
		$this->uri->rsegments = $segments;
	}
	
	protected function _set_404override_controller()
	{
		$this->_set_module_path($this->routes['404_override']);
	}

	protected function _set_default_controller()
	{
		if (empty($this->directory))
		{
			/* set the default controller module path */
			$this->_set_module_path($this->default_controller);
		}

		parent::_set_default_controller();
		
		if(empty($this->class))
		{
			$this->_set_404override_controller();
		}
	}

	/** Locate the controller **/
	public function locate($segments)
	{
		$this->located = 0;
		$ext = $this->config->item('controller_suffix').EXT;
		$commands_directory = "commands";

		/* use module route if available */
		if (isset($segments[0]) && $routes = Modules::parse_routes($segments[0], implode('/', $segments)))
		{
			$segments = $routes;
		}

		/* get the segments array elements */
		list($module, $directory, $controller) = array_pad($segments, 3, null);

		if ($module === $commands_directory) {
			list($module, $controller) = array_pad($segments, 2, null);
		}

		if (str_contains((string) $directory, 'command')) {
			$directory = str_replace('command','Command', (string) $directory);
			$controller = str_replace('command','Command', (string) $controller);
		}

		/* check modules */
		foreach (Modules::$locations as $location => $offset)
		{

			$hasCommand = str_contains((string) $directory, 'Command');

			if ($module === $commands_directory) {
				$source = $location . ucfirst($module) . '/';
				$controller_location = ucfirst($module) . '/';
			} else if ($hasCommand) {
				$source = $location . ucfirst($module) . '/Commands/';
				$controller_location = ucfirst($module) . '/Commands/';
				$controller = $controller ?? '';
			} else {
				$source = $location . ucfirst($module) . '/Controllers/';
				$controller_location = ucfirst($module) . '/Controllers/';
				$controller = $controller ?? '';
			}

			/* module exists? */
			if (is_dir($source))
			{
				$this->module = ucfirst($module);
				$this->directory = $offset . $controller_location;
				$this->controller = $controller;

				/* module sub-controller exists? */
				if($directory)
				{
					/* module sub-directory exists? */
					if(is_dir($source.ucfirst($directory).'/'))
					{	
						$source .= ucfirst($directory).'/';
						$this->directory .= ucfirst($directory).'/';

						/* module sub-directory controller exists? */
						if($controller)
						{
							if(is_file($source.ucfirst($controller).$ext))
							{
								$this->located = 3;
								return array_slice($segments, 2);
							}
							else $this->located = -1;
						}
					}
					else if (is_file($source.ucfirst($directory).$ext))
					{
						$this->located = 2;
						return array_slice($segments, 1);
					}
					else $this->located = -1;
				}
				
				/* module controller exists? */
				if(is_file($source.ucfirst($module).$ext))
				{
					$this->located = 1;
					return $segments;
				}

				/* controller exists in commands directory? */
				if (is_file($source . '/' . ucfirst($this->controller) . $ext)) {
					$this->located = 1;
					return $segments;
				}
			}
		}
		
		if( ! empty($this->directory)) return;

		// /* controller exists in App/Controllers directory? */
		// if (is_file(APPROOT . 'Controllers/' . ucfirst($module) . $ext)) {
		// 	$directory = $module;
		// }

		/* controller exists in commands directory? */
		if (is_file(APPPATH . 'controllers/'.$commands_directory.'/' . ucfirst($module) . $ext)) {
			$directory = $module;
		}

		/* application sub-directory controller exists? */
		if($directory)
		{

			/* controller exists in App/Controllers sub-sub-directory? */
			if($controller)
			{
				if(is_file(APPROOT.'Controllers/'.ucfirst($module).'/'.ucfirst($directory).'/'.ucfirst($controller).$ext))
				{
					$this->directory = ucfirst($module).'/'.ucfirst($directory).'/';
					return array_slice($segments, 2);
				}
			}

			if(is_file(APPPATH.'controllers/'.$module.'/'.ucfirst($directory).$ext))
			{
				$this->directory = $module.'/';
				return array_slice($segments, 1);
			}

			if (is_file(APPPATH.'controllers/'.$commands_directory.'/'. ucfirst($directory) . $ext)) {
				$this->directory = $commands_directory.'/';
				return $segments;
			}

			/* application sub-sub-directory controller exists? */
			if($controller)
			{
				if(is_file(APPPATH.'controllers/'.$module.'/'.$directory.'/'.ucfirst($controller).$ext))
				{
					$this->directory = $module.'/'.$directory.'/';
					return array_slice($segments, 2);
				}
			}

		}

		/* controller exists in App/Controllers sub-directory? */
		if (is_dir(APPROOT . 'Controllers/' . ucfirst($module) .'/')) {
			$this->directory = ucfirst($module).'/';
			return array_slice($segments, 1);
		}

		/* controller exists in App/Controllers exists? */
		if (is_file(APPROOT . 'Controllers/' . ucfirst($module) . $ext)) {
			return $segments;
		}

		/* application controllers sub-directory exists? */
		if (is_dir(APPPATH.'controllers/'.$module.'/'))
		{
			$this->directory = $module.'/';
			return array_slice($segments, 1);
		}

		/* application controller exists? */
		if (is_file(APPPATH.'controllers/'.ucfirst($module).$ext))
		{
			return $segments;
		}
		
		$this->located = -1;
	}

	/* set module path */
	protected function _set_module_path(&$_route)
	{
		if ( ! empty($_route))
		{
			// Are module/directory/controller/method segments being specified?
			$sgs = sscanf($_route, '%[^/]/%[^/]/%[^/]/%s', $module, $directory, $class, $method);

			// set the module/controller directory location if found
			if ($this->locate([$module, $directory, $class]))
			{
				//reset to class/method
				switch ($sgs)
				{
					case 1:	$_route = $module.'/index';
						break;
					case 2: $_route = ($this->located < 2) ? $module.'/'.$directory : $directory.'/index';
						break;
					case 3: $_route = ($this->located == 2) ? $directory.'/'.$class : $class.'/index';
						break;
					case 4: $_route = ($this->located == 3) ? $class.'/'.$method : $method.'/index';
						break;
				}
			}
		}
	}

	public function set_class($class)
	{
		$suffix = strval($this->config->item('controller_suffix'));
		
		$string_position = !empty($suffix) ? strpos($class, $suffix) : false;
		
		$class = str_contains($class,'command')
			? ucfirst(str_replace('command', 'Command', $class))
			: $class;

		if ($string_position === false)
		{
			$class .= $suffix;
		}
		
		parent::set_class($class);

	}
}
