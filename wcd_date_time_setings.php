<?php
	if(sanitize_text_field($_POST['oscimp_hidden']) == 'Y') {
		//Form data sent
		
		//getting date format
		$wcd_settings['date_format'] = $date_format = sanitize_text_field($_POST['date_format']);
        update_option('wcd_settings',$wcd_settings);

		// getting selected dates and times
		$dd = $_POST['delivery_date'];
		$options_value = array();
		if(!empty($dd)){
			$N = count($dd);
			for($i=0; $i < $N; $i++)
			{
				$options_value[$i]['date'] = $dd[$i];
				$s_time = $dd[$i].'starting_time';
				$c_time = $dd[$i].'closing_time';
				$options_value[$i]['s_time'] = sanitize_text_field($_POST[$s_time]);
				$options_value[$i]['c_time'] = sanitize_text_field($_POST[$c_time]);
			}
		}
		update_option('wcd_date_time',$options_value);
        $revised_date = make_revised_date($options_value);
	?>
	<div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>
	<?php
    } else {
        //Normal page display
        $date_f = get_option('wcd_settings');
        if(!empty($date_f)){
            $date_format = $date_f['date_format'];
        }

        $options_value = get_option('wcd_date_time');
        if(!empty($options_value)){
            $revised_date = make_revised_date($options_value);
        }
    }

    function make_revised_date($options_value){
        $r_a = array();
        foreach($options_value as $ov){
            $r_a[$ov['date']] = $ov['date'];
            $r_a[$ov['date'].'starting_time'] = $ov['s_time'];
            $r_a[$ov['date'].'closing_time'] = $ov['c_time'];
        }
        return $r_a;
    }
?>
<div class="wrap">
    <?php    echo "<h2>" . __( 'Woocommerce Customer Delivery Date and Time Settings', 'wcd_date_time' ) . "</h2>"; ?>
     
    <form name="wcd_date_time_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <input type="hidden" name="oscimp_hidden" value="Y">
        <?php    echo "<h3>" . __( 'System Settings', 'wcd_date_time' ) . "</h3>"; ?>
		<p class="submit" style="margin-top: -4%;width: 100%;">
        <input style="color: #fff; background-color: green;float: right;" type="submit" name="Submit" value="<?php _e('Update Options', 'wcd_date_time' ) ?>" />
        </p>
        <p><?php _e("Date Format: " ); ?>
			<select data-placeholder="" name="date_format" id="df" class="country_select">
				<option value="">Select Date Format</option>
				<option value="d-m-Y" <?php if($date_format=='d-m-Y')echo 'selected';?>>31-12-2015 (day-month-year)</option>
				<option value="m-d-Y" <?php if($date_format=='m-d-Y')echo 'selected';?>>12-31-2015 (month-day-year)</option>
				<option value="Y-d-m" <?php if($date_format=='Y-d-m')echo 'selected';?>>2015-31-12 (year-day-month)</option>
				<option value="Y-m-d" <?php if($date_format=='Y-m-d')echo 'selected';?>>2015-12-31 (year-month-day)</option>
			</select>
		</p>
        <hr />
        <?php    echo "<h3>" . __( 'Select your shop delivery date time Schedule ', 'wcd_date_time' ) . "</h3>"; ?>
			<p>NB: 30 days will be generated from current date</p>
			<?php
                date_default_timezone_set('UTC');
				for($i = 0;$i < 30; $i++){
					$date = date("d-m-Y", time() + 86400*$i);
					$formated_date = date('l jS \of F Y', time() + 86400*$i);

                    //Checking for Saved value
                    if (!empty($revised_date) && array_key_exists($date,$revised_date)){
                        $checked = ' checked';
                        $s_time = $date.'starting_time';
                        $c_time = $date.'closing_time';
                        if (array_key_exists($s_time,$revised_date)){
                            $st = $revised_date[$date.'starting_time'];
                        }else{
                            $st = '';
                        }
                        if (array_key_exists($c_time,$revised_date)){
                            $ct = $revised_date[$date.'closing_time'];
                        }else{
                            $ct = '';
                        }
                    }else{
                        $checked = '';
                        $st = '';
                        $ct = '';
                    }
					echo '<div style="width: 100%;float: left;margin-top: 2%;">';
					echo '<div style="width:33%;float:left;padding-top: 0.6%;">
						<input type="checkbox" name="delivery_date[]" value="'.$date.'"'.$checked.'> '.$formated_date.'
					</div>';
					echo '<div style="width:33%;float:left;">
						Starting Time : <input type="text" name="'.$date.'starting_time" value="'.$st.'" placeholder="24 hour format (10:30)">
					</div>';
					echo '<div style="width:33%;float:left;">
						Closing Time : <input type="text" name="'.$date.'closing_time" value="'.$ct.'" placeholder="24 hour format (20:00)">
					</div>';
					echo '</div>';
				}
			?>
			
        <p class="submit" style="margin-top: 2%;width: 100%;float: left;">
        <input style="color: #fff; background-color: green;" type="submit" name="Submit" value="<?php _e('Update Options', 'wcd_date_time' ) ?>" />
        </p>
    </form>
</div>