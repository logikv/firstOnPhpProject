<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Sveta A. Smirnova                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * @ingroup Filters
     **/
    class PCREFilter implements Filtrator
    {
        private $search = null;
        private $replace = null;
        private $limit = -1;

        /**
         * @param $search
         * @param $replace
         * @return PCREFilter
         */
        public function setExpression($search, $replace) : PCREFilter
        {
            $this->search = $search;
            $this->replace = $replace;

            return $this;
        }

        /**
         * @param $value
         * @return mixed
         */
        public function apply($value)
        {
            return
                preg_replace(
                    $this->search,
                    $this->replace,
                    $value,
                    $this->limit
                );
        }

        /**
         * @return PCREFilter
         **/
        public function setLimit($limit)
        {
            $this->limit = $limit;

            return $this;
        }
    }
}
