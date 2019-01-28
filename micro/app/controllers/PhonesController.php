<?php
/**
 * Class to store,retrieve,update and delete data
 *
 * PHP version 7
 *
 * @category Phones
 * @package  App\Controllers
 * @author   Anastasia Barchukova <stasya-88@list.ru>
 * @license  https://github.com/nastena88/phalcon_app Public
 * @link     https://github.com/nastena88/phalcon_app
 */

namespace App\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;
use App\Controllers\AuthController;

/**
 * Class PhonesController
 *
 * @category Phones
 * @package  App\Controllers
 * @author   Anastasia Barchukova <stasya-88@list.ru>
 * @license  https://github.com/nastena88/phalcon_app Public
 * @link     https://github.com/nastena88/phalcon_app
 */
class PhonesController extends ControllerBase
{
    protected $logger;
    protected $cache;
    protected $phoneParams = [
        'required' => [
            'first_name',
            'phone_number',
        ],
        'optional' => [
            'last_name',
            'country_code',
            'timezone',
            'inserted_on',
            'updated_on'
        ],
        'validate' => [
            'phone_number',
            'country_code',
            'timezone'
        ],
    ];
    protected $request;
    private $_postData;
    const CACHE_TIME = 172800;
    const LOG_PATH = APP_PATH.'/logs/Phones.log';
    const CACHE_PATH = APP_PATH.'/cache/';
    const DEFAULT_OFFSET = 0;
    const DEFAULT_LIMIT = 100;

    /**
     * Class constructor
     *
     * @return mixed
     */
    public function onConstruct()
    {
        $this->logger = new FileAdapter(self::LOG_PATH);
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
        $this->request = new \Phalcon\Http\Request();
        $this->_postData = $this->request->getPost();
        $auth = new AuthController();
        try {
            $auth->internalValidate();
        } catch (\Exception $e){

        }
    }

    /**
     * Get the list of phone numbers
     *
     * @return mixed
     */
    public function get()
    {
        if ($this->request->isPost() == true) {
            $limit = isset($this->_postData['limit']) ? $this->_postData['limit'] : self::DEFAULT_LIMIT;
            $offset = isset($this->_postData['offset']) ? $this->_postData['offset'] : self::DEFAULT_OFFSET;

            //checking if we have cache
            $cacheKey = 'get.'.$limit.'.'.$offset;
            $result = $this->cache->get($cacheKey);
            //the cache is empty
            //we receive current data and add it to cache
            if ($result == null) {
                //count all records
                try {
                    $count = \App\Models\PhonesModel::count();
                    $phones = \App\Models\PhonesModel::find(
                        [
                            'limit' => $limit,
                            'offset' => $offset,
                        ]
                    );
                } catch (\Exception $e) {
                    $this->logger->critical($e->getMessage());
                    $this->response
                        ->setStatusCode(500, 'Internal Server Error')
                        ->setJsonContent(
                            array(
                            'status' => 'error',
                            'result' => 'Could not get data from DB!'
                            ), JSON_UNESCAPED_UNICODE
                        );
                    return $this->response;
                }
                $this->response
                    ->setStatusCode(200, 'OK')
                    ->setJsonContent(
                        [
                        'status' => 'success',
                        'result' => [
                            'count' => $count,
                            'rows' => $phones,
                        ]
                        ], JSON_UNESCAPED_UNICODE
                    );
                $this->cache->save(
                    $cacheKey, json_encode(
                        [
                        'status' => 'success',
                        'result' => [
                        'count' => $count,
                        'rows' => $phones,
                        ]
                        ]
                    )
                );
            } else {
                $this->response
                    ->setStatusCode(200, 'OK')
                    ->setHeader('Content-Type', 'application/json')
                    ->setContent($result);
            }
        }
        return $this->response;
    }

