<?php
namespace App\Models;

use Phalcon\Mvc\Model;

class PhonesModel extends ModelBase
{
    public function initialize()
    {
        $this->setSource('phone_storage');
    }
}
