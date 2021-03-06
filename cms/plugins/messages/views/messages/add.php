<div class="map widget">
	<?php echo Form::open(NULL, array('class' => Bootstrap_Form::HORIZONTAL)); ?>
	<?php echo Form::hidden('token', Security::token()); ?>
	<div class="widget-title">
		<div class="control-group">
			<label class="control-label title"><?php echo __( 'Message title' ); ?></label>
			<div class="controls">
				<?php echo Form::input( 'title', NULL, array(
					'class' => 'span12 input-title focus'
				) ); ?>
			</div>
		</div>
		
		<br />
		
		<div class="control-group">
			<label class="control-label"><?php echo __( 'Message to' ); ?></label>
			<div class="controls">
				<?php echo Form::input( 'to', NULL, array(' autocomplete' => 'off') ); ?>
			</div>
		</div>
	</div>
	<div class="widget-content widget-no-border-radius widget-nopad">

		<?php echo Form::textarea('content', NULL, array('class' => 'span12', 'id' => 'message-conent')); ?>

		<script>
		$(function() {
			cms.filters.switchOn( 'message-conent', '<?php echo Setting::get('default_filter_id'); ?>');
		});
		</script>
				
	</div>
	<div class="widget-footer form-actions">
		<?php echo UI::button(__('Send message'), array('class' => 'btn btn-large')); ?>
	</div>
	<?php echo Form::close(); ?>
</div>