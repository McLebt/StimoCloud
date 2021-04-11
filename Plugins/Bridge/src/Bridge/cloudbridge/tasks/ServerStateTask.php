<?php
/* Copyright (c) 2020 Florian H. All rights reserved. */
namespace Bridge\cloudbridge\tasks;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\MainLogger;
use Bridge\cloudbridge\Main;


/**
 * Class ServerStateTask
 * @package Bridge\cloudbridge\tasks
 * @author Florian H.
 * @date 03.08.2020 - 19:12
 * @project CloudServer
 */
class ServerStateTask extends Task{

	public function onRun(int $currentTick){
		try {
			$file = new Config(Main::getInstance()->file . Main::getInstance()->name . ".json", Config::JSON);
			$list = [];
			foreach (Main::getInstance()->getServer()->getOnlinePlayers() as $player) {
				$list[] = $player->getName();
			}
			//Update files
			if (!Main::$inGame) {
					$file->setAll([
						"count"   => count(Main::getInstance()->getServer()->getOnlinePlayers()),
						"max"     => Main::getInstance()->max,
						"list"    => $list,
						"port"    => Main::getInstance()->getServer()->getPort(),
						"ingame"  => false,
						"offline" => false
					]);
					$file->save();
			} else {
				$file->setAll([
					"count"   => count(Main::getInstance()->getServer()->getOnlinePlayers()),
					"max"     => Main::getInstance()->max,
					"list"    => $list,
					"port"    => Main::getInstance()->getServer()->getPort(),
					"ingame"  => true,
					"offline" => false
				]);
				$file->save();
			}
		} catch (\Exception $exception) {
			MainLogger::getLogger()->info(Main::PREFIX . "crashed. Regenerate!");
		}
	}
}
