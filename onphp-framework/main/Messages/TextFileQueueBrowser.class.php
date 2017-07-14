<?php

/***************************************************************************
 *   Copyright (C) 2009 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * Class TextFileQueueBrowser
     * @ingroup Messages
     * @package OnPhp
     */
    class TextFileQueueBrowser implements MessageQueueBrowser
    {
        private $queue = null;

        /**
         * @return MessageQueue
         **/
        public function getQueue()
        {
            return $this->queue;
        }

        /**
         * @return TextFileQueueBrowser
         **/
        public function setQueue(MessageQueue $queue)
        {
            $this->queue = $queue;

            return $this;
        }

        public function getNextMessage()
        {
            throw new UnimplementedFeatureException;
        }
    }
}