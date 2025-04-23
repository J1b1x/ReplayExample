# MapVideoExample

![php](https://img.shields.io/badge/php-8.1-informational)
![api](https://img.shields.io/badge/pocketmine-5.0-informational)

This is a plugin **example**, of how to use the [Replay](https://github.com/J1b1x/Replay) library.
## NOTE

This is just an EXAMPLE plugin, of how you can use the Replay API, it's not really supposed to be a "feature rich" plugin (even though it is). 

### Functionality
You can either use this plugin as recorder or as a replayer.
Just configure it in the [config.yml](https://github.com/J1b1x/ReplayExample/blob/main/resources/config.yml).

#### Recording
If you're using this plugin for recording, you need to use the ```/record``` command.

- ``/record`` | _gamereplay.command_
- ``/record record [game-name] [identifier-length]`` | _gamereplay.command.record_
- ``/record stop`` | _gamereplay.command.stop_
- ``/record world <world> [teleport-players]`` | _gamereplay.command.world_

#### Replaying
Well, replaying is actually pretty simple. Just join the server and click on the compass.

If you have the permission ``gamereplay.search``, you can also watch replays of other people, if you have their replay's ID. Just press the ``Search Replay`` button and put in the replay ID.
Only people with the ``gamereplay.delete`` permission can delete replays.
