<?php defined('SYSPATH') or die('No direct access allowed.');

abstract class Model_Widget_Decorator {
	
	/**
	 *
	 * @var integer
	 */
	public $id;

	/**
	 *
	 * @var string 
	 */
	public $type;
	
	/**
	 *
	 * @var string 
	 */
	public $name;

	/**
	 *
	 * @var string 
	 */
	public $description = '';
	
	/**
	 *
	 * @var string 
	 */
	public $header = NULL;

	/**
	 *
	 * @var string
	 */
	public $template = NULL;
	
	/**
	 *
	 * @var string 
	 */
	public $backend_template = NULL;
	
	/**
	 *
	 * @var string 
	 */
	public $frontend_template = NULL;


	/**
	 *
	 * @var boolean 
	 */
	public $use_template = TRUE;

	
	/**
	 *
	 * @var array
	 */
	public $template_params = array();

	/**
	 *
	 * @var string
	 */
	public $block = NULL;
	
	/**
	 *
	 * @var boolean 
	 */
	public $crumbs = FALSE;

	/**
	 *
	 * @var bool 
	 */
	public $caching = FALSE;
	
	/**
	 *
	 * @var integer 
	 */
	public $cache_lifetime = Date::MONTH;
	
	/**
	 *
	 * @var array 
	 */
	public $cache_tags = array();


	/**
	 *
	 * @var bool 
	 */
	public $throw_404 = FALSE;
	
	/**
	 *
	 * @var Context 
	 */
	protected $_ctx = NULL;


	/**
	 *
	 * @var array 
	 */
	protected $_data = array();

	/**
	 * 
	 * @return string
	 */
	public function backend_template()
	{
		if($this->backend_template === NULL)
		{
			$this->backend_template = $this->type;
		}
		
		return $this->backend_template;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function frontend_template()
	{
		if($this->frontend_template === NULL)
		{
			$this->frontend_template = $this->type;
		}
		
		return $this->frontend_template;
	}

	/**
	 * 
	 * @param array $params
	 */
	public function render($params = array())
	{
		if(Kohana::$profiling === TRUE)
		{
			$benchmark = Profiler::start('Widget render', $this->name);
		}

		$this->_fetch_template();
		
		$allow_omments = (bool) Arr::get($params, 'comments');

		if( $this->block != 'PRE' )
		{
			echo "<!--{Widget: {$this->name}}-->";

//			if(AuthUser::isLoggedIn() AND Request::current()->headers('Content-Type') == 'text/html')
//			{
//				echo "<div class='widget-block'".
//					" data-id='$this->id' data-section='widget' data-type='$this->type'>";
//			}
		}
		
		if( 
			$this->caching === TRUE 
		AND 
			! Fragment::load($this->get_cache_id(), $this->cache_lifetime)
		)
		{
			echo $this->_fetch_render($params);
			Fragment::save_with_tags($this->cache_lifetime, $this->cache_tags);
		}
		else if( ! $this->caching )
		{
			echo $this->_fetch_render($params);
		}

		if( $this->block != 'PRE' )
		{
//			if(AuthUser::isLoggedIn() AND Request::current()->headers('Content-Type') == 'text/html')
//			{
//				$block_id = sprintf('obj.%x', crc32(rand().microtime()));
//				
//				echo "<div class='clearfix'></div></div>";
//			}
			echo "<!--{/Widget: {$this->name}}-->";
		}
		
		if(isset($benchmark))
		{
			Profiler::stop($benchmark);
		}
	}
	
	protected function _fetch_template()
	{
		if( empty($this->template) ) 
		{
			if( ($this->template = Kohana::find_file('views', 'widgets/template/' . $this->frontend_template())) === FALSE  )
			{
				$this->template = Kohana::find_file('views', 'widgets/template/default');
			}
		}
		else
		{
			$this->template = SNIPPETS_SYSPATH . $this->template . EXT;
		}
		
		return $this->template;
	}

		/**
	 * 
	 * @param array $params
	 * @return View
	 */
	protected function _fetch_render($params)
	{
		$params = Arr::merge($params, $this->template_params);
		$context = & Context::instance();

		$data = $this->fetch_data();
		$data['params'] = $params;
		$data['page'] = $context->get_page();
	
		return View_Front::factory($this->template, $data)
			->bind('header', $this->header)
			->bind('ctx', $this->get( 'ctx' ));
	}
	
	/**
	 * @return array
	 */
	abstract public function fetch_data();
	
	/**
	 * @param array $data
	 */
	public function set_values(array $data)
	{
		foreach($data as $key => $value)
		{
			if( method_exists( $this, 'set_' . $key ))
			{
				$this->{'set_'.$key}($value);
			}
			else 
			{
				$this->{$key} = $value;
			}
		}
		
		return $this;
	}
	
	/**
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return \Model_Widget_Decorator
	 */
	public function set( $name, $value )
	{
		$this->_data[$name] = $value;
		return $this;
	}
	
	/**
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return \Model_Widget_Decorator
	 */
	public function bind( $name, & $value )
	{
		$this->_data[$name] = & $value;
		return $this;
	}

	/**
	 * 
	 * @param string $name
	 * @param mixed $default
	 * @return mided
	 */
	public function & get( $name, $default = NULL)
	{
		$result = $default;
		if (array_key_exists($name, $this->_data))
		{
			$result = $this->_data[$name];
		}
		
		return $result;
	}

	/**
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set( $name, $value )
	{
		$this->set($name, $value);
	}
	
	/**
	 * 
	 * @param string $name
	 * @return mixed
	 */
	public function & __get( $name )
	{
		return $this->get( $name );
	}

	/**
	 * 
	 * @param array $data
	 * @return \Model_Widget_Decorator
	 */
	public function set_cache_settings(array $data)
	{
		$this->caching = (bool) Arr::get($data, 'caching', FALSE);
		$this->cache_lifetime = (int) Arr::get($data, 'cache_lifetime');
		
		$this->cache_tags = explode(',', Arr::get($data, 'cache_tags'));
		
		return $this;
	}

	/**
	 * 
	 * @return string
	 */
	public function get_cache_id()
	{
		return 'Widget::' . $this->type . '::' . $this->id;
	}
	
	public function cache_tags()
	{
		return implode(', ', (array) $this->cache_tags);
	}

	/**
	 * 
	 * @return \Model_Widget_Decorator
	 */
	public function clear_cache()
	{
		if($this->caching === TRUE)
		{
			Fragment::delete($this->get_cache_id());
		}

		return $this;
	}
	
	public function clear_cache_by_tags()
	{
		if(!empty($this->cache_tags))
		{
			$cache = Cache::instance();
			
			if($cache instanceof Cache_Tagging)
			{
				if( is_array( $this->cache_tags ))
				{
					foreach($this->cache_tags as $tag)
					{
						$cache->delete_tag($tag);
					}
				}
				else
				{
					$cache->delete_tag( $this->cache_tags );
				}
			}
		}
		
		return $this;
	}

	/**
	 * 
	 * @param array $params
	 */
	public function run($params = array()) 
	{
		return $this->render($params);
	}
	
	/**
	 * 
	 * @return bool
	 */
	public function loaded()
	{
		return isset($this->id) AND $this->id > 0;
	}
	
	/**
	 * 
	 * @return array
	 */
	public function load_template_data()
	{
		return array();
	}
	
	public function on_page_load() {}
	
	public function change_crumbs( &$crumbs) {}

	/**
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->render();
	}
	
	public function __wakeup()
	{
		$this->_ctx =& Context::instance();
	}
}