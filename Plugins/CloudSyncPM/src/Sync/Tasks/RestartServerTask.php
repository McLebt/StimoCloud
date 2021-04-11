<?php

namespace Sync\Tasks;

use pocketmine\plugin\Plugin;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\Config;

class RestartServerTask extends Task{

    public $plugin;

    public function __construct(Plugin $owner) {
        $this->plugin = $owner;
    }

    public function onRun(int $currentTick)
    {
        Server::getInstance()->shutdown();
    }
}

