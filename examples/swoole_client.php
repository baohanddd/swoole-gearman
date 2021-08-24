<?php
use Swoole\Client;

$client = new Client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 9500, 0.5))
{
    echo "connect failed. Error: {$client->errCode}\n";
}
$data = [
    'name' => 'timestamp::print',
    'data' => [
        'message' => ''
    ]
];
for ($i = 0; $i < 1024; $i++) {
    $data['data']['message'] = 'query on '.$i;
    $client->send(json_encode($data));
    echo $client->recv();
}
$client->close();