<?php
/**
 * Class RewindItem
 * @author Jibix
 * @date 20.04.2025 - 14:19
 * @project Replay
 */
namespace Jibix\ReplayExample\item\replay;
use Jibix\Replay\replay\replayer\util\ReplayPlayDirection;


class RewindItem extends SkipItem{

    public static function getSlot(): int{
        return 3;
    }

    protected static function getPlayDirection(): ReplayPlayDirection{
        return ReplayPlayDirection::BACKWARDS();
    }
}