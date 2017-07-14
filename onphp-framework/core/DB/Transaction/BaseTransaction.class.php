<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * Transaction's basis.
     *
     * @ingroup Transaction
     **/
    abstract class BaseTransaction
    {
        protected $db = null;

        /** @var IsolationLevel */
        protected $isoLevel = null;

        /** @var AccessMode */
        protected $mode = null;

        /**
         * BaseTransaction constructor.
         * @param DB $db
         */
        public function __construct(DB $db)
        {
            $this->db = $db;
        }

        /**
         * @return mixed
         */
        abstract public function flush();

        /**
         * @return DB
         **/
        public function getDB() : DB
        {
            return $this->db;
        }

        /**
         * @return BaseTransaction
         **/
        public function setDB(DB $db)
        {
            $this->db = $db;

            return $this;
        }

        /**
         * @return BaseTransaction
         **/
        public function setIsolationLevel(IsolationLevel $level)
        {
            $this->isoLevel = $level;

            return $this;
        }

        /**
         * @return BaseTransaction
         **/
        public function setAccessMode(AccessMode $mode)
        {
            $this->mode = $mode;

            return $this;
        }

        /**
         * @return string
         */
        protected function getBeginString() : string
        {
            $begin = 'start transaction';

            if ($this->isoLevel) {
                $begin .= ' ' . $this->isoLevel->toString();
            }

            if ($this->mode) {
                $begin .= ' ' . $this->mode->toString();
            }

            return $begin . ";\n";
        }
    }
}