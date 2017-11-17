<?php



/**
 * Gravity Forms Bootstrap Styles
 *
 * Applies bootstrap classes to various common field types.
 * Requires Bootstrap to be in use by the theme.
 *
 * Using this function allows use of Gravity Forms default CSS
 * in conjuction with Bootstrap (benefit for fields types such as Address).
 *
 * Original git https://github.com/5t3ph/gravity-forms-snippets
 *
 * @see  gform_field_content
 * @link http://www.gravityhelp.com/documentation/page/Gform_field_content
 *
 * @return string Modified field content
 */

 	if(!is_admin()){
		add_filter("gform_field_content", "anagram_bootstrap_styles_for_gravityforms_fields", 10, 5);
		add_filter( 'gform_field_container', 'anagram_field_container', 10, 6 );
		add_filter("gform_submit_button", "anagram_form_submit_button", 10, 2);
		add_filter( 'gform_next_button', 'anagram_next_button_markup', 10, 2 );
		add_filter( 'gform_previous_button', 'anagram_previous_button_markup', 10, 2 );
		add_filter("gform_validation_message", "anagram_change_message", 10, 2);
		add_filter("gform_field_validation", "anagram_custom_validation", 10, 4);
		add_action("gform_enqueue_scripts", "anagram_enqueue_custom_script", 10, 2);

		//Address Address code
		//add_filter( 'gform_field_container', 'anagram_address_field_container', 11, 6 );
		//add_filter( 'gform_address_display_format', 'anagram_address_format' );

		//add_filter( 'gform_pre_render', 'add_input_type_gravity_forms' );
			//add_filter( 'gform_field_input', 'anagram_credit_card_field', 10, 5 );

	};

/*
function add_input_type_gravity_forms( $form ) {
	foreach ( $form['fields'] as $f => $field )
		$form['fields'][$f]['cssClass'] .= 'input-type-' . $field['type'];

	return $form;
}
*/



function anagram_credit_card_field( $input, $field, $value, $lead_id, $form_id ) {
    // because this will fire for every form/field, only do it when it is the specific form and field
    if ( $field["type"] == 'creditcard' ) {

				//mapi_var_dump($input);
        //$input = '<input type="hidden" id="hidTracker" name="hidTracker" value="test">';
    }
    return $input;
}


function anagram_address_format( $format ) {
    return 'zip_before_city';
}


