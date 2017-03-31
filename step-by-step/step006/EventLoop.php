<?php

namespace Minbaby\Loop;


// 未处理重复注册！！！。
use Minbaby\Monitor\MemoryMonitor;

class EventLoop implements Loop
{

    private $running = false;

    /**
     * @var \SplObjectStorage
     */
    private $events;

    /**
     * @var \EventBase
     */
    private $eventBase;

    /**
     * EventLoop constructor.
     *
     * @param \EventConfig|null $cfg
     */
    public function __construct(\EventConfig $cfg = null)
    {
        $this->eventBase = new \EventBase($cfg);

        $this->events = new \SplObjectStorage();

        MemoryMonitor::init();
    }

    /**
     * 注意！！！ 如果事件循环中没有事件处理会导致。。。空转 @TODO
     *
     * run
     * @return void
     */
    public function run()
    {
        $this->running = true;

        $this->addTimer(1, function () {
            logInfo(MemoryMonitor::prettyPrint(true));
        }, true);

        while ($this->running) {
            $this->eventBase->loop(\EventBase::LOOP_ONCE);
        }
    }

    public function stop()
    {
        $this->running = false;
    }

    public function addTimer(float $interval, callable $callback, bool $isPeriodic = false): \Event
    {
        $flag = \Event::TIMEOUT;
        if ($isPeriodic) {
            $flag |= \Event::PERSIST;
        }

        $event = new \Event($this->eventBase, -1, $flag, $callback);
        $event->add($interval);

        // 如果不加上这行的话，会导致 cpu空转100%
        // 猜测原因是。。。变量作用域原因
        // 调用函数之后，$event 变量被 gc 掉。
        // 但是在事件循环中还需要这个变量，但是这个事件已经被 gc 掉了。
        $this->events->attach($event);
        return $event;
    }

    public function addReadEvent($resourceFd, callable $callback): \Event
    {
        $event = new \Event($this->eventBase, $resourceFd, \Event::READ | \Event::PERSIST, $callback);
        $event->add();
        $this->events->attach($event);
        return $event;
    }

    public function addWriteEvent($resourceFd, callable $callback): \Event
    {
        $event = new \Event($this->eventBase, $resourceFd, \Event::WRITE | \Event::PERSIST, $callback);
        $event->add();
        $this->events->attach($event);
        return $event;
    }

    public function removeEvent(\Event $event)
    {
        $this->events->detach($event);
        $event->free();
    }

    public function addSignalEvent($sig, callable $callback): \Event
    {
        $event = \Event::signal($this->eventBase, $sig, $callback);
        $event->add();
        $this->events->attach($event);
        return $event;
    }
}
