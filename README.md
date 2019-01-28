# Phalcon app

Phalcon framework based REST API to store, retrieve, update and delete phone numbers

Authorization
------------
* Send a `POST` request to `http://domain/api/v1/oauth/token`
    - client_id: test
    - client_secret: secret
    - grant_type: password
    - username: abc
    - password: abc

* Get an access_token to send requests to API using Bearer authorization

Try it out
==========

1. `POST` request `http://domain/get`. Optional parameters
    - limit: default = 100
    - offset: default = 0
    
2. `POST` request `http://domain/getById`. Parameters:
    - id

3. `POST` request `http://domain/getByName`. Parameters:
    - name

4. `POST` request `http://domain/save`. Required parameters:
    - first_name
    - phone_number

    Optional:
    - last_name
    - country_code
    - timezone
    
5. `POST` request `http://domain/update`. Required parameters:
    - id
    - first_name
    - phone_number

    Optional:
    - last_name
    - country_code
    - timezone

6. `POST` request `http://domain/delete`. Required parameters:
    - id
