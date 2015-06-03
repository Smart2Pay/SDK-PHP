<?php

namespace S2P_SDK;

abstract class S2P_SDK_Database_Wrapper extends S2P_SDK_Language
{
    abstract public function select( array $parameters );
    abstract public function insert( array $fields );
    abstract public function edit( array $fields, $condition );
    abstract public function escape( $string );
    abstract public function close_all();

    public function init()
    {

    }

}
