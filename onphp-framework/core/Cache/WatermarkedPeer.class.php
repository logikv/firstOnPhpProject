<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * Watermark's all cache activity to avoid namespace collisions.
     *
     * @ingroup Cache
     **/
    class WatermarkedPeer extends SelectivePeer
    {
        private $peer = null;
        private $originalWatermark = null;
        private $watermark = null;

        /// map class -> watermark
        private $map = null;

        /**
         * WatermarkedPeer constructor.
         * @param CachePeer $peer
         * @param string $watermark
         */
        public function __construct(CachePeer $peer, $watermark = "Single onPHP's project")
        {
            $this->peer = $peer;
            $this->setWatermark($watermark);
        }

        /**
         * @return null
         */
        public function getWatermark()
        {
            return $this->watermark;
        }

        /**
         * @param $watermark
         * @return $this
         */
        public function setWatermark($watermark)
        {
            $this->originalWatermark = $watermark;
            $this->watermark = md5($watermark . ' [' . ONPHP_VERSION . ']::');

            return $this;
        }

        /**
         * associative array, className -> watermark
         *
         * @return WatermarkedPeer
         **/
        public function setClassMap($map)
        {
            $this->map = [];

            foreach ($map as $className => $watermark) {
                $this->map[$className] = md5($watermark . ' [' . ONPHP_VERSION . ']::');
            }

            return $this;
        }

        /**
         * @return CachePeer
         **/
        public function mark($className)
        {
            $this->className = $className;

            $this->peer->mark($this->getActualWatermark() . $className);

            return $this;
        }

        /**
         * @return null
         */
        public function getActualWatermark()
        {
            if (
                $this->className
                && isset($this->map[$this->className])
            ) {
                return $this->map[$this->className];
            }

            return $this->watermark;
        }

        /**
         * @param $key
         * @param $value
         * @return mixed
         */
        public function increment($key, $value)
        {
            return $this->peer->increment(
                $this->getActualWatermark() . $key,
                $value
            );
        }

        /**
         * @param $key
         * @param $value
         * @return mixed
         */
        public function decrement($key, $value)
        {
            return $this->peer->decrement(
                $this->getActualWatermark() . $key,
                $value
            );
        }

        /**
         * @param $indexes
         * @return array|null
         */
        public function getList($indexes)
        {
            $peerIndexMap = [];
            foreach ($indexes as $index) {
                $peerIndexMap[$this->getActualWatermark() . $index] = $index;
            }

            $peerIndexes = array_keys($peerIndexMap);
            $peerResult = $this->peer->getList($peerIndexes);

            $result = [];
            if (!empty($peerResult)) {
                foreach ($peerResult as $key => $value) {
                    $result[$peerIndexMap[$key]] = $value;
                }
            } else {
                $result = $peerResult;
            }

            return $result;
        }

        /**
         * @param $key
         * @return mixed
         */
        public function get($key)
        {
            return $this->peer->get($this->getActualWatermark() . $key);
        }

        /**
         * @param $key
         * @return mixed
         */
        public function delete($key)
        {
            return $this->peer->delete($this->getActualWatermark() . $key);
        }

        /**
         * @return CachePeer
         **/
        public function clean()
        {
            $this->peer->clean();

            return parent::clean();
        }

        /**
         * @return bool
         */
        public function isAlive()
        {
            return $this->peer->isAlive();
        }

        /**
         * @param $key
         * @param $data
         * @return mixed
         */
        public function append($key, $data)
        {
            return $this->peer->append($this->getActualWatermark() . $key, $data);
        }

        /**
         * @return CachePeer
         */
        public function getRuntimeCopy()
        {
            $newWm = new WatermarkedPeer(
                $this->peer->getRuntimeCopy(),
                $this->originalWatermark
            );
            $newWm->map = $this->map;
            return $newWm;
        }

        /**
         * @param $action
         * @param $key
         * @param $value
         * @param int $expires
         * @return mixed
         */
        protected function store(
            $action,
            $key,
            $value,
            $expires = Cache::EXPIRES_MEDIUM
        )
        {
            return
                $this->peer->$action(
                    $this->getActualWatermark() . $key, $value, $expires
                );
        }
    }
}