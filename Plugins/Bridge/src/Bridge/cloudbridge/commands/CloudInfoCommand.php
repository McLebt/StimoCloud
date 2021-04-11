<?php


namespace Bridge\cloudbridge\commands;


use FormAPI\FormAPI;
use MongoDB\Driver\Monitoring\CommandStartedEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use Bridge\cloudbridge\Main;
use Bridge\cloudbridge\Transfer\TransferAPI;

class CloudInfoCommand extends Command
{
    private $main;
    private static $lobby = [];

    public function __construct(Main $main)
    {
        parent::__construct("cloudinfo", "", "", []);
        $this->setDescription("CloudInfo Command");
        $this->main = $main;
    }

    public static function onlineServerCountForm(Player $player)
    {
        $api = FormAPI::getInstance();
        $form = $api->createSimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            if ($result === null) {
                return;
            }

        });
        $form->setTitle(Main::PREFIX . "§eInfo");
        $form->setContent("§aCurrent §cOnline§7-§cTemplates§7:\n§4" . Main::getAllCloudServers());
        $form->addButton("§4Close.");
        $form->sendToPlayer($player);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender instanceof Player) {
            $this::onlineServerCountForm($sender);
        }
    }
}