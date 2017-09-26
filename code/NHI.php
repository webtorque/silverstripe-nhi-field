<?php

class NHI extends Varchar
{
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, 7, $options);
    }

    public function scaffoldFormField($title = null, $params = null)
    {
        if (!$this->nullifyEmpty) {
            return new NullableField(new NHIField($this->name, $title));
        } else {
            return new NHIField($this->name, $title);
        }
    }
}