function anagram_bootstrap_styles_for_gravityforms_fields($content, $field, $value, $lead_id, $form_id){
	// Currently only applies to most common field types, but could be expanded.
 // mapi_var_dump($field);
	if($field["type"] != 'hidden' && $field["type"] != 'list' && $field["type"] != 'multiselect' && $field["type"] != 'checkbox' && $field["type"] != 'fileupload' && $field["type"] != 'date' && $field["type"] != 'html' && $field["type"] != 'address') {
		$content = str_replace('class=\'medium', 'class=\'form-control medium', $content);
		$content = str_replace('class=\'large', 'class=\'form-control medium', $content);
		$content = str_replace('class=\'small', 'class=\'form-control medium', $content);
	}

	if($field["type"] == 'list') {
		$content = str_replace('<input ', '<input class=\'form-control\' ', $content);
	}

	if($field["type"] == 'multiselect') {
		$content = str_replace('<select ', '<select class=\'form-control\' ', $content);
	}

	if($field["type"] == 'fileupload') {

		$content = str_replace(' gform_button_select_files', ' gform_button_select_files btn btn-default btn-sm', $content);
	}

	if($field["type"] == 'name') {
		$content = str_replace('<input ', '<input class=\'form-control\' ', $content);
		//$content = str_replace('<select ', '<select class=\'form-control\' ', $content);
	}

	if( $field["type"] == 'address') {
			//mapi_var_dump($field);


		foreach($field["inputs"] as $key => $thefield ){
			//mapi_var_dump('name=\'input_'.$thefield["id"].'\' ');
			$content = str_replace('name=\'input_'.$thefield["id"].'\' ', 'name=\'input_'.$thefield["id"].'\'   class=\''.sanitize_title($thefield["label"]).' form-control\' ', $content);
			//Select mnth/year fiields
		};
		//$content = str_replace('<input ', '<input class=\'form-control\' ', $content);
		//$content = str_replace('<select ', '<select class=\'form-control\' ', $content);
	}

	if($field["type"] == 'textarea' || ($field["type"] == 'survey' && $field["inputType"] = 'textarea') ) {
		$content = str_replace('class=\'textarea', 'class=\'form-control textarea', $content);
	}

	if( ($field["type"] == 'survey' && $field["inputType"] = 'checkbox' && !empty($field["inputs"]) )  ) {
		//echo '<pre>';var_dump($field);echo '</pre>';
		$content = str_replace('li class=\'', 'li class=\'checkbox ', $content);
		$content = str_replace('<input ', '<input style=\'margin-left:1px;\' ', $content);
	}


	if( ($field["type"] == 'survey' && empty($field["inputType"])  ) ) {

		$content = str_replace('li class=\'', 'li class=\'radio ', $content);
		//$content = str_replace('type=\'radio\' ', 'type=\'radio\' style=\'margin-left:1px;\' ', $content);
		//$content = str_replace('type=\'text\' ', 'type=\'text\' style=\'margin-left:20px;\' ', $content);

	}

	if( $field["type"] == 'tos'  ) {
		if($field["cssClass"]=='display-inline'){
			$content = str_replace('li class=\'', 'li class=\'checkbox-inline ', $content);
		}else{
			$content = str_replace('li class=\'', 'li class=\'checkbox ', $content);
			//$content = str_replace('<input ', '<input style=\'margin-left:1px;\' ', $content);
		};

	}

	if( $field["type"] == 'gptos_terms'  ) {
		if($field["cssClass"]=='display-inline'){
			$content = str_replace('li class=\'', 'li class=\'checkbox-inline ', $content);
		}else{
			$content = str_replace('li class=\'', 'li class=\'checkbox ', $content);
			//$content = str_replace('<input ', '<input style=\'margin-left:1px;\' ', $content);
		};

	}

	if($field["type"] == 'radio' ) {

		if($field["cssClass"]=='display-inline'){
			$content = str_replace('li class=\'', 'li class=\'radio-inline ', $content);
		}else{
			$content = str_replace('li class=\'', 'li class=\'radio ', $content);
			//$content = str_replace('type=\'radio\' ', 'type=\'radio\' style=\'margin-left:1px;\' ', $content);
		};

		//this is for the other option
		//$content = str_replace('type=\'text\' ', 'type=\'text\' style=\'margin-left:20px;\' ', $content);

	}

	if($field["type"] == 'product' &&  ( $field["inputType"] =='radio' || $field["inputType"] =='checkbox' ) ) {

		if($field["cssClass"]=='display-inline'){
			$content = str_replace('li class=\'', 'li class=\'radio-inline ', $content);
		}else{
			$content = str_replace('li class=\'', 'li class=\'radio ', $content);
			//$content = str_replace('type=\'radio\' ', 'type=\'radio\' style=\'margin-left:1px;\' ', $content);
		};

		//this is for the other option
		//$content = str_replace('type=\'text\' ', 'type=\'text\' style=\'margin-left:20px;\' ', $content);

	}

	if($field["type"] == 'option' ) {

		if($field["cssClass"]=='display-inline'){
			$content = str_replace('li class=\'', 'li class=\'radio-inline ', $content);
		}else{
			$content = str_replace('li class=\'', 'li class=\'radio ', $content);
			//$content = str_replace('type=\'radio\' ', 'type=\'radio\' style=\'margin-left:1px;\' ', $content);
		};

		//this is for the other option
		//$content = str_replace('type=\'text\' ', 'type=\'text\' style=\'margin-left:20px;\' ', $content);

	}

	if($field["isRequired"] == true && !($field["type"] == 'checkbox' || $field["type"] == 'survey' || $field["type"] == 'radio'  ) ) {
		//$content = str_replace('<input ', '<input required="required" ', $content);
	}

	if($field["type"] == 'creditcard' ) {
		//mapi_var_dump($field);


		foreach($field["inputs"] as $key => $thefield ){
			//mapi_var_dump('name=\'input_'.$thefield["id"].'\' ');
			$content = str_replace('name=\'input_'.$thefield["id"].'\' ', 'name=\'input_'.$thefield["id"].'\'   class=\''.sanitize_title($thefield["label"]).' form-control\' ', $content);
			//Select mnth/year fiields
		};
			$content = str_replace('_month\' tab', '_month\' class=\'card_month form-control  col-xs-6\' tab', $content);
			$content = str_replace('_year\' tab', '_year\' class=\'card_year form-control  col-xs-6\' tab', $content);

		//$content = str_replace('<input ', '<input class=\' form-control\' ', $content);
		//$content = str_replace('<select ', '<select class=\'form-control col-xs-6\' ', $content);
	}




	return $content;

} // End bootstrap_styles_for_gravityforms_fields()


	//replace class for container block

        function anagram_field_container( $field_container, $field, $form, $class_attr, $style, $field_content ) {

            if($field["type"] == 'name') {
	            //echo '<pre>';var_dump($field);echo '</pre>';
				//$field_content = preg_replace('~<span(.*?) class=\'(.*?)\'>~i', '<span$1 class="col-sm-6">', $field_content);
				$field_content = str_replace('name_prefix_select', 'name_prefix_select col-sm-12', $field_content);
				if (strpos($field_content,'name_middle') !== false) {
					$field_content = str_replace('name_middle', 'name_middle col-xs-12 col-sm-4', $field_content);
					$field_content = str_replace('name_first', 'name_first col-xs-12 col-sm-4', $field_content);
					$field_content = str_replace('name_last', 'name_last col-xs-12 col-sm-4', $field_content);
				}else{
					$field_content = str_replace('name_first', 'name_first col-xs-12 col-sm-6', $field_content);
					$field_content = str_replace('name_last', 'name_last col-xs-12 col-sm-6', $field_content);
				}
				$field_content = str_replace('name_suffix ', 'name_suffix  col-xs-12 col-sm-1', $field_content);
			}
			if($field["type"] == 'address') {
	            //echo '<pre>';var_dump($field_content);echo '</pre>';
				//$field_content = preg_replace('~<span(.*?) class=(.*?)(.*?)(.*?)>~i', '<span$1 class="col-sm-6">', $field_content);
				$field_content = str_replace('ginput_full', 'ginput_full col-xs-12 col-sm-12', $field_content);
				$field_content = str_replace('ginput_left', 'ginput_left col-xs-12 col-sm-6', $field_content);
				$field_content = str_replace('ginput_right', 'ginput_right col-xs-12 col-sm-6', $field_content);
			}
			//For ajaxes address
/*
			if($field["type"] == 'address') {
	            //echo '<pre>';var_dump($field_content);echo '</pre>';
				$field_content = str_replace('<div class=\'ginput_complex ', '<div id="geocomplete_holder" class="form-group"><input id="geocomplete" tabindex="1013" class="form-control" type="text" placeholder="Type in an address" value="" /><span id="geo_error" class="help-block"></span></div><div class=\'ginput_complex ', $field_content);


			}
*/

			if($field["isRequired"] == true) {

				$class_attr = str_replace('gfield_error', 'gfield_error has-error', $class_attr);
			}

			if($field["type"] == 'creditcard') {


				$field_content = '<div class=" panel panel-creditcard "><div class="panel-body">'.$field_content.'<div class="card-wrapper"></div></div></div>';

				$field_content = str_replace('American Express', '<i class="fa fa-2x fa-cc-amex"></i>', $field_content);
				$field_content = str_replace('Discover', '<i class="fa fa-2x fa-cc-discover"></i>', $field_content);
				$field_content = str_replace('MasterCard', '<i class="fa fa-2x fa-cc-mastercard"></i>', $field_content);
				$field_content = str_replace('Visa', '<i class="fa fa-2x fa-cc-visa"></i>', $field_content);


	          	//$field_content = str_replace('ginput_cardinfo_left', 'ginput_cardinfo_left row', $field_content);

				$field_content = str_replace('<span class=\'ginput_card_security_code_icon\'>&nbsp;</span>', '', $field_content);
				//Below is an attempt to add credit card icon
				//$field_content = str_replace('ginput_cardinfo_right', 'ginput_cardinfo_right input-group', $field_content);
				//$field_content = str_replace('<span class=\'ginput_card_security_code_icon\'>&nbsp;</span>', '<span class="input-group-addon"><i class="fa fa-credit-card"></i></span>', $field_content);
				//$field_content = str_replace('ginput_card_security_code_icon', 'ginput_card_security_code_icon glyphicon glyphicon-credit-card', $field_content);

				//$field_content = str_replace('<select', '<div class="col-xs-6"><div class="form-group"><select', $field_content);
				//$field_content = str_replace('</select>', '</select></div></div>', $field_content);

			   $field_content = str_replace('gfield_creditcard_warning_message', 'gfield_creditcard_warning_message alert alert-danger', $field_content);

			   //$field_content = str_replace('ginput_cardextras', 'ginput_cardextras form-inline', $field_content);
	           //$field_content = str_replace('ginput_full', 'ginput_full col-md-12', $field_content);
				//$field_content = '<div class="col-md-4">'.$field_content.'</div>';


			}else{
				$field_content = str_replace('ginput_complex', 'ginput_complex row', $field_content);

			}

			if($field["size"] == 'number') {
				$field_content = '<div class="row"><div class="col-md-4">'.$field_content.'</div></div>';
			}


			return '<li id="field_'.$form['id'].'_'.$field['id'].'" class="'.$class_attr.' form-group">'. $field_content .'</li>';

        }
