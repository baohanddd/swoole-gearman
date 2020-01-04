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
     * Context constructor.
     * @param Collection $payload
     * @throws ContextException
     */
    public function __construct(Collection $payload)
    {
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
}