<?php

namespace Sync;

use mysql_xdevapi\Exception;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\MainLogger;
use Sync\Commands\RunCommand;
use Sync\Commands\ServerInfoCommand;

class Sync extends PluginBase{
    public $prefix = "§7[§bCloudSync§7] ";

    /** @var bool */
    public static $run;

    public $name;
    public $max;
    public $ing;
    public $file = "/root/Server/CloudDatenbank/temp/";

    public function onEnable()
    {

    	self::$run = false;

        @mkdir($this->getDataFolder());
        @mkdir($this->file);
        $this->intiConfig();
        $f = new Config($this->getDataFolder() . 'config.yml', Config::YAML);
        $this->name = $f->get("name");
        $this->max = $f->get("maxplayers");
        $this->getScheduler()->scheduleRepeatingTask(new SyncTask($this), 1 * 5);
        $this->getLogger()->info($this->prefix . "§aRegistered as " . $this->name);
        $this->registerCommands();
    }

    public function onDisable() {

		self::$run = false;

        try {
            $file = new Config($this->file.$this->name.".json", Config::JSON);
            $file->setAll([
                "offline" => true,
                "ingame" => false,
				"pid" => getmypid()
            ]);
            $file->save();
        }catch (Exception $exception){
            MainLogger::getLogger()->info($this->prefix . "crashed. Regenerate!");
        }
    }

    public function intiConfig(){
        $file = new Config($this->getDataFolder() . 'config.yml', Config::YAML);
        $file->set("name", Server::getInstance()->getMotd());
        $file->set("maxplayers", Server::getInstance()->getMaxPlayers());
		$file->set("offline", false);
        $file->save();
    }

    public function registerCommands() {
        $map = Server::getInstance()->getCommandMap();
        $map->register("serverinfo", new ServerInfoCommand($this));
		$map->register("run", new RunCommand($this));
    }

}