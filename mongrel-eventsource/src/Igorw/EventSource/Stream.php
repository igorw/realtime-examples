<?php

namespace Igorw\EventSource;

class Stream
{
    private $buffer;
    private $handler;

    public function __construct(\Closure $handler)
    {
        $this->buffer = new \SplQueue();
        $this->buffer->setIteratorMode(\SplQueue::IT_MODE_DELETE);
        $this->handler = $handler;
    }

    public function event()
    {
        $event = new Event();
        $this->buffer->enqueue($event);

        $that = $this;

        $wrapper = new EventWrapper($event, function () use ($that) {
            return $that;
        });

        return $wrapper;
    }

    public function flush()
    {
        foreach ($this->buffer as $event) {
            $chunk = $event->dump();
            if ('' !== $chunk) {
                call_user_func($this->handler, $chunk);
            }
        }
    }

    static public function getHeaders()
    {
        return array(
            'Content-Type'  => 'text/event-stream',
            'Transfer-Encoding' => 'identity',
            'Cache-Control' => 'no-cache',
        );
    }
}
