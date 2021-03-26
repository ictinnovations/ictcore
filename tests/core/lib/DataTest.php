<?php

namespace ICT\Core\Test;

use ICT\Core\Data;
use PHPUnit_Framework_TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2017-01-07 at 13:50:24.
 */
class DataTest extends PHPUnit_Framework_TestCase
{

  /**
   * @var Data
   */
  protected $object;
  protected $data = array(
      'element1' => 'Value for first element',
      'array1' => array(
          'child1' => 'Child one',
          'child2' => 'Child two',
          'child3' => array(
              'dog1' => 'tommy',
              'dog2' => 'jack'
          )
      ),
      'element2' => 'Value for third element'
  );

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    $this->object = new Data($this->data);
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    unset($this->object);
  }

  /**
   * @covers ICT\Core\Data::merge
   */
  public function testMerge()
  {
    $newData = array(
        'array1' => array(
            'child3' => array(
                'dog3' => 'some deep test'
            )
        ),
        'element2' => 'replace test pass',
        'element3' => 'new value test'
    );
    $newObject = new Data($newData);
    $this->object->merge($newObject);
    $this->assertArrayHasKey('element3', $this->object);
    $this->assertCount(4, $this->object);
    $this->assertEquals($newData['element2'], $this->object->element2);
  }

  /**
   * @covers ICT\Core\Data::getDataCopy
   */
  public function testGetDataCopy()
  {
    $this->assertArraySubset($this->object->getDataCopy(), $this->data);
  }

  /**
   * @covers ICT\Core\Data::offsetExists
   */
  public function testOffsetExists()
  {
    $this->assertArrayHasKey('array1:child3:dog1', $this->object);
  }

  /**
   * @covers ICT\Core\Data::offsetGet
   */
  public function testOffsetGet()
  {
    $this->assertSame($this->object['element1'], $this->data['element1']);
    $this->assertSame($this->object['array1:child3:dog1'], $this->data['array1']['child3']['dog1']);
  }

  /**
   * @covers ICT\Core\Data::offsetSet
   */
  public function testOffsetSet()
  {
    $this->object['test1'] = 'level1';
    $this->assertSame($this->object['test1'], 'level1');
    $this->object['test2:element1'] = 'level2';
    $this->assertSame($this->object['test2:element1'], 'level2');
  }

  /**
   * @covers ICT\Core\Data::offsetUnset
   */
  public function testOffsetUnset()
  {
    unset($this->object['element1']);
    $this->assertNull($this->object['element1']);
  }

  /**
   * @covers ICT\Core\Data::__isset
   */
  public function test__isset()
  {
    $this->assertArrayHasKey('array1:child3:dog1', $this->object);
  }

  /**
   * @covers ICT\Core\Data::__get
   */
  public function test__get()
  {
    $this->assertSame($this->object->element1, $this->data['element1']);
    $this->assertSame($this->object->{'array1:child3:dog2'}, $this->data['array1']['child3']['dog2']);
  }

  /**
   * @covers ICT\Core\Data::__set
   */
  public function test__set()
  {
    $this->object->{'test1'} = 'level1';
    $this->assertSame($this->object->{'test1'}, 'level1');
    $this->object->{'test2:deep'} = 'level2';
    $this->assertSame($this->object->{'test2:deep'}, 'level2');
  }

  /**
   * @covers ICT\Core\Data::__unset
   */
  public function test__unset()
  {
    unset($this->object->element1);
    $this->assertNull($this->object->element1);
  }

}