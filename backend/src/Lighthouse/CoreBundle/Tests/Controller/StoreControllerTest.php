<?php

namespace Lighthouse\CoreBundle\Tests\Controller;

use Lighthouse\CoreBundle\Test\WebTestCase;
use Lighthouse\CoreBundle\Test\Assert;

class StoreControllerTest extends WebTestCase
{
    public function testPostStoreAction()
    {
        $this->clearMongoDb();

        $storeData = array(
            'number' => 'магазин_номер-32',
            'address' => 'СПБ, профессора Попова пр., д. 37, пом 3А',
            'contacts' => 'тел. 344-32-54, тел/факс +7-921-334-2343, email:super@store.spb.ru',
        );

        $accessToken = $this->authAsRole("ROLE_COMMERCIAL_MANAGER");

        $response = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/stores',
            $storeData
        );

        Assert::assertResponseCode(201, $this->client);

        Assert::assertJsonHasPath('id', $response);
        foreach ($storeData as $name => $value) {
            Assert::assertJsonPathEquals($value, $name, $response);
        }
    }

    public function testStoreUnique()
    {
        $this->clearMongoDb();

        $storeId = $this->createStore("42");

        $storeData = array(
            'number' => '42',
            'address' => 'qwe',
            'contacts' => 'qwew',
        );

        $accessToken = $this->authAsRole('ROLE_COMMERCIAL_MANAGER');

        $response = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/stores',
            $storeData
        );

        Assert::assertResponseCode(400, $this->client);
        Assert::assertJsonPathEquals('Такой магазин уже есть', 'children.number.errors.0', $response);
    }

    /**
     * @param $expectedCode
     * @param array $data
     * @param array $assertions
     *
     * @dataProvider validationStoreProvider
     */
    public function testPostStoreValidation($expectedCode, array $data, array $assertions = array())
    {
        $this->clearMongoDb();

        $storeData = $data + array(
            'number' => 'магазин_номер-32',
            'address' => 'СПБ, профессора Попова пр., д. 37, пом 3А',
            'contacts' => 'тел. 344-32-54, тел/факс +7-921-334-2343, email:super@store.spb.ru',
        );

        $accessToken = $this->authAsRole('ROLE_COMMERCIAL_MANAGER');

        $response = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/stores',
            $storeData
        );

        Assert::assertResponseCode($expectedCode, $this->client);

        foreach ($assertions as $path => $expected) {
            Assert::assertJsonPathContains($expected, $path, $response);
        }
    }

    /**
     * @param $expectedCode
     * @param array $data
     * @param array $assertions
     *
     * @dataProvider validationStoreProvider
     */
    public function testPutStoreValidation($expectedCode, array $data, array $assertions = array())
    {
        $this->clearMongoDb();

        $storeId = $this->createStore();

        $storeData = $data + array(
                'number' => 'магазин_номер-32',
                'address' => 'СПБ, профессора Попова пр., д. 37, пом 3А',
                'contacts' => 'тел. 344-32-54, тел/факс +7-921-334-2343, email:super@store.spb.ru',
            );

        $accessToken = $this->authAsRole('ROLE_COMMERCIAL_MANAGER');

        $response = $this->clientJsonRequest(
            $accessToken,
            'PUT',
            '/api/1/stores/' . $storeId,
            $storeData
        );

        $expectedCode = ($expectedCode == 201) ? 200 : $expectedCode;

        Assert::assertResponseCode($expectedCode, $this->client);

        foreach ($assertions as $path => $expected) {
            Assert::assertJsonPathContains($expected, $path, $response);
        }
    }

    public function validationStoreProvider()
    {
        return array(
            /****************************************
             * number
             */
            'not valid empty number' => array(
                400,
                array('number' => ''),
                array(
                    'children.number.errors.0'
                    =>
                    'Заполните это поле'
                )
            ),
            'not valid long 51 number' => array(
                400,
                array('number' => str_repeat('z', 51)),
                array(
                    'children.number.errors.0'
                    =>
                    'Не более 50 символов'
                )
            ),
            'valid long 50 number' => array(
                201,
                array('number' => str_repeat('z', 50)),
            ),
            'valid symbols number' => array(
                201,
                array('number' => 'QWEqweФЫВфыв123-_-34'),
            ),
            'not valid symbols number' => array(
                400,
                array('number' => 'ASdqwe123466!@#$%^&*(;"'),
                array(
                    'children.number.errors.0'
                    =>
                    'Значение недопустимо'
                )
            ),

            /****************************************
             * address
             */
            'valid long 300 address' => array(
                201,
                array('address' => str_repeat('z', 300)),
            ),
            'not valid long 301 address' => array(
                400,
                array('address' => str_repeat('z', 301)),
                array(
                    'children.address.errors.0'
                    =>
                    'Не более 300 символов'
                ),
            ),
            'valid symbols address' => array(
                201,
                array('address' => 'ASdqwe123466!@#$%^&*(;"'),
            ),
            'not valid empty address' => array(
                400,
                array('address' => ''),
                array(
                    'children.address.errors.0'
                    =>
                    'Заполните это поле'
                )
            ),

            /****************************************
             * contacts
             */
            'valid long 100 contacts' => array(
                201,
                array('contacts' => str_repeat('z', 100)),
            ),
            'not valid long 101 contacts' => array(
                400,
                array('contacts' => str_repeat('z', 101)),
                array(
                    'children.contacts.errors.0'
                    =>
                    'Не более 100 символов'
                ),
            ),
            'valid symbols contacts' => array(
                201,
                array('contacts' => 'ASdqwe123466!@#$%^&*(;"'),
            ),
            'not valid empty contacts' => array(
                400,
                array('contacts' => ''),
                array(
                    'children.contacts.errors.0'
                    =>
                    'Заполните это поле'
                )
            ),
        );
    }

    public function testGetStores()
    {
        $this->clearMongoDb();

        $storesIds = array();
        for ($i = 0; $i < 5; $i++) {
            $storesIds[$i] = $this->createStore(
                'номер_' . $i,
                'адрес ' . $i,
                'контакты ' . $i
            );
        }

        $accessToken = $this->authAsRole('ROLE_COMMERCIAL_MANAGER');

        $response = $this->clientJsonRequest(
            $accessToken,
            'GET',
            '/api/1/stores'
        );

        Assert::assertResponseCode(200, $this->client);
        Assert::assertJsonPathCount(5, '*.id', $response);

        foreach ($storesIds as $id) {
            Assert::assertJsonPathEquals($id, '*.id', $response);
        }
    }

    public function testGetStoreAction()
    {
        $this->clearMongoDb();

        $storeData = array(
            'number' => 'магазин_номер-32',
            'address' => 'СПБ, профессора Попова пр., д. 37, пом 3А',
            'contacts' => 'тел. 344-32-54, тел/факс +7-921-334-2343, email:super@store.spb.ru',
        );

        $accessToken = $this->authAsRole("ROLE_COMMERCIAL_MANAGER");

        $response = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/stores',
            $storeData
        );

        Assert::assertResponseCode(201, $this->client);

        Assert::assertJsonHasPath('id', $response);
        foreach ($storeData as $name => $value) {
            Assert::assertJsonPathEquals($value, $name, $response);
        }

        $id = $response['id'];

        $response = $this->clientJsonRequest(
            $accessToken,
            'GET',
            '/api/1/stores/' . $id
        );

        Assert::assertResponseCode(200, $this->client);

        foreach ($storeData as $name => $value) {
            Assert::assertJsonPathEquals($value, $name, $response);
        }
    }

    /**
     * @param string    $url
     * @param string    $method
     * @param string    $role
     * @param int       $responseCode
     * @param array|null $requestData
     *
     * @dataProvider accessStoreProvider
     */
    public function testAccessStore($url, $method, $role, $responseCode, $requestData = null)
    {
        $this->clearMongoDb();

        $storeId = $this->createStore();

        $url = str_replace(
            array(
                '__STORE_ID__',
            ),
            array(
                $storeId,
            ),
            $url
        );
        $accessToken = $this->authAsRole($role);
        if (is_array($requestData)) {
            $requestData = $requestData + array (
                'number' => 'магазин_номер-32',
                'address' => 'СПБ, профессора Попова пр., д. 37, пом 3А',
                'contacts' => 'тел. 344-32-54, тел/факс +7-921-334-2343, email:super@store.spb.ru',
            );
        }

        $response = $this->clientJsonRequest(
            $accessToken,
            $method,
            $url,
            $requestData
        );

        Assert::assertResponseCode($responseCode, $this->client);
    }

    public function accessStoreProvider()
    {
        return array(
            /**************************************
             * GET /api/1/stores
             */
            array(
                '/api/1/stores',
                'GET',                              // Method
                'ROLE_COMMERCIAL_MANAGER',          // Role
                '200',                              // Response Code
            ),
            array(
                '/api/1/stores',
                'GET',
                'ROLE_DEPARTMENT_MANAGER',
                '403',
            ),
            array(
                '/api/1/stores',
                'GET',
                'ROLE_STORE_MANAGER',
                '403',
            ),
            array(
                '/api/1/stores',
                'GET',
                'ROLE_ADMINISTRATOR',
                '403',
            ),

            /*************************************
             * GET /api/1/stores/__ID__
             */
            array(
                '/api/1/stores/__STORE_ID__',
                'GET',
                'ROLE_COMMERCIAL_MANAGER',
                '200',
            ),
            array(
                '/api/1/stores/__STORE_ID__',
                'GET',
                'ROLE_DEPARTMENT_MANAGER',
                '403',
            ),
            array(
                '/api/1/stores/__STORE_ID__',
                'GET',
                'ROLE_STORE_MANAGER',
                '403',
            ),
            array(
                '/api/1/stores/__STORE_ID__',
                'GET',
                'ROLE_ADMINISTRATOR',
                '403',
            ),

            /*************************************
             * POST /api/1/stores
             */
            array(
                '/api/1/stores',
                'POST',
                'ROLE_COMMERCIAL_MANAGER',
                '201',
                array(),
            ),
            array(
                '/api/1/stores',
                'POST',
                'ROLE_DEPARTMENT_MANAGER',
                '403',
                array(),
            ),
            array(
                '/api/1/stores',
                'POST',
                'ROLE_STORE_MANAGER',
                '403',
                array(),
            ),
            array(
                '/api/1/stores',
                'POST',
                'ROLE_ADMINISTRATOR',
                '403',
                array(),
            ),

            /*************************************
             * PUT /api/1/stores/__STORE_ID__
             */
            array(
                '/api/1/stores/__STORE_ID__',
                'PUT',
                'ROLE_COMMERCIAL_MANAGER',
                '200',
                array(),
            ),
            array(
                '/api/1/stores/__STORE_ID__',
                'PUT',
                'ROLE_DEPARTMENT_MANAGER',
                '403',
                array(),
            ),
            array(
                '/api/1/stores/__STORE_ID__',
                'PUT',
                'ROLE_STORE_MANAGER',
                '403',
                array(),
            ),
            array(
                '/api/1/stores/__STORE_ID__',
                'PUT',
                'ROLE_ADMINISTRATOR',
                '403',
                array(),
            ),
        );
    }
}
