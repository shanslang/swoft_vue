<?php declare(strict_types=1);

namespace App\WebSocket;

use App\WebSocket\Gossip\HomeController;
use Swoft\Http\Message\Request;
use Swoft\WebSocket\Server\Annotation\Mapping\OnOpen;
use Swoft\WebSocket\Server\Annotation\Mapping\OnMessage;
use Swoft\WebSocket\Server\Annotation\Mapping\WsModule;
use Swoft\WebSocket\Server\MessageParser\TokenTextParser;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

/**
 * Class GossipModule
 *
 * @WsModule(
 *     "/gossip",
 *     messageParser=TokenTextParser::class,
 *     controllers={HomeController::class}
 * )
 */
class GossipModule
{
    /**
     * @OnOpen()
     * @param Request $request
     * @param int     $fd
     */
    public function onOpen(Request $request, int $fd): void
    {
        server()->push($request->getFd(), "Opened, welcome!(FD: $fd)");
    }
  
    /**
     * @OnMessage()
     * @param Server $server
     * @param Frame $frame
     */
    public function onMessage(Server $server, Frame $frame)
    {
        $fd = $frame->fd;
        $data = json_decode($frame->data, true);
        if ($data['type'] == 'bind') {
            // 将uid与fd绑定
            $server->bind($fd, $data['sendUid']);
        }
        $start_fd = 0;
        while(true)
        {
            // 获取所有fd连接
            $conn_list = $server->getClientList($start_fd, 10);
            if($conn_list == false || count($conn_list) === 0)
            {
                break;
            }

            $start_fd = end($conn_list); // 获取数组的最后一个元素的值
            foreach($conn_list as $v)
            {
                $connection = $server->connection_info($v); // 根据fd获得UID
                if (isset($connection['uid']) && in_array($connection['uid'], [$data['receiveUid'], $data['sendUid']]))
                {
                    if (isset($data['content']))
                    {
                        $response = [
                            'type' => 'response',
                            'content' => $data['content'],
                            'uid' => $data['receiveUid'],
                        ];
                        if ($v != $fd) { // 避免重复发送给消息发起者的fd
                            $server->push($v, json_encode($response, JSON_UNESCAPED_UNICODE));
                        }
                    }
                }
            }
        }
        // 推送消息给客户端
        \Swoft::server()->sendTo($fd, $frame->data);
        //\bean("server")->sendTo($fd, $frame->data);
    }
}