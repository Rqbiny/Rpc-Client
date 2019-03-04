<?php
namespace App\Library\Tools;

use App\Rpc\thrift\TestServiceClient;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Protocol\TCompactProtocol; // 压缩后的二进制数据序列化的门面
use Thrift\Protocol\TMultiplexedProtocol;
use Thrift\Transport\TBufferedTransport;
use Thrift\Transport\TSocket;

class Socket
{
    // 保存对象实例化
    private $_instance;

    // 保存服务连接池
    private static $client = [];

    // 配置文件
    private $config = [];

    private function __construct($type)
    {
        $config = [
            'sendmessage' => [
                'host' => env('ERP_RPC_HOST'),
                'port' => env('ERP_RPC_PORT')
            ]
        ];
        $this->config = $config[$type];
    }

    /**
     * 连接服务
     * @param string $name  调用的方法名
     * @param array $args   1、参数数组 2、具体哪个方法名  3、所属的 Service 名称
     * @return bool
     */
    public static function __callStatic($name, $args)
    {
        if (substr($name, 0, 8) != 'SocketTo') {
            return false;
        }
        $type = strtolower(substr($name, 8));
        // 实例化操作
        if (!isset(self::$client[$type])) {
            self::$client[$type] = new self($type);
        }
        var_dump($args);
        return self::$client[$type]->invoke($args);
    }

    private function invoke($args)
    {
        try {
            $socket = new TSocket($this->config['host'], $this->config['port']);
            $socket->setRecvTimeout(50000);
            $socket->setDebug(true);
            $transport = new TBufferedTransport($socket, 1024, 1024);
            $protocol = new TCompactProtocol($transport);
            $thriftProtocol = new TMultiplexedProtocol($protocol, 'SendServicceProcessor');
            $client = new TestServiceClient($thriftProtocol);
            $transport->open();
            // 拼装参数与类型
            $data = [
                'params' => $args[0],
                'methodName' => $args[1],
                'serviceName' => $args[2]
            ];
            $result = $client->invokeMethod(json_encode($data));
            $result->data = json_decode($result->data, true);
            $transport->close();
            return $result;
        } catch (\TException $Te) {
            app('log')->error('服务连接失败 ', ['host' => $this->config, 'methodName' => $args[1], 'content' => $Te->getMessage()]);
            return ['host' => $this->config, 'methodName' => $args[1], 'content' => $Te->getMessage()];
        }
    }
}
