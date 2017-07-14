<?php
/***************************************************************************
 *   Copyright (C) 2008 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * @ingroup Primitives
     **/
    class PrimitiveTimestampRange extends PrimitiveDateRange
    {

        function __construct($name)
        {
            parent::__construct($name);
        }

        /**
         * @return string
         */
        protected function getObjectName() : string
        {
            return 'TimestampRange';
        }

        /**
         * @param $string
         * @return TimestampRange
         * @throws WrongArgumentException
         */
        protected function makeRange($string)
        {
            if (strpos($string, ' - ') !== false) {
                list($first, $second) = explode(' - ', $string);

                return new TimestampRange(
                    new Timestamp(trim($first)),
                    new Timestamp(trim($second))
                );
            }

            throw new WrongArgumentException();
        }
    }
}