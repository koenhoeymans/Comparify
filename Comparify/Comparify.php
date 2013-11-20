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
	/**
	 * @param string $input
	 * @return string
	 */
	public function transform($text)
	{
		$text = trim($text);
		$text = $this->handleSelfClosingTags($text);

		return $text;
	}

	private function handleSelfClosingTags($text)
	{
		$attributes =
			"(?<attributes>
				(
				\s+
				\w+(=(?:\"[^\"]*?\"|\'[^\']*?\'|[^\'\">\s]+))?
				)*
			)";

		$pattern = 	"@<(?<tag>hr|div|br)" . $attributes . "/?>@x";

		return preg_replace($pattern, '<\1 \2/>', $text);
	}
}