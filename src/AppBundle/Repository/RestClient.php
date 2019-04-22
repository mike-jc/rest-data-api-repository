<?php

namespace AppBundle\Repository;

use AppBundle\Exception\Exception;
use AppBundle\Exception\HttpException;
use AppBundle\Exception\NeedLoginException;
use AppBundle\Service\JwtService;
use AuthBundle\Service\TokenService;
use AuthBundle\OAuth\AuthorisedUser;
use GuzzleHttp\Client as HttpClient;
//use AppBundle\Repository\HttpClientWIthDebug as HttpClient;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;

class RestClient {
    /**
     * @var string
     */
    private $baseApiUrl;
    /**
     * @var array
     */
    private $headers = [];
    /**
     * @var Session
     */
    private $session;
    /**
     * @var $accessToken AccessToken
     */
    private $accessToken;
    /**
     * @var $jwtToken string
     */
    private $jwtToken;
    /**
     * @var ClientRegistry
     */
    private $oauth2Registry;
    /**
     * @var JwtService
     */
    private $jwtService;
    /**
     * @var TokenService
     */
    private $tokenService;
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @param $baseApiUrl
     * @param boolean|string $debug
     * @param Session|null $session
     * @param ClientRegistry|null $oauth2Registry
     * @param JwtService $jwtService
     * @param TokenService|null $tokenService
     */
    public function __construct($baseApiUrl, $debug = false, Session $session = null, ClientRegistry $oauth2Registry = null, JwtService $jwtService = null, TokenService $tokenService = null) {
        $this->baseApiUrl = $baseApiUrl;
        $this->session = $session;
        $this->oauth2Registry = $oauth2Registry;
        $this->jwtService = $jwtService;
        $this->tokenService = $tokenService;

        $this->httpClient = new HttpClient([
            'base_uri' => $this->baseApiUrl,
            'debug' => is_string($debug) ? fopen($debug, 'a') : $debug,
            'http_errors' => false,
        ]);
        $this->headers = [
            'Accept' => 'application/ld+json',
        ];

        // supress any exception here and give it a try later on a method call (see __call())
        try {
            $this->setAuthTokenFromSession();
        } catch (\Exception $e) {}
    }

    public function setAuthTokenFromSession() {
        if (!is_null($this->session)) {
            $authMode = $this->session->get('auth_mode') ?: 'oauth';
            if ($authMode == 'jwt' && $this->session->get('user') instanceof AuthorisedUser) {
                $this->setJwtToken($this->jwtService->getJwtDataApi());
            } elseif ($this->session->get('access_token')) {
                $this->setAccessToken($this->session->get('access_token'));
            }
        }
    }

    /**
     * @param string $jwtToken
     */
    public function setJwtToken($jwtToken) {
        $this->jwtToken = $jwtToken;
        $this->headers['Auth'] = "JWT {$jwtToken}";
    }

    /**
     * @param AccessToken $accessToken
     */
    public function setAccessToken(AccessToken $accessToken) {
        $this->accessToken = $accessToken;
        $this->headers['Authorization'] = "Bearer {$accessToken->getToken()}";
    }

    /**
     * @param string $url
     * @return string
     */
    public function generateUrl($url) {
        return $this->baseApiUrl . $url;
    }

    /**
     * @return string
     */
    public function getBaseUrl() {
        return $this->baseApiUrl;
    }

    /**
     * @param string $method
     * @param mixed $arguments
     * @return mixed
     * @throws Exception
     * @throws HttpException
     * @throws NeedLoginException
     */
    public function __call($method, $arguments) {
        if (method_exists($this, $method)) {
            $this->setAuthTokenFromSession();
            $this->refreshAccessTokenIfNeeded();
            $this->setTraceId();

            try {
                return call_user_func_array([$this, $method], $arguments);
            } catch (\Exception $e) {
                throw new HttpException($e->getMessage() .". Method: ". $method .". Arguments: ". json_encode($arguments) .". Trace: ". $e->getTraceAsString(), $e->getCode());
            }
        }
        throw new Exception("Method $method does not exist in ". get_class() ." class");
    }

    /**
     * @return AuthorisedUser|null
     * @throws HttpException
     * @throws NeedLoginException
     */
    protected function getAuthUser() {
        try {
            $response = $this->httpClient->get("me", [
                'headers' => $this->headers,
            ]);
            $this->checkedResponse($response, 'get');

        } catch (HttpException $e) {
            if ($e->getCode() == 404) {
                return null;
            }
            throw $e;
        }

        return AuthorisedUser::fromArray(json_decode($response->getBody(), true));
    }

