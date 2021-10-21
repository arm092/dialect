<?php

use Src\MockJsonDialectModel;

class JsonDialectTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Assert that defined JSON attributes are properly parsed and exposed through
     * mutators.
     */
    public function testInspectJsonColumns()
    {
        // Mock the model with data
        $mock = new MockJsonDialectModel();
        $mock->setJsonColumns(['testColumn']);
        $mock->setAttribute('testColumn', json_encode(['foo' => 'bar']));

        // Execute the inspect call
        $mock->inspectJsonColumns();

        // Assert that the column were properly parsed and various bits have
        // been set on the model
        $this->assertTrue($mock->hasGetMutator('foo'));
        $this->assertContains('foo', $mock->getMutatedAttributes());
        $this->assertArrayNotHasKey('testColumn', $mock->toArray());
        $this->assertArrayHasKey('foo', $mock->toArray());
        $this->assertEquals('bar', $mock->foo);
    }

    /**
     * Assert that the json columns show up when configured to do so
     */
    public function testDisableHiddenJsonColumns()
    {
        // Mock the model with data
        $mock = new MockJsonDialectModel();
        $mock->setJsonColumns(['testColumn']);
        $mock->setAttribute('testColumn', json_encode(['foo' => 'bar']));
        $mock->showJsonColumns(true);

        // Execute the inspect call
        $mock->inspectJsonColumns();

        // Assert that the testColumn shows up
        $this->assertArrayHasKey('testColumn', $mock->toArray());
    }

    /**
     * Assert that the json attributes do not show up when configured to do so
     */
    public function testEnableHiddenJsonAttributes()
    {
        // Mock the model with data
        $mock = new MockJsonDialectModel();
        $mock->setJsonColumns(['testColumn']);
        $mock->setAttribute('testColumn', json_encode(['foo' => 'bar']));
        $mock->showJsonAttributes(false);

        // Execute the insepect call
        $mock->inspectJsonColumns();

        // Assert that attribute isn't there
        $this->assertArrayNotHasKey('foo', $mock->toArray());
    }

    /**
     * Assert that an exception is thrown when given invalid json as a
     * structure hint
     */
    public function testInvalidJsonAttribute()
    {
        $this->expectException(Eloquent\Dialect\InvalidJsonException::class);
        // Mock the model with data
        $mock = new MockJsonDialectModel();
        $mock->hintJsonStructure( 'testColumn', json_encode(['foo'=>null]) );

        // Set testColumn to invalid json
        $mock->setAttribute('testColumn', '{');

        // Try to access a property on invalid json - we should get an
        // exception
        $mock->foo;
    }

    /**
     * Assert that no exception is thrown when given null json
     * @throws \Eloquent\Dialect\InvalidJsonException
     */
    public function testNullJsonAttribute()
    {
        // Mock the model with data
        $mock = new MockJsonDialectModel();
        $mock->hintJsonStructure( 'testColumn', json_encode(['foo'=>null]) );

        // Set testColumn to 'null'
        $mock->setAttribute('testColumn', 'null');
        $this->assertNull($mock->foo);

        // Set testColumn to null
        $mock->setAttribute('testColumn', null);
        $this->assertNull($mock->foo);
    }

    /**
     * Assert that JSON attributes can be set through mutators
     */
    public function testSetAttribute()
    {
        // Mock the model with data
        $mock = new MockJsonDialectModel();
        $mock->setJsonColumns(['testColumn']);
        $mock->setAttribute('testColumn', json_encode(['foo' => 'bar']));

        // Execute the inspect call
        $mock->inspectJsonColumns();

        $mock->foo = 'baz';
        $mock->setJsonAttribute('testColumn', 'fizz', 'buzz');

        // Assert that the column were properly parsed and various bits have
        // been set on the model
        $this->assertEquals('baz', $mock->foo);
        $this->assertEquals('buzz', $mock->fizz);
    }

    /**
     * Assert that JSON array attributes can be set through mutators
     * @throws \Eloquent\Dialect\InvalidJsonException
     */
    public function testSetArrayAttribute()
    {
        // Mock the model with data
        $mock = new MockJsonDialectModel();
        $mock->hintJsonStructure( 'testColumn', json_encode(['foo'=>null]) );

        // Execute the hint call
        $mock->addHintedAttributes();

        $mock->foo = ['bar','baz'];

        // Assert that the column were properly parsed and various bits have
        // been set on the model
        $this->assertEquals('array', gettype($mock->foo));
        $this->assertCount(2, $mock->foo);
        $this->assertEquals('bar', $mock->foo[0] );
    }

    /**
     * Assert that attributes with JSON operators are properly recognized as JSON
     * attributes
     */
    public function testGetMutator()
    {
        // Mock the model with data
        $mock = new MockJsonDialectModel();
        $this->assertTrue($mock->hasGetMutator("testColumn->>'foo'"));
    }

    /**
     * Assert that defined JSON attributes are properly parsed and exposed
     * through mutators.
     * @throws \Eloquent\Dialect\InvalidJsonException
     */
    public function testHintedJsonColumns()
    {
        // Mock the model with data
        $mock = new MockJsonDialectModel();
        $mock->hintJsonStructure( 'testColumn', json_encode(['foo'=>null]) );

        // Execute the hint call
        $mock->addHintedAttributes();

        // Assert that the column were properly parsed and various bits have
        // been set on the model
        $this->assertTrue($mock->hasGetMutator('foo'));
        $this->assertContains('foo', $mock->getMutatedAttributes());
        $this->assertContains('testColumn', $mock->getHidden());

        $this->assertNull( $mock->foo );

        // Set a value for foo
        $mock->foo = 'bar';

        // assert that the column has been set properly
        $this->assertEquals( 'bar', $mock->foo );
        $this->assertEquals( json_encode(['foo'=>'bar']), $mock->testColumn);
    }

    /**
     * Assert that defined JSON attributes are returned in the getDirty()
     * response when expected.
     * @throws \Eloquent\Dialect\InvalidJsonException
     */
    public function testGetDirtyJson()
    {
        // Mock the model with data
        $mock = new MockJsonDialectModel();
        $mock->hintJsonStructure( 'testColumn', json_encode(['foo'=>null]) );

        // At this point 'foo' should not be dirty
        $this->assertArrayNotHasKey( 'foo', $mock->getDirty(true) );

        $mock->setAttribute('testColumn', json_encode(['foo' => 'bar']));

        // Now that 'foo' has been changed it should show up in the getDirty()
        // response
        $this->assertArrayHasKey( 'foo', $mock->getDirty(true) );
    }

    /**
     * Assert that an exception is thrown when given invalid json as a
     * structure hint
     */
    public function testInvalidHint()
    {
        $this->expectException(Eloquent\Dialect\InvalidJsonException::class);
        // Mock the model with data
        $mock = new MockJsonDialectModel();
        $mock->hintJsonStructure( 'testColumn', '{' );

        // Execute the hint call
        $mock->addHintedAttributes();
    }

    /**
     * Test the ability to allow models to provide their own custom attribute
     * getters for json attributes
     * @throws \Eloquent\Dialect\InvalidJsonException
     */
    public function testCustomGetter()
    {
        // Mock the model with data
        $mock = new MockJsonDialectModel();
        $mock->hintJsonStructure( 'foo', json_encode(['custom_get'=>null]) );

        // Execute the hint call
        $mock->addHintedAttributes();

        // Assert that the column were properly parsed and various bits have
        // been set on the model
        $this->assertTrue($mock->hasGetMutator('custom_get'));
        $this->assertEquals('custom getter result', $mock->custom_get);
    }

    /**
     * Test the ability to allow models to provide their own custom attribute
     * getters for json attributes
     * @throws \Eloquent\Dialect\InvalidJsonException
     */
    public function testCustomSetter()
    {
        // Mock the model with data
        $mock = new MockJsonDialectModel();
        $mock->hintJsonStructure( 'foo', json_encode(['custom_set'=>null]) );

        // Execute the hint call
        $mock->addHintedAttributes();

        // Assert that the column were properly parsed and various bits have
        // been set on the model
        $this->assertTrue($mock->hasSetMutator('custom_set'));

        // Set a value
        $mock->custom_set = 'value';

        // Assert that the attribute was mutated by the mutator on our mock
        // model
        $this->assertEquals('custom value', $mock->custom_set);
    }
}