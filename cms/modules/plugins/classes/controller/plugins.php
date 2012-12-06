<?php defined('SYSPATH') or die('No direct access allowed.');class Controller_Plugins extends Controller_System_Backend{	public function action_index()	{		$this->template->content = View::factory('plugins/index');				$this->template->title = __('Plugins');		$this->breadcrumbs			->add($this->template->title, 'plugins');	}	public function get_index()	{		$plugins = array();				foreach (Plugins::find_all() as $plugin)		{			$p = $plugin->as_array();			if($plugin->enabled())			{				$p['status'] = TRUE;			}			$plugins[] = $p;		}				$this->json = array('data' => $plugins);	}		public function put_index()	{		$plugin_data = json_decode($this->request->body());		Plugins::find_all();		$plugin = Plugins::get_registered( $plugin_data->id );		if ( $plugin_data->status === TRUE )		{			Plugins::activate( $plugin_data->id );		}		else		{			Plugins::deactivate( $plugin_data->id );		}				$plugin = $plugin->as_array();		$plugin['status'] = $plugin_data->status;		Kohana::cache('Database::cache(plugins_list)', NULL, -1);				$this->json = $plugin;	}	} // end class PluginsController