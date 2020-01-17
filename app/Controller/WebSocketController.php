<?php


namespace App\Controller;


use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;
class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    public function onMessage(WebSocketServer $server, Frame $frame): void
    {
        $server->push($frame->fd, 'Recv: ' . $frame->data);
        var_dump($frame->data);
        //todo 根据用户查看用户是否在线
        //todo 在线直接发送对应fd
        //todo 保存消息 ,如果用户在线标记已读，不在线标记未读
    }

    public function onClose(Server $server, int $fd, int $reactorId): void
    {
        var_dump('closed');
        //todo unbind user to fd 解绑用户和通道
    }

    public function onOpen(WebSocketServer $server, Request $request): void
    {
        var_dump($request);
        $server->push($request->fd, 'Opened');
        //todo 获取用户token对应的id
        //todo bind user to fd 绑定用户和通道
        //todo 查找该用户未读的消息

    }
}