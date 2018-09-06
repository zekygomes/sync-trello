<?php
/**
 * Created by IntelliJ IDEA.
 * User: smorales
 * Date: 09/03/18
 * Time: 08:14
 */

namespace App\Services\ApiService\Api;


use App\Services\ApiService\Api\Cmd\AbstractHubCommand;
use Illuminate\Support\Facades\Session;

class HubResponse
{
	private $error;
	private $data;
	public $requestData;
	public $responseData;
	public $statusCode;
	public $hubPayload;

	/** @var  AbstractHubCommand */
	public $command;


	/**
	 * @param callable      $dataFilter     Callable function which modifies data before it's assigned to the response object.
	 * @param integer       $statusCode     The status code.
	 * @param array|object  $data           The response data.
	 * @param array|null    $requestData    The request payload.
	 *
	 * @return HubResponse
	 */
	public static function create($dataFilter, $statusCode, $data, array $requestData = null)
	{
		$response = new HubResponse();
		$response->requestData = $requestData;
		$response->responseData = $data;
		$response->statusCode = $statusCode;

		if(property_exists($data, 'error') && $data->error)
			$response->error = $data->error;

		if(property_exists($data, 'data') && $data->data)
			$response->data = $dataFilter ? $dataFilter($data->data) : $data->data;

		if(config('app.debug') === true && property_exists($data, 'requestPayload') && $data->requestPayload)
			$response->hubPayload = $data->requestPayload;

		// redirect to homepage if session is invalid
		if(!$response->isSuccess() && strtolower($response->firstErrorMessage()) == "invalid token")
		{
			$request = request();
		    $request->session()->flush();
			$request->session()->regenerate();
            abort(200, '', ['Location' => '/']);
        }

		return $response;
	}

	public function hasErrors()
	{
		return $this->error != null;
	}

	public function isSuccess()
	{
		return $this->data != null || ($this->statusCode >= 200 && $this->statusCode < 300);
	}

	public function firstErrorMessage()
	{
		if($this->error)
		{
			try
			{
				$msgObject = json_decode($this->error[0]->message);

				if(is_array($msgObject))
					$msgObject = $msgObject[0];

				if(is_object($msgObject) && property_exists($msgObject, 'description'))
					return $msgObject->description;

				if(is_object($msgObject) && property_exists($msgObject, 'error'))
					return $msgObject->error;
			}
			catch(\Exception $e)
			{

			}

			if(is_object($this->error) && property_exists($this->error, 'message'))
				return $this->error->message;

			if(is_string($this->error))
				return $this->error;

			if(is_array($this->error))
				return $this->error['message'];

			return $this->error[0]->message;
		}

		return null;
	}

	public function getData()
	{
		return $this->data;
	}

	public function setData($data)
	{
		$this->data = $data;
	}

	public function getJsonData()
	{
		return json_encode($this->data);
	}

	public function getError()
	{
		return $this->error;
	}

	public function getJsonErrors()
	{
		return json_encode($this->error);
	}

	public function getJsonRequestAndResponseData()
	{
		$data = [
			'request' => $this->requestData,
			'errors' => $this->error,
			'response' => $this->data,
		];

		if($this->hasErrors())
			$data['errorMessage'] = $this->firstErrorMessage();

		return json_encode($data);
	}

	public function __toString()
	{
		if($this->hasErrors())
			return json_encode($this->getError());

		if($this->isSuccess())
			return $this->getJsonData();

		return json_encode([]);
	}
}
