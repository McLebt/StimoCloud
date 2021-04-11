<?php

namespace Bridge\cloudbridge\tasks;

use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use Bridge\cloudbridge\Main;
use Bridge\cloudbridge\Transfer\TransferAPI;

class Teleport extends Task{
	private $plugin;
	/** @var string */
	public static $creator = "";
	public static $template = "";
	public $counter = 6;

	public function __construct(Main $plugin, string $creator, string $template){
		$this->plugin = $plugin;
		self::$creator = $creator;
		self::$template = $template;
	}

	public function onRun(int $currentTick){
		$name = self::$creator;
		$player = Server::getInstance()->getPlayerExact($name);
		$TargetTransfer = self::$template."-".self::$creator;
		$server = $TargetTransfer;
		$this->counter = $this->counter - 1;
		switch ($this->counter) {
			case 6:
				if ($player !== null) {
					$player->addTitle("§a§lStarting", "§e●§7●●");
				}
				break;
			case 5:
				if ($player !== null) {
					$player->addTitle("§a§lStarting", "§7●§e●§7●");
				}
				break;
			case 4:
				if ($player !== null) {
					$player->addTitle("§a§lStarting", "§7●●§e●");
				}
				break;
			case 3:
				if ($player !== null) {
					$player->addTitle("§a§lStarting", "§e●§7●●");
				}
				break;
			case 2:
				if ($player !== null) {
					$player->addTitle("§a§lStarting", "§7●§e●§7●");
				}
				break;
			case 1:
				if ($player !== null) {
					//$player->sendMessage("");
				}
				break;
			case 0:
				if ($player !== null) {
					$player->sendMessage(Main::PREFIX . "§aYou will be now transferred§7!\n§eServer§7:§a {$TargetTransfer}");
					TransferAPI::transfer($player, $TargetTransfer);
				}
				$this->counter = 6;
				Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
				break;
		}
	}
}