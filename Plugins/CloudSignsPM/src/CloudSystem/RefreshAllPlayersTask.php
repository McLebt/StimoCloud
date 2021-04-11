<?php

namespace CloudSystem;

use pocketmine\plugin\Plugin;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;
use pocketmine\utils\Utils;

class RefreshAllPlayersTask extends Task{
    public $plugin;

    public function __construct(Plugin $owner) {
        $this->plugin = $owner;
    }

    public function onRun(int $current) {
        $sf = new Config($this->plugin->getDataFolder().'servers.yml');
        $servers = $sf->get('servers');
        $i = 0;

        foreach ($servers as $server){
            $status = json_decode(Utils::getURL('https://api.mcsrvstat.us/1/PeazyGames.net:'.$server['port']));

            $players = $status->players->online;

            $i = $i + $players;
        }

        $this->plugin->lastc = $i;
    }
}
