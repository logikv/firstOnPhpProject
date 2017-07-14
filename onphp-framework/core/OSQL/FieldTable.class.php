<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * @ingroup OSQL
     * @ingroup Module
     **/
    abstract class FieldTable extends Castable
    {
        /** @var null */
        protected $field = null;

        /**
         * FieldTable constructor.
         * @param $field
         */
        public function __construct($field)
        {
            $this->field = $field;
        }

        /**
         * @return null
         */
        public function getField()
        {
            return $this->field;
        }

        /**
         * @param Dialect $dialect
         * @return string
         */
        public function toDialectString(Dialect $dialect)
        {
            $out = $dialect->fieldToString($this->field);

            return
                $this->cast
                    ? $dialect->toCasted($out, $this->cast)
                    : $out;
        }
    }
}