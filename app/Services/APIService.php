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
    public static function execute($progress): array
    {
        $service = new APIService();
        $progress->progressAdvance();

        $boardsId = $service->getAllBoardsId();
        $progress->progressAdvance();

        $privateBoard = $service->isBoardCreated($boardsId);
        $progress->progressAdvance();

        $listDoneIds = $service->getListDoneId($boardsId);
        $progress->progressAdvance();

        if($privateBoard == 0)
            $privateBoard = $service->createBoard();
        $progress->progressAdvance();

        $membersId = $service->getAllMembersId($boardsId);
        $progress->progressAdvance();

        $data = $service->setAllMembersCards($membersId, $boardsId, $privateBoard, $listDoneIds);
        $progress->progressAdvance();

        return ['status'=>'ok'];
    }

    public function setAllMembersCards($membersId, $boardsId, $privateBoardId, $listDoneIds): array
    {
        $method = "GET";
        $data = [];

        foreach ($membersId as $id => $name){
            $response = $this->getAllCardsFromMembers($id);

            $privateListId = $this->isListCreated($privateBoardId, $name);

            if(!empty($response))
                foreach ($response as $item){
                    if($item->idBoard != $privateBoardId){
                        $dados = [
                            "name" => $this->getBoardName($boardsId, $item->idBoard)." - ".$item->name,
                            "desc" => $item->desc,
                            "dueComplete" => $item->dueComplete,
                            "idMembers" => $item->idMembers,
                            "idList" => $item->idList
                        ];
                        $data[] = $dados;
                        $this->createCard($privateListId, $dados, $listDoneIds);
                    }
                }
        }

        return $data;
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
     * @return string
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



    /**
     * @return string
     */
    private function getAllCardsFromMembers($id)
    {
        $method = "GET";
        $data = [];

        $response = json_decode($this->call($method, "members/$id/cards"));

        return $response;
    }

    /**
     * @return array
     */
    private function getListDoneId($boardsId): array
    {
        $method = "GET";
        $data = [];

        foreach ($boardsId as $boardId){
            $response = json_decode($this->call($method, "boards/{$boardId['id']}/lists"));
            foreach ($response as $item){
                if($item->name == 'Done' ||  $item->name == 'Feito'){
                    $data[] = "$item->id";
                }
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
                $privateBoardId = $item["id"];

                return $privateBoardId;
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

    private function createCard($idList, $data, $listDoneIds)
    {
        $api_url = "cards";
        $method = "POST";

        if(in_array($data["idList"], $listDoneIds)){
            return 0;
        }

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
