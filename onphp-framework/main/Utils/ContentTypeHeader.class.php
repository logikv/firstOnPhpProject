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
     * @ingroup Utils
     **/
    class ContentTypeHeader
    {
        private $mediaType = null;
        private $parameters = [];

        private $charset = null; // reference


        /**
         * @return null
         */
        public function getMediaType()
        {
            return $this->mediaType;
        }

        /**
         * @return ContentTypeHeader
         **/
        public function setMediaType($mediaType)
        {
            $this->mediaType = $mediaType;

            return $this;
        }

        /**
         * @return ContentTypeHeader
         **/
        public function setParameter($attribute, $value)
        {
            $this->parameters[$attribute] = $value;

            return $this;
        }

        /**
         * @param $attribute
         * @return $this
         * @throws MissingElementException
         */
        public function dropParameter($attribute)
        {
            if (!isset($this->parameters[$attribute])) {
                throw new MissingElementException();
            }

            unset($this->parameters[$attribute]);

            return $this;
        }

        /**
         * @param $attribute
         * @return bool
         */
        public function hasParameter($attribute)
        {
            return isset($this->parameters[$attribute]);
        }

        /**
         * @param $attribute
         * @return mixed
         * @throws MissingElementException
         */
        public function getParameter($attribute)
        {
            if (!isset($this->parameters[$attribute])) {
                throw new MissingElementException();
            }

            return $this->parameters[$attribute];
        }

        /**
         * @return ContentTypeHeader
         **/
        public function setParametersList($parameters)
        {
            Assert::isArray($parameters);

            $this->parameters = $parameters;

            return $this;
        }

        /**
         * @return array
         */
        public function getParametersList()
        {
            return $this->parameters;
        }

        /**
         * @return null
         */
        public function getCharset()
        {
            return $this->charset;
        }

        /**
         * @return ContentTypeHeader
         **/
        public function setCharset($charset)
        {
            if (!$this->charset) {
                $this->parameters['charset'] = $charset;
                $this->charset = &$this->parameters['charset'];
            } else {
                $this->charset = $charset;
            }

            return $this;
        }

        /**
         * @return ContentTypeHeader
         *
         * sample argument: text/html; charset="utf-8"
         **/
        public function import($string)
        {
            $this->charset = null;
            $this->parameters = [];
            $matches = [];

            if (
            preg_match(
                '~^[\s\t]*([^/\s\t;]+/[^\s\t;]+)[\s\t]*(.*)$~',
                $string, $matches
            )
            ) {
                $this->mediaType = $matches[1];
                $remainingString = $matches[2];

                preg_match_all(
                    '~;[\s\t]*([^\s\t\=]+)[\s\t]*\=[\s\t]*'    // 1: attribute
                    . '(?:([^"\s\t;]+)|(?:"(.*?(?<!\\\))"))'    // 2 or 3: value
                    . '[\s\t]*~',
                    $remainingString, $matches
                );

                foreach ($matches[1] as $i => $attribute) {
                    $attribute = strtolower($attribute);

                    $value =
                        empty($matches[2][$i])
                            ? $matches[3][$i]
                            : $matches[2][$i];

                    $this->parameters[$attribute] = $value;

                    if ($attribute == 'charset') // NOTE: reference
                    {
                        $this->charset = &$this->parameters[$attribute];
                    }
                }
            }

            return $this;
        }

        /**
         * @return string
         */
        public function toString() : string
        {
            $parts = [$this->mediaType];

            foreach ($this->parameters as $attribute => $value) {
                $parts[] = $attribute . '="' . $value . '"';
            }

            return implode('; ', $parts);
        }
    }
}

