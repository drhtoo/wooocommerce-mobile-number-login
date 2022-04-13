<?php 

defined( 'ABSPATH' ) || exit; 

?>

<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label for="account_phone">
        <?php esc_html_e( 'Phone number', 'woocommerce' ); ?>
        <!-- &nbsp;<span class="required">*</span> -->
    </label>
    <input type="tel" class="woocommerce-Input woocommerce-Input--email input-text" name="account_phone" id="account_phone" value="<?php echo esc_attr( get_user_meta( get_current_user_id(), 'phone_number', true ) ); ?>" />
</p>

<?php 