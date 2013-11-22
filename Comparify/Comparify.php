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

	/**
	 * @param string $input
	 * @return string
	 */
	public function transform($text)
	{
		$text = $this->handleSelfClosingTags($text);
		$text = $this->removeWhitespaceBeforeTagsOnOwnLine($text);
		$text = $this->setBlockElementsOnOwnLine($text);
		$text = $this->removeBlankLineBetweenElements($text);
		$text = trim($text);

		return $text;
	}

	private function handleSelfClosingTags($text)
	{
		$pattern = 	"@<(?<tag>hr|div|br)" . $this->attributes . "/?>@x";

		return preg_replace($pattern, '<\1 \2/>', $text);
	}

	private function removeBlankLineBetweenElements($text)
	{
		$pattern =
			"@
			(?<html>
				<(?<tag>\w+)" . $this->attributes . ">
					(?<content>
						(
							[^<]
							|
							(?&html)
						)*
					)
				</\g{tag}>
			)
			[\n][\n]
			@x";

		return preg_replace_callback(
			$pattern,
			function($match)
			{
				return $match['html'] . "\n";
			},
			$text
		);
	}

	private function setBlockElementsOnOwnLine($text)
	{
		$tags = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'p');
		$tags = implode('|', $tags);

		$pattern =
			"@
			[\n]*
			(?<html>
				(?<full_tag><(?<tag>" . $tags . ")" . $this->attributes . ">)
					(?<content>
						(
							[^<]
							|
							(?&html)
						)*
					)
				</\g{tag}>
			)
			[\n]*
			@x";

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