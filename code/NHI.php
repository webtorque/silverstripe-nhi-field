<?php

/**
 * Database field to track a NZ `National Health Index` number. This will generate a VARCHAR(7) column on your table.
 *
 * When editing this field, an NHIField will be use.
 *
 * To use thid field on a DataObject:
 * ```php
 * class Patient extends DataObject {
 *
 *     private static $db = [
 *         'NHINumber'  => 'NHI',
 *         'Name'       => 'Varchar'
 *     ];
 *
 * }
 *
 * ```
 */
class NHI extends Varchar
{

    /**
     * Instanciate a new NHI db field.
     * @param $name string The name of the field.
     * @param array  $options Optional parameters, e.g. array("nullifyEmpty"=>false). See
     *                        {@link StringField::setOptions()} for information on the available options.
     */
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, 7, $options);
    }

    /**
     * @inheritDoc
     * @param  string|null $title  Form Field title.
     * @param  array $params
     * @return FormField
     */
    public function scaffoldFormField($title = null, $params = null)
    {
        if (!$this->nullifyEmpty) {
            return new NullableField(new NHIField($this->name, $title));
        } else {
            return new NHIField($this->name, $title);
        }
    }
}
