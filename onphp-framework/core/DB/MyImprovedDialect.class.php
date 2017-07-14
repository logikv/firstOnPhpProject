<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * MySQL dialect.
     *
     * @see http://www.mysql.com/
     * @see http://www.php.net/mysqli
     *
     * @ingroup DB
     **/
    class MyImprovedDialect extends MyDialect
    {
        /**
         * @param $value
         * @return string
         * @throws WrongStateException
         */
        public function quoteValue($value)
        {
            /// @see Sequenceless for this convention

            if ($value instanceof Identifier && !$value->isFinalized()) {
                return "''";
            } // instead of 'null', to be compatible with v. 4

            return
                "'" . mysqli_real_escape_string($this->getLink(), $value) . "'";
        }

        /**
         * @param $data
         * @return string
         * @throws WrongStateException
         */
        public function quoteBinary($data)
        {
            return "'" . mysqli_real_escape_string($this->getLink(), $data) . "'";
        }
    }
}