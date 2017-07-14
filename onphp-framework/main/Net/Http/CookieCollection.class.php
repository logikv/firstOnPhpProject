<?php
/***************************************************************************
 *   Copyright (C) 2008 by Evgeny V. Kokovikhin                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/*$id$*/
namespace OnPhp {
    /**
     * using java.utils.Collection Interface
     * see http://java.sun.com/javase/6/docs/api/java/util/Collection.html
     *
     * @ingroup Http
     **/
    class CookieCollection extends AbstractCollection
    {

        /**
         * @return $this
         */
        public function httpSetAll()
        {
            foreach ($this->items as $item) {
                $item->httpSet();
            }

            return $this;
        }
    }
}

