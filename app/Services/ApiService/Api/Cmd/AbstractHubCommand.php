<?php
/**
 * Created by IntelliJ IDEA.
 * User: smorales
 * Date: 09/03/18
 * Time: 08:32
 */

namespace App\Services\ApiService\Api\Cmd;

use App\Services\ApiService\Api\HubResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Session;

abstract class AbstractHubCommand
{
	/**
	 * @var Client
	 */
	protected $httpClient;

	/** @var string */
	protected $path = "";

	/** @var string */
	protected $method = 'POST';

	/** @var array */
	protected $headers = [];

	/** @var  HubResponse */
	public $response;

	public function __construct()
	{
		$this->httpClient = new Client();
	}

	public function execute($data = null)
	{
		$this->call($data);
	}

	/**
	 * @return bool
	 */
	public function hasErrors()
	{
		return $this->response && $this->response->hasErrors();
	}

	/**
	 * @return bool
	 */
	public function failed()
	{
		return $this->hasErrors() && !$this->response->isSuccess();
	}

	/**
	 * @return bool
	 */
	public function succeeded()
	{
		return !$this->hasErrors() && $this->response->isSuccess();
	}

	public function getData()
	{
		if($this->response->isSuccess())
			return $this->response->getData();
		return null;
	}

	public function getErrorData()
	{
		if($this->hasErrors())
			return $this->response->getError();
		return null;
	}

	public function getFirstErrorMessage()
	{
		if($this->hasErrors())
			return $this->response->firstErrorMessage();
		return null;
	}

	protected function call($data = null)
	{
		$httpClient = new Client();

		$path = ltrim($this->path, '/');
		$url = config('app.hub_service_url_base').$path;

		$requestData = [
			'headers' => $this->headers(),
			'body'    => $data ? json_encode($data) : ""
		];

		try
		{
			$clientResponse = $httpClient->request($this->method, $url, $requestData);
			$code = $clientResponse->getStatusCode();
			$response = json_decode($clientResponse->getBody());
		}
		catch (BadResponseException $e)
		{
			$code = $e->getCode();
			$response = $this->getExceptionData($e);
		}

		$this->response = HubResponse::create([$this, 'dataFilter'], $code, $response, $requestData);
		$this->response->command = $this;
	}

	public function dataFilter($data)
	{
		return $data;
	}

	private function getExceptionData(BadResponseException $e)
	{
		if($e->hasResponse())
		{
			$r = json_decode($e->getResponse()->getBody());

			return (object)[
				'error' => [
					'message' => property_exists($r, 'message') ? $r->message : ''
				],
				'requestPayload' => property_exists($r, 'data') && $r->data && property_exists($r->data, 'requestPayload') ? $r->data->requestPayload : ''
			];
		}

		return (object)[
			'error' => [
				'data' => (object)['error'=>$e->getMessage()]
			],
			'requestPayload' => property_exists($e, 'data') && $e->data && property_exists($e->data, 'requestPayload') ? $e->data->requestPayload : ''
		];
	}

	private function headers()
	{
		$headers = array_merge($this->headers, [
			'Content-Type' => 'application/json',
			'Accept'       => 'application/json',
			'HUB-KEY'      => config('app.hub_key'),
			'HUB-SECRET'   => config('app.hub_secret'),
		]);

		if(Session::has('user'))
			$headers['Authorization'] = 'Bearer '.Session::get('user')->access_token;

		return $headers;
	}
}
