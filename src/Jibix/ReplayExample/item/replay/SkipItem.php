<?php
/**
 * Class SkipItem
 * @author Jibix
 * @date 20.04.2025 - 14:17
 * @project Replay
 */
namespace Jibix\ReplayExample\item\replay;
use Jibix\Replay\replay\replayer\util\ReplayPlayDirection;
use Jibix\ReplayExample\item\ReplayItem;
use Jibix\ReplayExample\session\ReplaySession;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;


class SkipItem extends ReplayItem{

    private const DEFAULT_SKIP_SECONDS = 5;
    private const SKIP_SECONDS = [0.5, 1, 5, 10, 30, 60];
    private const SKIP_TAG = "skip";

    public static function getSlot(): int{
        return 5;
    }

    protected static function getPlayDirection(): ReplayPlayDirection{
        return ReplayPlayDirection::FORWARDS();
    }

    protected static function getItem(Player $player): Item{
        $seconds = self::SKIP_SECONDS[array_search(
            $player->getInventory()->getItemInHand()->getNamedTag()->getFloat(self::SKIP_TAG, self::DEFAULT_SKIP_SECONDS),
            self::SKIP_SECONDS
        ) +1] ?? self::SKIP_SECONDS[0];
        $playDirection = static::getPlayDirection();
        return VanillaItems::ARROW()
            ->setNamedTag(CompoundTag::create()->setFloat(self::SKIP_TAG, $seconds))
            ->setCustomName("§b" . ucwords($playDirection->name()) . "§8 -§6 $seconds §bseconds");
    }

    public function onDrop(Player $player, Item $item): bool{
        ReplaySession::get($player)?->setReplayItem(static::get($player));
        return parent::onDrop($player, $item);
    }

    public function onUse(Player $player, ?Vector3 $useVector = null): bool{
        ReplaySession::get($player)->getReplay()->skip(
            static::getPlayDirection(),
            $player->getInventory()->getItemInHand()->getNamedTag()->getFloat(self::SKIP_TAG, 5) * 20
        );
        return parent::onUse($player, $useVector);
    }
}