<?php
/* Copyright (c) 2020 Florian H. All rights reserved. */
namespace Bridge\cloudbridge\commands;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\defaults\VanillaCommand;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use Bridge\cloudbridge\Main;
use Bridge\cloudbridge\packets\StartPrivateServerPacket;
use Bridge\cloudbridge\packets\StartServerPacket;
use Bridge\cloudbridge\PServer\PrivateServerForms;
use Bridge\cloudbridge\ServerManager;
use Bridge\cloudbridge\SocketShit;


/**
 * Class StartServerCommand
 * @package Bridge\cloudbridge\commands
 * @author Florian H.
 * @date 02.08.2020 - 19:06
 * @project CloudServer
 */
class CreatePrivateServerCommand extends Command{

	private $main;

	public function __construct(Main $main){
		parent::__construct("createprivateserver", "", "", ["cs", "pserver"]);
		$this->setDescription("PServer Command");
		$this->main = $main;
	}

	/**
	 * Function execute
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return mixed|void
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if (!$sender instanceof Player) {
			return;
		}

		if (!$sender->hasPermission("cloudbridge.command.privateserver")) {
			$sender->sendMessage("§cYou don't have the Permissions to use this command!");
			return;
		}

		if ($sender->hasPermission("cloudbridge.command.privateserver")){
		    PrivateServerForms::pserverForm($sender);
        }

	}

}
