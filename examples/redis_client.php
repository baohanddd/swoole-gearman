<?php
$gmc = new Redis();
$gmc->connect('redis', '6379');
$gmc->auth('futureLinkDev:Redis2021');


$data = [
    'name' => 'timestamp::print',
    'data' => [
        'message' => ''
    ]
];

for ($i = 0; $i < 102400; $i++) {
    $data['data']['message'] = 'msg#'.($i + 1);
    $gmc->lPush('jobs', json_encode($data));
}