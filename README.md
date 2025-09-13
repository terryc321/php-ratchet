# php-ratchet

http://socketo.me/

http://socketo.me/docs/install

install php
```
sudo apt install php-cli
```

install composer

https://getcomposer.org/download/

```
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'.PHP_EOL; } else { echo 'Installer corrupt'.PHP_EOL; unlink('composer-setup.php'); exit(1); }"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

```
sudo mv composer.phar /usr/local/bin/composer
```

composer.phar is now /usr/local/bin/composer 

```
php ~/composer.phar require cboden/ratchet
becomes 
php /usr/local/bin/composer require cboden/ratchet
```

now ready to start using ratchet
```
<?php
    require __DIR__ . '/vendor/autoload.php';
```

lets make a project directory
```
GITHUB_USER='terryc321'
PROJECT='php-ratchet'
mkdir -pv ~/code/$PROJECT
cd ~/code/$PROJECT

echo "# $PROJECT" >> README.md
git init
git add README.md
git commit -m "first commit"
git branch -M main
git remote add origin git@github.com:$GITHUB_USER/$PROJECT.git
git push -u origin main
```


We're going to hold everything in the MyApp namespace. Your composer file [composer.json] should look something like this:

```
{
    "autoload": {
        "psr-4": {
            "MyApp\\": "src"
        }
    },
    "require": {
        "cboden/ratchet": "^0.4"
    }
}
```



Now lets create src/Chat.php file 
```
<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    public function onOpen(ConnectionInterface $conn) {
    }

    public function onMessage(ConnectionInterface $from, $msg) {
    }

    public function onClose(ConnectionInterface $conn) {
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
    }
}

```

next create [bin/chat-server.php] file
```
<?php
use Ratchet\Server\IoServer;
use MyApp/Chat;

    require dirname(__DIR__) . '/vendor/autoload.php';

    $server = IoServer::factory(
        new Chat(),
        8080
    );

    $server->run();
```

note there is no closing brace on [bin/chat-server.php] file

now lets run the chat server
```
php bin/chat-server.php
```


```
terry@terry-MS-7D96:~/code/php-ratchet$ tree -I vendor -I learning
.
├── bin
│   └── chat-server.php
├── composer.json
├── composer.lock
├── index.html
├── README.md
└── src
    └── Chat.php
```

## DIR

learning/dir.php
```
<?php
echo "test\n";
echo "dirname ={" , dirname(__DIR__) , "}";
echo "\n\n";
```

we see the output 
```
test
dirname ={/home/terry/code/php-ratchet}
```
so we know that ```__DIR__``` gives ```/home/terry/code/php-ratchet```

## composer

```
composer dump-autoload
```

## chat server errors 
```
> php bin/chat-server.php
PHP Fatal error:  Uncaught Error: Class "MyApp\Chat" not found in /home/terry/code/php-ratchet/bin/chat-server.php:8
Stack trace:
#0 {main}
  thrown in /home/terry/code/php-ratchet/bin/chat-server.php on line 8
```

## php curl extension

```
sudo apt install php-curl
```

## php composer update

```
> php /usr/local/bin/composer update
Loading composer repositories with package information
Updating dependencies
Nothing to modify in lock file
Writing lock file
Installing dependencies from lock file (including require-dev)
Nothing to install, update or remove
Generating autoload files
12 packages you are using are looking for funding.
Use the `composer fund` command to find out more!
No security vulnerability advisories found.
```

# finish logic

in Chat.php finish the logic
```
<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        foreach ($this->clients as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
```

# trial the final chat application

open three terminals , we can then type in either telnet clients and output is relayed to other telnet client , this shows websocket connection is working

```
> php bin/chat-server.php

> telnet localhost 8080

> telnet localhost 8080 

```

# add WsServer class 

```
<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\Chat;

    require dirname(__DIR__) . '/vendor/autoload.php';

    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new Chat()
            )
        ),
        8080
    );

    $server->run();
```

open a couple of web browsers

```
var conn = new WebSocket('ws://localhost:8080');
conn.onopen = function(e) {
    console.log("Connection established!");
};

conn.onmessage = function(e) {
    console.log(e.data);
};

```

Once you see the console message "Connection established!" you can start sending messages to other connected browsers:

```
conn.send('Hello World!');
```
