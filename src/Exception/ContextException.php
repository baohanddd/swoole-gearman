<?php
namespace baohan\SwooleGearman\Exception;

class ContextException extends \Exception
{
    /**
     * @var array
     */
    protected $context = [];

    /**
     * ContextException constructor.
     * @param string $message
     * @param int $code
     * @param array $context
     */
    public function __construct($message = "", $code = 0, array $context = [])
    {
        parent::__construct($message, $code);
        $this->context = $context;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }
}