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
     * @ingroup Projections
     **/
    abstract class AggregateProjection extends BaseProjection
    {
        /**
         * @return JoinCapableQuery
         **/
        public function process(Criteria $criteria, JoinCapableQuery $query)
        {
            Assert::isNotNull($this->property);

            return
                $query
                    ->get(
                        (new SQLFunction($this->getFunctionName(), $criteria->getDao()->guessAtom($this->property, $query)))
                            ->setAlias($this->alias)
                    );
        }

        abstract public function getFunctionName();
    }
}