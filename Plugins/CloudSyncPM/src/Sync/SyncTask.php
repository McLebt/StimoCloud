<?php

namespace Sync;

use mysql_xdevapi\Exception;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\MainLogger;

class SyncTask extends Task{
    public $plugin;


    public function __construct(Sync $owner) {
        $this->plugin = $owner;
    }

    public function onRun(int $currentTick)
    {
        try {
            $file = new Config($this->plugin->file.$this->plugin->name.".json", Config::JSON);

            $list = [];
            foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
                $list[] = $player->getName();
            }

            if (!Sync::$run) {
                $file->setAll([
                    "count" => count($this->plugin->getServer()->getOnlinePlayers()),
                    "max" => $this->plugin->max,
                    "list" => $list,
                    "port" => $this->plugin->getServer()->getPort(),
                    "ingame" => false,
                    "offline" => false,
					"mypid" => getmypid(),
                ]);
                $file->save();
            } else {
                $file->setAll([
                    "count" => count($this->plugin->getServer()->getOnlinePlayers()),
                    "max" => $this->plugin->max,
                    "list" => $list,
                    "port" => $this->plugin->getServer()->getPort(),
                    "ingame" => true,
                    "offline" => false,
					"mypid" => getmypid()
                ]);
                $file->save();
            }
        }catch (Exception $exception){
            MainLogger::getLogger()->info($this->plugin->prefix . "crashed. Regenerate!");
        }
    }
}
