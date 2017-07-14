<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * @ingroup Html
     **/
    class HtmlAssembler
    {
        private $tags = null;

        public function __construct($tags)
        {
            Assert::isTrue(current($tags) instanceof SgmlToken);

            $this->tags = $tags;
        }

        public static function makeDomNode(DOMNode $node)
        {
            $result = null;

            if ($node instanceof DOMElement) {

                $result = '<' . $node->nodeName;

                $attributes = self::getDomAttributes($node);

                if ($attributes) {
                    $result .= ' ' . $attributes;
                }

                if (!$node->firstChild) {
                    $result .= ' />';
                } else {
                    $result .= '>';
                }

                $childNode = $node->firstChild;

                while ($childNode) {
                    $result .= self::makeDomNode($childNode);
                    $childNode = $childNode->nextSibling;
                }

                if ($node->firstChild) {
                    $result .= '</' . $node->nodeName . '>';
                }

            } elseif ($node instanceof DOMCharacterData) {

                $result = $node->data;

            } else {
                throw new UnimplementedFeatureException(
                    'assembling of ' . get_class($node) . ' is not implemented yet'
                );
            }

            return $result;
        }

        private static function getDomAttributes(DOMNode $node)
        {
            $result = null;

            $attributes = [];

            if ($node->attributes) {
                $i = 0;

                while ($item = $node->attributes->item($i)) {
                    $attributes[] = $item->name . '="' . $item->value . '"';

                    ++$i;
                }
            }

            if ($attributes) {
                $result = implode(' ', $attributes);
            }

            return $result;
        }

        public function getHtml()
        {
            $result = null;

            foreach ($this->tags as $tag) {
                $result .= self::makeTag($tag);
            }

            return $result;
        }

        public static function makeTag(SgmlToken $tag)
        {
            if ($tag instanceof Cdata) {
                $result = $tag->getData();
            } elseif ($tag instanceof SgmlIgnoredTag) {
                Assert::isNotNull($tag->getId());

                $result = '<' . $tag->getId()
                    . $tag->getCdata()->getData()
                    . $tag->getEndMark() . '>';

            } elseif ($tag instanceof SgmlOpenTag) {
                Assert::isNotNull($tag->getId());

                $attributes = self::getAttributes($tag);

                $result = '<' . $tag->getId()
                    . ($attributes ? ' ' . $attributes : null)
                    . ($tag->isEmpty() ? '/' : null) . '>';

            } elseif ($tag instanceof SgmlEndTag) {
                $result = '</' . $tag->getId() . '>';

            } else {
                throw new WrongArgumentException(
                    "don't know how to assemble tag class '"
                    . get_class($tag) . "'"
                );
            }

            return $result;
        }

        private static function getAttributes(SgmlOpenTag $tag)
        {
            $attributes = [];

            foreach ($tag->getAttributesList() as $name => $value) {
                if ($value === null) {
                    $quotedValue = null;
                } else // FIXME: is multibyte safe?
                {
                    $quotedValue = '="' . str_replace('"', '&quot;', $value) . '"';
                }

                $attributes[] = $name . $quotedValue;
            }

            return implode(' ', $attributes);
        }
    }
}

