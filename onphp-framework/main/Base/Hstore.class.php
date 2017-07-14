<?php
/***************************************************************************
 *   Copyright (C) 2009 by Sergey S. Sergeev                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * @ingroup Helpers
     **/
    class Hstore implements Stringable
    {
        protected $properties = [];

        function __construct($string)
        {
            $this
                ->toValue($string);
        }


        /**
         * @return Hstore
         **/
        public function toValue($raw)
        {
            if (!$raw)
                return $this;

            $this->properties = $this->parseString($raw);

            return $this;
        }

        private function parseString($raw)
        {
            $raw = preg_replace('/([$])/u', "\\\\$1", $raw);
            $unescapedHStore = [];
            eval('$unescapedHStore = array(' . $raw . ');');
            return $unescapedHStore;
        }

        /**
         * Create Hstore by array.
         *
         * @return Hstore
         **/
        public static function make($array)
        {
            $self = new self();

            return $self->setList($array);
        }

        /**
         * @return Hstore
         **/
        public function setList($array)
        {
            $this->properties = $array;

            return $this;
        }

        public function getList()
        {
            return $this->properties;
        }

        public function get($key)
        {
            if (!$this->isExists($key))
                throw new ObjectNotFoundException("Property with name '{$key}' does not exists");

            return $this->properties[$key];
        }

        public function isExists($key)
        {
            return array_key_exists($key, $this->properties);
        }

        /**
         * @return Hstore
         **/
        public function set($key, $value)
        {
            $this->properties[$key] = $value;

            return $this;
        }

        /**
         * @return Hstore
         **/
        public function drop($key)
        {
            unset($this->properties[$key]);

            return $this;
        }

        public function toString() : string
        {
            if (empty($this->properties))
                return null;

            $string = '';

            foreach ($this->properties as $k => $v) {
                if ($v !== null)
                    $string .= "\"{$this->quoteValue($k)}\"=>\"{$this->quoteValue($v)}\",";
                else
                    $string .= "\"{$this->quoteValue($k)}\"=>NULL,";
            }

            return $string;
        }

        protected function quoteValue($value)
        {
            return addslashes($value);
        }
    }
}