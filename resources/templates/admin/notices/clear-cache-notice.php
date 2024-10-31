<?php
/**
 * @var NativeRent\Admin\Views\CacheNoticeContent $view
 */

?>

<input type="hidden"
	   name="nrent_cache_notice"
	   data-action="<?php echo esc_attr( $view->action ); ?>"
	   data-action-method="<?php echo esc_attr( $view->actionMethod ); ?>"
	   value="1"
>
<p>Native Rent:
	<?php
	if ( 2 == $view->notice->cacheFlag ) {
		esc_html_e(
			'Если на сайте настроено кэширование, то для применения настроек сбросьте кэш.',
			'nativerent'
		);
	} elseif ( 3 == $view->notice->cacheFlag ) {
		esc_html_e(
			'Если на сайте настроено кэширование, то для отключения плагина сбросьте кэш.',
			'nativerent'
		);
	} else {
		esc_html_e(
			'Если на сайте настроено кэширование, то необходимо сбросить кэш.',
			'nativerent'
		);
	}
	?>
</p>
<?php if ( $view->clearButton ) : ?>
	<form class="nrent-clear-cache-form"
		  data-error-message="<?php esc_attr_e( 'Что-то пошло ни так, пожалуйста сбросьте кэш вручную', 'nativerent' ); ?>"
	>
		<p>
			<?php wp_nonce_field( 'nrent_clear-cache' ); ?>
			<input type="hidden" name="nrent_clear_cache" value="1">
			<button class="button button-primary" name="nrent_clear_cache_btn">
				<?php esc_html_e( 'Сбросить кэш', 'nativerent' ); ?>
			</button>
		</p>
	</form>
<?php endif; ?>
