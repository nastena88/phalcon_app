<?php
/**
 * Class to validate request parameters
 *
 * PHP version 7
 *
 * @category Validation
 * @package  App\Controllers
 * @author   Anastasia Barchukova <stasya-88@list.ru>
 * @license  https://github.com/nastena88/phalcon_app Public
 * @link     https://github.com/nastena88/phalcon_app
 */

namespace App\Controllers;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\InclusionIn;

/**
 * Class ValidationController
 *
 * @category Validation
 * @package  App\Controllers
 * @author   Anastasia Barchukova <stasya-88@list.ru>
 * @license  https://github.com/nastena88/phalcon_app Public
 * @link     https://github.com/nastena88/phalcon_app
 */
class ValidationController extends PhonesController
{
    /**
     * Method to validate all fields
     *
     * @param array $data request parameters
     *
     * @return array|bool
     * @throws \Exception
     */
    public function validateFields($data = []) 
    {
        if (empty($data)) {
            return [
                'valid'=> false,
                'messages' => ['Empty request!']
            ];
        }
        $messages = [];
        $validation = new Validation();
        foreach ($this->phoneParams['required'] as $required) {
            $validation->add(
                $required,
                new PresenceOf(
                    [
                        'message' => $required.' is required',
                    ]
                )
            );
        }
        foreach ($this->phoneParams['validate'] as $validate) {
            switch ($validate) {
            case 'phone_number':
                if (!empty($data['phone_number'])) {
                    $validation->add(
                        'phone_number',
                        new Regex(
                            [
                                'message'    => 'The phone number is not valid!',
                                'pattern'    => '/^\+[0-9]{1,3}\s[0-9]{3}\s[0-9]{6,12}$/',
                                'allowEmpty' => false,
                            ]
                        )
                    );
                }
                break;
            case 'country_code':
                if (!empty($data['country_code'])) {
                    $ext = new ExternalRequestController();
                    $codes = $ext->getCountries();
                    if (!$codes) {
                        $messages[] = 'Could not validate country_code! Please try sending request later!';
                    } else {
                        $validation->add(
                            'country_code',
                            new InclusionIn(
                                [
                                    'message'    => 'The country_code is not valid!',
                                    'domain'    => $codes,
                                ]
                            )
                        );
                    }
                }
                break;
            case 'timezone':
                if (!empty($data['timezone'])) {
                    $ext = new ExternalRequestController();
                    $codes = $ext->getTimezones();
                    if (!$codes) {
                        $messages[] = 'Could not validate timezone! Please try sending request later!';
                    } else {
                        $validation->add(
                            'timezone',
                            new InclusionIn(
                                [
                                    'message'    => 'The timezone is not valid!',
                                    'domain'    => $codes,
                                ]
                            )
                        );
                    }
                }
                break;
            }
        }
        $errorMessages = $validation->validate($data);
        if (count($errorMessages)) {
            foreach ($errorMessages as $error) {
                $messages[] = (string)$error;
            }
        }
        if (count($messages)) {
            throw new \Exception(implode(' ', $messages));
        }
        return true;
    }
}
