<?php
$gmc = new GearmanClient();
$gmc->addServer('gearman', '4730');
$data = [
    'name' => 'timestamp::print',
    'data' => [
        'message' => ''
    ]
];
for ($i = 0; $i < 1024; $i++) {
    $data['data']['message'] = $i + 1;
    $gmc->doBackground('timestamp::print', json_encode($data));
    if ($gmc->returnCode() != GEARMAN_SUCCESS) {
        echo "bad return code".PHP_EOL;
        exit;
    }
}