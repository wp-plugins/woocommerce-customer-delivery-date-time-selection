<?php 

/*
Plugin Name: Woocommerce customer delivery date time selection
Plugin URI: http://myplugin.tophostbd.com/woocommerce-customer-delivery-date-and-time-selection/
Description: Customer will be able to select date and time for delivery they expect .
Author: Md. Muntasir Rahman Rafi
Version: 1.0
Author URI: http://myplugin.tophostbd.com/
Contributor: Application Village Bangladesh .
*/

$wpefield_version = '1.0';


add_action('woocommerce_after_order_notes', 'wcd_custom_checkout_action');

function wcd_custom_checkout_action( $checkout ) {

    wp_enqueue_script(
		'wcd_date_time.js',
		plugins_url('/js/wcd_date_time.js', __FILE__),
		'',
		'',
		false
	);

    wp_enqueue_style( 'wcd_date_time', plugins_url('/css/wcd_date_time.css', __FILE__) , '', '', false);

    echo '<div class="hidden_delivery_time">'.generate_delivery_time().'</div>';
	
	echo '<div id="my_custom_checkout_field" style="width: 100%; float: left;">';

    //Custom checkout field for delivery date
    woocommerce_form_field( 'wcd_dd', array(
        'type'          => 'select',
        'class'         => array('wcd_cdd form-row-first country_select'),
        'required' 		=> true,
        'label'         => __('Delivery Date'),
        'options'     => generate_delivery_date()
    ), $checkout->get_value( 'wcd_dd' ));

    //Custom checkout field for delivey time
    woocommerce_form_field( 'wcd_dt', array(
        'type'          => 'select',
        'class'         => array('wcd_cdt form-row-last country_select'),
        'required' 		=> true,
        'label'         => __('Delivery Time'),
        'options'     => array(
            '' => __('Select Delivery Time', 'woocommerce' )
        )
    ), $checkout->get_value( 'wcd_dt' ));

	echo '</div>';
}

function get_date_format(){
    //get settings for date format
    $date_f = get_option('wcd_settings');
    $date_format = $date_f['date_format'];
    return $date_format;
}

function generate_delivery_date(){
    $options_value = get_option('wcd_date_time');
    $formatted_dd = array(''=>'Select Delivery Date');

    //get settings for date format
    $date_f = get_option('wcd_settings');
    $date_format = $date_f['date_format'];

    date_default_timezone_set('UTC');
    $today = date($date_format);
    foreach($options_value as $ov){
        $old_date = trim($ov['date']);
        $old_date_timestamp = strtotime($old_date);
        $newDate = date($date_format, $old_date_timestamp);
        if($newDate>=$today)
            $formatted_dd[$newDate] = __($newDate, 'woocommerce' );
    }
    return $formatted_dd;
}

//add_action( 'wp_ajax_my_action', 'generate_delivery_time' );
//add_action( 'wp_ajax_nopriv_my_action', 'generate_delivery_time' );

function generate_delivery_time(){
    $options_value = get_option('wcd_date_time');
    $html = '';

    //get settings for date format
    $date_f = get_option('wcd_settings');
    $date_format = $date_f['date_format'];

    date_default_timezone_set('UTC');
    $today = date($date_format);

    foreach($options_value as $ov){

        $old_date = trim($ov['date']);
        $old_date_timestamp = strtotime($old_date);
        $newDate = date($date_format, $old_date_timestamp);

        if($newDate>=$today){
            $open = $ov['s_time'];
            $close = $ov['c_time'];

            //Checking for empty
            if(empty($open)){
                $open = '11:00';
            }
            if(empty($close)){
                $close = '22:00';
            }

            //Checking format for open
            $open_arr = explode(":", $open);
            $open_size = sizeof($open_arr);
            if($open_size<2){
                $open = $open . ':00';
            }
            //Checking format for close
            $close_arr = explode(":", $close);
            $close_size = sizeof($close_arr);
            if($close_size<2){
                $close = $close . ':00';
            }

            // Getting open close time difference
            $datetime1 = new DateTime($open);
            $datetime2 = new DateTime($close);
            $interval = $datetime1->diff($datetime2);
            $min = $interval->format('%i');
            $hour = $interval->format('%h');
            $total_min = $hour*60 + $min;
            $loop = $total_min / 15;
            for($l=0 ; $l <= $loop ; $l++){
                $time = date('h:i a', strtotime($open) + (900*$l));
                $html .= '<option class="'.$newDate.'" value="'.$time.'">'.$time.'</option>';
            }
            $i++;
        }
    }
    return $html;
}

