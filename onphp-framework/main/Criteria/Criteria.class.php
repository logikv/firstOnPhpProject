<?php
/****************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

/**
 * @see http://www.hibernate.org/hib_docs/v3/reference/en/html/querycriteria.html
 *
 * @ingroup Criteria
 **/
namespace OnPhp {
    class Criteria extends QueryIdentification
    {
        private $dao = null;
        private $daoClass = null;
        private $logic = null;
        private $order = null;
        private $strategy = null;
        private $projection = null;

        private $distinct = false;

        private $limit = null;
        private $offset = null;

        private $collections = [];

        // dao-like behaviour: will throw ObjectNotFoundException when 'false'
        private $silent = true;

        public function __construct(/* ProtoDAO */
            $dao = null
        )
        {
            if ($dao) {
                Assert::isTrue($dao instanceof ProtoDAO);
            }

            $this->dao = $dao;
            $this->logic = Expression::andBlock();
            $this->order = new OrderChain();
            $this->strategy = FetchStrategy::join();
            $this->projection = Projection::chain();
        }

        /**
         * __clone
         */
        public function __clone()
        {
            $this->logic = clone $this->logic;
            $this->order = clone $this->order;
            $this->strategy = clone $this->strategy;
            $this->projection = clone $this->projection;
        }

        /**
         * __sleep
         * @return array
         */
        public function __sleep()
        {
            $this->daoClass =
                $this->getDao()
                    ? get_class($this->dao)
                    : null;

            $vars = get_object_vars($this);
            unset($vars['dao']);
            return array_keys($vars);
        }

        /**
         * @return ProtoDAO
         **/
        public function getDao()
        {
            return $this->dao;
        }

        /**
         * @return Criteria
         **/
        public function setDao(ProtoDAO $dao)
        {
            $this->dao = $dao;

            return $this;
        }

        /**
         * __wakeup
         */
        public function __wakeup()
        {
            if ($this->daoClass) {
                $this->dao = Singleton::getInstance($this->daoClass);
            }
        }

        /**
         * @return LogicalChain
         **/
        public function getLogic()
        {
            return $this->logic;
        }

        /**
         * @return Criteria
         **/
        public function add(LogicalObject $logic)
        {
            $this->logic->expAnd($logic);

            return $this;
        }

        /**
         * @return OrderChain
         **/
        public function getOrder()
        {
            return $this->order;
        }

        /**
         * @return Criteria
         **/
        public function addOrder(/* MapableObject */
            $order
        )
        {
            if (!$order instanceof MappableObject) {
                $order = new OrderBy($order);
            }

            $this->order->add($order);

            return $this;
        }

        /**
         * @return Criteria
         **/
        public function prependOrder(/* MapableObject */
            $order
        )
        {
            if (!$order instanceof MappableObject) {
                $order = new OrderBy($order);
            }

            $this->order->prepend($order);

            return $this;
        }

        /**
         * @return Criteria
         **/
        public function dropOrder()
        {
            $this->order = new OrderChain();

            return $this;
        }

        public function getLimit()
        {
            return $this->limit;
        }

        /**
         * @return Criteria
         **/
        public function setLimit($limit)
        {
            $this->limit = $limit;

            return $this;
        }

        /**
         * @return null
         */
        public function getOffset()
        {
            return $this->offset;
        }

        /**
         * @return Criteria
         **/
        public function setOffset($offset)
        {
            $this->offset = $offset;

            return $this;
        }

        /**
         * @return Criteria
         **/
        public function setFetchStrategy(FetchStrategy $strategy)
        {
            $this->strategy = $strategy;

            return $this;
        }

        /**
         * @return Criteria
         **/
        public function addProjection(ObjectProjection $projection)
        {
            if (
                !$projection instanceof ProjectionChain
                || !$projection->isEmpty()
            ) {
                $this->projection->add($projection);
            }

            return $this;
        }

        /**
         * @return Criteria
         **/
        public function dropProjection()
        {
            $this->projection = Projection::chain();

            return $this;
        }

        /**
         * @return bool
         */
        public function isDistinct() : bool
        {
            return $this->distinct;
        }

        /**
         * @return Criteria
         **/
        public function setDistinct($orly = true)
        {
            $this->distinct = ($orly === true);

            return $this;
        }

