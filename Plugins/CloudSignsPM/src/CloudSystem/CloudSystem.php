<?php

namespace CloudSystem;

use CloudSystem\groupSigns\GroupSigns;
use CloudSystem\tasks\SignTask;
use mysql_xdevapi\Exception;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\ScriptCustomEventPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Binary;
use pocketmine\utils\Config;

class CloudSystem extends PluginBase {
    public $prefix = "§bCloud§8 | §r";

    public $file = "/root/Server/CloudDatenbank/temp/";
    //public $file;
    public $group_file;

    public $datenbank = "/root/Server/CloudDatenbank/";

    public $server;
    public $who = "";

    public $create = [];

    public $mode = 0;

    public $invisible = [];

    public $delay = [];

    public $groups;

    private static $instance;

    public function onEnable() {
        self::$instance = $this;
        @mkdir($this->getDataFolder());
        @mkdir($this->file);
        $this->group_file = "/root/Server/CloudDatenbank/group_signs.json";
        if (!file_exists($this->group_file)) {
            $this->initGroupJson();
        }

        $this->getServer()->getPluginManager()->registerEvents(new CloudSystemListener($this), $this);

        $this->getScheduler()->scheduleRepeatingTask(new SignTask($this), 20);

        $level = $this->getServer()->getDefaultLevel();
        $level->setDifficulty(0);
        GroupSigns::loadGroupSigns();
    }

    public function initGroupJson() {
        $cfg = new Config($this->group_file, Config::JSON);
        $cfg->setAll([]);
        $cfg->save();
    }

    public function onDisable() {
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if ($command == "regsign") {
            if ($sender instanceof Player) {
                if ($sender->hasPermission("cloud.system")) {
                    if (empty($args[0])) {
                        $sender->sendMessage($this->prefix . "/regsign -server-");
                        return false;
                    }

                    $this->server = $args[0];
                    $this->who = $sender->getName();
                    $this->mode = 1;

                    $sender->sendMessage($this->prefix . "Hit now an sign.");

                } else {
                    $sender->sendMessage($this->prefix . "§cYou don't have the permission to execute this command§7!");
                }
                return true;
            }
        } elseif ($command == "reggroupsign") {
            if ($sender->hasPermission("cloud.system")) {
                if (isset($args[0])) {
                    if (GroupSigns::isGroup($args[0])) {
                        $this->create[$sender->getName()] = $args[0];
                        $sender->sendMessage("Hit a Sign");
                    } else {
                        $sender->sendMessage($this->prefix . $args[0] . " is not a registered group");
                    }
                } else {
                    $sender->sendMessage($this->prefix . "/reggroupsign <group>");
                }
            } else {
                $sender->sendMessage($this->prefix . "§cYou don't have the permission to execute this command§7!");
            }
        }
        return false;
    }

    public function getConfigFile(string $server): ?Config
    {
        if (file_exists($this->file.$server.".json")) {
            try {
                return new Config($this->file.$server.".json", Config::JSON);
            } catch (Exception $exception){
                return null;
            }

        }
        return null;
    }

    public static function transfer(Player $player, String $server): bool
    {
        $pk = new ScriptCustomEventPacket();
        $pk->eventName = "bungeecord:main";
        $pk->eventData = Binary::writeShort(strlen("Connect")) . "Connect" . Binary::writeShort(strlen($server)) . $server;
        $player->sendDataPacket($pk);
        return true;
    }

    /**
     * @return mixed
     */
    public static function getInstance() : CloudSystem
    {
        return self::$instance;
    }
}

