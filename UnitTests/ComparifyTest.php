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

	/**
	 * @test
	 */
	public function stripsWhitespaceOfStart()
	{
		$this->assertEquals('foo', $this->comparify->transform(' foo'));
	}

	/**
	 * @test
	 */
	public function stripsWhitespaceOfEnd()
	{
		$this->assertEquals('foo', $this->comparify->transform('foo '));
	}

	/**
	 * @test
	 */
	public function addsWhitespaceBeforeClosingOfSelfClosingTag()
	{
		$this->assertEquals('<br />', $this->comparify->transform('<br/>'));
	}

	/**
	 * @test
	 */
	public function removesBlankLineAfterTag()
	{
		$text = "<p>paragraph</p>

<p>other paragraph</p>";

		$result = "<p>paragraph</p>
<p>other paragraph</p>";

		$this->assertEquals($result, $this->comparify->transform($text));
	}
}