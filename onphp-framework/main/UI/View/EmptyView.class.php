<?php
/***************************************************************************
 *   Copyright (C) 2008 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * @ingroup Flow
     **/
    class EmptyView implements View, Stringable
    {
        /**
         * @param null $model
         * @return $this
         */
        public function render(/* Model */
            $model = null
        )
        {
            return $this;
        }

        /**
         * @return string
         */
        public function toString() : string
        {
            return '';
        }
    }
}