<?php

namespace CloudSystem\tasks;

use CloudSystem\CloudSystem;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class HitDelayTask extends Task
{
    public $plugin;

    public $name;

    public function __construct(CloudSystem $plugin, string $name)
    {
        $this->plugin = $plugin;
        $this->name = $name;
    }

    public function onRun(int $currentTick)
    {
        $player = Server::getInstance()->getPlayer($this->name);
        if ($player !== null) {
            $key = array_search($this->name, $this->plugin->delay);
            unset($this->plugin->delay[$key]);
        }
    }
}