    /**
     * Get record by id
     *
     * @return mixed
     */
    public function getById()
    {
        if ($this->request->isPost() == true) {
            if (!isset($this->_postData['id'])) {
                $this->response
                    ->setStatusCode(200, 'OK')
                    ->setJsonContent(
                        array(
                        'status' => 'error',
                        'result' => 'ID is required!'
                        ), JSON_UNESCAPED_UNICODE
                    );
                return $this->response;
            }
            try {
                $phones = \App\Models\PhonesModel::find(
                    [
                        'conditions' => 'id=:id:',
                        'bind' => ['id' => $this->_postData['id']]
                    ]
                );
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
                $this->response
                    ->setStatusCode(500, 'Internal Server Error')
                    ->setJsonContent(
                        array(
                        'status' => 'error',
                        'result' => 'Could not get data from DB!'
                        ), JSON_UNESCAPED_UNICODE
                    );
                return $this->response;
            }
            $this->response
                ->setStatusCode(200, 'OK')
                ->setJsonContent(
                    array(
                    'status' => 'success',
                    'result' => [
                        'count' => count($phones),
                        'rows' => $phones,
                    ]
                    ), JSON_UNESCAPED_UNICODE
                );
        }
        return $this->response;
    }

    /**
     * Search record by name
     *
     * @return mixed
     */
    public function getByName()
    {
        if ($this->request->isPost() == true) {
            if (!isset($this->_postData['name'])) {
                $this->response
                    ->setStatusCode(200, 'OK')
                    ->setJsonContent(
                        array(
                        'status' => 'error',
                        'result' => 'NAME is required!'
                        ), JSON_UNESCAPED_UNICODE
                    );
                return $this->response;
            }
            try {
                $phones = \App\Models\PhonesModel::find(
                    [
                        'conditions' => 'first_name LIKE :name: OR last_name LIKE :name:',
                        'bind' => ['name' => $this->_postData['name']]
                    ]
                );
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
                $this->response
                    ->setStatusCode(500, 'Internal Server Error')
                    ->setJsonContent(
                        array(
                        'status' => 'error',
                        'result' => 'Could not get data from DB!'
                        ), JSON_UNESCAPED_UNICODE
                    );
                return $this->response;
            }
            $this->response
                ->setStatusCode(200, 'OK')
                ->setJsonContent(
                    array(
                    'status' => 'success',
                    'result' => [
                        'count' => count($phones),
                        'rows' => $phones,
                    ]
                    ), JSON_UNESCAPED_UNICODE
                );
        }
        return $this->response;
    }

    /**
     * Save new record to DB
     *
     * @return mixed
     */
    public function save()
    {
        if ($this->request->isPost() == true) {
            $validator = new ValidationController();
            try {
                $valid = $validator->validateFields($this->_postData);
            } catch (\Exception $e) {
                $this->response
                    ->setStatusCode(200, 'OK')
                    ->setJsonContent(
                        array(
                        'status' => 'error',
                        'result' => $e->getMessage()
                        ), JSON_UNESCAPED_UNICODE
                    );
                return $this->response;
            }
            $phones = new \App\Models\PhonesModel();
            $allFields = array_merge($this->phoneParams['required'], $this->phoneParams['optional']);
            foreach ($allFields as $field) {
                switch ($field) {
                case 'inserted_on':
                case 'updated_on':
                    $phones->$field = date('Y-m-d h:i:s');
                    break;
                default:
                    if (isset($this->_postData[$field])) {
                        $phones->$field = $this->_postData[$field];
                    }
                    break;
                }
            }
            try {
                $result = $phones->save();
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
                $this->response
                    ->setStatusCode(500, 'Internal Server Error')
                    ->setJsonContent(
                        array(
                        'status' => 'error',
                        'result' => 'Could not save data to DB!'
                        ), JSON_UNESCAPED_UNICODE
                    );
                return $this->response;
            }
            if ($result) {
                $this->response
                    ->setStatusCode(200, 'OK')
                    ->setJsonContent(
                        array(
                        'status' => 'success',
                        'result' => 'The phone number successfully added to DB!'
                        ), JSON_UNESCAPED_UNICODE
                    );
            } else {
                $this->logger->warning(implode(' ', $phones->getMessages()));
                $this->response
                    ->setStatusCode(500, 'Internal Server Error')
                    ->setJsonContent(
                        array(
                        'status' => 'error',
                        'result' => implode(' ', $phones->getMessages())
                        ), JSON_UNESCAPED_UNICODE
                    );
            }
        }
        return $this->response;
    }

