<?php

/**
 * Test the NHI db field.
 */
class NHIFieldTest extends SapphireTest
{
    public function testValidNHI()
    {
        $field = NHIField::create('dummy');
        $validator = RequiredFields::create();

        $this->assertTrue($field->setValue('CGC2720')->validate($validator), 'CGC2720 is valid');
        $this->assertTrue($field->setValue('EPT6335')->validate($validator), 'EPT6335 is valid');
        $this->assertTrue($field->setValue('ept6335')->validate($validator), 'ept6335 is valid');

        $this->assertNull($validator->getErrors());
    }

    public function testInvalidNHI()
    {
        $field = NHIField::create('dummy');

        $this->assertFalse($field->setValue('0000000')->validate($validator = RequiredFields::create()), '0000000 is an invalid NHI');
        $this->assertCount(1, $validator->getErrors());

        $this->assertFalse($field->setValue('CGC272')->validate($validator = RequiredFields::create()), 'CGC272 is an invalid NHI');
        $this->assertCount(1, $validator->getErrors());

        $this->assertFalse($field->setValue('CGC27200')->validate($validator = RequiredFields::create()), 'CGC27200 is an invalid NHI');
        $this->assertCount(1, $validator->getErrors());

        $this->assertFalse($field->setValue('ABCDEFG')->validate($validator = RequiredFields::create()), 'ABCDEFG is an invalid NHI');
        $this->assertCount(1, $validator->getErrors());

        $this->assertFalse($field->setValue('abcDEFG')->validate($validator = RequiredFields::create()), 'abcDEFG is an invalid NHI');
        $this->assertCount(1, $validator->getErrors());

        $this->assertFalse($field->setValue('DAB8233')->validate($validator = RequiredFields::create()), 'DAB8233 is an invalid NHI because of it\'s checksum');
        $this->assertCount(1, $validator->getErrors());
    }

    public function testDisabledChecksumTest()
    {
        NHIField::config()->disable_checksum_validation = true;

        $field = NHIField::create('dummy');

        $this->assertTrue($field->setValue('DAB8233')->validate($validator = RequiredFields::create()), 'DAB8233\'s checksum should not be validated when the `disable_checksum_validation` flag is set to true.');
        $this->assertNull($validator->getErrors());
    }

    public function testNormalisedValue()
    {
        $field = NHIField::create('dummy', 'dummy', 'ept6335');

        $this->assertEquals('EPT6335', $field->Value(), 'Value() should return an all uppercase string.');

        $this->assertEquals('EPT6335', $field->dataValue(), 'dataValue() should return an all uppercase string.');
    }
}
