<?php
/****************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich, Konstantin V. Arkhipov *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
namespace OnPhp {
    /**
     * Full-text ranking. Mostly used in "ORDER BY".
     *
     * @ingroup OSQL
     **/
    class FullTextRank extends FullText
    {
        /**
         * @param Dialect $dialect
         * @throws UnimplementedFeatureException
         */
        public function toDialectString(Dialect $dialect)
        {
            return
                $dialect->fullTextRank(
                    $this->field,
                    $this->words,
                    $this->logic
                );
        }
    }
}