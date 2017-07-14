<?php
/***************************************************************************
 *   Copyright (C) 2007 by Dmitry A. Lomash, Dmitry E. Demidov             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * Class FeedFormat
     * @ingroup Feed
     * @package OnPhp
     */
    abstract class FeedFormat extends Singleton
    {
        abstract public function isAcceptable(SimpleXMLElement $xmlFeed);

        public function parse(SimpleXMLElement $xmlFeed)
        {
            $this->checkWorkers();

            return
                $this->getChannelWorker()
                    ->makeChannel($xmlFeed)
                    ->setFeedItems(
                        $this
                            ->getItemWorker()
                            ->makeItems($xmlFeed)
                    );
        }

        private function checkWorkers()
        {
            if (!$this->getChannelWorker()) {
                throw new WrongStateException(
                    'Setup channelWorker must be assigned'
                );
            }

            if (!$this->getItemWorker()) {
                throw new WrongStateException(
                    'Setup itemWorker must be assigned'
                );
            }

            return $this;
        }

        abstract public function getChannelWorker();

        abstract public function getItemWorker();

        public function toXml(FeedChannel $channel)
        {
            $this->checkWorkers();

            $itemsXml = null;
            $itemWorker = $this->getItemWorker();

            foreach ($channel->getFeedItems() as $feedItem) {
                $itemsXml .= $itemWorker->toXml($feedItem);
            }

            return $this->getChannelWorker()->toXml($channel, $itemsXml);
        }
    }
}

