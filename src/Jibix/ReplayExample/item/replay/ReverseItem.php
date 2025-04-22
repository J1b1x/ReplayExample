<?php
/**
 * Class ReverseItem
 * @author Jibix
 * @date 20.04.2025 - 13:55
 * @project Replay
 */
namespace Jibix\ReplayExample\item\replay;
use Jibix\Replay\replay\replayer\util\ReplayPlayDirection;
use Jibix\ReplayExample\item\ReplayItem;
use Jibix\ReplayExample\session\ReplaySession;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;


class ReverseItem extends ReplayItem{

    protected static function getItem(Player $player): Item{
        return ReplaySession::get($player)->getReplay()?->getPlayDirection()?->equals(ReplayPlayDirection::BACKWARDS()) ?
            VanillaItems::INK_SAC()->setCustomName("§aForwards") :
            VanillaItems::GLOW_INK_SAC()->setCustomName("§cBackwards");
    }

    public static function getSlot(): int{
        return 0;
    }

    public function onUse(Player $player, ?Vector3 $useVector = null): bool{
        $replay = ReplaySession::get($player)->getReplay();
        ReplaySession::get($player)->getReplay()?->setPlayDirection(ReplayPlayDirection::opposite($replay->getPlayDirection()));
        return parent::onUse($player, $useVector);
    }
}