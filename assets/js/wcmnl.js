jQuery( function ( $ ) {
    var phoneInput = {
        isVerified: true,

        phoneNumber: '',

        email_selectors: 'form.register input#reg_email',

        input_selectors: 'form.register.woocommerce-form-register input#reg_phone, ' +
            'form.edit-account input#account_phone, ' +
            'form.checkout.woocommerce-checkout input#billing_phone, ' +
            'form.checkout.woocommerce-checkout input#shipping_phone',

        init: function() {
            $( document.body )
                .on( 'click', '.toggle-phone', this.hide_email )
                .on( 'click', '.toggle-email', this.hide_phone )
                .on( 'click', 'form.register button[type="submit"], button#place_order', this.click_submit )
                .on( 'change', this.input_selectors, function () {
                    phoneInput.isVerified = false;
                });

            $( this.input_selectors ).each( function ( index, input ) {
                if( $( this ).attr( 'name' ) != '' ) {
                    window.intlTelInput( this, {
                        formatOnDisplay: false,
                        allowDropdown: true,
                        preferredCountries: ['MM'],
                        autoPlaceholder: 'aggressive',
                        placeholderNumberType: 'MOBILE',
                        initialCountry: 'MM',
                        nationalMode: true,
                        hiddenInput: 'full_' + $( this ).attr( 'name' ),
                        autoHideDialCode: false,
                        utilsScript: wcmnl_params.utilsScript,
                    } );
                }
            } );

            if( $( 'form.register input#reg_phone' ).val() != '' ) {
                $( phoneInput.email_selectors ).attr( 'name', '' ).closest( 'p' ).attr( 'hidden', 'hidden' );
            }

            if( typeof wcmnl_params.firebaseConfig !== 'undefined' && typeof firebase !== 'undefined' ) {
                console.log(wcmnl_params);
                $( 'form.register button[type="submit"]' ).attr( 'id', 'wcmnl_submit' );
                if( firebase.apps.length === 0 ) {
                    firebase.initializeApp( wcmnl_params.firebaseConfig );
                    firebase.auth();
                }
            }
        },

        click_submit: ( event ) => {
            if( typeof wcmnl_params.firebaseConfig === 'undefined' || typeof firebase === 'undefined' || phoneInput.isVerified || $( 'input[name="email"]' ).length || ( $( 'input#createaccount' ).length && $( 'input#createaccount:checked' ).length == 0 ) ) {
                return;
            }

            var input  = event.target.getAttribute( 'id' ) === 'place_order' ? document.getElementById( 'billing_phone' ) : document.getElementById( 'reg_phone' );
                phoneNumber = window.intlTelInputGlobals.getInstance( input ).getNumber();

            if( phoneNumber === phoneInput.phoneNumber ) {
                phoneInput.isVerified = true;
                return;
            }

            event.preventDefault();

            if( typeof window.recaptchaVerifier === 'undefined' ) {
                $( document.body ).append( '<div id="wcmnl_reCAPTCHA"></div>' );
                window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier( 'wcmnl_reCAPTCHA', {
                    'size': 'invisible',
                } );
    
                recaptchaVerifier.render().then( (widgetId) => {
                    window.recaptchaWidgetId = widgetId;
                } );                    
            }

            firebase.auth().signInWithPhoneNumber( phoneNumber, window.recaptchaVerifier )
                .then( function( confirmationResult ) {
                    window.confirmationResult = confirmationResult;
                    window.signingIn = false;
        
                    var code = window.prompt( 'Enter the verification code you received by SMS' );
                    if( code ) {
                        confirmationResult.confirm(code).then( function() {
                            phoneInput.isVerified = true;
                            event.target.click();
        
                        }).catch( function( error ) {
                            window.alert( 'Error while checking the verification code:\n\n' + error.code + '\n\n' + error.message );
                        });
                    }
                } )
                .catch( function( error ) {
                    // Error; SMS not sent
                    window.alert('Error during signInWithPhoneNumber:\n'
                        + error.code + '\n' + error.message);
                    window.signingIn = false;
                } );                
        },

        hide_email: function ( event ) {
            event.preventDefault();
            $( event.target ).attr( 'hidden', 'hidden' ).siblings( 'a' ).removeAttr( 'hidden' );
            $( phoneInput.email_selectors ).attr( 'name', '' ).closest( 'p' ).attr( 'hidden', 'hidden' );
            $( phoneInput.input_selectors ).attr( 'name', 'phone' ).closest( 'p' ).removeAttr( 'hidden' );
        },

        hide_phone: function ( event ) {
            event.preventDefault();
            $( event.target ).attr( 'hidden', 'hidden' ).siblings( 'a' ).removeAttr( 'hidden' );
            $( phoneInput.email_selectors ).attr( 'name', 'email' ).closest( 'p' ).removeAttr( 'hidden' );
            $( phoneInput.input_selectors ).attr( 'name', '' ).closest( 'p' ).attr( 'hidden', 'hidden' );
        }
        
    }

    phoneInput.init();
});