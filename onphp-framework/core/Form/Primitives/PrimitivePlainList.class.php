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
     * @ingroup Primitives
     **/
    class PrimitivePlainList extends PrimitiveList
    {
        /**
         * @return PrimitivePlainList
         **/
        public function setList($list) : PrimitivePlainList
        {
            $this->list = array_combine($list, $list);

            return $this;
        }
    }
}
