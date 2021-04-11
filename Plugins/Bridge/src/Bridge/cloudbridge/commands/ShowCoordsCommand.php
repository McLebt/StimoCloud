<?php
/* Copyright (c) 2020 Florian H. All rights reserved. */
namespace Bridge\cloudbridge\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use Bridge\cloudbridge\Main;

/**
 * Class XYZCommand
 * @package Bridge\cloudbridge\commands
 * @author Florian H./xxAROX
 * @date 03.08.2020 - 19:17
 * @project CloudServer
 */
class ShowCoordsCommand extends Command{
    private $main;

    public function __construct(Main $main){
        parent::__construct("xyz");
        $this->setDescription("xyz Command");
        $this->main = $main;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if (!$sender instanceof Player){
            return;
        }

        if ($sender->hasPermission("cloudbridge.command.xyz")){
            $sender->sendMessage(Main::PREFIX . "§eX§7: {$sender->x} §8| §eY§7: {$sender->y} §8| §eZ§7: {$sender->z}");
        } else {
            $sender->sendMessage("§cYou don't have the Permissions to use this command!");
        }
    }
}