// filter the Gravity Forms button type

function anagram_form_submit_button($button, $form){
	$button = str_replace('gform_button', 'gform_button btn btn-default', $button);
    return $button;
}

//Next button
function anagram_next_button_markup( $next_button, $form ) {
    $next_button = str_replace('gform_next_button', 'gform_next_button btn btn-default', $next_button);
    return $next_button;
}
//previous button
function anagram_previous_button_markup( $previous_button, $form ) {
	$previous_button = str_replace('gform_previous_button', 'gform_previous_button btn btn-default', $previous_button);
    return $previous_button;

}
//Validation message
function anagram_change_message($message, $form){
	$message= str_replace('validation_error', 'validation_error alert alert-danger', $message);
	return $message;
}

function anagram_custom_validation($result, $value, $form, $field){
	$result['message'] = '<span class="help-block">This field is required.</span>';
	return $result;
}





 function anagram_address_field_container( $field_container, $field, $form, $class_attr, $style, $field_content ) {

			if($field["type"] == 'address') {
	            //echo '<pre>';var_dump($field_content);echo '</pre>';
				$field_content = str_replace('<div class=\'ginput_complex ', '<div id="geocomplete_holder" class="form-group"><input id="geocomplete" tabindex="1013" class="form-control" type="text" placeholder="Type in an address" value="" /><span id="geo_error" class="help-block"></span></div><div class=\'ginput_complex ', $field_content);


			}

			return '<li id="field_'.$form['id'].'_'.$field['id'].'" class="'.$class_attr.' form-group">'. $field_content .'</li>';
}



