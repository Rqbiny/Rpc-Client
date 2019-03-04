<?php
namespace App\Http\Controllers;

use App\Library\Tools\Socket;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function client(Request $request)
    {
       // return ['code' => 20000, 'data' => ['name' => 'admin', 'roles' => ['admin']]];
       //接收参数
       $param = [
         'flag' => 1,
         'phone' => '13462344969',
         'name' => 'name',
         'code' => '2121'
       ];
       //调用Service
        $data = Socket::SocketToSendMessage($param, 'send', 'login');
        return response()->json($data);
    }
}
