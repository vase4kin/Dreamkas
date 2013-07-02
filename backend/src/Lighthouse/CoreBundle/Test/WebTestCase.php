<?php

namespace Lighthouse\CoreBundle\Test;

use Doctrine\ODM\MongoDB\DocumentManager;
use Lighthouse\CoreBundle\Document\Auth\Client as AuthClient;
use Lighthouse\CoreBundle\Document\User\User;
use Lighthouse\CoreBundle\Document\User\UserRepository;
use Lighthouse\CoreBundle\Security\User\UserProvider;
use Lighthouse\CoreBundle\Test\Client\JsonRequest;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use AppKernel;

/**
 * @codeCoverageIgnore
 */
class WebTestCase extends BaseTestCase
{
    /**
     * Init app with debug
     * @var bool
     */
    static protected $appDebug = true;

    /**
     * @var Client
     */
    protected $client;

    protected $oauthClient;

    /**
     * @var User[]
     */
    protected $oauthUsers = array();

    protected function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * @return AppKernel
     */
    protected static function initKernel()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        return static::$kernel;
    }

    /**
     * @param array $options
     * @return AppKernel
     */
    protected static function createKernel(array $options = array())
    {
        $options['debug'] = isset($options['debug']) ? $options['debug'] : static::$appDebug;
        return parent::createKernel($options);
    }

    /**
     * @return Container
     */
    protected function getContainer()
    {
        return static::initKernel()->getContainer();
    }

    protected function clearMongoDb()
    {
        /* @var DocumentManager $mongoDb */
        $mongoDb = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $mongoDb->getSchemaManager()->dropCollections();
        $mongoDb->getSchemaManager()->createCollections();
        $mongoDb->getSchemaManager()->ensureIndexes();
    }

    /**
     * @param stdClass|string $token
     * @param string $method
     * @param string $uri
     * @param array $data
     * @param array $parameters
     * @param array $server
     * @param bool $changeHistory
     * @param bool $oauth
     * @return array
     * @throws \Exception
     */
    protected function clientJsonRequest(
        $token,
        $method,
        $uri,
        $data = null,
        array $parameters = array(),
        array $server = array(),
        $changeHistory = false
    ) {
        $request = new JsonRequest($uri, $method);

        $request->parameters = $parameters;
        $request->server = $server;
        $request->changeHistory = $changeHistory;

        if ($token) {
            $request->setAccessToken($token);
        }

        $request->setJsonData($data);
        $request->setJsonHeaders();

        return $this->jsonRequest($request);
    }

    /**
     * @param JsonRequest $jsonRequest
     * @param stdClass|string $accessToken
     * @return array
     */
    protected function jsonRequest(JsonRequest $jsonRequest, $accessToken = null)
    {
        if (null !== $accessToken) {
            $jsonRequest->setAccessToken($accessToken);
        }

        $this->client->request(
            $jsonRequest->method,
            $jsonRequest->uri,
            $jsonRequest->parameters,
            $jsonRequest->files,
            $jsonRequest->server,
            $jsonRequest->content,
            $jsonRequest->changeHistory
        );

        return $this->parseJsonResponse($this->client);
    }

    /**
     * @param array $modifiedData
     * @return string
     */
    protected function createInvoice(array $modifiedData = array())
    {
        $accessToken = $this->authAsRole('ROLE_DEPARTMENT_MANAGER');

        $invoiceData = $modifiedData + array(
            'sku' => 'sdfwfsf232',
            'supplier' => 'ООО "Поставщик"',
            'acceptanceDate' => '2013-03-18 12:56',
            'accepter' => 'Приемных Н.П.',
            'legalEntity' => 'ООО "Магазин"',
            'supplierInvoiceSku' => '1248373',
            'supplierInvoiceDate' => '17.03.2013',
        );

        $postResponse = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/invoices',
            $invoiceData
        );

        Assert::assertResponseCode(201, $this->client);
        Assert::assertJsonHasPath('id', $postResponse);

        return $postResponse['id'];
    }

    /**
     * @param string $invoiceId
     * @param string $productId
     * @param int $quantity
     * @param float $price
     * @return string
     */
    public function createInvoiceProduct($invoiceId, $productId, $quantity, $price)
    {
        $accessToken = $this->authAsRole('ROLE_DEPARTMENT_MANAGER');

        $invoiceProductData = array(
            'product' => $productId,
            'quantity' => $quantity,
            'price' => $price
        );

        $postResponse = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/invoices/' . $invoiceId . '/products.json',
            $invoiceProductData
        );

        Assert::assertResponseCode(201, $this->client);

        return $postResponse['id'];
    }

    public function createPurchaseWithProduct($productId, $sellingPrice, $quantity, $date = 'now')
    {
        $purchaseProductData = array(
            'product' => $productId,
            'sellingPrice' => $sellingPrice,
            'quantity' => $quantity,
        );

        $accessToken = $this->authAsRole('ROLE_ADMINISTRATOR');

        $postResponse = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/purchases.json',
            array(
                'createdDate' => date('c', strtotime($date)),
                'products' => array($purchaseProductData),
            )
        );

        Assert::assertResponseCode(201, $this->client);
        Assert::assertJsonHasPath('id', $postResponse);

        return $postResponse['id'];
    }

    /**
     * @param string $extra
     * @return string
     */
    protected function createProduct($extra = '')
    {
        $productData = array(
            'name' => 'Кефир "Веселый Молочник" 1% 950гр' . $extra,
            'units' => 'gr',
            'barcode' => '4607025392408',
            'purchasePrice' => 3048,
            'sku' => 'КЕФИР "ВЕСЕЛЫЙ МОЛОЧНИК" 1% КАРТОН УПК. 950ГР' . $extra,
            'vat' => 10,
            'vendor' => 'Вимм-Билль-Данн',
            'vendorCountry' => 'Россия',
            'info' => 'Классный кефирчик, употребляю давно, всем рекомендую для поднятия тонуса',
        );

        $accessToken = $this->authAsRole('ROLE_COMMERCIAL_MANAGER');
        $request = new JsonRequest('/api/1/products', 'POST', $productData);
        $postResponse = $this->jsonRequest($request, $accessToken);

        Assert::assertResponseCode(201, $this->client);
        Assert::assertJsonHasPath('id', $postResponse);

        return $postResponse['id'];
    }

    /**
     * @param $productId
     * @param $invoiceId
     * @return array
     */
    protected function createInvoiceProducts($productId, $invoiceId)
    {
        $productsData = array(
            array(
                'product' => $productId,
                'quantity' => 10,
                'price' => 11.12,
                'productAmount' => 10,
            ),
            array(
                'product' => $productId,
                'quantity' => 5,
                'price' => 12.76,
                'productAmount' => 15,
            ),
            array(
                'product' => $productId,
                'quantity' => 1,
                'price' => 5.99,
                'productAmount' => 16,
            ),
        );

        $accessToken = $this->authAsRole('ROLE_DEPARTMENT_MANAGER');

        foreach ($productsData as $i => $row) {

            $invoiceProductData = array(
                'quantity' => $row['quantity'],
                'price' => $row['price'],
                'product' => $row['product'],
            );

            $response = $this->clientJsonRequest(
                $accessToken,
                'POST',
                '/api/1/invoices/' . $invoiceId . '/products.json',
                $invoiceProductData
            );

            Assert::assertResponseCode(201, $this->client);
            $productsData[$i]['id'] = $response['id'];
        }

        $getResponse = $this->clientJsonRequest(
            $accessToken,
            'GET',
            '/api/1/invoices/' . $invoiceId . '/products.json'
        );

        Assert::assertResponseCode(200, $this->client);

        Assert::assertJsonPathCount(3, "*.id", $getResponse);

        foreach ($productsData as $productData) {
            Assert::assertJsonPathEquals($productData['id'], '*.id', $getResponse);
        }

        return $productsData;
    }

    /**
     * @param string $number
     * @param int $date timestamp
     * @return mixed
     */
    protected function createWriteOff($number = '431-6782', $date = null)
    {
        $date = $date ? : date('c', strtotime('-1 day'));

        $postData = array(
            'number' => $number,
            'date' => $date,
        );

        $accessToken = $this->authAsRole('ROLE_DEPARTMENT_MANAGER');

        $postResponse = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/writeoffs.json',
            $postData
        );

        Assert::assertResponseCode(201, $this->client);

        Assert::assertJsonHasPath('id', $postResponse);

        return $postResponse['id'];
    }

    /**
     * @param string $writeOffId
     * @param string $productId
     * @param float $price
     * @param int $quantity
     * @param string $cause
     * @return string
     */
    protected function createWriteOffProduct($writeOffId, $productId, $price = 5.99, $quantity = 10, $cause = 'Порча')
    {
        $postData = array(
            'product' => $productId,
            'price' => $price,
            'quantity' => $quantity,
            'cause' => $cause,
        );

        $accessToken = $this->authAsRole('ROLE_DEPARTMENT_MANAGER');
        $request = new JsonRequest('/api/1/writeoffs/' . $writeOffId . '/products', 'POST', $postData);
        $postResponse = $this->jsonRequest($request, $accessToken);

        Assert::assertResponseCode(201, $this->client);

        Assert::assertJsonHasPath('id', $postResponse);

        return $postResponse['id'];
    }

    /**
     * @param string $name
     * @return string
     */
    protected function createKlass($name = 'Продовольственные товары')
    {
        $postData = array(
            'name' => $name,
        );

        $accessToken = $this->authAsRole('ROLE_COMMERCIAL_MANAGER');

        $postResponse = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/klasses.json',
            $postData
        );

        Assert::assertResponseCode(201, $this->client);

        Assert::assertJsonHasPath('id', $postResponse);

        return $postResponse['id'];
    }

    /**
     * @param mixed $json
     * @param array $assertions
     * @param bool  $contains
     */
    protected function performJsonAssertions($json, array $assertions, $contains = false)
    {
        foreach ($assertions as $path => $expected) {
            if (null === $expected) {
                Assert::assertNotJsonHasPath($path, $json);
            } elseif ($contains) {
                Assert::assertJsonPathContains($expected, $path, $json);
            } else {
                Assert::assertJsonPathEquals($expected, $path, $json);
            }
        }
    }

    /**
     * @param string $productId
     * @param array $assertions
     */
    protected function assertProduct($productId, array $assertions)
    {
        $accessToken = $this->authAsRole('ROLE_COMMERCIAL_MANAGER');

        $request = new JsonRequest('/api/1/products/' . $productId);
        $request->setAccessToken($accessToken);

        $productJson = $this->jsonRequest($request);

        Assert::assertResponseCode(200, $this->client);

        $this->performJsonAssertions($productJson, $assertions);
    }

    /**
     * @param string $klassId
     * @param string $name
     * @return string
     */
    protected function createGroup($klassId, $name = 'Винно-водочные изделия')
    {
        $groupData = array(
            'name' => $name,
            'klass' => $klassId,
        );

        $accessToken = $this->authAsRole('ROLE_COMMERCIAL_MANAGER');

        $postResponse = $this->clientJsonRequest(
            $accessToken,
            'POST',
            '/api/1/groups',
            $groupData
        );

        Assert::assertResponseCode(201, $this->client);
        Assert::assertJsonHasPath('id', $postResponse);

        return $postResponse['id'];
    }

    /**
     * @param string $secret
     * @return AuthClient
     */
    protected function createAuthClient($secret = 'secret')
    {
        $client = new AuthClient();
        $client->setSecret($secret);

        $dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');

        $dm->persist($client);
        $dm->flush();

        return $client;
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $role
     * @param string $name
     * @param string $position
     * @return User
     */
    protected function createUser(
        $username = 'admin',
        $password = 'password',
        $role = 'ROLE_ADMINISTRATOR',
        $name = 'Админ Админыч',
        $position = 'Администратор'
    ) {
        /* @var UserRepository $userRepository */
        $userRepository = $this->getContainer()->get('lighthouse.core.document.repository.user');
        /* @var UserProvider $userProvider */
        $userProvider = $this->getContainer()->get('lighthouse.core.user.provider');

        $user = new User();
        $user->name = $name;
        $user->username = $username;
        $user->role = $role;
        $user->position = $position;

        $userProvider->setPassword($user, $password);

        $userRepository->getDocumentManager()->persist($user);
        $userRepository->getDocumentManager()->flush();

        return $user;
    }

    /**
     * @param string $role
     * @return stdClass accessToken
     */
    protected function authAsRole($role)
    {
        $user = $this->getRoleUser($role);
        return $this->auth($user);
    }

    /**
     * @param string $role
     * @return User
     */
    protected function getRoleUser($role)
    {
        if (!isset($this->oauthUsers[$role])) {
            $this->oauthUsers[$role] = $this->createUser($role, 'password', $role, $role, $role);
        }

        return $this->oauthUsers[$role];
    }

    /**
     * @param User $oauthUser
     * @param string $password
     * @param AuthClient $oauthClient
     * @return stdClass access token
     */
    protected function auth(User $oauthUser = null, $password = 'password', AuthClient $oauthClient = null)
    {
        if (!$oauthClient) {
            if (!$this->oauthClient) {
                $this->oauthClient = $this->createAuthClient();
            }
            $oauthClient = $this->oauthClient;
        }

        if (!$oauthUser) {
            $oauthUser = $this->getRoleUser('ROLE_ADMINISTRATOR');
        }

        $authParams = array(
            'grant_type' => 'password',
            'username' => $oauthUser->username,
            'password' => $password,
            'client_id' => $oauthClient->getPublicId(),
            'client_secret' => $oauthClient->getSecret()
        );

        $this->client->request(
            'POST',
            '/oauth/v2/token',
            $authParams,
            array(),
            array('Content-Type' => 'application/x-www-form-urlencoded')
        );

        $response = $this->client->getResponse()->getContent();
        $json = json_decode($response);

        return $json;
    }

    /**
     * @param Client $client
     * @return mixed
     * @throws \UnexpectedValueException
     */
    protected function parseJsonResponse(Client $client)
    {
        $content = $client->getResponse()->getContent();
        $json = json_decode($content, true);

        if (0 != json_last_error()) {
            throw new \UnexpectedValueException('Failed to parse json: ' . $content);
        }

        return $json;
    }

    /**
     * @param string $format
     * @return string
     */
    protected function getNowDate($format = 'Y-m-d\\TH:i:')
    {
        return date($format);
    }
}
