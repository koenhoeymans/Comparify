<?php

/**
 * @package Comparify
 */
namespace Comparify;

/**
 * @package Comparify
 */
class Comparify
{
	private $attributes =
		"(?<attributes>
			(
			\s+
			\w+(=(?:\"[^\"]*?\"|\'[^\']*?\'|[^\'\">\s]+))?
			)*
		)";

	private function selfClosingElement()
	{
		return "<(?<full_tag>(?<tag>hr|br)" . $this->attributes . ")[ ]?/?>";
	}

	private function element(array $allowedTags = null)
	{
		if ($allowedTags === null)
		{
			$allowedTags = '\w+';
		}
		else
		{
			$allowedTags = implode('|', $allowedTags); 
		}

		return
			"
			(?<element>
			(?<full_tag><(?<tag>" . $allowedTags . ")" . $this->attributes . ">)
				(?<content>
					(
						[^<]
						|
						(?&element)
						|
						<code>(.|[\n])+</code>
					)*
				)
			</\g{tag}>
			)";
	}

	/**
	 * @param string $input
	 * @return string
	 */
	public function transform($text)
	{
		$text = $this->handleSelfClosingTags($text);
		$text = $this->removeSpacingOnBlankLines($text);
		$text = $this->setOpeningTagsOnOneLine($text);
		$text = $this->setEmptyTagsOnOneLine($text);
		$text = $this->removeWhitespaceBeforeTagsOnOwnLine($text);
		$text = $this->setBlockElementsOnOwnLine($text);
		$text = $this->removeBlankLineInsideElement($text);
		$text = $this->removeBlankLineBetweenElements($text);
		$text = trim($text);

		return $text;
	}

	private function handleSelfClosingTags($text)
	{
		return preg_replace('@' . $this->selfClosingElement() . '@', '<\1 \2/>', $text);
	}

	private  function removeSpacingOnBlankLines($text)
	{
		return preg_replace("@(?<=^|\n)([ ]|\t)+(?=\n|$)@", "", $text);
	}

	private function setOpeningTagsOnOneLine($text)
	{
		$pattern = '@' . $this->element() . '@x';

		return preg_replace_callback(
			$pattern,
			function($match)
			{
				$openingTag = preg_replace("@\n([ ]+|\t+)@", ' ', $match['full_tag']);
				return $openingTag . $match['content'] . '</' . $match['tag'] . '>';
			},
			$text
		);
	}

	private function setEmptyTagsOnOneLine($text)
	{
		$pattern = '@' . $this->selfClosingElement() . '@x';

		return preg_replace_callback(
			$pattern,
			function($match)
			{
				return '<' . preg_replace("@\n([ ]+|\t+)@", ' ', $match['full_tag']) . ' />';
			},
			$text
		);
	}

	private function removeBlankLineBetweenElements($text)
	{
		$pattern =
			"@" . $this->element() . "[\n][\n]+	@x";

		return preg_replace_callback(
			$pattern,
			function($match)
			{
				return $match['element'] . "\n";
			},
			$text
		);
	}

	private function removeBlankLineInsideElement($text)
	{
		$pattern =
			"@
			(?<=^|[\n])
			(?<html>
				(?<full_tag><(?!code)(?<tag>\w+)" . $this->attributes . ">)
					(?<content>
						(
							[^<]
							|
							(?&html)
							|
							<code>(.|[\n])+</code>
						)*
					)
				</\g{tag}>
			)
			(?=[\n]|$)
			@x";

		return preg_replace_callback(
			$pattern,
			function($match)
			{
				$content = $this->removeBlankLineInsideElement($match['content']);
				$content = preg_replace("@\n\n@", "\n", $content);
				return $match['full_tag'] . $content . '</' . $match['tag'] . ">";
			},
			$text
		);		
	}

	private function setBlockElementsOnOwnLine($text)
	{
		$tags = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'p', 'ul', 'li');
		$pattern = "@[\n]*" . $this->element($tags) . "[\n]*@x";

		return preg_replace_callback(
			$pattern,
			function($match)
			{
				$content = $this->setBlockElementsOnOwnLine($match['content']);
				return "\n"
					. $match['full_tag'] . $content
					. '</' . $match['tag'] . ">\n";
			},
			$text
		);
	}

	private function removeWhitespaceBeforeTagsOnOwnLine($text)
	{
		$pattern =
			"@
			(?<=[\n]|^)
				([ \t]+)
				(
					<(\w+)" . $this->attributes . ">
					|
					</(\w+)>
				)
			@x";

		return preg_replace($pattern, '\2', $text);
	}
}