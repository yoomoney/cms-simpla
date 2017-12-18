<?php

namespace Tests\YaMoney\Common;

use PHPUnit\Framework\TestCase;
use YaMoney\Common\AbstractObject;

class AbstractObjectTest extends TestCase
{
    protected function getTestInstance()
    {
        return new TestAbstractObject();
    }

    /**
     * @dataProvider offsetDataProvider
     * @param $value
     * @param $exists
     */
    public function testOffsetExists($value, $exists)
    {
        $instance = $this->getTestInstance();
        if ($exists) {
            self::assertTrue($instance->offsetExists($value));
            self::assertTrue(isset($instance[$value]));
            self::assertTrue(isset($instance->{$value}));
        } else {
            self::assertFalse($instance->offsetExists($value));
            self::assertFalse(isset($instance[$value]));
            self::assertFalse(isset($instance->{$value}));

            $instance->offsetSet($value, $value);
            self::assertTrue($instance->offsetExists($value));
            self::assertTrue(isset($instance[$value]));
            self::assertTrue(isset($instance->{$value}));
        }
    }

    /**
     * @dataProvider offsetDataProvider
     * @param $value
     */
    public function testOffsetGet($value)
    {
        $instance = $this->getTestInstance();

        $tmp = $instance->offsetGet($value);
        self::assertNull($tmp);
        $tmp = $instance[$value];
        self::assertNull($tmp);
        $tmp = $instance->{$value};
        self::assertNull($tmp);

        $instance->offsetSet($value, $value);

        $tmp = $instance->offsetGet($value);
        self::assertEquals($value, $tmp);
        $tmp = $instance[$value];
        self::assertEquals($value, $tmp);
        $tmp = $instance->{$value};
        self::assertEquals($value, $tmp);
    }

    /**
     * @dataProvider offsetDataProvider
     * @param $value
     * @param $exists
     */
    public function testOffsetUnset($value, $exists)
    {
        $instance = $this->getTestInstance();
        if ($exists) {
            self::assertTrue($instance->offsetExists($value));
            $instance->offsetUnset($value);
            self::assertTrue($instance->offsetExists($value));
            unset($instance[$value]);
            self::assertTrue($instance->offsetExists($value));
            unset($instance->{$value});
            self::assertTrue($instance->offsetExists($value));
        } else {
            self::assertFalse($instance->offsetExists($value));
            $instance->offsetUnset($value);
            self::assertFalse($instance->offsetExists($value));
            unset($instance[$value]);
            self::assertFalse($instance->offsetExists($value));
            unset($instance->{$value});
            self::assertFalse($instance->offsetExists($value));

            $instance->{$value} = $value;
            self::assertTrue($instance->offsetExists($value));
            $instance->offsetUnset($value);
            self::assertFalse($instance->offsetExists($value));

            $instance->{$value} = $value;
            self::assertTrue($instance->offsetExists($value));
            unset($instance[$value]);
            self::assertFalse($instance->offsetExists($value));

            $instance->{$value} = $value;
            self::assertTrue($instance->offsetExists($value));
            unset($instance->{$value});
            self::assertFalse($instance->offsetExists($value));
        }
    }

    public function offsetDataProvider()
    {
        return array(
            array('property', true),
            array('not_exists', false),
        );
    }
}

class TestAbstractObject extends AbstractObject
{
    private $_property;

    public function getProperty()
    {
        return $this->_property;
    }

    public function setProperty($value)
    {
        $this->_property = $value;
    }
}