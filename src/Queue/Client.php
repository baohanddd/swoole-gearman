<?php
namespace baohan\SwooleGearman\Queue;


class Client
{
    /**
     * @var \GearmanClient
     */
    private $c;

    public function __construct($cfg)
    {
        $this->c = new \GearmanClient();
        $this->c->addServer($cfg['host'], $cfg['port']);
    }

    /**
     * @param string $evtName
     * @param string $payload
     */
    public function fire($evtName, $payload)
    {
        $this->c->doBackground($evtName, json_encode($payload));
    }

    /**
     * @param $evtName
     * @param $data
     * @return string
     */
    public function call($evtName, $data)
    {
        return $this->c->doHigh($evtName, json_encode($data));
    }
}