<?php
/**
 * @var NativeRent\Admin\Views\Settings $view
 */

use NativeRent\Common\Entities\AdUnitProps;

?>

<form method="post"
	  action="<?php echo esc_attr( $view->actionURL ); ?>"
	  id="NativeRentAdmin_settingsForm"
	  onchange="NativeRentAdmin_submitEnable( this )"
>
	<?php wp_nonce_field( 'nrent_settings' ); ?>
	<div class="NativeRentAdmin_description">
		<?php
		esc_html_e(
			'Выберите места на страницах статей, где будет выводиться реклама. Можно указать селектор, который будет показывать место на странице для блока, или использовать преднастроенные селекторы параграфов (p).',
			'nativerent'
		);
		?>
	</div>
	<?php if ( $view->regularSettings ) : ?>
		<div class="NativeRentAdmin_settings-section">
			<h2><?php echo esc_html__( 'Коды вставки аренды статей', 'nativerent' ); ?></h2>
			<table class="form-table">
				<tbody>
				<tr>
					<td colspan="2" class="NativeRentAdmin_description">
						<div class="NativeRentAdmin_description">
							<?php
							esc_html_e(
								'Расположение блока влияет на эффективность рекламных кампаний. Рекламодатели выбирают сайты, у которых выше видимость блоков и CTR.',
								'nativerent'
							);
							?>
							<br/>
							<?php
							esc_html_e(
								'В длинных статьях, где расстояние между блоками очень большое, автоматически могут быть встроены до двух дополнительных блоков, дублирующих верхний или нижний блок.',
								'nativerent'
							);
							?>
						</div>
					</td>
				</tr>
				<?php foreach ( $view->adUnitsConfig->regular as $_unit => $_props ) : ?>
					<tr>
						<th scope="row" class="NativeRentAdmin_shortName">
							<?php echo esc_html( $view->labels[ $_unit ]['title'] ); ?>
						</th>
						<td class="forminp forminp-text NativeRentAdmin_placementSettings"
							data-unit-type="regular"
							data-unit-id="<?php echo esc_attr( $_unit ); ?>"
						>
							<?php if ( 'popupTeaser' === $_unit ) : ?>
								<input type="hidden" name="nrent[adUnitsConfig][regular][popupTeaser][settings][_]" value="1">
								<?php foreach ( $view->labels[ $_unit ]['settings'] as $opt => $optLabel ) : ?>
									<label for="NativeRentAdmin_adUnitsConfig_teaser_<?php echo esc_attr( $opt ); ?>">
										<input type="checkbox"
											   id="NativeRentAdmin_adUnitsConfig_teaser_<?php echo esc_attr( $opt ); ?>"
											   name="nrent[adUnitsConfig][regular][popupTeaser][settings][<?php echo esc_attr( $opt ); ?>]"
											   value="1"
											<?php echo( $_props->settings[ $opt ] ? 'checked="checked"' : '' ); ?>
										/>
										<?php echo esc_html( $optLabel ); ?>
									</label>
									<br/>
								<?php endforeach; ?>
							<?php else : ?>
								<?php
								$view->showPlacementSelectors( $_props, "nrent[adUnitsConfig][regular][{$_unit}]" );
								?>
								<p class="NativeRentAdmin_validationError"></p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="NativeRentAdmin_description">
							<div class="NativeRentAdmin_description">
								<?php if ( 'popupTeaser' === $_unit ) : ?>
									<p>
										<?php esc_html_e( 'Всплывающий блок показывается в трех форматах:', 'nativerent' ); ?>
									</p>
									<ul style="list-style-type: '- '; margin-left: 10px;">
										<li><?php esc_html_e( 'Тизер высотой до 100 пикселей для десктопа', 'nativerent' ); ?></li>
										<li><?php esc_html_e( 'Тизер высотой до 100 пикселей внизу экрана для мобильных', 'nativerent' ); ?></li>
										<li><?php esc_html_e( 'Фулскрин только для мобильных', 'nativerent' ); ?></li>
									</ul>
									<p>
										<?php
										esc_html_e(
											'При каждой загрузке страницы отображается только один из форматов всплывающего блока.',
											'nativerent'
										);
										?>
									</p>
								<?php else : ?>
									<?php echo esc_html( $view->labels[ $_unit ]['description'] ); ?>
								<?php endif; ?>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
				<tr>
					<td colspan="2" class="NativeRentAdmin_description"><?php $view->showDemoUnitsPrompt(); ?></td>
				</tr>
				</tbody>
			</table>
		</div>
	<?php endif; ?>

	<?php if ( $view->ntgbSettings ) : ?>
		<?php
		$_activeNtgbCount = count( $view->adUnitsConfig->ntgb->getActiveUnits() );
		?>
		<div class="NativeRentAdmin_settings-section" id="NativeRentAdmin_settings-section-ntgb">
			<h2><?php echo esc_html__( 'Коды вставки неуникального текстово-графического блока (НТГБ)', 'nativerent' ); ?></h2>
			<table class="form-table">
				<tbody>
				<tr>
					<td colspan="2" class="NativeRentAdmin_description">
						<div class="NativeRentAdmin_description">
							<?php
							esc_html_e(
								'Не размещайте блок НТГБ вплотную с другими рекламными блоками. При показе этого блока реклама других сетей блокироваться не будет.',
								'nativerent'
							);
							?>
						</div>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Выводить максимум', 'nativerent' ); ?></th>
					<td class="forminp forminp-text">
						<input type="number" min="1" max="3"
							   id="NativeRentAdmin_ntgbUnitsNum"
							   value="<?php echo esc_attr( $_activeNtgbCount ); ?>">
						<span class="_label">
						<?php if ( $_activeNtgbCount < 2 ) : ?>
							<?php esc_html_e( 'блок на странице', 'nativerent' ); ?>
						<?php else : ?>
							<?php esc_html_e( 'блока на странице', 'nativerent' ); ?>
						<?php endif; ?>
						</span>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="NativeRentAdmin_description">
						<div class="NativeRentAdmin_description">
							<?php
							esc_html_e(
								'Между размещенными блоками на странице должно быть расстояние не менее 500 пикселей.',
								'nativerent'
							);
							?>
						</div>
					</td>
				</tr>

				<?php $_ntgbUnitNum = 0; ?>
				<?php foreach ( $view->adUnitsConfig->ntgb->getIterable() as $_unit => $_props ) : ?>
					<?php
					/**
					 * @var string      $_unit
					 * @var AdUnitProps $_props
					 */

					$_ntgbUnitNum++;
					$_inactiveClass = ! empty( $_props->settings['inactive'] ) ? ' ntgb-config-item-inactive' : '';
					$_noInsertion = ! empty( $_props->settings['noInsertion'] );
					?>

					<?php if ( $_ntgbUnitNum > 1 ) : ?>
						<tr class="ntgb-config-item<?php echo esc_attr( $_inactiveClass ); ?>"
							data-unit-num="<?php echo esc_attr( $_ntgbUnitNum ); ?>"
						>
							<td colspan="2" style="padding: 0">
								<hr class="NativeRentAdmin_hrSep"/>
							</td>
						</tr>
					<?php endif; ?>

					<tr class="NativeRentAdmin_placementSettings ntgb-config-item<?php echo esc_attr( $_inactiveClass ); ?>"
						data-unit-num="<?php echo esc_attr( $_ntgbUnitNum ); ?>"
						data-unit-type="ntgb"
						data-unit-id="<?php echo esc_attr( $_ntgbUnitNum ); ?>"
					>
						<th scope="row" class="NativeRentAdmin_shortName">
							<?php echo esc_html( $view->labels['ntgb']['title'] . ' ' . $_ntgbUnitNum ); ?>
						</th>
						<td class="forminp forminp-text" id="ntgb-config-placement-selectors-<?php echo esc_attr( $_ntgbUnitNum ); ?>">
							<?php $view->showPlacementSelectors( $_props, "nrent[adUnitsConfig][ntgb][{$_unit}]", $_noInsertion ); ?>
							<p class="NativeRentAdmin_validationError"></p>
						</td>
					</tr>

					<tr class="NativeRentAdmin_noInsertionFlag ntgb-config-item<?php echo esc_attr( $_inactiveClass ); ?>"
						data-unit-num="<?php echo esc_attr( $_ntgbUnitNum ); ?>"
					>
						<th scope="row" class="NativeRentAdmin_shortName">
							<?php echo esc_html( $view->labels['ntgb']['settings']['noInsertion'] ); ?>
						</th>
						<td class="forminp forminp-text">
							<input type="checkbox"
								   name="nrent[adUnitsConfig][ntgb][<?php echo esc_attr( $_unit ); ?>][settings][noInsertion]"
								   onchange="NativeRentAdmin_changeNoInsertionFlag(this, <?php echo esc_attr( $_unit ); ?>)"
								   value="1"
								<?php echo( $_noInsertion ? 'checked="checked"' : '' ); ?>
							/>
						</td>
					</tr>

					<tr class="ntgb-config-item<?php echo esc_attr( $_inactiveClass ); ?>"
						data-unit-num="<?php echo esc_attr( $_ntgbUnitNum ); ?>"
						data-unit-id="<?php echo esc_attr( $_ntgbUnitNum ); ?>"
					>
						<th scope="row" class="NativeRentAdmin_shortName">
							<?php echo esc_html( $view->labels['ntgb']['settings']['fallbackCode']['title'] ); ?>
						</th>
						<td class="forminp forminp-text">
							<input type="hidden"
								   class="ntgb-config-item-inactive-input"
								   name="nrent[adUnitsConfig][ntgb][<?php echo esc_attr( $_unit ); ?>][settings][inactive]"
								   value="<?php echo esc_attr( ! empty( $_props->settings['inactive'] ) ? 1 : 0 ); ?>"/>
							<div class="NativeRentAdmin_fallbackCodeArea">
								<?php
								$_fallbackCode = @$_props->settings['fallbackCode'];
								if ( empty( $_fallbackCode ) ) {
									$_fallbackCode = '';
								}
								?>
								<textarea
									name="nrent[adUnitsConfig][ntgb][<?php echo esc_attr( $_unit ); ?>][settings][fallbackCode]"
									class="large-text code NativeRentAdmin_fallbackCodeTextArea"
									<?php echo $_noInsertion ? 'disabled' : ''; ?>
								><?php echo esc_textarea( base64_decode( $_fallbackCode ) ); ?></textarea>
								<p class="NativeRentAdmin_validationError"></p>
								<p class="NativeRentAdmin_inputDescription">
									<?php echo esc_html( $view->labels['ntgb']['settings']['fallbackCode']['description'] ); ?>
								</p>
							</div>
						</td>
					</tr>

					<tr>
						<td colspan="2" class="NativeRentAdmin_description">
							<div class="NativeRentAdmin_description">
								<?php echo esc_html( $view->labels['ntgb']['description'] ); ?>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
				<tr>
					<td colspan="2" class="NativeRentAdmin_description"><?php $view->showDemoUnitsPrompt( true ); ?></td>
				</tr>
				</tbody>
			</table>
		</div>
	<?php endif; ?>

	<input type="submit"
		   value="<?php esc_html_e( 'Применить', 'nativerent' ); ?>"
		   class="button button-primary"
		   style="vertical-align: middle; margin-right: 10px"
		   disabled
	/>
	<script>
		(function () {
			if (typeof NativeRentAdmin_updateSelectors === 'function') {
				NativeRentAdmin_updateSelectors()
			} else {
				document.addEventListener('DOMContentLoaded', NativeRentAdmin_updateSelectors)
			}
		})();
	</script>
</form>
