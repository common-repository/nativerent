<?php
/**
 * @var \NativeRent\Admin\Views\Layout $view
 */

?>
<div class="wrap">
	<div class="card NativeRentAdmin_header">
		<img src="https://nativerent.ru/img/logo.svg"
			 class="NativeRentAdmin_logo"
			 title="Native Rent"
			 alt="Native Rent"/>
	</div>
	<div class="card NativeRentAdmin_container">
		<h1><?php esc_html_e( 'Интеграция с платформой Native Rent', 'nativerent' ); ?></h1>
		<div class="NativeRentAdmin_container-content">
			<?php nrentview_e( $view->content ); ?>
		</div>
	</div>
	<?php if ( $view->withFooter ) : ?>
		<div class="card NativeRentAdmin_footer">
			<form action="<?php echo esc_attr( nrentroute( 'logout' )->path ); ?>"
				  method="post"
				  id="NativeRentAdmin_logoutForm"
			>
				<?php wp_nonce_field( 'nrent_logout' ); ?>
				<input type="submit" style="display: none"/>
				<a href="javascript://" id="NativeRent_logoutButton">
					<?php esc_html_e( 'Прекратить работу плагина', 'nativerent' ); ?>
				</a>
				<?php esc_html_e( '(отключиться от платформы Native Rent)', 'nativerent' ); ?>
			</form>

			<script>
				(function () {
					var deactivatePluginButton = document.getElementById('NativeRent_logoutButton')
					if (deactivatePluginButton) {
						deactivatePluginButton.addEventListener('click', function () {
							if (confirm('Прекратить работу плагина?')) {
								document.getElementById('NativeRentAdmin_logoutForm').submit()
							}
						})
					}
				})()
			</script>
		</div>
	<?php endif; ?>
</div>
