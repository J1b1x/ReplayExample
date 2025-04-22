<?php
/**
 * Class EventLogItem
 * @author Jibix
 * @date 20.04.2025 - 16:11
 * @project Replay
 */
namespace Jibix\ReplayExample\item\replay;
use Jibix\Forms\form\type\MenuForm;
use Jibix\Forms\menu\Button;
use Jibix\Forms\menu\type\BackButton;
use Jibix\Replay\entity\type\ReplayHuman;
use Jibix\Replay\replay\log\EventLog;
use Jibix\ReplayExample\item\ReplayItem;
use Jibix\ReplayExample\session\ReplaySession;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;


class EventLogItem extends ReplayItem{

    protected static function getItem(Player $player): Item{
        return VanillaItems::COMPASS()->setCustomName("§bEvent Log");
    }

    public static function getSlot(): int{
        return 7;
    }

    public function onUse(Player $player, ?Vector3 $useVector = null): bool{
        $buttons = [new Button("§bTeleporter", fn (Player $player) => $player->sendForm($this->getTeleporterForm($player)))];
        $list = [];
        foreach (ReplaySession::get($player)->getReplay()->getEventLogs() as $events) {
            foreach ($events as $event) {
                if (isset($list[$id = $event::id()])) continue;
                $list[$id] = true;
                $buttons[] = new Button("§b" . ucwords($event::getName()), fn (Player $player) => $player->sendForm($this->getEventForm($player, $event)));
            }
        }
        $buttons[] = new BackButton();
        $player->sendForm(new MenuForm("Event Log", "", $buttons));
        return parent::onUse($player, $useVector);
    }

    private function getEventForm(Player $player, EventLog $event): MenuForm{
        $id = $event::id();
        foreach (ReplaySession::get($player)->getReplay()?->getEventLogs() ?? [] as $tick => $events) {
            foreach ($events as $event) {
                if ($event::id() !== $id) continue;
                $second = round(max($tick / 20, 0), 2);
                $buttons[] = new Button("§a$second:§r " . $event->getDisplayData(), function (Player $player) use ($tick, $event): void{
                    if (!$replay = ReplaySession::get($player)->getReplay()) return;
                    $replay->skipToTick($tick - $event::getTickOffset());
                    $event->handle($replay);
                });
            }
        }
        $buttons[] = new BackButton();
        return new MenuForm(ucwords($event::getName()), "", $buttons);
    }

    private function getTeleporterForm(Player $player): MenuForm{
        foreach (ReplaySession::get($player)->getReplay()->getEntities() as $entity) {
            if (!$entity instanceof ReplayHuman || !$entity->isPlayer()) continue;
            $id = $entity->getActualId();
            $buttons[] = new Button("§b" . $entity->getDisplayName(), function (Player $player) use ($id): void{
                if (!$entity = ReplaySession::get($player)->getReplay()?->getEntity($id)) return;
                $player->teleport($entity->getPosition());
            });
        }
        $buttons[] = new BackButton();
        return new MenuForm("Teleporter", "", $buttons);
    }
}