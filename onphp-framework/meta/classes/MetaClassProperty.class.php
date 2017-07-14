<?php
/***************************************************************************
 *   Copyright (C) 2006-2009 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * @ingroup MetaBase
     **/
    class MetaClassProperty
    {
        private $class = null;

        private $name = null;
        private $columnName = null;

        private $type = null;
        private $size = null;

        private $required = false;
        private $identifier = false;

        private $relation = null;

        private $strategy = null;

        public function __construct(
            $name,
            BasePropertyType $type,
            MetaClass $class
        )
        {
            $this->name = $name;

            $this->type = $type;

            $this->class = $class;
        }

        public function equals(MetaClassProperty $property)
        {
            return (
                ($property->getName() == $this->getName())
                && ($property->getColumnName() == $this->getColumnName())
                && ($property->getType() == $this->getType())
                && ($property->getSize() == $this->getSize())
                && ($property->getRelation() == $this->getRelation())
                && ($property->isRequired() == $this->isRequired())
                && ($property->isIdentifier() == $this->isIdentifier())
            );
        }

        public function getName()
        {
            return $this->name;
        }

        /**
         * @return MetaClassProperty
         **/
        public function setName($name)
        {
            $this->name = $name;

            return $this;
        }

        public function getColumnName()
        {
            return $this->columnName;
        }

        /**
         * @return MetaClassProperty
         **/
        public function setColumnName($name)
        {
            $this->columnName = $name;

            return $this;
        }

        /**
         * @return BasePropertyType
         **/
        public function getType()
        {
            return $this->type;
        }

        public function getSize()
        {
            return $this->size;
        }

        /**
         * @throws WrongArgumentException
         * @return MetaClassProperty
         **/
        public function setSize($size)
        {
            if ($this->type instanceof NumericType) {
                if (strpos($size, ',') !== false) {
                    list($size, $precision) = explode(',', $size, 2);

                    $this->type->setPrecision($precision);
                }
            }

            Assert::isInteger(
                $size,
                'only integers allowed in size parameter'
            );

            if ($this->type->isMeasurable()) {
                $this->size = $size;
            } else {
                throw new WrongArgumentException(
                    "size not allowed for '"
                    . $this->getName() . '::' . get_class($this->type)
                    . "' type"
                );
            }

            return $this;
        }

        /**
         * @return MetaRelation
         **/
        public function getRelation()
        {
            return $this->relation;
        }

        /**
         * @return MetaClassProperty
         **/
        public function setRelation(MetaRelation $relation)
        {
            $this->relation = $relation;

            return $this;
        }

        public function isRequired()
        {
            return $this->required;
        }

        public function isIdentifier()
        {
            return $this->identifier;
        }

        /**
         * @return MetaClassProperty
         **/
        public function setIdentifier($really = false)
        {
            $this->identifier = ($really === true);

            return $this;
        }

        /**
         * @return MetaClassProperty
         **/
        public function getConvertedName()
        {
            return strtolower(
                preg_replace(':([A-Z]):', '_\1', $this->name)
            );
        }

        public function isOptional()
        {
            return !$this->required;
        }

        /**
         * @return MetaClassProperty
         **/
        public function required()
        {
            $this->required = true;

            return $this;
        }

        /**
         * @return MetaClassProperty
         **/
        public function optional()
        {
            $this->required = false;

            return $this;
        }

        /**
         * @return MetaClassProperty
         **/
        public function setFetchStrategy(FetchStrategy $strategy)
        {
            $this->strategy = $strategy;

            return $this;
        }

        /**
         * @return FetchStrategy
         **/
        public function getFetchStrategy()
        {
            return $this->strategy;
        }

        public function toMethods(
            MetaClass $class,
            MetaClassProperty $holder = null
        )
        {
            return $this->type->toMethods($class, $this, $holder);
        }

        public function toColumn()
        {
            if (
                $this->getType() instanceof ObjectType
                && (
                    ($this->getType() instanceof InternalType)
                    || (
                        !$this->getType()->isGeneric()
                        && (
                            $this->getType()->getClass()->getPattern()
                            instanceof ValueObjectPattern
                        )
                    )
                )
            ) {
                $columns = [];

                $prefix =
                    $this->getType() instanceof InternalType
                        ? $this->getColumnName() . '_'
                        : null;

                $remote = $this->getType()->getClass();

                foreach ($remote->getAllProperties() as $property) {
                    $columns[] = $property->buildColumn(
                        $prefix . $property->getRelationColumnName()
                    );
                }

                return $columns;
            }

            return $this->buildColumn($this->getRelationColumnName());
        }

        private function buildColumn($columnName)
        {
            if (is_array($columnName)) {
                $out = [];

                foreach ($columnName as $name) {
                    $out[] = $this->buildColumn($name);
                }

                return $out;
            }

            $column = <<<EOT
addColumn(
            (new \\OnPhp\\DBColumn(
                {$this->type->toColumnType($this->size)}
EOT;

            if ($this->required) {
                $column .= <<<EOT
\n                    ->setNull(false)
EOT;
            }

            if ($this->size) {
                $column .= <<<EOT
\n                    ->setSize({$this->size})
EOT;
            }

            if ($this->type instanceof NumericType) {
                $column .= <<<EOT
\n                    ->setPrecision({$this->type->getPrecision()})
EOT;
            }

            $column .= <<<EOT
, '{$columnName}'
            ))
EOT;

            if ($this->identifier) {
                $column .= <<<EOT
\n                ->setPrimaryKey(true)
EOT;

                if ($this->getType() instanceof IntegerType) {
                    $column .= <<<EOT
\n                ->setAutoincrement(true)\n
EOT;
                }
            }

            if ($this->type->hasDefault()) {
                $default = $this->type->getDefault();

                if ($this->type instanceof BooleanType) {
                    if ($default) {
                        $default = 'true';
                    } else {
                        $default = 'false';
                    }
                } elseif ($this->type instanceof StringType) {
                    $default = "'{$default}'";
                }

                $column .= <<<EOT
\n                ->setDefault({$default})\n
EOT;
            }

            $column .= <<<EOT

        )
EOT;

            return $column;
        }

        public function getRelationColumnName()
        {
            if ($this->type instanceof ObjectType && !$this->type->isGeneric()) {
                if ($this->relation->getId() == MetaRelation::MANY_TO_MANY) {
                    $columnName = $this->type->getClass()->getTableName() . '_id';
                } else {
                    $columnName = $this->getColumnName();
                }
            } elseif ($this->type instanceof InternalType) {
                $out = [];
                foreach ($this->type->getSuffixList() as $suffix) {
                    $out[] = $this->getColumnName() . '_' . $suffix;
                }
                return $out;
            } else {
                $columnName = $this->getColumnName();
            }

            return $columnName;
        }

        public function toLightProperty(MetaClass $holder)
        {
            $className = null;

            if (
                ($this->getRelationId() == MetaRelation::ONE_TO_MANY)
                || ($this->getRelationId() == MetaRelation::MANY_TO_MANY)
            ) {
                // collections
                $primitiveName = 'identifierList';
            } elseif ($this->isIdentifier()) {
                if ($this->getType() instanceof IntegerType) {
                    $primitiveName = 'integerIdentifier';
                    $className = $holder->getName();
                } elseif ($this->getType() instanceof StringType) {
                    $primitiveName = 'scalarIdentifier';
                    $className = $holder->getName();
                } else {
                    $primitiveName = $this->getType()->getPrimitiveName();
                }
            } elseif (
                !$this->isIdentifier()
                && !$this->getType()->isGeneric()
                && ($this->getType() instanceof ObjectType)
            ) {
                $pattern = $this->getType()->getClass()->getPattern();

                if ($pattern instanceof EnumerationClassPattern) {
                    $primitiveName = 'enumeration';
                } elseif ($pattern instanceof EnumClassPattern) {
                    $primitiveName = 'enum';
                } elseif (
                    $pattern instanceof DictionaryClassPattern
                    && ($identifier = $this->getType()->getClass()->getIdentifier())
                ) {
                    if ($identifier->getType() instanceof IntegerType) {
                        $primitiveName = 'integerIdentifier';
                    } elseif ($identifier->getType() instanceof StringType) {
                        $primitiveName = 'scalarIdentifier';
                    } else {
                        $primitiveName = $this->getType()->getPrimitiveName();
                    }
                } else {
                    $primitiveName = $this->getType()->getPrimitiveName();
                }
            } else {
                $primitiveName = $this->getType()->getPrimitiveName();
            }

            $inner = false;

            if ($this->getType() instanceof ObjectType) {
                $className = $this->getType()->getClassName();

                if (!$this->getType()->isGeneric()) {
                    $class = $this->getType()->getClass();
                    $pattern = $class->getPattern();

                    if ($pattern instanceof InternalClassPattern) {
                        $className = $holder->getName();
                    }

                    if (
                        (
                            ($pattern instanceof InternalClassPattern)
                            || ($pattern instanceof ValueObjectPattern)
                        ) && (
                            $className <> $holder->getName()
                        )
                    ) {
                        $inner = true;
                    }
                }
            }

            $propertyClassName = (
            $inner
                ? '\\OnPhp\\InnerMetaProperty'
                : '\\OnPhp\\LightMetaProperty'
            );

            if (
            ($this->getType() instanceof IntegerType)
            ) {
                $size = $this->getType()->getSize();
            } elseif (
                ($this->getType() instanceof ObjectType)
                && ($this->getRelationId() == MetaRelation::ONE_TO_ONE)
                && ($identifier = $this->getType()->getClass()->getIdentifier())
                && ($this->getType()->isMeasurable())
            ) {
                $size = $identifier->getType()->getSize();
            } elseif ($this->getType()->isMeasurable()) {
                $size = $this->size;
            } else {
                $size = null;
            }

            return
                call_user_func_array(
                    [$propertyClassName, 'fill'],
                    [
                        new $propertyClassName,
                        $this->getName(),
                        $this->getName() <> $this->getRelationColumnName()
                            ? $this->getRelationColumnName()
                            : null,
                        $primitiveName,
                        $className,
                        $size,
                        $this->isRequired(),
                        $this->getType()->isGeneric(),
                        $inner,
                        $this->getRelationId(),
                        $this->getFetchStrategyId()
                    ]
                );
        }

        public function getRelationId()
        {
            if ($this->relation) {
                return $this->relation->getId();
            }

            return null;
        }

        public function getFetchStrategyId()
        {
            if ($this->strategy) {
                return $this->strategy->getId();
            } elseif (
                $this->getClass()->getFetchStrategyId()
                && ($this->getRelationId() == MetaRelation::ONE_TO_ONE)
                && ($this->getType() instanceof ObjectType)
                && (!$this->getType()->isGeneric())
            ) {
                return $this->getClass()->getFetchStrategyId();
            }

            return null;
        }

        /**
         * @return MetaClass
         **/
        public function getClass()
        {
            return $this->class;
        }

        private function toVarName($name)
        {
            return strtolower($name[0]) . substr($name, 1);
        }
    }
}
?>