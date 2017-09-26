<?php

/**
 * Form Field to edit a NHI Number. Provide validation for the field and normalise the value before saving it to the
 * database.
 *
 * Validation is based on the sample code at
 * {@link https://en.wikipedia.org/w/index.php?title=NHI_Number&oldid=770434870}
 */
class NHIField extends TextField
{
    /**
     * Regular expression to validate an NHI.
     * @var string
     */
    const REGEX_PATTERN = '^[a-zA-Z]{3}[0-9]{4}$';

    // Make sure we apply the text class to the field so it displays like a normal text field in the CMS.
    private static $default_classes = array('nhi', 'text');

    /**
     * Instanciate a new NHIField.
     * @param string $name          Form field name
     * @param string $title         Label to use for the field.
     * @param string $value         Inital value
     * @param Form   $form          Form to add the field to.
     * @param bool   $html5pattern  Output a pattern attribute on the input.
     */
    public function __construct($name, $title = null, $value = '', $form = null, $html5pattern=false)
    {
        parent::__construct($name, $title, $value, 7, $form);
        $this->setHtml5Pattern($html5pattern);
    }

    /**
     * Get whatever to output the `pattern` attribute on the input tag.
      * @return boolean
     */
    public function getHtml5Pattern()
    {
        return $this->getAttribute('pattern') == self::REGEX_PATTERN;
    }

    /**
     * Set whatever to output the `pattern` attribute on the input tag.
     * @param  boolean $enabled Enable or disable the pattern.
     * @return NHIField
     */
    public function setHtml5Pattern($enabled)
    {
        if ($enabled) {
            $this->setAttribute('pattern', self::REGEX_PATTERN);
        } else {
            $this->setAttribute('pattern', '');
        }
        return $this;
    }

    /**
     * @inheritDoc
     * @param  Validator $validator
     * @return boolean
     */
    public function validate($validator)
    {
        return parent::validate($validator) && $this->formatValidation($validator);
    }

    /**
     * @inheritDoc
     * @param string $value
     * @return NHIField
     */
    public function setValue($value)
    {
        // Always convert the value to uppercase.
        return parent::setValue(strtoupper($value));
    }

    /**
     * Validate the NHI using the 15 steps highlighted in
     * {@link https://en.wikipedia.org/w/index.php?title=NHI_Number&oldid=770434870}
     * @param  Validator $validator
     * @return boolean
     */
    protected function formatValidation($validator)
    {
        $nhi = $this->value;

        // Step 1 and 2
        $pattern = "/" . self::REGEX_PATTERN . "/";
        if (!preg_match($pattern, $nhi)) {
            $validator->validationError(
                  $this->name,
                 _t(
                     'NHIField.VALIDATEPATTERN',
                     'The value for {name} must be a sequence of 3 letters followed by 4 digits.',
                     array('name' => $this->Title())
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
        $calc1 = $this->extractLetter($chars[0])*7;

        // Step 4 - Assign second letter its corresponding value from the Alphabet Conversion Table and multiply value by 6.
        $calc2 = $this->extractLetter($chars[1])*6;

        // Step 5 - Assign third letter its corresponding value from the Alphabet Conversion Table and multiply value by 5.
        $calc3 = $this->extractLetter($chars[2])*5;

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
                     array('name' => $this->Title())
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
                     array('name' => $this->Title())
                 ),
                 "validation"
             );
            return false;
        }

        return true;
    }

    /**
     * Convert a letter to a numeric value for the checksum.
     * @param  string $c Chracter to validate
     * @return int
     */
    private function extractLetter($c)
    {
        $c = strtolower($c);
        $ascii1 = ord($c);

        // I and O are removed from the Alphabet for readability, dirty hack to account for that
        if ($ascii1 > 105) {
            if ($ascii1 > 111) {
                $ascii1 = $ascii1-2;
            } else {
                $ascii1 = $ascii1-1;
            }
        }

        $alphanr1 = $ascii1 - 96;
        return $alphanr1;
    }
}
