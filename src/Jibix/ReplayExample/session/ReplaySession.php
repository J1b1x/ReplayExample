<?php
/**
 * Class ReplaySession
 * @author Jibix
 * @date 21.04.2025 - 13:56
 * @project ReplayExample
 */
namespace Jibix\ReplayExample\session;
use InvalidArgumentException;
use Jibix\FunctionalItem\FunctionalItemManager;
use Jibix\ReplayExample\item\replay\EventLogItem;
use Jibix\ReplayExample\item\replay\PauseItem;
use Jibix\ReplayExample\item\replay\QuitReplayItem;
use Jibix\ReplayExample\item\replay\ReverseItem;
use Jibix\ReplayExample\item\replay\RewindItem;
use Jibix\ReplayExample\item\replay\SkipItem;
use Jibix\ReplayExample\item\replay\SpeedItem;
use Jibix\ReplayExample\item\ReplaySelectorItem;
use Jibix\Replay\replay\replayer\Replay;
use Jibix\ReplayExample\item\ReplayItem;
use Jibix\ReplayExample\Main;
use pocketmine\item\Item;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use WeakMap;


class ReplaySession{

    private static WeakMap $sessions;

    public static function get(Player $player): self{
        if (!isset(self::$sessions)) self::$sessions = new WeakMap();
        return self::$sessions[$player] ??= new self($player);
    }

    private ?Replay $replay = null;
    private array $availableReplays;

    //TODO: player-replay settings?
    private function __construct(private Player $player){
        Main::getInstance()->getSettings()->getProvider()->initializeReplays($this->player, fn (array $replays) => $this->availableReplays = $replays);
    }

    public function setup(): void{
        $this->player->getXpManager()->setXpAndProgress(0, 0);
        $this->player->getInventory()->clearAll();
        $this->setReplayItem(ReplaySelectorItem::get($this->player));
        $this->player->setGamemode(GameMode::ADVENTURE);
        //$this->getAttachment(UtilitiesAttachment::class)->basicInit(GameMode::ADVENTURE(), 6);
        $this->player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
        $this->player->getInventory()->setHeldItemIndex(4);
    }

    public function getPlayer(): Player{
        return $this->player;
    }

    public function getAvailableReplays(): array{
        return $this->availableReplays;
    }

    public function deleteAvailableReplay(string $identifier): void{
        unset($this->availableReplays[array_search($identifier, $this->availableReplays)]);
    }

    public function getReplay(): ?Replay{
        return $this->replay;
    }

    public function setReplay(?Replay $replay): void{
        $this->replay = $replay;
        if ($replay !== null) {
            //TODO: Boss bar?
            $this->player->setGamemode(GameMode::SURVIVAL());
            $this->player->getInventory()->clearAll();
            $this->setReplayItem(
                ReverseItem::get($this->player),
                SpeedItem::get($this->player),
                RewindItem::get($this->player),
                PauseItem::get($this->player),
                SkipItem::get($this->player),
                EventLogItem::get($this->player),
                QuitReplayItem::get($this->player)
            );
            $this->player->getInventory()->setHeldItemIndex(4);
            $this->player->setAllowFlight(true);
            $this->player->setFlying(true);
        } elseif ($this->replay !== null) {
            $this->setup();
        }
    }

    public function setReplayItem(Item ...$items): void{
        $inv = $this->player->getInventory();
        foreach ($items as $item) {
            $functionalItem = FunctionalItemManager::getInstance()->getItem($item);
            if (!$functionalItem instanceof ReplayItem) throw new InvalidArgumentException("Item is not a replay item");
            $inv->setItem($functionalItem::getSlot(), $item);
        }
    }
}