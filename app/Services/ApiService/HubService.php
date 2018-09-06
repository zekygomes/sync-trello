<?php
/**
 * Created by IntelliJ IDEA.
 * User: smorales
 * Date: 10/04/18
 * Time: 13:16
 */

namespace App\Services\ApiService;


use App\_\Models\Promotion;
use App\Services\ApiService\Api\Cmd\DeleteCard;
use App\Services\ApiService\Api\Cmd\EnrollPromotion;
use App\Services\ApiService\Api\Cmd\EnrollSubPromotions;
use App\Services\ApiService\Api\Cmd\GetCards;
use App\Services\ApiService\Api\Cmd\GetCoupons;
use App\Services\ApiService\Api\Cmd\GetProfile;
use App\Services\ApiService\Api\Cmd\GetPromotion;
use App\Services\ApiService\Api\Cmd\GetPromotions;
use App\Services\ApiService\Api\Cmd\GetUserPromotions;
use App\Services\ApiService\Api\Cmd\HookDelete;
use App\Services\ApiService\Api\Cmd\HookSignUp;
use App\Services\ApiService\Api\Cmd\IsEnrollAble;
use App\Services\ApiService\Api\Cmd\Login;
use App\Services\ApiService\Api\Cmd\LoginSocial;
use App\Services\ApiService\Api\Cmd\Logout;
use App\Services\ApiService\Api\Cmd\RequestPasswordReset;
use App\Services\ApiService\Api\Cmd\ResetPassword;
use App\Services\ApiService\Api\Cmd\SignUp;
use App\Services\ApiService\Api\Cmd\SignUpSocial;
use App\Services\ApiService\Api\Cmd\StoreCard;
use App\Services\ApiService\Api\Cmd\StorePromotion;
use App\Services\ApiService\Api\Cmd\UpdateCard;
use App\Services\ApiService\Api\Cmd\UpdateProfile;
use App\Services\ApiService\Api\Cmd\UpdatePromotion;

class HubService
{
	/**
	 * @param $userData
	 * @return Api\HubResponse
	 */
	public static function signUp($userData)
	{
		$cmd = new SignUp();
		$cmd->execute($userData);
		return $cmd->response;
	}

	/**
	 * @param $userData
	 * @return Api\HubResponse
	 */
	public static function signUpSocial($userData)
	{
		$cmd = new SignUpSocial();
		$cmd->execute($userData);
		return $cmd->response;
	}

	/**
	 * @param $credentials
	 * @return Api\HubResponse
	 */
	public static function login($credentials)
	{
		$cmd = new Login();
		$cmd->execute($credentials);
		return $cmd->response;
	}

	/**
	 * @param $credentials
	 * @return Api\HubResponse
	 */
	public static function loginSocial($credentials)
	{
		$cmd = new LoginSocial();
		$cmd->execute($credentials);
		return $cmd->response;
	}

	/**
	 * @return Api\HubResponse
	 */
	public static function getUserPromotions()
	{
		$cmd = new GetUserPromotions();
		$cmd->execute();
		return $cmd->response;
	}

	/**
	 * @param $data
	 * @return Api\HubResponse
	 */
	public static function requestPasswordReset($data)
	{
		$cmd = new RequestPasswordReset();
		$cmd->execute($data);
		return $cmd->response;
	}

	/**
	 * @param $data
	 * @return Api\HubResponse
	 */
	public static function resetPassword($data)
	{
		$cmd = new ResetPassword();
		$cmd->execute($data);
		return $cmd->response;
	}

	/**
	 * @return Api\HubResponse
	 */
	public static function logout()
	{
		$cmd = new Logout();
		$cmd->execute();
		return $cmd->response;
	}

	/**
	 * @return Api\HubResponse
	 */
	public static function getProfile()
	{
		$cmd = new GetProfile();
		$cmd->execute();
		return $cmd->response;
	}

	/**
	 * @param $profileData
	 * @return Api\HubResponse
	 */
	public static function updateProfile($profileData)
	{
		$cmd = new UpdateProfile();
		$cmd->execute($profileData);
		return $cmd->response;
	}

	/**
	 * @return Api\HubResponse
	 */
	public static function getCards()
	{
		$cmd = new GetCards();
		$cmd->execute();
		return $cmd->response;
	}

    /**
     * @param int $page
     * @param int $perPage
     * @return Api\HubResponse
     */
    public static function getCoupons($page = 1, $perPage = 10)
    {
        $cmd = new GetCoupons($page, $perPage);
        $cmd->execute();
        return $cmd->response;
    }

	/**
	 * @param $cardData
	 * @return Api\HubResponse
	 */
	public static function storeCard($cardData)
	{
		$cmd = new StoreCard();
		$cmd->execute($cardData);
		return $cmd->response;
	}

	/**
	 * @param $cardData
	 * @return Api\HubResponse
	 */
	public static function updateCard($cardData)
	{
		$cmd = new UpdateCard();
		$cmd->execute($cardData);
		return $cmd->response;
	}

	/**
	 * @param $cardId
	 * @return Api\HubResponse
	 */
	public static function deleteCard($cardId)
	{
		$cmd = new DeleteCard();
		$cmd->execute(['card_id'=>$cardId]);
		return $cmd->response;
	}

	/**
	 * @return Api\HubResponse
	 */
	public static function getPromotions()
	{
		$cmd = new GetPromotions();
		$cmd->execute();
		return $cmd->response;
	}

	/**
	 * @param $clientId
	 * @return Api\HubResponse
	 */
	public static function getPromotion($clientId)
	{
		$cmd = new GetPromotion($clientId);
		$cmd->execute();
		return $cmd->response;
	}

	/**
	 * @param $clientId
	 * @param $data
	 * @return Api\HubResponse
	 */
	public static function updatePromotion($clientId, $data)
	{
		$cmd = new UpdatePromotion($clientId);
		$cmd->execute($data);
		return $cmd->response;
	}

	/**
	 * @param $data
	 * @return Api\HubResponse
	 */
	public static function storePromotion($data)
	{
		$cmd = new StorePromotion();
		$cmd->execute($data);
		return $cmd->response;
	}

	/**
	 * @param Promotion $promotion
	 * @param null|array $optInData. E.x: ['prize_id' => 2]
	 * @return Api\HubResponse
	 */
	public static function enrollPromotion(Promotion $promotion, $optInData = null)
	{
		$cmd = new EnrollPromotion($promotion->client_id, $promotion->secret_id);
		$cmd->execute($optInData);
		return $cmd->response;
	}

	/**
	 * @param Promotion $promotion
	 * @return Api\HubResponse
	 */
	public static function checkIfEnrollAble(Promotion $promotion)
	{
		$cmd = new IsEnrollAble($promotion->client_id, $promotion->secret_id);
		$cmd->execute();
		return $cmd->response;
	}

	/**
	 * @param Promotion $promotion
	 * @return Api\HubResponse
	 */
	public static function signUpHook(Promotion $promotion)
	{
		$cmd = new HookSignUp();
		$cmd->execute([
			'token'            => config('app.hook_token'),
			'secret'           => config('app.hook_secret'),
			'url'              => config('app.hook_url'),
			'promotion_id'     => $promotion->client_id,
		]);
		return $cmd->response;
	}

	/**
	 * @param Promotion $promotion
	 * @return Api\HubResponse
	 */
	public static function deleteHook(Promotion $promotion)
	{
		$cmd = new HookDelete();
		$cmd->execute(['promotion_id'     => $promotion->client_id]);
		return $cmd->response;
	}
}
