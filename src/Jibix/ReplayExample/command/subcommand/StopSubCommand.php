<?php
/**
 * Class StopSubCommand
 * @author Jibix
 * @date 21.04.2025 - 14:27
 * @project ReplayExample
 */
namespace Jibix\ReplayExample\command\subcommand;
use CortexPE\Commando\BaseSubCommand;
use Jibix\Replay\replay\data\ReplayInformation;
use Jibix\Replay\replay\recorder\RecordHandler;
use Jibix\Replay\util\Utils;
use pocketmine\command\CommandSender;


class StopSubCommand extends BaseSubCommand{

    protected function prepare(): void{
        $this->setPermission("gamereplay.command.stop");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if (!RecordHandler::getInstance()->getRecorder($world = $sender->getWorld())) {
            $sender->sendMessage("§cYou're currently not in a recording world!");
            return;
        }
        $sender->sendMessage("§aSaving replay...");
        RecordHandler::getInstance()->stopRecording($world, function (ReplayInformation $information, float $size) use ($sender): void{
            $sender->sendMessage(
                "§8-------§aSaved replay§8-------\n" .
                "§bIdentifier:§6 {$information->getIdentifier()}\n" .
                "§bDuration:§6 " . Utils::formatReplayDuration(intval($information->getDuration() / 20)) . "\n" .
                "§bTimestamp:§6 " . $information->getTimestamp()->format("H:i:s Y.m.d") . "\n" .
                "§bGame Details:§6 " . $information->getGameDetails() . "\n" .
                "§bEstimated Replay Size:§6 ~" . $size . "mb\n" .
                "§8---------------------------"
            );
        });
    }
}