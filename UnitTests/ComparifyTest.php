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

	/**
	 * @test
	 */
	public function removesMultipleBlankLinesAfterTag()
	{
		$text = "<p>paragraph</p>



<p>other paragraph</p>";

		$result = "<p>paragraph</p>
<p>other paragraph</p>";

		$this->assertEquals($result, $this->comparify->transform($text));
	}

	/**
	 * @test
	 */
	public function removesMultipleBlankLines_2()
	{
		$text = "<p>This is the <b>simple case</b></p>


<p>This one has a <b>line break</b></p>";

		$result = "<p>This is the <b>simple case</b></p>
<p>This one has a <b>line break</b></p>";

		$this->assertEquals($result, $this->comparify->transform($text));
	}

	/**
	 * @test
	 */
	public function removesBlankLinesAfterCodeElementInsideOtherElement()
	{
		$text = "<div>

<code>
foo
</code>

</div>

";

		$result = "<div>
<code>
foo
</code>
</div>";

		$this->assertEquals($result, $this->comparify->transform($text));
	}

	/**
	 * @test
	 */
	public function removesBlankLinesAfterDoubleCodeElementInsideOtherElement()
	{
		$text = "<p><code>foo</code></p>
<pre><code>foo</code></pre>


<p><code>foo</code></p>
<p>paragraph</p>";

		$result = "<p><code>foo</code></p>
<pre><code>foo</code></pre>
<p><code>foo</code></p>
<p>paragraph</p>";

		$this->assertEquals($result, $this->comparify->transform($text));
	}

	/**
	 * @test
	 */
	public function elementDoesNotAcceptLesserThanSignInsideCode()
	{
		$text = "<pre><code>foo</code></pre>
<p>a</p>

<p>Markdown:</p>
<pre><code>foo</code></pre>
<pre><code>foo</code></pre>

";

		$result = "<pre><code>foo</code></pre>
<p>a</p>
<p>Markdown:</p>
<pre><code>foo</code></pre>
<pre><code>foo</code></pre>";

		$this->assertEquals($result, $this->comparify->transform($text));
	}

	/**
	 * @test
	 */
	public function removesBlankLineAfterElementContainingSelfClosingElement()
	{
		$text = "<div>
<img />

</div>";

		$result = "<div>
<img />
</div>";

		$this->assertEquals($result, $this->comparify->transform($text));
	}

	/**
	 * @test
	 */
	public function removesBlankLinesAfterTagRecursive()
	{
		$text = "<div>
<p>paragraph</p>

<p>other paragraph</p>
</div>";

		$result = "<div>
<p>paragraph</p>
<p>other paragraph</p>
</div>";

		$this->assertEquals($result, $this->comparify->transform($text));		
	}

	/**
	 * @test
	 */
	public function removesSpacesOnBlankLines()
	{
		$text = "<p>foo</p>
 
<p>bar</p>";

		$result = "<p>foo</p>
<p>bar</p>";

		$this->assertEquals($result, $this->comparify->transform($text));
	}

	/**
	 * @test
	 */
	public function removesTabsOnBlankLines()
	{
		$text = "<p>foo</p>
	
<p>bar</p>";

		$result = "<p>foo</p>
<p>bar</p>";

		$this->assertEquals($result, $this->comparify->transform($text));
	}

	/**
	 * @test
	 */
	public function removesBlankLineInsideElement()
	{
		$text = "<a>

foo
</a>";

		$result = "<a>
foo
</a>";

		$this->assertEquals($result, $this->comparify->transform($text));
	}

	/**
	 * @test
	 */
	public function doesntRemoveBlankLineInsideCodeElements()
	{
		$text = "<code>

bar
</code>";

		$result = "<code>

bar
</code>";

		$this->assertEquals($result, $this->comparify->transform($text));		
	}

	/**
	 * @test
	 */
	public function setsHeaderElementsOnOwnLine()
	{
		$text = "<p>paragraph</p><h1>header</h1>";

		$result = "<p>paragraph</p>
<h1>header</h1>";

		$this->assertEquals($result, $this->comparify->transform($text));		
	}

	/**
	 * @test
	 */
	public function setsBlockquotesOnOwnLine()
	{
		$text = "<p>paragraph</p><blockquote>foo</blockquote>";

		$result = "<p>paragraph</p>
<blockquote>foo</blockquote>";

		$this->assertEquals($result, $this->comparify->transform($text));
	}

	/**
	 * @test
	 */
	public function setsParagraphsOnOwnLine()
	{
		$text = "<a>b</a><p>paragraph</p>";

		$result = "<a>b</a>
<p>paragraph</p>";

		$this->assertEquals($result, $this->comparify->transform($text));
	}

	/**
	 * @test
	 */
	public function setsOrderedListItemsOnOwnLine()
	{
		$text = "<ul><li>item</li><li>item</li></ul>";

		$result = "<ul>
<li>item</li>
<li>item</li>
</ul>";

		$this->assertEquals($result, $this->comparify->transform($text));
	}

	/**
	 * @test
	 */
	public function setsListItemsContainingElementOnOwnLine()
	{
		$text = "<ol><li><em>a</em></li><li><em>b</em></li></ol>";

		$result = "<ol>
<li><em>a</em></li>
<li><em>b</em></li>
</ol>";

		$this->assertEquals($result, $this->comparify->transform($text));
	}

	/**
	 * @test
	 */
	public function removesWhiteSpaceBeforeTagsOnOwnLine()
	{
		$text = "<foo>bar</foo>
 <a>b</a>";

		$result = "<foo>bar</foo>
<a>b</a>";

		$this->assertEquals($result, $this->comparify->transform($text));		
	}

	/**
	 * @test
	 */
	public function tagsOnMultipleLinesArePutOnOneLine()
	{
		$text = "<foo
	id=\"bar\">bar</foo>";

		$result = "<foo id=\"bar\">bar</foo>";

		$this->assertEquals($result, $this->comparify->transform($text));		
	}

	/**
	 * @test
	 */
	public function setsHorizontalRuleOnOneLine()
	{
		$text = "<hr
	id=\"bar\" >";

		$result = "<hr id=\"bar\" />";

		$this->assertEquals($result, $this->comparify->transform($text));		
	}

	/**
	 * @test
	 */
	public function setsPreOnOwnLine()
	{
		$text = "<li><pre><code>code block
as first element of a list item
</code></pre></li>";

		$result = "<li>
<pre><code>code block
as first element of a list item
</code></pre>
</li>";

		$this->assertEquals($result, $this->comparify->transform($text));
	}

	/**
	 * @test
	 */
	public function opensClosedElementsOtherThanSelfClosing()
	{
		$text = "<div />";

		$result = "<div></div>";

		$this->assertEquals($result, $this->comparify->transform($text));
	}
}