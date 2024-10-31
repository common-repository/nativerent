<?php
/**
 * @var \NativeRent\Admin\Views\NoticeLayout $view
 */

$_dismissible_class = ! empty( $view->notice->getOptions()['dismissible'] )
	? 'is-dismissible'
	: '';

?>

<div class="notice notice-<?php echo esc_attr( $view->notice->getLevel() ); ?> <?php echo esc_attr( $_dismissible_class ); ?>">
	<?php /** TODO: небезопасно и нарушает правила WordPress.Security */ ?>
	<?php echo $view->notice->getContent(); // phpcs:ignore ?>
</div>
