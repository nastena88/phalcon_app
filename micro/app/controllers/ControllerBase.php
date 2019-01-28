<?php

namespace App\Controllers;

use App\Library\Response;
use Phalcon\Mvc\Controller;

class ControllerBase extends Controller
{
    /**
     * Check if payload is empty
     * @author Tega Oghenekohwo <tega@cottacush.com>
     * @return bool
     */
    public function isPayloadEmpty()
    {
        $postData = $this->getPayload();
        return empty((array)$postData);
    }

    /**
     * Get payload of current request
     * @author Tega Oghenekohwo <tega@cottacush.com>
     * @return array|bool|\stdClass
     */
    public function getPayload()
    {
        return $this->request->getJsonRawBody();
    }
}