// My custom field validation
add_action('woocommerce_checkout_process', 'wcd_my_field_processor');

function wcd_my_field_processor() {
    // Check if set, if its not set add an error.
    if ( ! sanitize_text_field($_POST['wcd_dd']) )
        wc_add_notice( __( 'Delivery Date is a required field.' ), 'error' );
    if ( ! sanitize_text_field($_POST['wcd_dt']) )
        wc_add_notice( __( 'Delivery Time is a required field.' ), 'error' );
}

// Update order meta to display in order review
add_action('woocommerce_checkout_update_order_meta', 'wcd_update_order_meta');

function wcd_update_order_meta( $order_id ) {
	if (sanitize_text_field($_POST['wcd_dd'])) {
		update_post_meta( $order_id, 'Delivery Date', esc_attr($_POST['wcd_dd']));
	}

    if (sanitize_text_field($_POST['wcd_dt'])) {
        update_post_meta( $order_id, 'Delivery Time', esc_attr($_POST['wcd_dt']));
    }
}

 /**
* This function is used for show delivery date in the email notification 
**/
add_filter('woocommerce_email_order_meta_keys', 'wcd_add_delivery_date_time',10,1);

function wcd_add_delivery_date_time( $keys )
{
    $keys[] = "Delivery Date";
    $keys[] = "Delivery Time";
    return $keys;
}
/**
 * This function are used for show custom column on order page listing. woo-orders
 * 
 */
add_filter( 'manage_edit-shop_order_columns', 'wcd_order_delivery_date_time_column', 20, 1 );
function wcd_order_delivery_date_time_column($columns){
    $new_columns = (is_array($columns)) ? $columns : array();
    unset( $new_columns['order_actions'] );

    //edit this for you column(s)
    //all of your columns will be added before the actions column
    $new_columns['order_delivery_date'] = 'Delivery Date'; //Title for column heading
    $new_columns['order_actions'] = $columns['order_actions'];
    return $new_columns;
}

/**
 * This fnction used to add value on the custom column created on woo- order
 * 
 */
add_action( 'manage_shop_order_posts_custom_column', 'wcd_custom_column_value', 20, 1 );
function wcd_custom_column_value($column){

    global $post;
    
    $data = get_post_meta( $post->ID );
    //if you did the same, follow this code
    if ( $column == 'order_delivery_date' ) {    
        echo (isset($data['Delivery Date'][0]) ? $data['Delivery Date'][0].'  '.$data['Delivery Time'][0] : '');
    }
}

// ************************ 8 ******************************

//Code to create the settings page for the plugin
add_action('admin_menu', 'wcd_add_admin_setting_page');
function wcd_add_admin_setting_page()
{
	add_menu_page( 'Woocommerce customer delivery date time','WCD Date Time','manage_options', 'wcd_date_time','wcd_date_time_setings','',6);
	add_option('wcd_date_time','');
    add_option('wcd_settings','');
}

function wcd_date_time_setings(){
	require_once('wcd_date_time_setings.php');
}
        
function wcd_custom_scripts($hook){

    wp_register_script( 'wcd_date_time', plugins_url() . '/woocommerce-customer-delivery-date-time/js/wcd_date_time.js', array(''));

}
add_action( 'admin_enqueue_scripts', 'wcd_custom_scripts' );

add_filter('woocommerce_order_details_after_order_table','wcd_add_delivery_date_to_order_page');

function wcd_add_delivery_date_to_order_page($order)
{
	$my_order_meta = get_post_custom( $order->id );
	if(array_key_exists('Delivery Date',$my_order_meta))
	{
		$order_page_delivery_date = $my_order_meta['Delivery Date'];
        $order_page_delivery_time = $my_order_meta['Delivery Time'];
		if ( $order_page_delivery_date != "" )
		{
			echo '<p><strong>'.__(('Delivery Date'),'order-delivery-date').':</strong> ' . $order_page_delivery_date[0] .'  '.$order_page_delivery_time[0]. '</p>';
		}
	}
 }

?>