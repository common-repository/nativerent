<?php
/**
 * @var NativeRent\Admin\Views\PromptDemoUnits $view
 */

$_nrdemo = ( '?_nrdemo=' . ( $view->ntgb ? 3 : 1 ) );
?>
<p>
	<?php
	esc_html_e(
		'Проверить отображение рекламных блоков можно на любой странице, где есть коды, добавив к ссылке параметр',
		'nativerent'
	);
	?>
	<code><?php echo esc_html( $_nrdemo ); ?></code>
	<?php if ( ! empty( $view->pageURL ) ) : ?>
		(<a href="<?php echo esc_attr( $view->pageURL . $_nrdemo ); ?>" target="_blank"><?php esc_html_e( 'пример', 'nativerent' ); ?></a>)
	<?php endif; ?>
</p>
