<?php defined( 'SYSPATH' ) or die( 'No direct access allowed.' );

class KodiCMS_Controller_Layout extends Controller_System_Backend {

	public $auth_required = array( 'administrator', 'developer' );
	
	public function before()
	{
		parent::before();
		$this->breadcrumbs
			->add(__('Layouts'), $this->request->controller());
	}

	public function action_index()
	{
		$this->template->title = __('Layouts');

		$this->template->content = View::factory( 'layout/index', array(
			'layouts' => Model_File_Layout::find_all()
		) );
	}
	
	public function action_rebuild()
	{
		$layouts = Model_File_Layout::find_all();
		
		foreach($layouts as $layout)
		{
			$layout->rebuild_blocks();
		}
		
		Messages::success( __( 'Layout blocks succefully update!' ) );
		
		$this->go_back();
	}

	public function action_add()
	{
		// check if trying to save
		if ( Request::current()->method() == Request::POST )
		{
			return $this->_add();
		}
		
		$this->template->title = __('Add layout');
		$this->breadcrumbs
			->add($this->template->title);

		// check if user have already enter something
		$layout = Flash::get( 'post_data' );

		if ( empty( $layout ) )
		{
			$layout = new Model_File_Layout;
		}

		$this->template->content = View::factory( 'layout/edit', array(
			'action' => 'add',
			'layout' => $layout
		) );
	}

	protected function _add()
	{
		$data = $this->request->post();
		Flash::set( 'post_data', (object) $data );

		$layout = new Model_File_Layout( $data['name'] );
		$layout->content = $data['content'];

		try
		{
			$status = $layout->save();
		}
		catch(Validation_Exception $e)
		{
			$this->go_back();
		}

		if ( ! $status )
		{
			Messages::errors( __( 'Something went wrong!' ) );
			$this->go( 'layout/add/' );
		}
		else
		{
			Messages::success( __( 'Layout has been saved!' ) );
			Observer::notify( 'layout_after_add', array( $layout ) );
		}
		
		Session::instance()->delete('post_data');

		// save and quit or save and continue editing?
		if ( $this->request->post('commit') !== NULL )
		{
			$this->go( 'layout' );
		}
		else
		{
			$this->go( 'layout/edit/' . $layout->name );
		}
	}

	public function action_edit( )
	{
		$layout_name = $this->request->param('id');
		$layout = new Model_File_Layout( $layout_name );

		if ( ! $layout->is_exists() )
		{
			Messages::errors(__( 'Layout not found!' ) );
			$this->go( 'layout' );
		}
		
		$this->breadcrumbs
			->add($layout_name);

		// check if trying to save
		if ( Request::current()->method() == Request::POST )
		{
			return $this->_edit( $layout );
		}

		$this->template->content = View::factory( 'layout/edit', array(
			'action' => 'edit',
			'layout' => $layout
		) );
	}

	protected function _edit( $layout )
	{
		$layout->name = $this->request->post('name');
		$layout->content = $this->request->post('content');
		
		try
		{
			$status = $layout->save();
		}
		catch(Validation_Exception $e)
		{
			$this->go_back();
		}

		if ( !$status )
		{
			Messages::errors( __( 'Something went wrong!' ) );
			$this->go_back();
		}
		else
		{
			Messages::success( __( 'Layout has been saved!' ) );
			Observer::notify( 'layout_after_edit', array( $layout ) );
		}

		// save and quit or save and continue editing?
		if ( $this->request->post('commit') !== NULL )
		{
			$this->go( 'layout' );
		}
		else
		{
			$this->go_back();
		}
	}

	public function action_delete( )
	{
		$this->auto_render = FALSE;
		$layout_name = $this->request->param('id');

		$layout = new Model_File_Layout( $layout_name );

		// find the user to delete
		if ( ! $layout->is_used() )
		{
			if ( $layout->delete() )
			{
				Messages::success( __( 'Layout has been deleted!' ) );
				Observer::notify( 'layout_after_delete', array( $layout_name ) );
			}
			else
			{
				Messages::errors( __( 'Something went wrong!' ) );
			}
		}
		else
		{
			Messages::errors( __( 'Layout is used! It CAN NOT be deleted!' ) );
		}

		$this->go_back();
	}

}

// end LayoutController class