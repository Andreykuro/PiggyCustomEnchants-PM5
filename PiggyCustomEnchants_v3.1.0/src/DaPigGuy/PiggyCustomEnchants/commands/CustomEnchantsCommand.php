<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\commands;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;
use DaPigGuy\PiggyCustomEnchants\commands\subcommands\AboutSubCommand;
use DaPigGuy\PiggyCustomEnchants\commands\subcommands\EnchantSubCommand;
use DaPigGuy\PiggyCustomEnchants\commands\subcommands\InfoSubCommand;
use DaPigGuy\PiggyCustomEnchants\commands\subcommands\ListSubCommand;
use DaPigGuy\PiggyCustomEnchants\commands\subcommands\NBTSubCommand;
use DaPigGuy\PiggyCustomEnchants\commands\subcommands\RemoveSubCommand;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use Vecnavium\FormsUI\SimpleForm;

class CustomEnchantsCommand extends BaseCommand
{
    /** @var PiggyCustomEnchants */
    protected Plugin $plugin;

    // Constructor to initialize the plugin
    public function __construct(PiggyCustomEnchants $plugin) {
        $this->plugin = $plugin;
        parent::__construct($plugin, "ce", "Manage custom enchants");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        $subcommands = array_values(array_map(function (BaseSubCommand $subCommand): string {
            return $subCommand->getName();
        }, $this->getSubCommands()));
        if ($sender instanceof Player && $this->plugin->areFormsEnabled()) {
            $form = new SimpleForm(function (Player $player, ?int $data) use ($subcommands): void {
                if ($data !== null && isset($subcommands[$data])) {
                    $this->plugin->getServer()->dispatchCommand($player, "ce " . $subcommands[$data]);
                }
            });
            $form->setTitle(TextFormat::GREEN . "PiggyCustomEnchants Menu");
            foreach ($subcommands as $subcommand) $form->addButton(ucfirst($subcommand));
            $sender->sendForm($form);
            return;
        }
        $sender->sendMessage("Usage: /ce <" . implode("|", $subcommands) . ">");
    }

    public function prepare(): void {
        $this->setPermission("piggycustomenchants.command.ce");

        $this->registerSubCommand(new AboutSubCommand("about", "Displays basic information about the plugin", [], $this->plugin));
        $this->registerSubCommand(new EnchantSubCommand("enchant", "Apply an enchantment on an item", [], $this->plugin));
        $this->registerSubCommand(new InfoSubCommand("info", "Get info on a custom enchant", [], $this->plugin));
        $this->registerSubCommand(new ListSubCommand("list", "Lists all registered custom enchants", [], $this->plugin));
        $this->registerSubCommand(new NBTSubCommand("nbt", "Displays NBT tags of currently held item", [], $this->plugin));
        $this->registerSubCommand(new RemoveSubCommand("remove", "Remove an enchantment from an item", [], $this->plugin));
    }

    public function getPermission(): ?string {
        return $this->getPermission();
    }
}