<?php

namespace Bridge\cloudbridge\Transfer;

use pocketmine\network\mcpe\protocol\ScriptCustomEventPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Binary;
use pocketmine\utils\MainLogger;

class TransferAPI
{

    public static function transfer(Player $player, String $server): bool
    {
        $pk = new ScriptCustomEventPacket();
        $pk->eventName = "bungeecord:main";
        $pk->eventData = Binary::writeShort(strlen("Connect")) . "Connect" . Binary::writeShort(strlen($server)) . $server;
        $player->sendDataPacket($pk);
        return true;
    }

    public static function sendMessage(String $player, String $message)
    {
        $sender = Server::getInstance()->getOnlinePlayers()[array_rand(Server::getInstance()->getOnlinePlayers())];
        if ($sender != null && $sender instanceof Player) {
            $pk = new ScriptCustomEventPacket();
            $pk->eventName = "bungeecord:main";
            $pk->eventData = Binary::writeShort(strlen("Message")) . "Message" . Binary::writeShort(strlen($player)) . $player . Binary::writeShort(strlen($message)) . $message;
            $sender->sendDataPacket($pk);
            return true;
        } else {
            MainLogger::getLogger()->warning("You cannot send a message to a player when no player is online on this server!");
            return false;
        }
    }

    public static function transferOther(String $player, String $server): bool
    {
        $sender = Server::getInstance()->getOnlinePlayers()[array_rand(Server::getInstance()->getOnlinePlayers())];
        if ($sender != null && $sender instanceof Player) {
            $pk = new ScriptCustomEventPacket();
            $pk->eventName = "bungeecord:main";
            $pk->eventData = Binary::writeShort(strlen("ConnectOther")) . "ConnectOther" . Binary::writeShort(strlen($player)) . $player . Binary::writeShort(strlen($server)) . $server;
            $sender->sendDataPacket($pk);
            return true;
        } else {
            MainLogger::getLogger()->warning("You cannot transfer a player when no player is online on this server!");
            return false;
        }
    }
}