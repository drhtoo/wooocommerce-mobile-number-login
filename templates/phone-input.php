<?php defined( 'ABSPATH' ) || exit; ?>

<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide" <?php echo empty( $_POST['phone'] ) ? 'hidden' : ''; ?> >
    <label for="reg_phone"><?php esc_html_e( 'Phone number', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
    <input type="tel" class="woocommerce-Input woocommerce-Input--text input-text" name="phone" id="reg_phone" value="<?php echo ( isset( $_POST['phone'] ) && ! empty( $_POST['phone'] ) ) ? esc_attr( wp_unslash( $_POST['phone'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
</p>

<?php 