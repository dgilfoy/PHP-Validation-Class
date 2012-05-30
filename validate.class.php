<?php

/**
*   Validate Form Class
*   Author: Daniel P. Gilfoy
*   Description: Validate's a value or an array of data.  
*           
*/
class ValidateForm{
    
    protected $vadlidate_data = array();
    protected $no_errors;
    
    public function __construct( ){
        $this->no_errors = false;
    }
    
    /*
    *   Iterates through a passed array (see format) and validates each imput, returning an array with "error field" added
    *   Format: array(
    *               "Name_of_field" => array(
    *               "value" => "field_value_to_validate",
    *               "rules" => "rule_here" <- such as date or matches[value_to_match]
    *           ) );
    */
    public function validate_array( $vadlidate_data ){
        $this->vadlidate_data = $vadlidate_data;
        foreach( $this->vadlidate_data as $label=>$field ){
            foreach( explode( '|', $field['rules'] ) as $rule ){
                if( preg_match( '`(\w*)\[(.*?)\]`is', $rule, $rule_segments ) ){
                    $valid_rule = 'valid_' . $rule_segments[1];
                    if( method_exists( $this, $valid_rule ) ) self::$valid_rule( $label, $field['value'], $rule_segments[2] );
                }else{
                    $valid_rule = 'valid_' . $rule;
                    if( method_exists( $this, $valid_rule ) ) self::$valid_rule( $label, $field['value'] );
                }
            }
        }
        return $this->vadlidate_data;
    }

    /*
    *   Validates a single field returning true or false (you can get the error message if you need it through get_validate_data())
    */
    public function validate_field( $value, $rule, $label = 'validated_field' ){
        $valid_rule = 'valid' . $rule;
        if( method_exists( $this, $valid_rule ) ) self::$valid_rule( $label, $value );
        return $this->no_errors;
    }

    /*
    *   returns the array of validated data
    */
    public function get_vadlidate_data(){
        return $this->vadlidate_data;
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
        $this->vadlidate_data[$label]['error'] = $msg;
        $this->no_errors = true;
    }

    /*
    *   Make's certain that the field, if required, is not empty.
    */
    protected function valid_required( $label, $value ){
        if( strlen( trim( $value ) ) < 1 ) self::set_error( $label, $label , ' is required.' );
    }
    
    /*
    *   Is a valid date ( yyyy-mm-dd ) 
    *   Note: will add ability to pass your own date format eventually - change this manually if your needs are different
    */
    protected function valid_date( $label, $value ){
        if( preg_match( '`(\d{4}/-d{2}/-d{2})`', $value ) )
            self::set_error( $label, $label . ' is not a valid date or is not in the proper format yyyy-mm-dd'); 
    }
    
    /*
    *   valid phone number either in 5555555555, 555-555-5555, (555)555-5555, (555) 555-5555 or even 555.555.5555
    */
    protected function valid_phone( $label, $value ){
        if ( preg_match('`[^0-9 .-\)\(]`is', $value ) ) 
            self::set_error( $label, $label . ' is not a valid Phone Number.' ); 
                        
    }
    
    /*
    *   Is alpha
    */
     protected function valid_alpha( $label, $value ){
        if ( preg_match( '`[^A-Z ]`is', $value ) )  
            self::set_error( $label, $label . ' contains non-alpha characters.' ); 
    }
    
    /*
    *   Is Alpha-numerical
    */
     protected function valid_alphanum( $label, $value ){
        if ( preg_match( '`[^A-Z0-9 ]`is', $value ) ) 
            self::set_error( $label, $label . ' contains non-alpha-numeric characters.' ); 
    }
    
    /*
    *   Alpha Numerical plus other digits
    */
     protected function valid_alphanumplus( $label, $value ){
         if ( preg_match( '`[^A-Za-z0-9 \-\!\'\,\.\:\+\(\)\[\]\*\&\%\$\#\@\?\:\;\"\/n\/r]`is', $value ) ) 
            self::set_error( $label, $label . ' contains non-alpha + characters.' ); 
    }
    
    /*
    *   Is a valid name (alpha plus - and ' )
    */
     protected function valid_name( $label, $value ){
        if ( preg_match( '`[^A-Za-z\- \' ]`is', $value ) )
            self::set_error( $label, $label . ' contains non-alpha characters.' ); 
    }
    
    /*
    *   is a number
    */
     protected function valid_numeric( $label, $value ){
        if (! is_numeric( $value ) )
            self::set_error( $label, $label . ' is not numeric.'); 
    }
    
    /*
    *   is a valid integer
    */
     protected function valid_integer( $label, $value ){
         if(! filter_var( $value, FILTER_VALIDATE_INT ) )
           self::set_error( $label, $label . ' is not an integer.' ); 
    }
    
    /*
    *   is a valid float
    */
     protected function valid_float( $label, $value ){
        if(! filter_var( $value, FILTER_VALIDATE_FLOAT ) )
           self::set_error( $label, $label . ' is not a proper floating point intenger.' ); 
    }
    
    /*
    *   is a valid email
    */
     protected function valid_email( $label, $value ){
        if(! filter_var( $value, FILTER_VALIDATE_EMAIL ) )
           self::set_error( $label, $label . 'is an invalid email address.' ); 
    }
    
    /*
    *   is a valid URL
    */
     protected function valid_url( $label, $value ){
        if(! filter_var( $value, FILTER_VALIDATE_URL ) )
           self::set_error( $label, $label . 'is an invalid URL.' ); 
    }
    
    /*
    *   is a valid IP address
    */
     protected function valid_ipaddress( $label, $value ){
        if(! filter_var( $value, FILTER_VALIDATE_IP ) )
           self::set_error( $label, $label . 'is an invalid IP Address.' ); 
    }
    
    /*
    *   is a valid array
    */
    protected function valid_array( $label, $value ){
        if( !is_array( $value ) )
            self::set_error( $label, $label . ' is not an array.' );
    }
    
    /*
    *   matches a given value
    */
    protected function valid_matches( $label, $value, $segments ){
        if ( strlen( trim( $value ) ) > 0 && $value != $segments )
            self::set_error( $label, $label . ' must match ' . $segments ); 
    }
    
    /*
    *   has a certain length: length[5] for a string that is 5 characters long, or for between a range: length[5|10]
    */
    protected function valid_length( $label, $value, $segments ){
        $data_length = strlen( $value );
        if (preg_match('`(\d*):(\d*)`is', $segments, $length_rules)) {
           if( $length_rules[1] > $data_length  || $data_length > $length_rules[2] )
                self::set_error( $label, $label . ' must be at least ' . $length_rules[1]. ' and no more than '.$length_rules[2].' characters.' );
        }else{
            if ( $data_length != $segments )
                self::set_error( $label, $label . ' must contain at least '.$segments.' characters.' ); 
        }
    }
}