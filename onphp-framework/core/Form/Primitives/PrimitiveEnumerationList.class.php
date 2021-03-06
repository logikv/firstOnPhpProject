<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Ivan Y. Khvostishkov                       *
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
    class PrimitiveEnumerationList extends PrimitiveEnumeration
    {
        protected $value = [];

        /**
         * @return PrimitiveEnumerationList
         */
        public function clean() : PrimitiveEnumerationList
        {
            parent::clean();

            // restoring our very own default
            $this->value = [];

            return $this;
        }


        /**
         * @param Enumeration $value
         * @return PrimitiveEnumerationList
         * @throws WrongArgumentException
         */
        public function setValue($value) : PrimitiveEnumerationList
        {
            if ($value) {
                Assert::isArray($value);
                Assert::isInstance(current($value), 'Enumeration');
            }

            $this->value = $value;

            return $this;
        }

        /**
         * @param $value
         * @return bool|null
         * @throws WrongStateException
         */
        public function importValue($value)
        {
            if (is_array($value)) {
                try {
                    Assert::isInteger(current($value));

                    return $this->import(
                        [$this->name => $value]
                    );
                } catch (WrongArgumentException $e) {
                    return $this->import(
                        [$this->name => ArrayUtils::getIdsArray($value)]
                    );
                }
            }

            return parent::importValue($value);
        }

        /**
         * @param $scope
         * @return bool|null
         * @throws WrongStateException
         */
        public function import($scope)
        {
            if (!$this->className) {
                throw new WrongStateException(
                    "no class defined for PrimitiveIdentifierList '{$this->name}'"
                );
            }

            if (!BasePrimitive::import($scope)) {
                return null;
            }

            if (!is_array($scope[$this->name])) {
                return false;
            }

            $list = array_unique($scope[$this->name]);

            $values = [];

            foreach ($list as $id) {
                if (!Assert::checkInteger($id)) {
                    return false;
                }

                $values[] = $id;
            }

            $objectList = [];

            foreach ($values as $value) {
                $className = $this->className;
                $objectList[] = new $className($value);
            }

            if (count($objectList) == count($values)) {
                $this->value = $objectList;
                return true;
            }

            return false;
        }

        /**
         * @return array|null
         */
        public function exportValue()
        {
            if (!$this->value) {
                return null;
            }

            return ArrayUtils::getIdsArray($this->value);
        }
    }
}