function anagram_enqueue_custom_script($form, $is_ajax){

		//echo '<pre>';var_dump($form );echo '</pre>';
//array_key_exists($form["fields"],'creditcard' )
			// add theme scripts
		 // load gforms-style.css
	//wp_enqueue_style( 'anagram_gforms-style', get_stylesheet_directory_uri().'/css/gforms-style.css', array(), filemtime( get_stylesheet_directory().'/css/gforms-style.css') );


		foreach ( $form['fields'] as $field ) {

			//echo '<pre>';var_dump($field->type );echo '</pre>';
			if ( $field->type == 'address' ) {
				//add_action( 'wp_footer', 'add_address_scripts' );
				wp_enqueue_script('anagram_googlemaps', 'https://maps.googleapis.com/maps/api/js?libraries=places',false);
				wp_enqueue_script('anagram_addressjs', (get_template_directory_uri()."/js/address.js"),'jquery',filemtime( get_stylesheet_directory().'/js/address.js'),true);

			}

			//echo '<pre>';var_dump($field->type );echo '</pre>';
			if ( $field->type == 'creditcard' ) {
				//add_action( 'wp_footer', 'add_credit_scripts' );
				wp_enqueue_script('anagram_cardjs', (get_template_directory_uri()."/js/card.js"),'jquery',filemtime( get_stylesheet_directory().'/js/card.js'),false);

			}
		}

}
//Note: this will allow for the labels to be used during the submission process in case values are enabled
function add_credit_scripts() {
?>
<script>
        new Card({
            form: document.querySelector('form'),
            formSelectors: {
		        numberInput: 'input.card-number',
		        expiryInput: 'select.card_month,select.card_year',
		        cvcInput: 'input.security-code',
		        nameInput: 'input.cardholder-name'
		    },

            container: '.card-wrapper'
        });
    </script>"
<?php
}