        /**
         * @return Criteria
         **/
        public function fetchCollection(
            $path, // to collection
            $lazy = false, // fetching mode
            /* Criteria */
            $criteria = null
        )
        {
            Assert::isBoolean($lazy);
            Assert::isTrue(
                ($criteria === null)
                || ($criteria instanceof Criteria)
            );

            $this->collections[$path]['lazy'] = $lazy;
            $this->collections[$path]['criteria'] = $criteria;
            $this->collections[$path]['propertyPath']
                = new PropertyPath(
                $this->checkAndGetDao()->getObjectName(),
                $path
            );

            return $this;
        }

        /**
         * @return StorableDAO
         * @throws WrongStateException
         */
        public function checkAndGetDao()
        {
            if (!$this->dao) {
                throw new WrongStateException('You forgot to set dao');
            }

            return $this->dao;
        }

        /**
         * @return mixed|null
         * @throws ObjectNotFoundException
         * @throws WrongStateException
         */
        public function get()
        {
            try {
                $list = [
                    $this->checkAndGetDao()->getByQuery($this->toSelectQuery())
                ];
            } catch (ObjectNotFoundException $e) {
                if (!$this->isSilent()) {
                    throw $e;
                }

                return null;
            }

            if (!$this->collections || !$list) {
                return reset($list);
            }

            $list =
                $this->checkAndGetDao()->fetchCollections($this->collections, $list);

            return reset($list);
        }

        /**
         * @return SelectQuery
         **/
        public function toSelectQuery()
        {
            if (!$this->projection->isEmpty()) {
                $query =
                    $this->getProjection()->process(
                        $this,
                        $this->checkAndGetDao()->makeSelectHead()->dropFields()
                    );
            } else {
                $query = $this->checkAndGetDao()->makeSelectHead();
            }

            if ($this->distinct) {
                $query->distinct();
            }

            return $this->fillSelectQuery($query);
        }

        /**
         * @return ProjectionChain
         **/
        public function getProjection()
        {
            return $this->projection;
        }

        /**
         * @return Criteria
         **/
        public function setProjection(ObjectProjection $chain)
        {
            if ($chain instanceof ProjectionChain) {
                $this->projection = $chain;
            } else {
                $this->projection = Projection::chain()->add($chain);
            }

            return $this;
        }

        /**
         * @return SelectQuery
         **/
        public function fillSelectQuery(SelectQuery $query)
        {
            $query
                ->limit($this->limit, $this->offset);

            if ($this->distinct) {
                $query->distinct();
            }

            if ($this->logic->getSize()) {
                $query
                    ->andWhere(
                        $this->logic->toMapped($this->checkAndGetDao(), $query)
                    );
            }

            if ($this->order) {
                $query->setOrderChain(
                    $this->order->toMapped($this->checkAndGetDao(), $query)
                );
            }

            if (
                $this->projection->isEmpty()
                && (
                    $this->strategy->getId() <> FetchStrategy::CASCADE
                )
            ) {
                $this->joinProperties(
                    $query,
                    $this->checkAndGetDao(),
                    $this->checkAndGetDao()->getTable(),
                    true
                );
            }

            return $query;
        }

        /**
         * @param SelectQuery $query
         * @param ProtoDAO $parentDao
         * @param $parentTable
         * @param $parentRequired
         * @param null $prefix
         */
        private function joinProperties(
            SelectQuery $query,
            ProtoDAO $parentDao,
            $parentTable,
            $parentRequired,
            $prefix = null
        )
        {
            $proto = call_user_func([$parentDao->getObjectName(), 'proto']);

            foreach ($proto->getPropertyList() as $property) {
                if (
                    ($property instanceof LightMetaProperty)
                    && $property->getRelationId() == MetaRelation::ONE_TO_ONE
                    && !$property->isGenericType()
                    && (
                        (
                            !$property->getFetchStrategyId()
                            && (
                                $this->getFetchStrategy()->getId()
                                == FetchStrategy::JOIN
                            )
                        ) || (
                            $property->getFetchStrategyId()
                            == FetchStrategy::JOIN
                        )
                    )
                ) {
                    if (
                        is_subclass_of(
                            $property->getClassName(),
                            'Enumeration'
                        ) ||
                        is_subclass_of(
                            $property->getClassName(),
                            'Enum'
                        )
                    ) {
                        // field already added by makeSelectHead
                        continue;
                    } elseif ($property->isInner()) {
                        $proto = call_user_func(
                            [$property->getClassName(), 'proto']
                        );

                        foreach ($proto->getPropertyList() as $innerProperty) {
                            $query->get(
                                new DBField(
                                    $innerProperty->getColumnName(),
                                    $parentTable
                                )
                            );
                        }

                        continue;
                    }


                    if (!in_array('dao', get_class_methods($property->getClassName()))) {
                        continue;
                    };

                    $propertyDao = call_user_func(
                        [$property->getClassName(), 'dao']
                    );

                    // add's custom dao's injection possibility
                    if (!$propertyDao instanceof ProtoDAO) {
                        continue;
                    }

                    $tableAlias = $propertyDao->getJoinName(
                        $property->getColumnName(),
                        $prefix
                    );

                    $fields = $propertyDao->getFields();

                    if (!$query->hasJoinedTable($tableAlias)) {
                        $logic =
                            Expression::eq(
                                new DBField($property->getColumnName(), $parentTable),
                                new DBField($propertyDao->getIdName(), $tableAlias)
                            );

                        if ($property->isRequired() && $parentRequired) {
                            $query->join($propertyDao->getTable(), $logic, $tableAlias);
                        } else {
                            $query->leftJoin($propertyDao->getTable(), $logic, $tableAlias);
                        }
                    }

                    foreach ($fields as $field) {
                        $query->get(
                            new DBField($field, $tableAlias),
                            $propertyDao->getJoinPrefix($property->getColumnName(), $prefix)
                            . $field
                        );
                    }

                    $this->joinProperties(
                        $query,
                        $propertyDao,
                        $tableAlias,
                        $property->isRequired() && $parentRequired,
                        $propertyDao->getJoinPrefix($property->getColumnName(), $prefix)
                    );
                }
            }
        }

