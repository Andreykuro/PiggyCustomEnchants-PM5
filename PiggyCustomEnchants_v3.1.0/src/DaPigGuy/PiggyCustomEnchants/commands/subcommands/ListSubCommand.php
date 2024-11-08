<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\commands\subcommands;

use CortexPE\Commando\BaseSubCommand;
use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use Vecnavium\FormsUI\SimpleForm;

class ListSubCommand extends BaseSubCommand
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
        if ($sender instanceof Player && $this->plugin->areFormsEnabled()) {
            $this->sendTypesForm($sender);
            return;
        }
        $sender->sendMessage($this->getCustomEnchantList());
    }

    /**
     * @return CustomEnchant[][]
     */
    public function getEnchantmentsByType(): array
    {
        $enchantmentsByType = [];
        foreach (CustomEnchantManager::getEnchantments() as $enchantment) {
            if (!isset($enchantmentsByType[$enchantment->getItemType()])) $enchantmentsByType[$enchantment->getItemType()] = [];
            $enchantmentsByType[$enchantment->getItemType()][] = $enchantment;
        }
        return array_map(function (array $typeEnchants) {
            uasort($typeEnchants, function (CustomEnchant $a, CustomEnchant $b) {
                return strcmp($a->getDisplayName(), $b->getDisplayName());
            });
            return $typeEnchants;
        }, $enchantmentsByType);
    }

    public function getCustomEnchantList(): string
    {
        $enchantmentsByType = $this->getEnchantmentsByType();
        $listString = "";
        foreach (Utils::TYPE_NAMES as $type => $name) {
            if (isset($enchantmentsByType[$type])) {
                $listString .= TextFormat::EOL . TextFormat::GREEN . TextFormat::BOLD . Utils::TYPE_NAMES[$type] . TextFormat::EOL . TextFormat::RESET;
                $listString .= implode(", ", array_map(function (CustomEnchant $enchant) {
                    return $enchant->getDisplayName();
                }, $enchantmentsByType[$type]));
            }
        }
        return $listString;
    }

    public function sendTypesForm(Player $player): void
    {
        $enchantmentsByType = $this->getEnchantmentsByType();
        $form = new SimpleForm(function (Player $player, ?int $data) use ($enchantmentsByType): void {
            if ($data !== null) {
                if ($data === count($enchantmentsByType)) {
                    $player->getServer()->dispatchCommand($player, "ce");
                    return;
                }
                $type = array_keys($enchantmentsByType)[$data];
                $this->sendEnchantsForm($player, $type);
            }
        });
        $form->setTitle(TextFormat::GREEN . "Custom Enchants List");
        foreach ($enchantmentsByType as $type => $enchantments) {
            $form->addButton(Utils::TYPE_NAMES[$type]);
        }
        $form->addButton("Back");
        $player->sendForm($form);
    }

    public function sendEnchantsForm(Player $player, int $type): void
    {
        $enchantmentsByType = $this->getEnchantmentsByType();
        $enchantForm = new SimpleForm(function (Player $player, ?int $data) use ($type, $enchantmentsByType): void {
            if ($data !== null) {
                if ($data === count($enchantmentsByType[$type])) {
                    $player->getServer()->dispatchCommand($player, "ce list");
                    return;
                }
                $infoForm = new SimpleForm(function (Player $player, ?int $data) use ($type): void {
                    if ($data !== null) $this->sendEnchantsForm($player, $type);
                });
                /** @var CustomEnchant $selectedEnchantment */
                $selectedEnchantment = array_values($enchantmentsByType[$type])[$data];
                $infoForm->setTitle(TextFormat::GREEN . $selectedEnchantment->getDisplayName() . " Enchantment");
                $infoForm->setContent(TextFormat::GREEN . $selectedEnchantment->getDisplayName() . TextFormat::EOL . TextFormat::RESET . "ID: " . $selectedEnchantment->getId() . TextFormat::EOL . "Description: " . $selectedEnchantment->getDescription() . TextFormat::EOL . "Type: " . Utils::TYPE_NAMES[$type] . TextFormat::EOL . "Rarity: " . Utils::RARITY_NAMES[$selectedEnchantment->getRarity()] . TextFormat::EOL . "Max Level: " . $selectedEnchantment->getMaxLevel());
                $infoForm->addButton("Back");
                $player->sendForm($infoForm);
            }
        });
        $enchantForm->setTitle(TextFormat::GREEN . Utils::TYPE_NAMES[$type] . " Enchants");
        foreach ($enchantmentsByType[$type] as $enchantment) {
            $enchantForm->addButton($enchantment->getDisplayName());
        }
        $enchantForm->addButton("Back");
        $player->sendForm($enchantForm);
    }

    public function prepare(): void
    {
        $this->setPermission("piggycustomenchants.command.ce.list");
    }
}