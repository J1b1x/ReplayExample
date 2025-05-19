<?php
/**
 * Class ReplayCommand
 * @author Jibix
 * @date 20.04.2025 - 17:01
 * @project ReplayExample
 */
namespace Jibix\ReplayExample\command;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use Jibix\ReplayExample\command\subcommand\ChangeWorldSubCommand;
use Jibix\ReplayExample\command\subcommand\RecordSubCommand;
use Jibix\ReplayExample\command\subcommand\StopSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\plugin\Plugin;


class ReplayCommand extends BaseCommand{

    public function __construct(Plugin $plugin, string $name){
        parent::__construct($plugin, $name, "Manage replay recording");
    }

    protected function prepare(): void{
        $this->setPermission("gamereplay.command");
        $this->addConstraint(new InGameRequiredConstraint($this));
        $this->registerSubCommand(new RecordSubCommand("record"));
        $this->registerSubCommand(new StopSubCommand("stop"));
        $this->registerSubCommand(new ChangeWorldSubCommand("world"));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        throw new InvalidCommandSyntaxException();
    }
}