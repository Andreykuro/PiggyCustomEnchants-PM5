<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\commands\subcommands;

use CortexPE\Commando\BaseSubCommand;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

class NBTSubCommand extends BaseSubCommand
{
    /** @var PiggyCustomEnchants */
    protected Plugin $plugin;

    public function __construct(string $name, string $description = "", array $aliases = [], Plugin $plugin)
    {
        parent::__construct($name, $description, $aliases, $plugin);
        $this->plugin = $plugin; // Initialize the $plugin property
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            $sender->sendMessage($sender->getInventory()->getItemInHand()->getNamedTag()->toString());
            return;
        }
        $sender->sendMessage(TextFormat::RED . "Please use this in-game.");
    }

    public function prepare(): void
    {
        $this->setPermission("piggycustomenchants.command.ce.nbt");
    }
}