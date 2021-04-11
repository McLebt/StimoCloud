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

class RunCommand extends Command implements Listener
{

	private $plugin;

	public function __construct(Sync $plugin)
	{
		$this->plugin = $plugin;
		parent::__construct("run", "Run Command", "/run");
		$this->setPermission("cloud.admin");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{

		if (!$this->testPermission($sender)){
			return;
		}

		if (Sync::$run){
			$sender->sendMessage($this->plugin->prefix . "§cDisabled §eRunning§c mode§8.");
			Sync::$run = false;
		} else {
			$sender->sendMessage($this->plugin->prefix . "§aEnabled §eRunning§a mode§8.");
			Sync::$run = true;
		}
	}

}