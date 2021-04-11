<?php

namespace CloudSystem;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Config;
use pocketmine\utils\Utils;

class RefreshAllPlayers extends AsyncTask{
    public $plugin;

    public function __construct(CloudSystem $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun() {
        $sf = new Config($this->plugin->datenbank . 'servers.yml');
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