    /**
     * @param string $endpoint
     * @param string|int $id
     * @param array $query
     * @param array $headers
     * @return mixed
     * @throws HttpException
     * @throws NeedLoginException
     */
    protected function getById($endpoint, $id, array $query = [], array $headers = []) {
        try {
            $response = $this->httpClient->get("$endpoint/$id", [
                'query' => $query,
                'headers' => $this->withBasicHeaders($headers),
            ]);

            $this->checkedResponse($response, 'get');

        } catch (HttpException $e) {
            if ($e->getCode() == 404) {
                return null;
            }
            throw $e;
        }

        return $this->parseIds(json_decode($response->getBody(), true));
    }

    /**
     * Create Symfony response from Guzzle response.
     * Use for following requests to Data API (where authorization is necessary)
     * @param string $url
     * @param array $headers
     * @return Response
     * @throws HttpException
     */
    protected function getResponse($url, array $headers = []) {
        $response = $this->httpClient->get($url, [
            'headers' => $this->withBasicHeaders($headers),
        ]);
        if ($response->getStatusCode() != 200) {
            throw new HttpException($response->getBody(), $response->getStatusCode());
        }

        return new Response($response->getBody(), $response->getStatusCode(), $response->getHeaders());
    }

    /**
     * @param string $endpoint
     * @param array $query
     * @param array $headers
     * @return mixed
     * @throws \Exception
     */
    protected function get($endpoint, array $query = [], array $headers = []) {
        $page = 0;
        $totalPages = 1;
        $result = [];

        do {
            $page++;
            $pageResult = $this->getPage($endpoint, $page, $query, $headers, $totalPages);
            $result = array_merge($result, $pageResult);

        } while ($page < $totalPages);

        return $result;
    }

    /**
     * @param string $endpoint
     * @param int $page
     * @param array $query
     * @param array $headers
     * @param int|null $totalPages
     * @return mixed
     * @throws \Exception
     */
    protected function getPage($endpoint, $page, array $query = [], array $headers = [], &$totalPages = null) {
        if ($page > 1) {
            $query['page'] = $page;
        }

        $response = $this->httpClient->get($endpoint, [
            'query' => $query,
            'headers' => $this->withBasicHeaders($headers),
        ]);

        try {
            $this->checkedResponse($response, 'get');
        } catch (\Exception $e) {
            if ($response->getStatusCode() == 404) {
                return [];
            } else {
                throw $e;
            }
        }

        $body = json_decode($response->getBody(), true);
        $pages = 1;
        $result = [];

        if (!isset($body['hydra:totalItems']) && isset($body['hydra:member']) && is_array($body['hydra:member'])) {
            $body['hydra:totalItems'] = count($body['hydra:member']);
        }

        if (isset($body['hydra:totalItems']) && $body['hydra:totalItems'] > 0) {
            if (!empty($body['hydra:itemsPerPage'])) {
                $pages = (int)ceil($body['hydra:totalItems'] / $body['hydra:itemsPerPage']);
                if ($pages <= 0) {
                    $pages = 1;
                }
            }
            if (!empty($body['hydra:member'])) {
                foreach ($body['hydra:member'] as $item) {
                    $result[] = $this->parseIds($item);
                }
            }
        }
        if (!is_null($totalPages)) {
            $totalPages = $pages;
        }

        return $result;
    }

    /**
     * @param string $endpoint
     * @param mixed $object
     * @param array $headers
     * @return mixed
     * @throws HttpException
     * @throws NeedLoginException
     */
    protected function post($endpoint, $object, array $headers = []) {
        $response = $this->httpClient->post($endpoint, [
            'headers' => $this->withBasicHeaders($headers),
            'json' => $this->injectIds($object),
        ]);
        $this->checkedResponse($response, 'post');

        return $this->parseIds(json_decode($response->getBody(), true));
    }

    /**
     * @param string $endpoint
     * @param string|int $id
     * @param mixed $object
     * @param array $headers
     * @return mixed
     * @throws HttpException
     * @throws NeedLoginException
     */
    protected function put($endpoint, $id, $object, array $headers = []) {
        $response = $this->httpClient->put("$endpoint/$id", [
            'headers' => $this->withBasicHeaders($headers),
            'json' => $this->injectIds($object),
        ]);
        $this->checkedResponse($response, 'put');

        return $this->parseIds(json_decode($response->getBody(), true));
    }

