<?php
/* Copyright (c) 2020 Florian H. All rights reserved. */
namespace Bridge\cloudbridge\commands;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\defaults\VanillaCommand;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use Bridge\cloudbridge\Main;
use Bridge\cloudbridge\packets\StartServerPacket;
use Bridge\cloudbridge\packets\StopServerGroupPacket;
use Bridge\cloudbridge\ServerManager;
use Bridge\cloudbridge\SocketShit;


/**
 * Class StartServerCommand
 * @package Bridge\cloudbridge\commands
 * @author Florian H./xxAROX
 * @date 02.08.2020 - 19:06
 * @project CloudServer
 */
class StopServerGroupCommand extends Command{

	private $main;

	public function __construct(Main $main){
		parent::__construct("stopgroup");
		$this->setDescription("StopGroup Command");
		$this->main = $main;
	}

	/**
	 * Function execute
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return mixed|void
	 * @throws \Exception
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) {
            return;
        }
        if (!$sender->hasPermission("cloudbridge.stopgroup")) {
            return;
        }

        if (!isset($args[0])) {
            $sender->sendMessage($this->getUsage());
            return;
        }

        if (Main::getInstance()->isTemplate($args[0])) {
            $packet = new StopServerGroupPacket();
            $packet->template = $args[0];
            SocketShit::sendPacket($packet);
            var_dump($packet);
            $sender->sendMessage(Main::PREFIX . "§cYou have stopped the §eServer§8-§eGroup§a of the §eTemplate {$args[0]}§7!");
        } else {
            $sender->sendMessage(Main::PREFIX . "§cThis §eTemplate§c don't exists§7!");
        }
    }

}
