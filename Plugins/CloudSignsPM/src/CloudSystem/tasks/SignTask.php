<?php

namespace CloudSystem\tasks;

use CloudSystem\CloudSystem;
use CloudSystem\groupSigns\GroupSigns;
use mysql_xdevapi\Exception;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\Task;
use pocketmine\tile\Sign;
use pocketmine\utils\Config;
use pocketmine\utils\MainLogger;

class SignTask extends Task{
    public $plugin;
    private $file;

    public function __construct(Plugin $owner) {
        $this->plugin = $owner;
    }

    public function onRun(int $currentTick) {
        $gs = new GroupSigns();
        $gs::loadGroupSigns();
    }
}
