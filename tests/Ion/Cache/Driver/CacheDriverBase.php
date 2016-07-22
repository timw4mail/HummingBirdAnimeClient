<?php

trait CacheDriverBase {
	
	protected $foo = [
		'bar' => [
			'baz' => 'foobar'
		]
	];
	
	protected $bar = 'secondvalue';
	
	public function testHasCacheDriver()
	{
		$this->assertTrue((bool) $this->driver);
	}
	
	public function testDriverGetSet()
	{
		$this->driver->set('foo', $this->foo);
		$this->driver->set('bar', 'baz');
		$this->assertEquals($this->driver->get('foo'), $this->foo);
		$this->assertEquals($this->driver->get('bar'), 'baz');
	}
	
	public function testInvalidate()
	{
		$this->driver->set('foo', $this->foo);
		$this->driver->invalidate('foo');
		$this->assertEmpty($this->driver->get('foo'));
	}
	
	public function testInvalidateAll()
	{
		$this->driver->set('foo', $this->foo);
		$this->driver->set('bar', $this->bar);
		
		$this->driver->invalidateAll();
		
		$this->assertEmpty($this->driver->get('foo'));
		$this->assertEmpty($this->driver->get('bar'));
	}
}