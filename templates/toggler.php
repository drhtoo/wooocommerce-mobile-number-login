<p>
    <a href="#" class="toggle toggle-email" <?php echo empty( $_POST['email'] ) ? 'hidden' : ''; ?>>
        <?php echo __( 'Register with email', 'jituushop-wcmnl' ); ?>
    </a>

    <a href="#" class="toggle toggle-phone" <?php echo empty( $_POST['email'] ) ? '' : 'hidden'; ?>>
        <?php echo __( 'Register with phone number', 'jituushop-wcmnl' ); ?>
    </a>
</p>