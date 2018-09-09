<?php
/**
 * Created by IntelliJ IDEA.
 * User: zekygomes
 * Date: 07/09/18
 * Time: 15:22
 */

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;


class APIService
{
    public  $BASE_PATH = 'https://api.trello.com/1/';
    private $KEY = "0ad1ca18b89c4b270bac082ea189684c";
    private $TOKEN = "a2ba8f32c92b742627823dca29fffbd52bfc745615984b3972735cead6de8126";

    /**
     * @return string
     */
    public static function execute(): array
    {
        $service = new APIService();

        $boardsId = $service->getAllBoardsId();
        $privateBoard = $service->isBoardCreated($boardsId);

        if($privateBoard == 0)
            $privateBoard = $service->createBoard();


        $membersId = $service->getAllMembersId($boardsId);
        $data = $service->getAllMembersCards($membersId, $boardsId, $privateBoard);


        return ['status'=>'ok'];
    }

    /**
     * @return boolean
     */
    public function createBoard(): string
    {
        $api_url = "boards/";
        $method = "POST";
        $data = [
            'name' => 'Private',
            'defaultLists' => false
        ];

        $response = json_decode($this->call($method, $api_url, $data));

        return $response->id;

    }

    protected function call($method = "POST", $api_url = "", $data = null)
    {
        $httpClient = new Client();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json'
        ];
        $url = $this->BASE_PATH . "$api_url?key=$this->KEY&token=$this->TOKEN";

        $requestData = [
            //'headers' => $headers,
            $data != null ?'form_params':'body'    => $data ? $data : ""
        ];

        try
        {
            $apiResponse = $httpClient->request($method, $url, $requestData);
            $code = $apiResponse->getStatusCode();

            return $apiResponse->getBody()->getContents();

        }
        catch (BadResponseException $e)
        {

            return $e->getMessage();
        }

    }


    /**
     * @return array
     */
    public function getAllBoardsId(): array
    {
        $api_url = "members/me/boards";
        $method = "GET";
        $data = [];

        $response = json_decode($this->call($method, $api_url));

        foreach ($response as $key => $value){
            $data[] = ["name"=>$value->name, "id"=>$value->id];
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getAllMembersId($boardsId): array
    {
        $method = "GET";
        $data = [];

        foreach ($boardsId as $id){
            $response = json_decode($this->call($method, "boards/{$id["id"]}/members"));
            foreach ($response as $item){
                $data[$item->id] = $item->fullName;

            }

        }

        return $data;
    }

    public function getAllMembersCards($membersId, $boardsId, $privateBoardId): array
    {
        $method = "GET";
        $data = [];

        foreach ($membersId as $id => $name){
            $response = json_decode($this->call($method, "members/$id/cards"));

            $privateListId = $this->isListCreated($privateBoardId, $name);

            if(!empty($response))
            foreach ($response as $item){
                $dados = [
                    "name" => $this->getBoardName($boardsId, $item->idBoard)." - ".$item->name,
                    "desc" => $item->desc,
                    "dueComplete" => $item->dueComplete,
                    "idMembers" => $item->idMembers,
                    "idList" => 0
                ];
                $data[] = $dados;
                $this->createCard($privateListId, $dados);
            }
        }

        return $data;
    }

    /**
     * @return string
     */
    private function getBoardName(array $boards, $idSearch): string
    {

        foreach ($boards as $item){
            if($item["id"] == $idSearch){
                return $item["name"];
            }
        }
        return "";
    }

    /**
     * @return string
     */
    private function isBoardCreated(array $boards)
    {
        foreach ($boards as $item){
            if($item["name"] == "Private"){
                return $item["id"];
            }
        }
        return 0;
    }

    /**
     * @return string
     */
    private function isListCreated($privateBoardId, $listName)
    {
        $api_url = "boards/$privateBoardId/lists";
        $method = "GET";

        $lists = json_decode($this->call($method, $api_url));

        foreach ($lists as $item){
            if($item->name == $listName){
                $this->archiveAllCardsFromList($item->id);

                return $item->id;
            }
        }

        if(!in_array($listName, $lists ) || empty($lists)){

            return $this->createList($privateBoardId, $listName);

        }

    }

    /**
     * @return bool
     */
    private function archiveAllCardsFromList($lisId): bool
    {
        $api_url = "lists/$lisId/archiveAllCards";
        $method = "POST";

        $this->call($method, $api_url);

        return true;
    }

    private function createCard($idList, $data)
    {
        $api_url = "cards";
        $method = "POST";
        $data["idList"] = $idList;

        $response = json_decode($this->call($method, $api_url, $data));

        return $response->id;
    }

    private function createList($privateBoardId, $listName)
    {
        $api_url = "lists";
        $method = "POST";
        $data = [
            'name' => $listName,
            'idBoard' => $privateBoardId,
            //'pos' => 'top'||'bottom'
        ];
        $response = json_decode($this->call($method, $api_url, $data));

        return $response->id;
    }
}
