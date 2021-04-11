<?php

namespace Sync\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\network\mcpe\protocol\ScriptCustomEventPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Binary;
use Sync\Sync;
use Sync\Tasks\RestartServerTask;

class ServerInfoCommand extends Command implements Listener
{

    private $plugin;

    public function __construct(Sync $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct("serverinfo", "serverinfo Command", "/serverinfo");
    }

    public static function transfer(Player $player, String $server): bool
    {
        $pk = new ScriptCustomEventPacket();
        $pk->eventName = "bungeecord:main";
        $pk->eventData = Binary::writeShort(strlen("Connect")) . "Connect" . Binary::writeShort(strlen($server)) . $server;
        $player->sendDataPacket($pk);
        return true;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $sender->sendMessage($this->plugin->prefix . "§7Server name: " . Server::getInstance()->getMotd());
    }

}
