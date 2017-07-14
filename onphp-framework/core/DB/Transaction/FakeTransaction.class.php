<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * Transaction-like wrapper around DB's queryNull.
     *
     * @ingroup Transaction
     **/
    class FakeTransaction extends BaseTransaction
    {

        /**
         * @param Query $query
         * @return FakeTransaction
         * @throws WrongArgumentException
         */
        public function add(Query $query) : FakeTransaction
        {
            $this->db->queryNull($query);

            return $this;
        }

        /**
         * @return FakeTransaction
         **/
        public function flush() : FakeTransaction
        {
            return $this;
        }
    }
}