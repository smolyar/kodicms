<?php echo Form::open(($action == 'edit')  ? 'snippet/edit/'. $snippet->name  : 'snippet/add/' . $snippet->name, array(
	'id' => 'snippetEditForm', 'class' => Bootstrap_Form::HORIZONTAL)); ?>

	<?php echo Form::hidden('token', Security::token()); ?>
	<?php echo Form::hidden('snippet_name', $snippet->name); ?>

	<div class="widget widget-nopad">
		<div class="widget-title">
			<div class="control-group">
				<label class="control-label title" for="snippetEditNamelabel"><?php echo __('Snippet name'); ?></label>
				<div class="controls">
					<div class="row-fluid">
					<?php echo Form::input('name', $snippet->name, array(
						'class' => 'slug focus span12 input-title', 'id' => 'snippetEditNamelabel',
						'tabindex'	=> 1
					)); ?>
					</div>
				</div>
			</div>
		</div>
		<div class="widget-header widget-inverse">
			<h4><?php echo __('Content'); ?></h4>
			
			<?php if( $snippet->is_writable()): ?>
			<?php echo UI::button(__('File manager'), array(
				'class' => 'btn btn-filemanager', 'data-el' => 'textarea_content',
				'icon' => UI::icon( 'folder-open')
			)); ?>
			<?php endif; ?>
		</div>
		<div class="widget-content">
			<?php echo Form::textarea('content', $snippet->content, array(
				'id'			=> 'textarea_content',
				'tabindex'		=> 2,
				'data-height'	=> 600,
				'data-readonly'		=> ( ! $snippet->is_exists() OR ($snippet->is_exists() AND $snippet->is_writable())) ? 'off' : 'on'
			)); ?>
		</div>
		<?php if( ! $snippet->is_exists() OR ($snippet->is_exists() AND $snippet->is_writable())): ?>
		<div class="form-actions widget-footer">
			<?php echo UI::actions($page_name); ?>
		</div>
		<?php endif; ?>
	</div>
<?php echo Form::close(); ?>
<!--/#snippetEditForm-->