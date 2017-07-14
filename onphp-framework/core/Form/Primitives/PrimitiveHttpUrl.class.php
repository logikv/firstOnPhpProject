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
    class PrimitiveHttpUrl extends PrimitiveString
    {
        /**
         * @var bool
         */
        private $checkPrivilegedPorts = false;

        /**
         * @param bool $check
         * @return PrimitiveHttpUrl
         */
        public function setCheckPrivilegedPorts($check = true) : PrimitiveHttpUrl
        {
            $this->checkPrivilegedPorts = $check ? true : false;

            return $this;
        }

        /**
         * @param $value
         * @return bool|null
         */
        public function importValue($value)
        {
            if ($value instanceof HttpUrl) {

                return
                    $this->import(
                        [$this->getName() => $value->toString()]
                    );
            } elseif (is_scalar($value)) {
                return parent::importValue($value);
            }

            return parent::importValue(null);
        }

        /**
         * @param $scope
         * @return bool|null
         */
        public function import($scope)
        {
            if (!$result = parent::import($scope)) {
                return $result;
            }

            try {
                $this->value =
                    (new HttpUrl())
                        ->parse($this->value)
                        ->setCheckPrivilegedPorts($this->checkPrivilegedPorts);
            } catch (WrongArgumentException $e) {
                $this->value = null;

                return false;
            }

            if (!$this->value->isValid()) {
                $this->value = null;
                return false;
            }

            $this->value->normalize();

            return true;
        }

        /**
         * @return null|string
         */
        public function exportValue()
        {
            if (!$this->value) {
                return null;
            }

            return $this->value->toString();
        }
    }
}
