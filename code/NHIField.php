<?php

class NHIField extends TextField
{
    private static $default_classes = array('nhi', 'text');

    public function __construct($name, $title = null, $value = '', $maxLength = null, $form = null)
    {
        parent::__construct($name, $title, $value, 7, $form);
        $this->setAttribute('pattern', '[A-Za-z]{3}[0-9]{4}');
    }

    public function validate($validator)
    {
        return parent::validate($validator) && $this->formatValidation($validator);
    }


    public function setValue($value)
    {
        return parent::setValue(strtoupper($value));
    }

    protected function formatValidation($validator)
    {
        $nhi = $this->value;

        // Step 1 and 2
        $pattern = "/^([a-zA-Z]){3}([0-9]){4}?$/";
        if (!preg_match($pattern, $nhi)) {
            $validator->validationError(
                  $this->name,
                 _t(
                     'NHIField.VALIDATEPATTERN',
                     'The value for {name} must be a sequence of 3 letters followed by 4 digits.',
                     array('name' => $this->getName())
                 ),
                 "validation"
             );
            return false;
        }

        // Flag to disable checksum check during testing.
        if (self::config()->get('disable_checksum_validation')) {
            return true;
        }

        $chars = preg_split('//', $nhi, -1, PREG_SPLIT_NO_EMPTY);

        // Step 3 - Assign first letter its corresponding value from the Alphabet Conversion Table and multiply value by 7
        $chars[0] = strtolower($chars[0]);
        $ascii1 = ord($chars[0]);

        // I and O are removed from the Alphabet for readability, dirty hack to account for that
        if ($ascii1 > 105) {
            if ($ascii1 > 111) {
                $ascii1 = $ascii1-2;
            } else {
                $ascii1 = $ascii1-1;
            }
        }

        $alphanr1 = $ascii1 - 96;
        $calc1 = $alphanr1*7;

        // Step 4 - Assign second letter its corresponding value from the Alphabet Conversion Table and multiply value by 6.
        $chars[1] = strtolower($chars[1]);
        $ascii2 = ord($chars[1]);
        if ($ascii2 > 105) {
            if ($ascii2 > 111) {
                $ascii2 = $ascii2-2;
            } else {
                $ascii2 = $ascii2-1;
            }
        }
        $alphanr2 = $ascii2 - 96;
        $calc2 = $alphanr2*6;

        // Step 5 - Assign third letter its corresponding value from the Alphabet Conversion Table and multiply value by 5.
        $chars[2] = strtolower($chars[2]);
        $ascii3 = ord($chars[2]);
        if ($ascii3 > 105) {
            if ($ascii3 > 111) {
                $ascii3 = $ascii3-2;
            } else {
                $ascii3 = $ascii3-1;
            }
        }
        $alphanr3 = $ascii3 - 96;
        $calc3 = $alphanr3*5;

        // Step 6 - Multiply first number by 4
        $calc4 = $chars[3]*4;

        // Step 7 - Multiply second number by 3
        $calc5 = $chars[4]*3;

        // Step 8 - Multiply third number by 2

        $calc6 = $chars[5]*2;

        // Step 9 - Total the result of steps 3 to 8
        $sum = $calc1 + $calc2 + $calc3 + $calc4 + $calc5 + $calc6;

        // Step 10 - Apply modulus 11 to create a checksum.
        $divisor = 11;
        $rest = fmod($sum, $divisor);
        $outcome = $sum / $divisor;

        // Step 11 - If checksum is zero then the NHI number is incorrect
        if ($rest == 0) {
            $validator->validationError(
                  $this->name,
                 _t(
                     'NHIField.VALIDATECHECKSUM',
                     'The value for {name} is not a valid NHI number.',
                     array('name' => $this->getName())
                 ),
                 "validation"
             );
            return false;
        }


        // Step 12 - Subtract checksum from 11 to create check digit
        $check_digit = $divisor-$rest;

        // Step 13 - If check digit equals 10 convert to zero
        if ($check_digit == 10) {
            $check_digit = 0;
        }

        // Step 14 - Fourth number must be equal to check digit
        if ($chars[6] != $check_digit) {
            $validator->validationError(
                  $this->name,
                 _t(
                     'NHIField.VALIDATECHECKSUM',
                     'The value for {name} is not a valid NHI number.',
                     array('name' => $this->getName())
                 ),
                 "validation"
             );
            return false;
        }

        return true;
    }
}
