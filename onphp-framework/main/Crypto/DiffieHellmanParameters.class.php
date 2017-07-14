<?php
/***************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * @see http://tools.ietf.org/html/rfc2631
     *
     * @ingroup Crypto
     **/
    class DiffieHellmanParameters
    {
        private $gen = null;
        private $modulus = null;

        /**
         * DiffieHellmanParameters constructor.
         * @param BigInteger $gen
         * @param BigInteger $modulus
         */
        public function __construct(BigInteger $gen, BigInteger $modulus)
        {
            Assert::brothers($gen, $modulus);

            $this->gen = $gen;
            $this->modulus = $modulus;
        }

        /**
         * @return BigInteger
         **/
        public function getGen()
        {
            return $this->gen;
        }

        /**
         * @return BigInteger
         **/
        public function getModulus()
        {
            return $this->modulus;
        }
    }
}