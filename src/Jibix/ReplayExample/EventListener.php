<?php
/**
 * Class EventListener
 * @author Jibix
 * @date 21.04.2025 - 13:35
 * @project ReplayExample
 */
namespace Jibix\ReplayExample;
use Jibix\Replay\replay\replayer\event\ReplayChangeDirectionEvent;
use Jibix\Replay\replay\replayer\event\ReplayRestartEvent;
use Jibix\Replay\replay\replayer\event\ReplayStartEvent;
use Jibix\Replay\replay\replayer\event\ReplayTogglePauseEvent;
use Jibix\ReplayExample\item\replay\PauseItem;
use Jibix\ReplayExample\item\replay\ReverseItem;
use Jibix\ReplayExample\session\ReplaySession;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;


class EventListener implements Listener{

    public function onLogin(PlayerLoginEvent $event): void{
        ReplaySession::get($event->getPlayer()); //Initialize
    }

    public function onJoin(PlayerJoinEvent $event): void{
        ReplaySession::get($event->getPlayer())->setup();
    }

    public function onQuit(PlayerQuitEvent $event): void{
        ReplaySession::get($event->getPlayer())->getReplay()?->end();
    }

    public function onReplayStart(ReplayStartEvent $event): void{
        ReplaySession::get($event->getReplay()->getWatcher())->setReplay($event->getReplay());
    }

    public function onReplayRestart(ReplayRestartEvent $event): void{
        $session = ReplaySession::get($player = $event->getReplay()->getWatcher());
        if ($event->isReversed()) $session->setReplayItem(ReverseItem::get($player));
        $session->setReplayItem(PauseItem::get($player));
        $player->getInventory()->setHeldItemIndex(4);
    }

    public function onReplayTogglePause(ReplayTogglePauseEvent $event): void{
        ReplaySession::get($player = $event->getReplay()->getWatcher())->setReplayItem(PauseItem::get($player));
    }

    public function onReplayChangeDirection(ReplayChangeDirectionEvent $event): void{
        ReplaySession::get($player = $event->getReplay()->getWatcher())->setReplayItem(ReverseItem::get($player));
    }
}