<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 *   Based on PEAR's Mail::MIME by Richard Heyes                           *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * @ingroup Mail
     **/
    class MimeMail implements MailBuilder
    {
        private $parts = [];

        // should be built by build()
        private $body = null;
        private $headers = null;
        private $contentType = null;
        private $boundary = null;


        /**
         * MimeMail constructor.
         */
        function __construct()
        {
            $this->contentType = 'multipart/mixed';
        }

        /**
         * @return null|string
         */
        public function getContentType()
        {
            return $this->contentType;
        }

        /**
         * @param $contentType
         * @return $this
         */
        public function setContentType($contentType)
        {
            $this->contentType = $contentType;

            return $this;
        }


        /**
         * @return MimeMail
         **/
        public function addPart(MimePart $part)
        {
            $this->parts[] = $part;

            return $this;
        }

        /**
         * @throws UnimplementedFeatureException
         */
        public function build()
        {
            if (!$this->parts) {
                throw new UnimplementedFeatureException();
            }

            if (!$this->boundary) {
                $this->boundary = '=_' . md5(microtime(true));
            }

            $mail =
                (new MimePart())
                    ->setContentType($this->getContentType())
                    ->setBoundary($this->boundary);

            $this->headers =
                "MIME-Version: 1.0\n"
                . $mail->getHeaders();

            foreach ($this->parts as $part) {
                $this->body .=
                    '--' . $this->boundary . "\n"
                    . $part->getHeaders()
                    . "\n\n"
                    . $part->getEncodedBody() . "\n";
            }

            $this->body .= '--' . $this->boundary . "--" . "\n\n";
        }

        public function getEncodedBody()
        {
            Assert::isTrue(
                $this->body && $this->headers
            );

            return $this->body;
        }

        public function getHeaders()
        {
            Assert::isTrue(
                $this->body && $this->headers
            );

            return $this->headers;
        }

        public function getBoundary()
        {
            return $this->boundary;
        }

        /**
         * @return MimeMail
         **/
        public function setBoundary($boundary)
        {
            $this->boundary = $boundary;

            return $this;
        }
    }
}