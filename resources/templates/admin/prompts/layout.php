<?php
/**
 * @var \NativeRent\Admin\Views\PromptLayout $view
 */

$_icons = [
	'info'    => 'info-outline',
	'warning' => 'warning',
];
?>
<div class="NativeRentAdmin_notice NativeRentAdmin_notice-<?php echo esc_attr( $view->prompt->getPromptType() ); ?>">
	<div class="NativeRentAdmin_notice-icon">
		<span class="dashicons dashicons-<?php echo esc_attr( @$_icons[ $view->prompt->getPromptType() ] ); ?>"></span>
	</div>
	<div class="NativeRentAdmin_notice-content">
		<?php if ( ! empty( $view->prompt->getTitle() ) ) : ?>
			<div class="NativeRentAdmin_notice-title"><?php echo esc_html( $view->prompt->getTitle() ); ?></div>
		<?php endif; ?>
		<div class="NativeRentAdmin_notice-body">
			<?php nrentview_e( $view->prompt ); ?>
		</div>
	</div>
</div>
