<?php
/**
 * Created by IntelliJ IDEA.
 * User: smorales
 * Date: 10/04/18
 * Time: 20:36
 */

namespace App\Services\ApiService\Api\Cmd;

use App\Services\ApiService\Api\VO\Card;

class GetCards extends AbstractHubCommand
{
	public function __construct()
	{
		$this->method = 'GET';
		$this->path = '/cards';
	}

	public function execute($data = null)
	{
		parent::execute($data);

		if($this->response->isSuccess())
		{
			$rawCards = $this->getData();

			if(!$rawCards) $rawCards = [];
			$cards = [];
			foreach($rawCards as $cardData)
				$cards[] = Card::create($cardData);

			$this->response->setData($cards);
		}
	}
}