    /**
     * @param string $endpoint
     * @param string|int $id
     * @param array $headers
     * @return bool
     * @throws HttpException
     * @throws NeedLoginException
     */
    protected function delete($endpoint, $id, array $headers = []) {
        $response = $this->httpClient->delete("$endpoint/$id", [
            'headers' => $this->withBasicHeaders($headers),
        ]);
        $this->checkedResponse($response, 'delete');

        return true;
    }

    /**
     * @param GuzzleResponse|ResponseInterface $response
     * @param string $method
     * @return bool
     * @throws HttpException
     * @throws NeedLoginException
     */
    protected function checkedResponse($response, $method) {
        static $params = [
            'get' => ['statusCode' => 200, 'contentType' => 'application/ld+json'],
            'post' => ['statusCode' => 201, 'contentType' => 'application/ld+json'],
            'put' => ['statusCode' => 200, 'contentType' => 'application/ld+json'],
            'delete' => ['statusCode' => 204],
        ];
        if (!empty($params[$method])) {
            $mParams = $params[$method];
            if (!empty($mParams['statusCode']) && $response->getStatusCode() != $mParams['statusCode']) {
                if ($response->getStatusCode() == 401) {
                    // will be handled in ExceptionListener
                    throw new NeedLoginException("Empty access token");

                } else {
                    throw new HttpException($response->getBody(), $response->getStatusCode());
                }
            }
            if (!empty($mParams['contentType']) && !in_array($mParams['contentType'], $response->getHeader('Content-Type'))) {
                throw new HttpException("Expected {$mParams['contentType']} content type but received ". implode(', ', $response->getHeader('Content-Type')) .". Body of response: ". $response->getBody(), 415);
            }
        }
        return true;
    }

    /**
     * @param array $input
     * @return array
     */
    protected function parseIds($input) {
        $result = array();
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $value = $this->parseIds($value);
            } elseif ($key === '@id') {
                $result['id'] = (int)substr($value, strrpos($value, '/') + 1);
            }
            $result[$key] = $value;
        }
        return $result;
    }

    /**
     * @param mixed $input
     * @return mixed
     */
    protected function injectIds($input) {
        $result = new \stdClass();
        foreach (get_object_vars($input) as $name => $value) {
            if ($name === 'endPoint') {
                continue;
            }
            if (is_object($value)) {
                $value = $this->injectIds($value);
            } elseif (is_array($value)) {
                $newValue = [];
                foreach ($value as $item) {
                    if (is_object($item)) {
                        $item = $this->injectIds($item);
                    }
                    $newValue[] = $item;
                }
                $value = $newValue;
            } elseif ($name === 'id') {
                $result->{'@id'} = property_exists($input, 'endPoint') ? '/'. trim($input->endPoint, '/') .'/'. $value : $value;
            }
            $result->{$name} = $value;
        }
        return $result;
    }

    /**
     * @param array $headers
     * @return array
     */
    protected function withBasicHeaders(array $headers = []) {
        $newHeaders = $this->headers;

        foreach ($headers as $name => $values) {
            if (!empty($newHeaders[$name])) {
                $newHeaders[$name] .= "; $values";
            } else {
                $newHeaders[$name] = $values;
            }
        }
        return $newHeaders;
    }

    /**
     * @throws NeedLoginException
     */
    protected function refreshAccessTokenIfNeeded() {
        if (!$this->oauth2Registry) {
            return;
        }

        if ($this->accessToken && $this->accessToken->hasExpired()) {

            /** @var OAuth2Client $client */
            $client = $this->oauth2Registry->getClient('24s_data_oauth');

            try {
                $this->accessToken = $client->getOAuth2Provider()->getAccessToken('refresh_token', [
                    'refresh_token' => $this->accessToken->getRefreshToken(),
                ]);
            } catch (\Exception $e) {
                // will be handled in ExceptionListener
                throw new NeedLoginException("Empty access token");
            }

            $this->setAccessToken($this->accessToken);

            if ($this->session) {
                $this->session->set('access_token', $this->accessToken);

                $user = $this->session->get('user');
                if ($user && $this->tokenService) {
                    $this->tokenService->applyGuardAuthentication($user);
                }
            }
        } elseif (!$this->accessToken && !$this->jwtToken && $this->session) {
            // will be handled in ExceptionListener
            throw new NeedLoginException("Empty access token");
        }
    }
}