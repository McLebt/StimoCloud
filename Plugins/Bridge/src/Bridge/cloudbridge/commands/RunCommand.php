<?php
/* Copyright (c) 2020 Florian H. All rights reserved. */
namespace Bridge\cloudbridge\commands;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use Bridge\cloudbridge\Main;


/**
 * Class RunCommand
 * @package Bridge\cloudbridge\commands
 * @author Florian H./xxAROX
 * @date 03.08.2020 - 19:17
 * @project CloudServer
 */
class RunCommand extends Command{
	private $main;

	public function __construct(Main $main){
		parent::__construct("run");
		$this->setDescription("Run Command");
		$this->main = $main;
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if ($sender->hasPermission("cloudbridge.command.run")){
			if (Main::$inGame === false){
				Main::$inGame = true;
				$sender->sendMessage(Main::PREFIX . "§aYou have enabled the §eRunning§8-§eMode§7!");
			} else {
				Main::$inGame = false;
				$sender->sendMessage(Main::PREFIX . "§cYou have disabled the §eRunning§8-§eMode§7!");
			}
		} else {
			$sender->sendMessage("§cYou don't have the Permissions to use this command!");
		}
	}
}
