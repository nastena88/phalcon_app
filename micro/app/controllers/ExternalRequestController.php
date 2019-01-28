<?php
/**
 * Class to make external requests to Hostaway API
 *
 * PHP version 7
 *
 * @category External_Request
 * @package  App\Controllers
 * @author   Anastasia Barchukova <stasya-88@list.ru>
 * @license  https://github.com/nastena88/phalcon_app Public
 * @link     https://github.com/nastena88/phalcon_app
 */

namespace App\Controllers;

use Phalcon\Mvc\Controller;
use GuzzleHttp\Client;
use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;

/**
 * Class ExternalRequestController
 *
 * @category External_Request
 * @package  App\Controllers
 * @author   Anastasia Barchukova <stasya-88@list.ru>
 * @license  https://github.com/nastena88/phalcon_app Public
 * @link     https://github.com/nastena88/phalcon_app
 */
class ExternalRequestController extends ControllerBase
{
    protected $client;
    protected $cache;
    protected $logger;

    const EXTERNAL_API_URL = 'https://api.hostaway.com';
    const REQUEST = 'GET';
    const LOG_PATH = APP_PATH.'/logs/ExternalRequest.log';
    const CACHE_TIME = 172800;
    const CACHE_PATH = APP_PATH.'/cache/';

    /**
     * Class constructor
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->logger = new FileAdapter(self::LOG_PATH);
        $this->client = new Client(
            [
            'base_uri' => self::EXTERNAL_API_URL,
            'timeout'  => 2.0,
            ]
        );
        $frontCache = new FrontData(
            [
                'lifetime' => self::CACHE_TIME,
            ]
        );
        $this->cache = new BackFile(
            $frontCache,
            [
                'cacheDir' => self::CACHE_PATH,
            ]
        );
    }

    /**
     * Get the list of country codes
     *
     * @return array|bool
     */
    public function getCountries()
    {
        $uri = '/countries';
        $cacheKey = substr($uri, 1);

        $result = $this->cache->get($cacheKey);
        if ($result == null) {
            $result = $this->requestData($uri);
            if (!$result) {
                return false;
            }
            $this->cache->save($cacheKey, $result);
        }
        return $result;

    }

    /**
     * Get the list of timezones
     *
     * @return array|bool
     */
    public function getTimezones()
    {
        $uri = '/timezones';
        $cacheKey = substr($uri, 1);

        $result = $this->cache->get($cacheKey);
        if ($result == null) {
            $result = $this->requestData($uri);
            if (!$result) {
                return false;
            }
            $this->cache->save($cacheKey, $result);
        }
        return $result;
    }

    /**
     * Make request
     *
     * @param string $uri request uri
     * 
     * @return array|bool
     */
    protected function requestData($uri)
    {
        try {
            $response = $this->client->request(self::REQUEST, $uri);
        } catch (\Exception $e) {
            $this->logger->alert(
                $e->getMessage()
            );
            return false;
        }
        $body = $response->getBody()->getContents();
        $body = json_decode($body);
        if ($body->status == 'success') {
            return array_keys((array)$body->result);
        }
        return [];
    }
}
