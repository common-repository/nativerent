<?php
/**
 * @var \NativeRent\Admin\Views\AuthForm $view
 */

?>

<form method="POST" action="<?php echo esc_attr( $view->actionURL ); ?>">
	<?php wp_nonce_field( 'nrent_auth' ); ?>
	<div class="NativeRentAdmin_description">
		<p>
			<?php
			esc_html_e(
				'Native Rent — платформа, объединяющая владельцев контентных сайтов и рекламодателей.',
				'nativerent'
			)
			?>
		</p>
		<p>
			<?php
			esc_html_e(
				'Владельцы сайтов подключаются к платформе, а рекламодатели выбирают статьи, где хотят разместить свою рекламу. Есть несколько способов монетизации:',
				'nativerent'
			);
			?>
		</p>
		<ul style="list-style-type: '- '; margin-left: 20px;">
			<li>
				<?php
				esc_html_e(
					'рекламодатель "арендует статью" целиком, вся остальная реклама на странице отключается;',
					'nativerent'
				);
				?>
			</li>
			<li>
				<?php
				esc_html_e(
					'рекламодатель выкупает только одно рекламное место на странице для своего неуникального текстово-графического блока.',
					'nativerent'
				);
				?>
			</li>
		</ul>

	</div>

	<h2 class="itemTitle">
		<?php
		esc_html_e(
			'Введите e-mail и пароль от вашего аккаунта на платформе Native Rent',
			'nativerent'
		);
		?>
	</h2>

	<?php if ( ! empty( $view->errors ) && isset( $view->errors[0] ) ) : ?>
		<div class="attention">
			<?php echo esc_html( $view->errors[0] ); ?>
		</div>
	<?php endif; ?>

	<table class="form-table">
		<tbody>
		<tr>
			<th class="NativeRentAdmin_veryShortName" scope="row">
				<?php esc_html_e( 'E-mail', 'nativerent' ); ?>
			</th>
			<td class="forminp forminp-text">
				<input type="email"
					   name="nrent_auth_login"
					   aria-required="true"
					   value="<?php echo esc_attr( $view->login ); ?>"
					   required="required"
					   autofocus
				/>
			</td>
		</tr>
		<tr>
			<th class="NativeRentAdmin_veryShortName" scope="row">
				<?php esc_html_e( 'Пароль', 'nativerent' ); ?>
			</th>
			<td class="forminp forminp-text">
				<input type="password"
					   name="nrent_auth_password"
					   aria-required="true"
					   required
				/>
			</td>
		</tr>
		</tbody>
	</table>

	<p class="submit">
		<input type="submit"
			   value="<?php esc_attr_e( 'Подключиться', 'nativerent' ); ?>"
			   class="button button-primary"
		/>
	</p>
	<p>
		<?php esc_html_e( 'Забыли пароль?', 'nativerent' ); ?>
		<a href="https://nativerent.ru/password/reset" target="_blank">
			<?php esc_html_e( 'Восстановить', 'nativerent' ); ?>
		</a>
	</p>
	<p>
		<a href="https://nativerent.ru/register/partner" target="_blank">
			<?php esc_html_e( 'Регистрация на платформе Native Rent', 'nativerent' ); ?>
		</a>
	</p>
</form>

