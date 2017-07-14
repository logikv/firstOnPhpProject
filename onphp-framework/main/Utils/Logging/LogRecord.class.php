<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y, Khvostishkov, Denis M. Gabaidulin       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * @ingroup Utils
     **/
    class LogRecord
    {
        private $message = null;
        private $level = LogLevel::INFO;

        private $date = null;

        /**
         * LogRecord constructor.
         */
        public function __construct()
        {
            $this->date = Timestamp::makeNow();
        }


        /**
         * @return null
         */
        public function getMessage()
        {
            return $this->message;
        }

        /**
         * @return LogRecord
         **/
        public function setMessage($message)
        {
            Assert::isString($message);

            $this->message = $message;

            return $this;
        }

        /**
         * @return Timestamp
         **/
        public function getDate()
        {
            return $this->date;
        }

        /**
         * @return LogRecord
         **/
        public function setDate(Timestamp $date)
        {
            $this->date = $date;

            return $this;
        }

        /**
         * @return LogLevel
         **/
        public function getLevel()
        {
            return $this->level;
        }

        /**
         * @return LogRecord
         **/
        public function setLevel(LogLevel $level)
        {
            $this->level = $level;

            return $this;
        }

        /**
         * returns message in human readable form, ex:
         *
         * Jul  7 07:07:07 warning: all your base are belong to us
         **/
        public function toString()
        {
            return sprintf(
                '%s %2s %s %s: %s',
                date('M', $this->date->toStamp()), $this->date->getDay(),
                $this->date->toTime(':', ':'),
                $this->level->getName(),
                $this->message
            );
        }
    }
}

