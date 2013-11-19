<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'TestHelper.php';

class Comparify_ComparifyTest extends PHPUnit_Framework_TestCase
{
	public function setup()
	{
		$this->comparify = new \Comparify\Comparify();
	}

	/**
	 * @test
	 */
	public function returnsString()
	{
		$this->assertTrue(is_string($this->comparify->transform(('foo'))));
	}
}