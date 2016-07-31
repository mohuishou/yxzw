<?php
/**
 * Created by mohuishou<1@lailin.xyz>.
 * User: mohuishou<1@lailin.xyz>
 * Date: 2016/6/30 0030
 * Time: 12:51
 */
namespace Mohuishou\Lib;
class DB
{
    function __construct()
    {
        $dotenv = new Dotenv\Dotenv(__DIR__);
        $dotenv->load();
        $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);


    }


}