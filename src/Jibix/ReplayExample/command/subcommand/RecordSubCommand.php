<?php
/**
 * Class RecordSubCommand
 * @author Jibix
 * @date 21.04.2025 - 14:26
 * @project ReplayExample
 */
namespace Jibix\ReplayExample\command\subcommand;
use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use Jibix\ReplayExample\libs\Jibix\Replay\provider\type\MySQLProvider;
use Jibix\ReplayExample\libs\Jibix\Replay\replay\recorder\RecordHandler;
use Jibix\ReplayExample\libs\Jibix\Replay\replay\recorder\RecordSettings;
use Jibix\ReplayExample\libs\Jibix\Replay\util\GameDetails;
use Jibix\ReplayExample\Main;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class RecordSubCommand extends BaseSubCommand {

    private const DEFAULT_GAME_NAME = "balls";
    private const DEFAULT_ID_LENGTH = 6;
    private const MAX_GAME_NAME_LENGTH = 20;

    public function __construct(string $name) {
        parent::__construct($name, "Start recording a replay");
        $this->setPermission("gamereplay.command.record");
    }

    protected function prepare(): void {
        $this->setPermission("gamereplay.command.record");
        $this->registerArgument(0, new RawStringArgument("game-name", true));
        $this->registerArgument(1, new IntegerArgument("identifier-length", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        if (RecordHandler::getInstance()->isRecording($world = $sender->getWorld())) {
            $sender->sendMessage("§cThe world§b {$world->getDisplayName()}§c is already being recorded!");
            return;
        }

        $gameName = $args['game-name'] ?? self::DEFAULT_GAME_NAME;
        if (strlen($gameName) > self::MAX_GAME_NAME_LENGTH) {
            $sender->sendMessage("§b$gameName §cis too long, please use max§6 " . self::MAX_GAME_NAME_LENGTH . "§c chars for the game name!");
            return;
        }
        $identifier_length = $args['identifier-length'] ?? self::DEFAULT_ID_LENGTH;
        if (
            $identifier_length <= 0 ||
            $identifier_length > MySQLProvider::MAX_IDENTIFIER_LENGTH
        ) $identifier_length = self::DEFAULT_ID_LENGTH;

        RecordHandler::getInstance()->record(new RecordSettings(
            $plugin = Main::getInstance(),
            $plugin->getSettings()->getProvider(),
            $identifier_length
        ), $world, GameDetails::fromData([
            'name' => $gameName ?? self::DEFAULT_GAME_NAME,
            'xuids' => array_map(fn (Player $player): string => $player->getXuid(), $sender->getWorld()->getPlayers())
        ]));
        $sender->sendMessage("§aThe world§b {$world->getDisplayName()}§a is being recorded now!");
    }

    public function getPermission(): ?string {
        return "gamereplay.command.record";
    }
}
