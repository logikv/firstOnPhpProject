<?php
/***************************************************************************
 *   Copyright (C) 2004-2009 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * PostgreSQL DB connector.
     *
     * @see http://www.postgresql.org/
     *
     * @ingroup DB
     **/
    class PgSQL extends DB
    {
        /**
         * @return $this
         * @throws DatabaseException
         */
        public function connect()
        {
            $conn =
                "host={$this->hostname} user={$this->username}"
                . ($this->password ? " password={$this->password}" : null)
                . ($this->basename ? " dbname={$this->basename}" : null)
                . ($this->port ? " port={$this->port}" : null);

            try {
                if ($this->persistent) {
                    $this->link = pg_pconnect($conn);
                } else {
                    $this->link = pg_connect($conn);
                }
            } catch (\Exception $e) {
                throw new DatabaseException(
                    'can not connect to PostgreSQL server: ' . $e->getMessage(),
                    $e->getCode(),
                    $e
                );
            }

            if ($this->encoding) {
                $this->setDbEncoding();
            }

            pg_set_error_verbosity($this->link, PGSQL_ERRORS_VERBOSE);

            return $this;
        }

        /**
         * @return $this
         */
        public function setDbEncoding()
        {
            pg_set_client_encoding($this->link, $this->encoding);

            return $this;
        }

        /**
         * @return $this
         */
        public function disconnect()
        {
            if ($this->isConnected()) {
                pg_close($this->link);
            }

            return $this;
        }

        /**
         * @return bool
         */
        public function isConnected() : bool
        {
            return is_resource($this->link);
        }

        /**
         * misc
         *
         * @param $sequence
         * @return mixed
         */
        public function obtainSequence($sequence)
        {
            $sequence = implode(
                '.',
                array_map(
                    function ($part) {
                        return '"' . $part . '"';
                    },
                    explode('.', $sequence)
                )
            );

            $query ="SELECT nextval('".$sequence."') as seq;";

            $res = $this->queryRaw($query);
            $row = pg_fetch_assoc($res);
            pg_free_result($res);
            return $row['seq'];
        }

        /**
         * query methods
         *
         * @param $queryString
         * @return resource
         */
        public function queryRaw($queryString)
        {
            try {
                return pg_query($this->link, $queryString);
            } catch (BaseException $e) {
                // manual parsing, since pg_send_query() and
                // pg_get_result() is too slow in our case
                list($error,) = explode("\n", pg_errormessage($this->link));
                $code = substr($error, 8, 5);

                if ($code == PostgresError::UNIQUE_VIOLATION) {
                    $e = 'OnPhp\\DuplicateObjectException';
                    $code = null;
                } else {
                    $e = 'OnPhp\\PostgresDatabaseException';
                }

                throw new $e($error . ' - ' . $queryString, $code);
            }
        }

        /**
         * Same as query, but returns number of affected rows
         * Returns number of affected rows in insert/update queries
         **/
        public function queryCount(Query $query)
        {
            return pg_affected_rows($this->queryNull($query));
        }

        /**
         * @param Query $query
         * @return array|null
         * @throws TooManyRowsException
         */
        public function queryRow(Query $query)
        {
            $res = $this->query($query);

            if ($this->checkSingle($res)) {
                $ret = pg_fetch_assoc($res);
                pg_free_result($res);
                return $ret;
            } else {
                return null;
            }
        }

        /**
         * @param $result
         * @return mixed
         * @throws TooManyRowsException
         */
        private function checkSingle($result)
        {
            if (pg_num_rows($result) > 1) {
                throw new TooManyRowsException(
                    'query returned too many rows (we need only one)'
                );
            }

            return $result;
        }

        /**
         * @param Query $query
         * @return array|null
         */
        public function queryColumn(Query $query)
        {
            $res = $this->query($query);

            if ($res) {
                $array = [];

                while ($row = pg_fetch_row($res)) {
                    $array[] = $row[0];
                }

                pg_free_result($res);
                return $array;
            } else {
                return null;
            }
        }

        /**
         * @param Query $query
         * @return array|null
         */
        public function querySet(Query $query)
        {
            $res = $this->query($query);

            if ($res) {
                $array = [];

                while ($row = pg_fetch_assoc($res)) {
                    $array[] = $row;
                }

                pg_free_result($res);
                return $array;
            } else {
                return null;
            }
        }

        /**
         * @return bool
         */
        public function hasSequences() : bool
        {
            return true;
        }

        /**
         * @throws ObjectNotFoundException
         * @return DBTable
         **/
        public function getTableInfo($table)
        {
            static $types = [
                'time' => DataType::TIME,
                'date' => DataType::DATE,

                'timestamp' => DataType::TIMESTAMP,
                'timestamptz' => DataType::TIMESTAMPTZ,
                'timestamp with time zone' => DataType::TIMESTAMPTZ,

                'bool' => DataType::BOOLEAN,

                'int2' => DataType::SMALLINT,
                'int4' => DataType::INTEGER,
                'int8' => DataType::BIGINT,
                'numeric' => DataType::NUMERIC,

                'float4' => DataType::REAL,
                'float8' => DataType::DOUBLE,

                'varchar' => DataType::VARCHAR,
                'bpchar' => DataType::CHAR,
                'text' => DataType::TEXT,

                'bytea' => DataType::BINARY,

                'ip4' => DataType::IP,
                'inet' => DataType::IP,

                'ip4r' => DataType::IP_RANGE,

                // unhandled types, not ours anyway
                'tsvector' => null,

                'ltree' => null,
                'hstore' => null,
            ];

            try {
                $res = pg_meta_data($this->link, $table);
            } catch (BaseException $e) {
                throw new ObjectNotFoundException(
                    "unknown table '{$table}'"
                );
            }

            $table = new DBTable($table);

            foreach ($res as $name => $info) {

                Assert::isTrue(
                    array_key_exists($info['type'], $types),
                    'unknown type "'
                    . $types[$info['type']]
                    . '" found in column "' . $name . '"'
                );

                if (empty($types[$info['type']])) {
                    continue;
                }

                $column =
                    new DBColumn(
                        (new DataType($types[$info['type']]))
                            ->setNull(!$info['not null']),
                        $name
                    );

                $table->addColumn($column);
            }

            return $table;
        }

        /**
         * @return PostgresDialect
         */
        protected function spawnDialect()
        {
            return new PostgresDialect();
        }
    }
}