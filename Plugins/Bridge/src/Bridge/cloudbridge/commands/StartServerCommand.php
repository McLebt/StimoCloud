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
use Bridge\cloudbridge\ServerManager;
use Bridge\cloudbridge\SocketShit;


/**
 * Class StartServerCommand
 * @package Bridge\cloudbridge\commands
 * @author Florian H./xxAROX
 * @date 02.08.2020 - 19:06
 * @project CloudServer
 */
class StartServerCommand extends Command{

	private $main;

	public function __construct(Main $main){
		parent::__construct("startserver");
		$this->setDescription("StartServer Command");
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
        if (!$sender->hasPermission("cloudbridge.command.startserver")) {
            $sender->sendMessage("§cYou don't have the Permissions to use this command!");
            return;
        }

        if (!isset($args[0])) {
            $sender->sendMessage($this->getUsage());
            return;
        }

        if (!isset($args[1])) {
            $sender->sendMessage($this->getUsage());
            return;
        }

        if (is_numeric($args[0])) {
            $sender->sendMessage($this->getUsage());
            return;
        }

        if (!is_numeric($args[1])) {
            $sender->sendMessage($this->getUsage());
            return;
        }

        $count = $args[1] ?? 1;

        if (!is_numeric($count)) {
            $count = 1;
        }
        if ($count > 8) {
            $count = 8;
        }
        if ($count < 1) {
            $count = 1;
        }

        if (Main::getInstance()->isTemplate($args[0])) {
            $packet = new StartServerPacket();
            $packet->template = $args[0];
            $packet->count = $count;
            SocketShit::sendPacket($packet);
            $sender->sendMessage(Main::PREFIX . "§aYou have §e{$count} §eServer §aof the §eTemplate {$args[0]} §astarted§7!");
        } else {
            $sender->sendMessage(Main::PREFIX . "§cThis §eSerer§8-§eTemplate§c don't exists§7!");
        }
    }

}
