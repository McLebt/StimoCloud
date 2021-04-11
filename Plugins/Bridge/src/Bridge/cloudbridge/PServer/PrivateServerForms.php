<?php

namespace Bridge\cloudbridge\PServer;

use FormAPI\FormAPI;
use pocketmine\Player;
use Bridge\cloudbridge\tasks\Teleport;
use Bridge\cloudbridge\Main;
use Bridge\cloudbridge\packets\StartPrivateServerPacket;
use Bridge\cloudbridge\SocketShit;

class PrivateServerForms {

	public static function createPServerPacket(Player $sender, string $template, string $owner)
	{
		if (Main::getInstance()->isTemplate($template)) {
		    if (!Main::getInstance()->isServer($template."-".$owner)) {
                $packet = new StartPrivateServerPacket();
                $packet->owner = $owner;
                $packet->template = $template;
                SocketShit::sendDelayPacket($packet, 1);
                $sender->sendMessage(Main::PREFIX . "§aYou have your server from the server group {$packet->template} §aStarted§7!");
                Main::getInstance()->getScheduler()->scheduleRepeatingTask(new Teleport(Main::getInstance(), $packet->owner, $packet->template), 20);
            } else {
                $sender->sendMessage(Main::PREFIX . "§cYou can't create a server of this Group§7!\n§cPlease wait until your server is stopped§7!");
            }
		} else {
			$sender->sendMessage(Main::PREFIX . "§cThis §eSerer§8-§eTemplate§c don't exists§7!");
		}
	}

   public static function bwTemplates(Player $player){
	   $api = FormAPI::getInstance();
	   $form = $api->createSimpleForm(function (Player $player, int $data = null){
		   $result = $data;
		   if ($result === null) {
			   return;
		   }
		   if ($result === 0) {
			   $template = "P-BW2x1";
			   self::createPServerPacket($player, $template, $player->getName());
		   }
	   });
	   $form->setTitle("§l§cPrivateServer§r §8| §l§4BETA");
	   $form->setContent("§aCreate your §ePrivate§7-§eServer§7!");
	   $form->addButton("§l§cBedWars§8-§c2x1");
	   $form->addButton("§4Soon");
	   $form->sendToPlayer($player);
   }

    public static function pserverForm(Player $player)
    {
        $api = FormAPI::getInstance();
        $form = $api->createSimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            if ($result === null) {
                return;
            }

            if ($result === 0){
                self::bwTemplates($player);
            }

        });
        $form->setTitle("§l§cPrivateServer§r §8| §l§4BETA");
        $form->setContent("§aSelect your §ePrivate§7-§eServer §cTemplate§7!");
        $form->addButton("§l§cBedWars");
        $form->addButton("§l§bMLGRush");
        $form->sendToPlayer($player);
    }
}
