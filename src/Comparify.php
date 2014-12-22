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
    private $selfClosingTags = 'hr|br|img';

    private $attributes =
        "(?J)(?<attributes>
			(
			\s+
			\w+(=(?:\"[^\"]*?\"|\'[^\']*?\'|[^\'\">\s]+))?
			)*
		)";

    private function selfClosingElement()
    {
        return "
			<(?<self_closing_tag>("
            .$this->selfClosingTags.")".$this->attributes.")[ ]?/?>";
    }

    private function element(array $allowedTags = null, array $deniedTags = null)
    {
        if ($allowedTags === null) {
            $allowedTags = '\w+';
        } else {
            $allowedTags = implode('|', $allowedTags);
        }

        if ($deniedTags === null) {
            $deniedTags = '';
        } else {
            $deniedTags = '(?!'.implode('|', $deniedTags).')';
        }

        return
            "
			(?<element>
			(?<full_tag>
				<"
                    .$deniedTags."(?<tag>".$allowedTags.")"
                    .$this->attributes
                .">
			)
				(?<content>
					(
						[^<]
						|
						<code>[^<]+?</code>
						|
						<(?<subtag>\w+)".$this->attributes.">
						(?&content)
						</\g{subtag}>
						|
						".$this->selfClosingElement()."
					)*
				)
			</\g{tag}>
			)";
    }

    /**
     * @param  string $input
     * @return string
     */
    public function transform($text)
    {
        $text = $this->handleSelfClosingTags($text);
        $text = $this->openClosedElements($text);
        $text = $this->removeSpacingOnBlankLines($text);
        $text = $this->setOpeningTagsOnOneLine($text);
        $text = $this->setEmptyTagsOnOneLine($text);
        $text = $this->removeWhitespaceBeforeTagsOnOwnLine($text);
        $text = $this->setSelectedElementsOnOwnLine($text);
        $text = $this->removeBlankLineInsideElement($text);
        $text = $this->removeBlankLineBetweenElements($text);
        $text = $this->trimeWhitespaceAtStartAndEndInsideElements($text);
        $text = trim($text);

        return $text;
    }

    private function handleSelfClosingTags($text)
    {
        return preg_replace('@'.$this->selfClosingElement().'@', '<\1 \2/>', $text);
    }

    private function openClosedElements($text)
    {
        return preg_replace(
            '@(?!'.$this->selfClosingElement().')<(?<tag>\w+)('.$this->attributes.')[ ]/>@x',
            '<\6\7></\6>',
            $text
        );
    }

    private function removeSpacingOnBlankLines($text)
    {
        return preg_replace("@(?<=^|\n)([ ]|\t)+(?=\n|$)@", "", $text);
    }

    private function setOpeningTagsOnOneLine($text)
    {
        $pattern = '@'.$this->element().'@x';

        return preg_replace_callback(
            $pattern,
            function ($match) {
                $openingTag = preg_replace("@\n([ ]+|\t+)@", ' ', $match['full_tag']);

                return $openingTag.$match['content'].'</'.$match['tag'].'>';
            },
            $text
        );
    }

    private function setEmptyTagsOnOneLine($text)
    {
        $pattern = '@'.$this->selfClosingElement().'@x';

        return preg_replace_callback(
            $pattern,
            function ($match) {
                return
                    '<'
                    .preg_replace("@\n([ ]+|\t+)@", ' ', $match['self_closing_tag'])
                    .' />';
            },
            $text
        );
    }

    private function removeBlankLineBetweenElements($text)
    {
        $pattern = "@".$this->element()."[\n][\n]+	@x";

        return preg_replace_callback(
            $pattern,
            function ($match) {
                return $match['element']."\n";
            },
            $text
        );
    }

    private function removeBlankLineInsideElement($text)
    {
        $pattern = "@(?<=^|[\n])".$this->element(null, array('code'))."(?=[\n]|$)@x";

        return preg_replace_callback(
            $pattern,
            function ($match) {
                $content = $this->removeBlankLineInsideElement($match['content']);
                $content = preg_replace("@\n\n@", "\n", $content);

                return $match['full_tag'].$content.'</'.$match['tag'].">";
            },
            $text
        );
    }

    private function setSelectedElementsOnOwnLine($text)
    {
        $tags = array(
            'blockquote', 'dd', 'dl', 'dt', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'li', 'ol', 'p', 'pre', 'table', 'tbody', 'td', 'tfoot',
            'th', 'thead', 'tr', 'ul',
        );
        $pattern = "@[\n]*((?J)"
            .$this->element($tags)."|".$this->selfClosingElement()
            .")[\n]*@x";

        return preg_replace_callback(
            $pattern,
            function ($match) {
                $content = $this->setSelectedElementsOnOwnLine($match['content']);
                if (!empty($match['self_closing_tag'])) {
                    return "\n<".$match['self_closing_tag']." />\n";
                } else {
                    return "\n"
                    .$match['full_tag'].$content
                    .'</'.$match['tag'].">\n";
                }
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
					<(\w+)".$this->attributes.">
					|
					</(\w+)>
				)
			@x";

        return preg_replace($pattern, '\2', $text);
    }

    private function trimeWhitespaceAtStartAndEndInsideElements($text)
    {
        $tags = array('p');
        $pattern = "@".$this->element($tags)."@x";

        return preg_replace_callback(
            $pattern,
            function ($match) {
                $content = $this->trimeWhitespaceAtStartAndEndInsideElements($match['content']);

                return $match['full_tag'].trim($content)
                            .'</'.$match['tag'].">";
            },
            $text
        );
    }
}
