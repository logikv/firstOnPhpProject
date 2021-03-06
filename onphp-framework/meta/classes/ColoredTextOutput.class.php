<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
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
    final class ColoredTextOutput extends TextOutput
    {
        /**
         * @return ColoredTextOutput
         **/
        public function setMode(
            $attribute = ConsoleMode::ATTR_RESET_ALL,
            $foreground = ConsoleMode::FG_WHITE,
            $background = ConsoleMode::BG_BLACK
        )
        {
            echo
                chr(0x1B)
                . '[' . $attribute . ';'
                . $foreground . ';'
                . $background . 'm';

            return $this;
        }

        /**
         * @return ColoredTextOutput
         **/
        public function resetAll()
        {
            echo chr(0x1B) . '[0m';

            return $this;
        }

        /**
         * @return string colored text
         **/
        public function wrapString(
            $text,
            $attribute = ConsoleMode::ATTR_RESET_ALL,
            $foreground = ConsoleMode::FG_WHITE,
            $background = ConsoleMode::BG_BLACK
        )
        {
            return
                chr(0x1B)
                . '[' . $attribute . ';'
                . $foreground . ';'
                . $background . 'm'
                . $text
                . chr(0x1B) . '[0m';
        }
    }
}