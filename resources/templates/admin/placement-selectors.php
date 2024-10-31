<?php
/**
 * @var NativeRent\Admin\Views\PlacementSelectors $view
 */

?>
<select name="<?php echo esc_attr( $view->baseFormsName . '[insert]' ); ?>"
		class="NativeRentAdmin_insertChange"
		onchange="NativeRentAdmin_insertChange( this, '<?php echo esc_attr( $view->baseFormsName . '[autoSelector]' ); ?>' )"
	<?php echo( $view->disabled ? 'disabled' : '' ); ?>
>
	<?php
	$selected = ! empty( $view->adUnitProps->insert ) ? $view->adUnitProps->insert : '';
	foreach ( $view->adUnitInsertOptions() as $key => $opt ) {
		if ( $selected === $key ) {
			echo '<option value="' . esc_attr( $key ) . '" selected="selected">' . esc_html( $opt ) . '</option>';
		} else {
			echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $opt ) . '</option>';
		}
	}
	?>
</select>
<select name="<?php echo esc_attr( $view->baseFormsName . '[autoSelector]' ); ?>"
		class="NativeRentAdmin_autoSelector"
		onchange="NativeRentAdmin_autoSelectorChanged( this, '<?php echo esc_attr( $view->baseFormsName . '[customSelector]' ); ?>' )"
	<?php echo( $view->disabled ? 'disabled' : '' ); ?>
>
	<?php
	$selected = ! empty( $view->adUnitProps->autoSelector ) ? $view->adUnitProps->autoSelector : '';
	foreach ( $view->adUnitSelectorOptions() as $key => $opt ) {
		if ( $selected === $key ) {
			echo '<option value="' . esc_attr( $key ) . '" selected="selected"></option>';
		} else {
			echo '<option value="' . esc_attr( $key ) . '"></option>';
		}
	}
	?>
</select>
<input type="text"
	   name="<?php echo esc_attr( $view->baseFormsName . '[customSelector]' ); ?>"
	   class="NativeRentAdmin_customSelector"
	   value="<?php echo esc_attr( $view->adUnitProps->customSelector ); ?>"
	   placeholder="<?php esc_attr_e( 'пример: h3', 'nativerent' ); ?>"
	<?php echo( $view->disabled ? 'disabled' : '' ); ?>
/>
