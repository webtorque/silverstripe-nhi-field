<?php

/**
 * Test the NHI db field.
 */
class NHITest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testGetSize()
    {
        $nhi = new NHI('Dummy');
        $this->assertEquals(7, $nhi->getSize());
    }

    public function testFormField()
    {
        $nhi = new NHI('Dummy');

        $field = $nhi->scaffoldFormField('Dummy Title');
        $this->assertInstanceOf('NHIField', $field);
        $this->assertEquals('Dummy', $field->getName());
        $this->assertEquals('Dummy Title', $field->Title());

        $nhi = new NHI('Dummy', ['nullifyEmpty' => true]);
        $this->assertInstanceOf('NullableField', $field);
    }
}
