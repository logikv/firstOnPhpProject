<?php
/****************************************************************************
 *   Copyright (C) 2009 by Vladlen Y. Koshelev                              *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
namespace OnPhp {
    /**
     * @ingroup OQL
     **/
    class OqlHavingClause extends OqlQueryExpressionClause
    {

        /**
         * @param OqlQueryExpression $expression
         * @throws WrongArgumentException
         */
        protected static function checkExpression(OqlQueryExpression $expression)
        {
            Assert::isInstance($expression->getClassName(), 'HavingProjection');
        }

        /**
         * @return HavingProjection
         **/
        public function toProjection()
        {
            return $this->toLogic();
        }
    }

}