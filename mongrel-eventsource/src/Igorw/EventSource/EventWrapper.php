<?php

namespace Igorw\EventSource;

class EventWrapper
{
    private $event;
    private $source;

    public function __construct(Event $event, \Closure $source = null)
    {
        $this->event = $event;
        $this->source = $source;
    }

    public function getWrappedEvent()
    {
        return $this->event;
    }

    public function end()
    {
        if ($this->source) {
            return call_user_func($this->source);
        }
    }

    public function __call($name, $args)
    {
        $method = array($this->event, $name);
        $value = call_user_func_array($method, $args);

        if ($this->event === $value) {
            return $this;
        }

        return $value;
    }
}
