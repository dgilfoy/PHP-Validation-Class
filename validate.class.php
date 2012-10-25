<?php

/**
*   Validate Form Class
*   Author: Daniel P. Gilfoy
*   Description: Validate's a value or an array of data.  
*           
*/
class ValidateForm{
    
    protected $validate_data = array();
    protected $no_errors;
    
    public function __construct( ){
        $this->no_errors = true;
    }
    
    /*
    *   Iterates through a passed array (see format) and validates each imput, returning an array with "error field" added
    *   Format: array(
    *               "Name_of_field" => array(
    *               "value" => "field_value_to_validate",
    *               "rules" => "rule_here" <- such as date or matches[value_to_match]
    *               "label" => "label of field"
    *           ) );
    */
    public function validate_array( $validate_data ){
        $this->validate_data = $validate_data;
        foreach( $this->validate_data as $field_name=>$field ){
            foreach( explode( '|', $field['rules'] ) as $rule ){
    			$label = ( isset( $field['label'] ) ) ? $field['label'] : ucwords( preg_replace( '`-_`', ' ', $field_name ) ) ;
                if( preg_match( '`(\w*)\[(.*?)\]`is', $rule, $rule_segments ) ){
                    $valid_rule = 'valid_' . $rule_segments[1];
                    if( method_exists( $this, $valid_rule ) ) self::$valid_rule( $field_name, $label, $field['value'], $rule_segments[2] );
                }else{
                    $valid_rule = 'valid_' . $rule;
                    if( method_exists( $this, $valid_rule ) ) self::$valid_rule( $field_name, $label, $field['value'] );
                }
            }
        }
        return $this->validate_data;
    }

    /*
    *   Validates a single field returning true or false (you can get the error message if you need it through get_validate_data())
    */
    public function validate_field( $value, $rule, $field_name = 'validated_field', $field_label = false ){
        $valid_rule = 'valid' . $rule;
		$label = ( $field_label ) ? $field_label : ucwords( preg_replace( '`-_`', ' ', $field_name ) ) ;
        if( method_exists( $this, $valid_rule ) ) self::$valid_rule( $field_name, $label, $value );
        return $this->no_errors;
    }

    /*
    *   returns the array of validated data
    */
    public function get_validate_data(){
        return $this->validate_data;
    }
    
    /*
    *   returns the status of errors for the object - for validate_array, let's you know if any element in the array has an error. 
    */
    public function has_errors(){
        return $this->no_errors;
    }
    
    /*
    *   set's the error message and the error flag
    */
    protected function set_error( $label, $msg ){
        $this->validate_data[$label]['error'] = $msg;
        $this->no_errors = false;
    }

    /*
    *   Make's certain that the field, if required, is not empty.
    */
    protected function valid_required( $name, $label, $value ){
        if( strlen( trim( $value ) ) < 1 ) self::set_error( $name, $label . ' is required.' );
    }
    
    /*
    *   Is a valid date ( yyyy-mm-dd ) 
    *   Note: will add ability to pass your own date format eventually - change this manually if your needs are different
    */
    protected function valid_date( $name, $label, $value ){
        if( preg_match( '`(\d{4}/-d{2}/-d{2})`', $value ) )
            self::set_error( $name, $label . ' is not a valid date or is not in the proper format yyyy-mm-dd'); 
    }
    
    /*
    *   valid phone number either in 5555555555, 555-555-5555, (555)555-5555, (555) 555-5555 or even 555.555.5555
    */
    protected function valid_phone( $name, $label, $value ){
        if ( preg_match('`[^0-9 .-\)\(]`is', $value ) ) 
            self::set_error( $name, $label . ' is not a valid Phone Number.' ); 
                        
    }
    
    /*
    *   Is alpha
    */
     protected function valid_alpha( $name, $label, $value ){
        if ( preg_match( '`[^A-Z ]`is', $value ) )  
            self::set_error( $name, $label . ' contains non-alpha characters.' ); 
    }
    
    /*
    *   Is Alpha-numerical
    */
     protected function valid_alphanum( $name, $label, $value ){
        if ( preg_match( '`[^A-Z0-9 ]`is', $value ) ) 
            self::set_error( $name, $label . ' contains non-alpha-numeric characters.' ); 
    }
    
    /*
    *   Alpha Numerical plus other digits
    */
     protected function valid_alphanumplus( $name, $label, $value ){
         if ( preg_match( '`[^A-Za-z0-9 \-\!\'\,\.\:\+\(\)\[\]\*\&\%\$\#\@\?\:\;\"\/n\/r]`is', $value ) ) 
            self::set_error( $name, $label . ' contains non-alpha + characters.' ); 
    }
    
    /*
    *   Is a valid name (alpha plus - and ' )
    */
     protected function valid_name( $name, $label, $value ){
        if ( preg_match( '`[^A-Za-z\- \' ]`is', $value ) )
            self::set_error( $name, $label . ' contains non-alpha characters.' ); 
    }
    
    /*
    *   is a number
    */
     protected function valid_numeric( $name, $label, $value ){
        if (! is_numeric( $value ) )
            self::set_error( $name, $label . ' is not numeric.'); 
    }
    
    /*
    *   is a valid integer
    */
     protected function valid_integer( $name, $label, $value ){
         if(! filter_var( $value, FILTER_VALIDATE_INT ) )
           self::set_error( $name, $label . ' is not an integer.' ); 
    }
    
    /*
    *   is a valid float
    */
     protected function valid_float( $name, $label, $value ){
        if(! filter_var( $value, FILTER_VALIDATE_FLOAT ) )
           self::set_error( $name, $label . ' is not a proper floating point intenger.' ); 
    }
    
    /*
    *   is a valid email
    */
     protected function valid_email( $name, $label, $value ){
        if(! filter_var( $value, FILTER_VALIDATE_EMAIL ) )
           self::set_error( $name, $label . 'is an invalid email address.' ); 
    }
    
    /*
    *   is a valid URL
    */
     protected function valid_url( $name, $label, $value ){
        if(! filter_var( $value, FILTER_VALIDATE_URL ) )
           self::set_error( $name, $label . 'is an invalid URL.' ); 
    }
    
    /*
    *   is a valid IP address
    */
     protected function valid_ipaddress( $name, $label, $value ){
        if(! filter_var( $value, FILTER_VALIDATE_IP ) )
           self::set_error( $name, $label . 'is an invalid IP Address.' ); 
    }
    
    /*
    *   is a valid array
    */
    protected function valid_array( $name, $label, $value ){
        if( !is_array( $value ) )
            self::set_error( $name, $label . ' is not an array.' );
    }
    
    /*
    *   matches a given value
    */
    protected function valid_matches( $name, $label, $value, $segments ){
        if ( strlen( trim( $value ) ) > 0 && $value != $segments )
            self::set_error( $name, $label . ' must match ' . $segments ); 
    }
    
    /*
    *   has a certain length: length[5] for a string that is 5 characters long, or for between a range: length[5|10]
    */
    protected function valid_length( $name, $label, $value, $segments ){
        $data_length = strlen( $value );
        if (preg_match('`(\d*):(\d*)`is', $segments, $length_rules)) {
           if( $length_rules[1] > $data_length  || $data_length > $length_rules[2] )
                self::set_error( $name, $label . ' must be at least ' . $length_rules[1]. ' and no more than '.$length_rules[2].' characters.' );
        }else{
            if ( $data_length != $segments )
                self::set_error( $name, $label . ' must contain at least '.$segments.' characters.' ); 
        }
    }
}