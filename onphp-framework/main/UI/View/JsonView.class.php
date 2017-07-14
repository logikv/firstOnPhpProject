<?php
/***************************************************************************
 *   Copyright (C) 2011 by Dmitriy V. Snezhinskiy                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * @ingroup Flow
     **/
    class JsonView implements View, Stringable
    {
        protected $options = 0;
        protected $callback = null;

        /**
         * @param bool $flag
         * @return JsonView
         **/
        public function setHexQuot($flag = false)
        {
            if ($flag) {
                $this->options = $this->options | JSON_HEX_QUOT;
            } else {
                $this->options = $this->options & ~JSON_HEX_QUOT;
            }

            return $this;
        }

        /**
         * @param bool $flag
         * @return JsonView
         **/
        public function setHexTag($flag = false)
        {
            if ($flag) {
                $this->options = $this->options | JSON_HEX_TAG;
            } else {
                $this->options = $this->options & ~JSON_HEX_TAG;
            }

            return $this;
        }

        /**
         * @param bool $flag
         * @return JsonView
         **/
        public function setHexAmp($flag = false)
        {
            if ($flag) {
                $this->options = $this->options | JSON_HEX_AMP;
            } else {
                $this->options = $this->options & ~JSON_HEX_AMP;
            }

            return $this;
        }

        /**
         * @param bool $flag
         * @return JsonView
         **/
        public function setHexApos($flag = false)
        {
            if ($flag) {
                $this->options = $this->options | JSON_HEX_APOS;
            } else {
                $this->options = $this->options & ~JSON_HEX_APOS;
            }

            return $this;
        }

        /**
         * @param bool $flag
         * @return JsonView
         **/
        public function setForceObject($flag = false)
        {
            if ($flag) {
                $this->options = $this->options | JSON_FORCE_OBJECT;
            } else {
                $this->options = $this->options & ~JSON_FORCE_OBJECT;
            }

            return $this;
        }

        /**
         * @param bool $flag
         * @return JsonView
         **/
        public function setNumericCheck($flag = false)
        {
            if ($flag) {
                $this->options = $this->options | JSON_NUMERIC_CHECK;
            } else {
                $this->options = $this->options & ~JSON_NUMERIC_CHECK;
            }

            return $this;
        }

        /**
         * @param bool $flag
         * @return JsonView
         **/
        public function setPrettyPrint($flag = false)
        {
            if (defined("JSON_PRETTY_PRINT")) {
                if ($flag) {
                    $this->options = $this->options | JSON_PRETTY_PRINT;
                } else {
                    $this->options = $this->options & ~JSON_PRETTY_PRINT;
                }
            }

            return $this;
        }

        /**
         * @param bool $flag
         * @return JsonView
         **/
        public function setUnescapedSlashes($flag = false)
        {
            if (defined("JSON_UNESCAPED_SLASHES")) {
                if ($flag) {
                    $this->options = $this->options | JSON_UNESCAPED_SLASHES;
                } else {
                    $this->options = $this->options & ~JSON_UNESCAPED_SLASHES;
                }
            }

            return $this;
        }

        /**
         * Create callback
         *
         * @param $callback
         * @return $this
         */
        public function setCallBack($callback)
        {
            $this->callback = $callback;
            return $this;
        }

        /**
         * @return JsonView
         **/
        public function render(/* Model */
            $model = null
        )
        {
            if (!headers_sent()) {
                header('Content-Type: ' . ($this->callback ? 'text/javascript' : 'application/json'));
            }
            if (is_null($this->callback)) {
                echo $this->toString($model);
            } else {
                echo $this->callback . '(' . $this->toString($model) . ')';
            }
            return $this;
        }

        /**
         * @param Model $model
         * @return string
         */
        public function toString($model = null) : string
        {
            Assert::isTrue($model === null || $model instanceof Model);
            if ($this->options) {
                return json_encode($model ? $model->getList() : [], $this->options);
            } else {
                return json_encode($model ? $model->getList() : []);
            }
        }
    }
}