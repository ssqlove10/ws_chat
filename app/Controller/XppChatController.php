<?php


namespace App\Controller;


use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Server;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;
class XppChatController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    public function onMessage(WebSocketServer $server, Frame $frame): void
    {
        //todo 验证FD是否还在线
        $chatUid = 2;
//        $bindFdToUidKey = "ws:Bind:fd[{$frame->}]-To-Uid";
        $redis = di()->get(\Redis::class);
        $redis->select(6);
        $getChatFdKey = "ws:Bind:uid[{$chatUid}]-To-Fds";
        $toChatFd = $redis->hGet($getChatFdKey,"CHAT-FD");
        if ($toChatFd) {
            $server->push($toChatFd,$frame->data);
        }else {
            $server->push($frame->fd,"不在线");
        }
        //todo 在线直接发送消息

        //todo 不在线直接把数据写入缓存，定时写入数据库
//        $server->push($frame->fd, 'Recv: ' . $frame->data);
//        if (!empty($frame->data)) {
//            $data = json_decode($frame->data,true);
//            var_dump($data);
//            if (isset($data['sendToFd'])) {
//                $server->push($data['sendToFd'],"MSG:".$data['msg']);
//            }
//        }
//
//        var_dump($frame->data);
        //todo 根据用户查看用户是否在线
        //todo 在线直接发送对应fd
        //todo 保存消息 ,如果用户在线标记已读，不在线标记未读
    }

    public function onClose(Server $server, int $fd, int $reactorId): void
    {
        var_dump('closed');
        //todo unbind user to fd 解绑用户和通道
        $redis = di()->get(\Redis::class);
        $redis->select(6);
        $bindFdToUidKey = "ws:Bind:fd[{$fd}]-To-Uid";
        $uid = $redis->get($bindFdToUidKey);
        if (!empty($uid)) {
            $redis->hDel("ws:Bind:uid[{$uid}]-To-Fds","CHAT-FD");
        }
    }


    public function onOpen(WebSocketServer $server, Request $request): void
    {
        var_dump($request);
        $server->push($request->fd, 'Opened');
        $token = $request->header['sec-websocket-protocol'];
        //检查登录情况 todo checkToken
        $token = false;

        if (empty($token) || $token !== 'token') {
            $token = true;
        }

        $redis = di()->get(\Redis::class);
        $redis->select(5);
        $tokenKey = "USERINFO:".$token;
        $uid = $redis->hGet($tokenKey,"id");

        if (empty($uid)) {
            $token = true;
        }
        if ($token) {
            $server->push($request->fd, '关闭连接');
            $server->close($request->fd);
        }
//        //todo 检查token对应的用户
//        //todo 获取用户token对应的id
//        //todo bind user to fd 绑定用户和通道
//        //获取到的uid跟当前情景的fd做绑定
        $redis2 = di()->get(\Redis::class);
        $redis2->select(6);
        $bindUidToFds = "ws:Bind:uid[{$uid}]-To-Fds";
        $redis2->hMSet($bindUidToFds,['TOKEN' => $token,'CHAT-FD' =>$request->fd]);
//        //查找对应用户未被读的消息，并且获取发送。
        $server->push($request->fd,"哈哈哈哈，沙雕。");
//
        $pathInfo = $request->server['path_info'];
        $sendTouid = intval(explode('/',$pathInfo)[1]);
        if (empty($uid)) {
            $server->push($request->fd, '不存在UID');
            $server->close($request->fd);
        }
        $bindFdToUidKey = "ws:Bind:fd[{$request->fd}]-SendTo-Uid";
        $redis->set($bindFdToUidKey,$sendTouid);
        //todo

    }
}