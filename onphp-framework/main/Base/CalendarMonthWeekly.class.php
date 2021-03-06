<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * Calendar month representation splitted by weeks.
     *
     * @ingroup Calendar
     **/
    class CalendarMonthWeekly
    {
        private $monthRange = null;
        private $fullRange = null;
        private $fullLength = null;

        private $weeks = [];
        private $days = [];

        public function __construct(
            Date $base, $weekStart = Timestamp::WEEKDAY_MONDAY
        )
        {
            $firstDayOfMonth = new Date(
                $base->getYear() . '-' . $base->getMonth() . '-01'
            );

            $lastDayOfMonth = new Date(
                $base->getYear() . '-' . $base->getMonth() . '-'
                . date('t', $base->toStamp()));

            $start = $firstDayOfMonth->getFirstDayOfWeek($weekStart);

            $end = $lastDayOfMonth->getLastDayOfWeek($weekStart);

            $this->monthRange = (new DateRange())->lazySet(
                $firstDayOfMonth, $lastDayOfMonth
            );

            $this->fullRange = (new DateRange())->lazySet(
                $start, $end
            );

            $rawDays = $this->fullRange->split();
            $this->fullLength = 0;

            foreach ($rawDays as $rawDay) {
                $day = new CalendarDay($rawDay->toStamp());

                if ($this->monthRange->contains($day))
                    $day->setOutside(false);
                else
                    $day->setOutside(true);

                $this->days[$day->toDate()] = $day;

                $weekNumber = floor($this->fullLength / 7);

                if (!isset($this->weeks[$weekNumber]))
                    $this->weeks[$weekNumber] = new CalendarWeek();

                $this->weeks[$weekNumber]->addDay($day);
                ++$this->fullLength;
            }

            ++$this->fullLength;
        }

        public function getWeeks()
        {
            return $this->weeks;
        }

        public function getDays()
        {
            return $this->days;
        }

        /**
         * @return DateRange
         **/
        public function getFullRange()
        {
            return $this->fullRange;
        }

        public function getFullLength()
        {
            return $this->fullLength;
        }

        /**
         * @return DateRange
         **/
        public function getMonthRange()
        {
            return $this->monthRange;
        }

        /**
         * @throws WrongArgumentException
         * @return CalendarMonthWeekly
         **/
        public function setSelected(Date $day)
        {
            if (!isset($this->days[$day->toDate()]))
                throw new WrongArgumentException($day->toDate() . ' not in calendar');

            $this->days[$day->toDate()]->setSelected(true);

            return $this;
        }

        /**
         * @return Date
         **/
        public function getNextMonthBase()
        {
            return $this->monthRange->getEnd()->spawn('+1 day');
        }

        /**
         * @return Date
         **/
        public function getPrevMonthBase()
        {
            return $this->monthRange->getStart()->spawn('-1 day');
        }

        /**
         * @return Date
         **/
        public function getBase()
        {
            return $this->monthRange->getStart();
        }
    }
}
