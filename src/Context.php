<?php
namespace baohan\SwooleGearman;

use baohan\SwooleGearman\Exception\ContextException;

class Context
{
    /**
     * @var string
     */
    public $name = "";

    /**
     * @var Collection
     */
    public $data;

    /**
     * @param string|null $json
     * @throws ContextException
     */
    public function __construct(?string $json)
    {
        $payload = $this->getPayload($json);
        if (!$this->validate($payload)) {
            throw new ContextException('Invalid context', 421, $payload->all());
        }
        $this->name = $payload->name;
        $this->data = new Collection($payload->data);
    }

    /**
     * @param array $context
     * @return bool
     */
    public function validate(Collection $context)
    {
        if (!isset($context['name']))     return false;
        if (!isset($context['data']))     return false;
        if (!is_array($context['data']))  return false;
        if (!is_string($context['name'])) return false;

        return true;
    }
    
    /**
     * @param string $json
     * @return Collection
     * @throws ContextException
     */
    public function getPayload(string $json): Collection
    {
        $payload = json_decode($json, true);
        if (json_last_error()) {
            throw new ContextException(json_last_error_msg(), 420, [$json]);
        }
        return new Collection($payload);
    }
}