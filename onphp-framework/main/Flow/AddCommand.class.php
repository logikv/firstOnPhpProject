<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPhp {
    /**
     * Class AddCommand
     * @ingroup Flow
     * @package OnPhp
     */
    class AddCommand extends MakeCommand
    {
        protected function daoMethod()
        {
            return 'add';
        }
    }
}