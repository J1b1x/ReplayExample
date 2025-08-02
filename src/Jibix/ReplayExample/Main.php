<?php
/**
 * Class Main
 * @author Jibix
 * @date 20.04.2025 - 16:52
 * @project ReplayExample
 */
namespace Jibix\ReplayExample;
use Jibix\AsyncMedoo\AsyncMedoo;
use Jibix\AsyncMedoo\MySQLCredentials;
use Jibix\Forms\Forms;
use Jibix\FunctionalItem\FunctionalItemManager;
use Jibix\Replay\entity\type\DefaultReplayEntity;
use Jibix\Replay\entity\type\ReplayHuman;
use Jibix\Replay\entity\type\ReplayItemEntity;
use Jibix\Replay\entity\type\ReplayLiving;
use Jibix\Replay\listener\replay\ReplayListener;
use Jibix\Replay\provider\type\JsonProvider;
use Jibix\ReplayExample\command\ReplayCommand;
use Jibix\ReplayExample\item\replay\EventLogItem;
use Jibix\ReplayExample\item\replay\PauseItem;
use Jibix\ReplayExample\item\replay\QuitReplayItem;
use Jibix\ReplayExample\item\replay\ReverseItem;
use Jibix\ReplayExample\item\replay\RewindItem;
use Jibix\ReplayExample\item\replay\SkipItem;
use Jibix\ReplayExample\item\replay\SpeedItem;
use Jibix\ReplayExample\item\ReplaySelectorItem;
use Jibix\Replay\provider\type\MySQLProvider;
use Jibix\Replay\replay\replayer\ReplaySettings;
use Jibix\ReplayExample\session\ReplaySession;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;


class Main extends PluginBase{
    use SingletonTrait{
        setInstance as private;
        reset as private;
    }

    private const TYPE_REPLAY = "replay";

    private ReplaySettings $settings;
    private bool $isReplayServer = false;

    protected function onLoad(): void{
        self::setInstance($this);
        $this->saveDefaultConfig();
        $data = $this->getConfig()->getAll();
        AsyncMedoo::initialize(MySQLCredentials::fromArray($data['mysql-credentials'] ?? []));
        $this->settings = new ReplaySettings(
            $this,
            match (mb_strtolower($data['provider'] ?? "mysql")) {
                "json" => new JsonProvider(empty($path = $data['json-path']) ? $this->getDataFolder() . "replays.json" : $path),
                default => new MySQLProvider(),
            },
            $data['unreversable-level-event-ids'] ?? [],
        );

        if (mb_strtolower($data["server-type"] ?? "") === self::TYPE_REPLAY) $this->isReplayServer = true;
    }

    protected function onEnable(): void{
        if ($this->isReplayServer) {
            $this->getServer()->getLogger()->notice("Using the replay system in§b replay§r mode");
            $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
            $this->getServer()->getPluginManager()->registerEvents(new ReplayListener(), $this);

            $factory = EntityFactory::getInstance();
            $factory->register(ReplayHuman::class, function (World $world, CompoundTag $nbt): ReplayHuman{
                return new ReplayHuman(...array_merge(self::parseReplayEntityNbt($nbt), [
                    EntityDataHelper::parseLocation($nbt, $world),
                    ReplayHuman::parseSkinNBT($nbt),
                    $nbt
                ]));
            }, ['ReplayHuman']);
            $factory->register(ReplayItemEntity::class, function (World $world, CompoundTag $nbt): ReplayItemEntity{
                $itemTag = $nbt->getCompoundTag(ReplayItemEntity::TAG_ITEM);
                if ($itemTag === null) throw new SavedDataLoadingException("Expected \"" . ReplayItemEntity::TAG_ITEM . "\" NBT tag not found");
                $item = Item::nbtDeserialize($itemTag);
                if ($item->isNull()) throw new SavedDataLoadingException("Item is invalid");
                return new ReplayItemEntity($nbt->getInt("actualId", PHP_INT_MAX), EntityDataHelper::parseLocation($nbt, $world), $item, $nbt);
            }, ['ReplayItem']);
            $factory->register(DefaultReplayEntity::class, function (World $world, CompoundTag $nbt): DefaultReplayEntity{
                return new DefaultReplayEntity(...array_merge(self::parseReplayEntityNbt($nbt), [
                    EntityDataHelper::parseLocation($nbt, $world),
                    $nbt
                ]));
            }, ['ReplayEntity']);
            $factory->register(ReplayLiving::class, function (World $world, CompoundTag $nbt): ReplayLiving{
                return new ReplayLiving(...array_merge(self::parseReplayEntityNbt($nbt), [
                    EntityDataHelper::parseLocation($nbt, $world),
                    $nbt
                ]));
            }, ['ReplayLiving']);
            FunctionalItemManager::register($this);
            FunctionalItemManager::getInstance()->registerFunctionalItem(
                new ReplaySelectorItem(),

                new EventLogItem(),
                new PauseItem(),
                new ReverseItem(),
                new RewindItem(),
                new SkipItem(),
                new SpeedItem(),
                new QuitReplayItem()
            );
            Forms::register($this);
        } else {
            $this->getServer()->getLogger()->notice("Using the replay system in§b record§r mode");
            $this->getServer()->getCommandMap()->register($this->getName(), new ReplayCommand($this, "replay"));
        }
    }

    protected function onDisable(): void{
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            ReplaySession::get($player)->getReplay()?->end();
        }
    }

    public function getSettings(): ReplaySettings{
        return $this->settings;
    }

    private static function parseReplayEntityNbt(CompoundTag $nbt): array{
        return [
            $nbt->getInt("actualId", PHP_INT_MAX),
            (bool)$nbt->getByte("isPlayer", true),
            new EntitySizeInfo($nbt->getFloat("height", 1.8), $nbt->getFloat("width", 0.6), $nbt->getFloat("eyeHeight", 1.62)),
            new Vector3($nbt->getInt("offsetX", 0), $nbt->getInt("offsetY", 0), $nbt->getInt("offsetZ", 0)),
        ];
    }
}