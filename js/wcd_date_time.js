jQuery(document).ready(function($) {

    jQuery('#wcd_dd').change(function(){
        var date = jQuery(this).val();

        //Handling delivery time
        var html_time = '<option value="">Select delivery Time</option>';

        var i = 0;

        jQuery('.hidden_delivery_time').find('option').each(function(){
            var t = jQuery(this).html();
            if(jQuery(this).hasClass( date )){
                html_time += '<option value="'+t+'">'+t+'</option>';
            }
        });

        jQuery('#wcd_dt option').attr('selected', false);
        jQuery('#wcd_dt').html(html_time);
    });
});