        /**
         * @return FetchStrategy
         **/
        public function getFetchStrategy()
        {
            return $this->strategy;
        }

        public function isSilent()
        {
            return $this->silent;
        }

        /**
         * @return Criteria
         **/
        public function setSilent($silent)
        {
            Assert::isBoolean($silent);

            $this->silent = $silent;

            return $this;
        }

        public function getList()
        {
            try {
                $list =
                    $this->checkAndGetDao()
                        ->getListByQuery($this->toSelectQuery());

            } catch (ObjectNotFoundException $e) {
                if (!$this->isSilent()) {
                    throw $e;
                }

                return [];
            }

            if (!$this->collections || !$list) {
                return $list;
            }

            return
                $this->checkAndGetDao()
                    ->fetchCollections($this->collections, $list);
        }

        /**
         * @return QueryResult
         **/
        public function getResult()
        {
            $result =
                $this->checkAndGetDao()
                    ->getQueryResult($this->toSelectQuery());

            if (!$this->collections || !$result->getCount()) {
                return $result;
            }

            return $result->setList(
                $this->checkAndGetDao()
                    ->fetchCollections(
                        $this->collections,
                        $result->getList()
                    )
            );
        }

        public function getCustom($index = null)
        {
            try {
                $result =
                    $this->checkAndGetDao()->getCustom($this->toSelectQuery());

                if ($index) {
                    if (array_key_exists($index, $result)) {
                        return $result[$index];
                    }

                    throw new MissingElementException(
                        'No such key: "' . $index . '" in result set.'
                    );
                }

                return $result;
            } catch (ObjectNotFoundException $e) {
                if (!$this->isSilent()) {
                    throw $e;
                }

                return null;
            }
        }

        public function getCustomList()
        {
            try {
                return
                    $this->checkAndGetDao()
                        ->getCustomList($this->toSelectQuery());

            } catch (ObjectNotFoundException $e) {
                if (!$this->isSilent()) {
                    throw $e;
                }

                return [];
            }
        }

        public function getPropertyList()
        {
            try {
                return
                    $this->checkAndGetDao()
                        ->getCustomRowList($this->toSelectQuery());

            } catch (ObjectNotFoundException $e) {
                if (!$this->isSilent()) {
                    throw $e;
                }

                return [];
            }
        }

        /**
         * @return null|string
         */
        public function toString() : string
        {
            return $this->toDialectString(
                $this->dao
                    ? DBPool::getByDao($this->dao)->getDialect()
                    : ImaginaryDialect::me()
            );
        }

        public function toDialectString(Dialect $dialect)
        {
            return $this->toSelectQuery()->toDialectString($dialect);
        }

        /**
         * @return Criteria
         **/
        public function dropProjectionByType(/* array */
            $dropTypes
        )
        {
            Assert::isInstance($this->projection, 'ProjectionChain');

            $this->projection->dropByType($dropTypes);

            return $this;
        }

        /**
         * @return AbstractProtoClass
         **/
        private function getProto()
        {
            return
                call_user_func(
                    [
                        $this
                            ->checkAndGetDao()
                            ->getObjectName(),
                        'proto'
                    ]
                );
        }
    }
}
