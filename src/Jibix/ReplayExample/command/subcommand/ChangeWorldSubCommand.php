<?php
/**
 * Class ChangeWorldSubCommand
 * @author Jibix
 * @date 21.04.2025 - 14:27
 * @project ReplayExample
 */
namespace Jibix\ReplayExample\command\subcommand;
use CortexPE\Commando\args\BooleanArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use Jibix\Replay\replay\recorder\RecordHandler;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;


class ChangeWorldSubCommand extends BaseSubCommand{

    protected function prepare(): void{
        $this->setPermission("replay.command.world");
        $this->registerArgument(0, new RawStringArgument("world", false));
        $this->registerArgument(1, new BooleanArgument("teleport-players", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if (!$recorder = RecordHandler::getInstance()->getRecorder($currentWorld = $sender->getWorld())) {
            $sender->sendMessage("§cYou're currently not in a recording world!");
            return;
        }
        $manager = $sender->getServer()->getWorldManager();
        if (!$manager->isWorldGenerated($worldName = $args['world'])) {
            $sender->sendMessage("§cThe world§b {$worldName}§c is not generated!");
            return;
        }
        if (!$manager->loadWorld($worldName, true)) {
            $sender->sendMessage("§cCould not load world§b {$worldName}§c!");
            return;
        }
        RecordHandler::getInstance()->changeRecordingWorld($recorder, $world = $manager->getWorldByName($worldName));
        if ($args['teleport-players'] ?? true) {
            $spawn = $world->getSafeSpawn();
            /** @var Player $player */
            foreach ($currentWorld->getPlayers() as $player) {
                $player->teleport($spawn);
            }
        }
        $sender->sendMessage("§aYou have successfully changed the recording world from§b {$currentWorld->getDisplayName()}§a to§6 {$world->getDisplayName()}§a!");
    }
}