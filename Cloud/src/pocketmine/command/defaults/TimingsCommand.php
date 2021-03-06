<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\scheduler\BulkCurlTask;
use pocketmine\Server;
use pocketmine\timings\TimingsHandler;
use pocketmine\utils\InternetException;
use function count;
use function fclose;
use function file_exists;
use function fopen;
use function fseek;
use function http_build_query;
use function is_array;
use function json_decode;
use function mkdir;
use function stream_get_contents;
use function strtolower;
use const CURLOPT_AUTOREFERER;
use const CURLOPT_FOLLOWLOCATION;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POST;
use const CURLOPT_POSTFIELDS;

class TimingsCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"Handle timings",
			"/timings on | off | paste | reset"
		);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
	    if(count($args) !== 1){
			throw new InvalidCommandSyntaxException();
		}

		$mode = strtolower($args[0]);

		if($mode === "on"){
			TimingsHandler::setEnabled();
			$sender->sendMessage("Timings enabled");

			return true;
		}elseif($mode === "off"){
			TimingsHandler::setEnabled(false);
			$sender->sendMessage("Disabled");
			return true;
		}

		if(!TimingsHandler::isEnabled()){
			$sender->sendMessage("Timings disabled");

			return true;
		}

		$paste = $mode === "paste";

		if($mode === "reset"){
			TimingsHandler::reload();
			$sender->sendMessage("Timings resttet");
		}elseif($mode === "merged" or $mode === "report" or $paste){
			$timings = "";
			if($paste){
				$fileTimings = fopen("php://temp", "r+b");
			}else{
				$index = 0;
				$timingFolder = $sender->getServer()->getDataPath() . "timings/";

				if(!file_exists($timingFolder)){
					mkdir($timingFolder, 0777);
				}
				$timings = $timingFolder . "timings.txt";
				while(file_exists($timings)){
					$timings = $timingFolder . "timings" . (++$index) . ".txt";
				}

				$fileTimings = fopen($timings, "a+b");
			}
			TimingsHandler::printTimings($fileTimings);

			if($paste){
				fseek($fileTimings, 0);
				$data = [
					"browser" => $agent = $sender->getServer()->getName() . " " . $sender->getServer()->getPocketMineVersion(),
					"data" => $content = stream_get_contents($fileTimings)
				];
				fclose($fileTimings);

				$host = "timings.pmmp.io";

				$sender->getServer()->getAsyncPool()->submitTask(new class($sender, $host, $agent, $data) extends BulkCurlTask{
					/** @var string */
					private $host;

					public function __construct(CommandSender $sender, string $host, string $agent, array $data){
						parent::__construct([
							["page" => "https://$host?upload=true", "extraOpts" => [
								CURLOPT_HTTPHEADER => [
									"User-Agent: $agent",
									"Content-Type: application/x-www-form-urlencoded"
								],
								CURLOPT_POST => true,
								CURLOPT_POSTFIELDS => http_build_query($data),
								CURLOPT_AUTOREFERER => false,
								CURLOPT_FOLLOWLOCATION => false
							]]
						], $sender);
						$this->host = $host;
					}

					public function onCompletion(Server $server){
						$sender = $this->fetchLocal();
						$result = $this->getResult()[0];
						if($result instanceof InternetException){
							$server->getLogger()->logException($result);
							return;
						}
						if(isset($result[0]) && is_array($response = json_decode($result[0], true)) && isset($response["id"])){
							$sender->sendMessage("Timing: "."https://" . $this->host . "/?id=" . $response["id"]);
						}else{
							$sender->sendMessage("Could not upload");
						}
					}
				});
			}else{
				fclose($fileTimings);
				$sender->sendMessage("Timing to file");
			}
		}

		return true;
	}
}
