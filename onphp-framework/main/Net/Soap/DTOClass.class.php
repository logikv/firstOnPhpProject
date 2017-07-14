<?php

/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * Class DTOClass
     * @package OnPhp
     */
    abstract class DTOClass implements PrototypedEntity
    {
        final public function makeObject(Form $form)
        {
            return (new FormToObjectConverter($this->entityProto()))->make($form);
        }

        /**
         * @return Form
         **/
        final public function toForm()
        {
            return
                (new ObjectToFormConverter($this->entityProto()))->make($this);
        }
    }
}