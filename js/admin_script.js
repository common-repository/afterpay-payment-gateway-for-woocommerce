( function ( $ ) {
    $(document).ready(function() {

        var offer_demo = document.getElementById('offer_element_id');
        var invoice_demo = document.getElementById('invoice_element_id');

        if(offer_demo !== null && invoice_demo !== null)
        {
            var offer_language = offer_demo.getAttribute("data-value");
            var invoice_language = invoice_demo.getAttribute("data-value");
        }

        $('.offer_element_demo').html('<afterpay-offer merchant="0000" language="' + offer_language + '" theme="default" amount="100"></afterpay-offer>');
        $('.invoice_element_demo').html('<afterpay-invoice language="'+ invoice_language + '" theme="default"></afterpay-invoice>');
        
        $('#woocommerce_afterpay_elements_settings_enable').change(function (event) {
            var value = $('#woocommerce_afterpay_elements_settings_enable').val();
            if(value === 'yes')
            {
                $('.afterpay_elements_advanced_settings').closest( 'tr' ).show();
            }
            else if(value === 'no')
            {
                $('.afterpay_elements_advanced_settings').closest( 'tr' ).hide();
            }
        }).change();

        $('#woocommerce_afterpay_elements_settings_show_offer_element').change(function (event) {
            var offer_theme = $('#woocommerce_afterpay_elements_settings_show_offer_element').val();
            var offer_demo = document.getElementById('offer_element_id');
            var offer_language = offer_demo.getAttribute("data-value");
            var afterpay_offer_tag = document.querySelector("afterpay-offer");

            if(offer_theme === 'no')
            {
                $('#woocommerce_afterpay_elements_settings_show_invoice_element').prop('disabled', false);
                $('.offer_element_example').hide();
                $('.offer_element_demo').closest( 'span' ).hide();
            }
            else
            {
                $('#woocommerce_afterpay_elements_settings_show_invoice_element').prop('disabled', true);
                $('.offer_element_example').show();
                $('.offer_element_demo').closest( 'span' ).show();
                afterpay_offer_tag.setAttribute('language', offer_language);
                afterpay_offer_tag.setAttribute('theme', offer_theme);
            }
        }).change();

        $('#woocommerce_afterpay_elements_settings_show_invoice_element').change(function (event) {
            var invoice_theme = $('#woocommerce_afterpay_elements_settings_show_invoice_element').val();
            var invoice_demo = document.getElementById('invoice_element_id');
            var invoice_language = invoice_demo.getAttribute("data-value");
            var afterpay_invoice_tag = document.querySelector("afterpay-invoice");

            if(invoice_theme === 'no')
            {
                $('#woocommerce_afterpay_elements_settings_show_offer_element').prop('disabled', false);
                $('.invoice_element_example').hide();
                $('.invoice_element_demo').closest( 'span' ).hide();
            }
            else
            {
                $('#woocommerce_afterpay_elements_settings_show_offer_element').prop('disabled', true);
                $('.invoice_element_example').show();
                $('.invoice_element_demo').closest( 'span' ).show();
                afterpay_invoice_tag.setAttribute('language', invoice_language);
                afterpay_invoice_tag.setAttribute('theme', invoice_theme);
            }
        }).change();
    });
})(jQuery);