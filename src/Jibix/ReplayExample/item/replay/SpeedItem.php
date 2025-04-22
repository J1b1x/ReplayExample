<?php
/**
 * Class SpeedItem
 * @author Jibix
 * @date 20.04.2025 - 14:12
 * @project Replay
 */
namespace Jibix\ReplayExample\item\replay;
use Jibix\Forms\element\type\StepSlider;
use Jibix\Forms\form\type\CustomForm;
use Jibix\ReplayExample\item\ReplayItem;
use Jibix\ReplayExample\session\ReplaySession;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;


class SpeedItem extends ReplayItem{

    private const SPEEDS = [/*0.25, */0.5, 1.0, 2.0, 4.0]; //0.25 looks a bit laggy

    protected static function getItem(Player $player): Item{
        $session = ReplaySession::get($player);
        $speed = self::SPEEDS[array_search($session->getReplay()?->getSpeed(), self::SPEEDS) +1] ?? self::SPEEDS[0];
        return VanillaItems::CLOCK()->setCustomName("§bSpeed§8 -§6 $speed");
    }

    public static function getSlot(): int{
        return 1;
    }

    public function onDrop(Player $player, Item $item): bool{
        $player->removeCurrentWindow();
        $player->sendForm(new CustomForm("Play Speed", [new StepSlider(
            "§bPlay Speed",
            array_map(fn (float $speed): string => (string)$speed, self::SPEEDS),
            array_search(ReplaySession::get($player)->getReplay()?->getSpeed() ?? 1, self::SPEEDS),
            function (Player $player, StepSlider $slider): void{
                $session = ReplaySession::get($player);
                $session->setReplayItem(self::get($player));
                $session->getReplay()?->setSpeed((float)$slider->getSelectedOption());
            }
        )]));
        return parent::onDrop($player, $item);
    }

    public function onUse(Player $player, ?Vector3 $useVector = null): bool{
        $session = ReplaySession::get($player);
        $replay = $session->getReplay();
        $replay->setSpeed(self::SPEEDS[array_search($replay->getSpeed(), self::SPEEDS) +1] ?? self::SPEEDS[0]);
        $session->setReplayItem(self::get($player));
        return parent::onUse($player, $useVector);
    }
}