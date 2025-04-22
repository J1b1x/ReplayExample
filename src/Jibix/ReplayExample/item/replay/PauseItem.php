<?php
/**
 * Class PauseItem
 * @author Jibix
 * @date 20.04.2025 - 13:53
 * @project Replay
 */
namespace Jibix\ReplayExample\item\replay;
use Jibix\ReplayExample\item\ReplayItem;
use Jibix\ReplayExample\session\ReplaySession;
use pocketmine\block\utils\DyeColor;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;


class PauseItem extends ReplayItem{

    protected static function getItem(Player $player): Item{
        $isPaused = ReplaySession::get($player)->getReplay()?->isPaused();
        return VanillaItems::DYE()
            ->setColor($isPaused ? DyeColor::LIME() : DyeColor::RED())
            ->setCustomName($isPaused ? "§aContinue" : "§cStop");
    }

    public static function getSlot(): int{
        return 4;
    }

    public function onUse(Player $player, ?Vector3 $useVector = null): bool{
        ReplaySession::get($player)->getReplay()?->togglePaused();
        return parent::onUse($player, $useVector);
    }
}