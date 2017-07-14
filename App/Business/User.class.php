<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2017-07-11 14:57:24                    *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/

namespace Business;

use Auto\Business\AutoUser;
use DAOs\UserDAO;
use Proto\ProtoUser;

use OnPhp\Prototyped;
use OnPhp\DAOConnected;
use OnPhp\Singleton;

class User extends AutoUser implements Prototyped
{
    /**
    * @return User
    **/
    public static function create()
    {
        return new self;
    }
    
    
    /**
    * @return ProtoUser
    **/
    public static function proto()
    {
        return Singleton::getInstance('\\Proto\\ProtoUser');
    }
    
        // your brilliant stuff goes here
}