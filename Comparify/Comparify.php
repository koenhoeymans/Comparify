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
		$text = $this->removeBlankLineBetweenElements($text);
		$text = $this->setHeadersOnOwnLine($text);
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
			[\n]
			@x";

		return preg_replace_callback(
			$pattern,
			function($match)
			{
				return $match['html'];
			},
			$text
		);
	}

	private function setHeadersOnOwnLine($text)
	{
		$pattern =
			"@
			[\n]*
			(?<html>
				<(?<tag>h1|h2|h3|h4|h5|h6)" . $this->attributes . ">
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
				return "\n" . $match['html'] . "\n";
			},
			$text
		);
	}
}