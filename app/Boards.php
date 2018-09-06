<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client;

class Boards extends Model
{
    const API = 'https://api.trello.com/1';
    const GET_ALL = '/members/me/boards';

    public function getBoardsID(){

    }
}
