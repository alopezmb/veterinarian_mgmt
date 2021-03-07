<?php declare(strict_types=1);

namespace Felican\Configurations\Database;

#use Doctrine\DBAL\Configuration;
#use Doctrine\DBAL\Connection;
#use Doctrine\DBAL\DriverManager;

use \RedBeanPHP\R as R;



final class ConnectionFactory
{

    public function __construct(string $databaseURL)
    {
        R::setup($databaseURL);
    }
    /*

    public function create(): ORM
    {
       $ORM = ORM;
       $ORM::setup($this->databaseURL);
       return $ORM;
    }
    */
}