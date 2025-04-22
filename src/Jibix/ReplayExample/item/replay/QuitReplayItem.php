<?php
/**
 * Class QuitReplayItem
 * @author Jibix
 * @date 20.04.2025 - 13:54
 * @project Replay
 */
namespace Jibix\ReplayExample\item\replay;
use Jibix\ReplayExample\item\ReplayItem;
use Jibix\ReplayExample\session\ReplaySession;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;


class QuitReplayItem extends ReplayItem{

    protected static function getItem(Player $player): Item{
        return VanillaBlocks::NETHER_WART()->asItem()->setCustomName("§cQuit");
    }

    public static function getSlot(): int{
        return 8;
    }

    public function onUse(Player $player, ?Vector3 $useVector = null): bool{
        $session = ReplaySession::get($player);
        $session->getReplay()?->end();
        $session->setup();
        return parent::onUse($player, $useVector);
    }
}