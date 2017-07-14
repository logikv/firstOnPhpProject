<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * @ingroup Types
     **/
    final class BinaryType extends BasePropertyType
    {
        public function getPrimitiveName()
        {
            return 'binary';
        }

        public function getDeclaration()
        {
            return 'null';
        }

        public function toColumnType($length = null)
        {
            return '(new \\OnPhp\\DataType(\\OnPhp\\DataType::BINARY))';
        }

        public function isMeasurable()
        {
            return false;
        }
    }
}
?>