<?php
/**
 * Created by IntelliJ IDEA.
 * User: smorales
 * Date: 10/04/18
 * Time: 21:03
 */

namespace App\Services\ApiService\Api\Cmd;


class StoreCard extends AbstractHubCommand
{
	public function __construct()
	{
		$this->method = 'POST';
		$this->path = '/cards';
	}
}
