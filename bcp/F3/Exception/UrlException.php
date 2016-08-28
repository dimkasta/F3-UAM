<?php
/**
 * Created by PhpStorm.
 * User: dimkasta
 * Date: 27/08/16
 * Time: 20:01
 */

namespace Iconic\F3\Exception;


class UrlException
{
    function __construct() {
        parent::__construct("invalid_url");
    }
}