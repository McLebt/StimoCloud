<?php

namespace pocketmine\cloud\commands;

use pocketmine\cloud\Cloud;
use pocketmine\command\CommandSender;
use pocketmine\command\defaults\VanillaCommand;

class StartPServer extends VanillaCommand {
    public $cloud;

    public function __construct(Cloud $cloud, string $name) {
        $this->cloud = $cloud;
        parent::__construct($name, "Start new Private servers by template", "/startp <template> <count> <Player>");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(isset($args[0]) or isset($args[1]) or isset($args[2])){
            if($this->cloud->isTemplate($args[0])){
                $template = $this->cloud->getTemplateByName($args[0]);
                if(isset($args[1])){
                    if(is_numeric($args[1])){
                        $count = intval($args[1]);
                    }else{
                        $count = 1;
                    }
                }else{
                    $count = 1;
                }
                for ($i = 0; $i < $count; $i++) {
					if (isset($args[2])) {
						if (!is_dir($this->cloud->getServerFolder() . $args[0] . "-" . $args[2])) {
							$server = $template->createNewPrivateServer($args[2]);
							$server->startPServer();
						} else {
							$sender->sendMessage("A Private-Server with this name already exists!");
						}
					} else {
						$sender->sendMessage($this->getUsage());
					}
				}
            }else{
                $sender->sendMessage("Template not found!");
            }
        }else{
            $sender->sendMessage($this->getUsage());
        }
    }
}