    /**
     * Update record by id
     *
     * @return mixed
     */
    public function update()
    {
        if ($this->request->isPost() == true) {
            if (!isset($this->_postData['id'])) {
                $this->response
                    ->setStatusCode(200, 'OK')
                    ->setJsonContent(
                        array(
                        'status' => 'error',
                        'result' => 'ID is required for update!'
                        ), JSON_UNESCAPED_UNICODE
                    );
                return $this->response;
            }
            try {
                $phone = \App\Models\PhonesModel::findFirst(
                    [
                        'conditions' => 'id=:id:',
                        'bind' => ['id' => $this->_postData['id']]
                    ]
                );
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
                $this->response
                    ->setStatusCode(500, 'Internal Server Error')
                    ->setJsonContent(
                        array(
                        'status' => 'error',
                        'result' => 'Could not connect DB!'
                        ), JSON_UNESCAPED_UNICODE
                    );
                return $this->response;
            }

            if (!$phone) {
                $this->response
                    ->setStatusCode(200, 'OK')
                    ->setJsonContent(
                        array(
                        'status' => 'error',
                        'result' => 'No record with ID '.$this->_postData['id'].'!'
                        ), JSON_UNESCAPED_UNICODE
                    );
                return $this->response;
            }
            $validator = new ValidationController();
            try {
                $valid = $validator->validateFields($this->_postData);
            } catch (\Exception $e) {
                $this->response
                    ->setStatusCode(200, 'OK')
                    ->setJsonContent(
                        array(
                        'status' => 'error',
                        'result' => $e->getMessage()
                        ), JSON_UNESCAPED_UNICODE
                    );
                return $this->response;
            }
            $allFields = array_merge($this->phoneParams['required'], $this->phoneParams['optional']);
            foreach ($allFields as $field) {
                switch ($field) {
                case 'updated_on':
                    $phone->$field = date('Y-m-d h:i:s');
                    break;
                default:
                    if (isset($this->_postData[$field])) {
                        $phone->$field = $this->_postData[$field];
                    }
                    break;
                }
            }
            try {
                $result = $phone->update();
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
                $this->response
                    ->setStatusCode(500, 'Internal Server Error')
                    ->setJsonContent(
                        array(
                        'status' => 'error',
                        'result' => 'Could not update DB!'
                        ), JSON_UNESCAPED_UNICODE
                    );
                return $this->response;
            }
            if ($result) {
                $this->response
                    ->setStatusCode(200, 'OK')
                    ->setJsonContent(
                        array(
                        'status' => 'success',
                        'result' => 'The phone number successfully updated!'
                        ), JSON_UNESCAPED_UNICODE
                    );
            } else {
                $this->logger->warning(implode(' ', $phone->getMessages()));
                $this->response
                    ->setStatusCode(200, 'OK')
                    ->setJsonContent(
                        array(
                        'status' => 'error',
                        'result' => implode(' ', $phone->getMessages())
                        ), JSON_UNESCAPED_UNICODE
                    );
            }
        }
        return $this->response;
    }

    /**
     * Delete record by id
     *
     * @return mixed
     */
    public function delete()
    {
        if ($this->request->isPost() == true) {
            if (!isset($this->_postData['id'])) {
                $this->response
                    ->setStatusCode(200, 'OK')
                    ->setJsonContent(
                        array(
                        'status' => 'error',
                        'result' => 'ID is required for update!'
                        ), JSON_UNESCAPED_UNICODE
                    );
                return $this->response;
            }
            $phones = new \App\Models\PhonesModel();
            $phones->id = $this->_postData['id'];
            try {
                $result = $phones->delete();
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
                $this->response
                    ->setStatusCode(500, 'Internal Server Error')
                    ->setJsonContent(
                        array(
                        'status' => 'error',
                        'result' => 'Could not delete data from DB!'
                        ), JSON_UNESCAPED_UNICODE
                    );
                 return $this->response;
            }
            if ($result) {
                $this->response
                    ->setStatusCode(200, 'OK')
                    ->setJsonContent(
                        array(
                        'status' => 'success',
                        'result' => 'The phone number was deleted!'
                        ), JSON_UNESCAPED_UNICODE
                    );
            } else {
                $this->logger->warning(implode(' ', $phones->getMessages()));
                $this->response
                    ->setStatusCode(200, 'OK')
                    ->setJsonContent(
                        array(
                        'status' => 'error',
                        'result' => implode(' ', $phones->getMessages())
                        ), JSON_UNESCAPED_UNICODE
                    );
            }
        }
        return $this->response;
    }
}
