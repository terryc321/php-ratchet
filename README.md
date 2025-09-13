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

# Push Integration ZeroMQ 

your composer.json should include zeromq

```
{
    "autoload": {
        "psr-4": {
            "MyApp\\": "src"
        }
    },
    "require": {
        "cboden/ratchet": "0.4.*",
        "react/zmq": "0.2.*|0.3.*"
    }
}
```

# wamp websocket application messaging protocol

save this as /src/MyApp/Pusher.php 
```
<?php
namespace MyApp;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Pusher implements WampServerInterface {
    public function onSubscribe(ConnectionInterface $conn, $topic) {
    }
    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
    }
    public function onOpen(ConnectionInterface $conn) {
    }
    public function onClose(ConnectionInterface $conn) {
    }
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->close();
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {
    }
}
```


# editing blog submission

post.php where are we putting this ? what is pdo ?

```
<?php
    // post.php ???
    // This all was here before  ;)
    $entryData = array(
        'category' => $_POST['category']
      , 'title'    => $_POST['title']
      , 'article'  => $_POST['article']
      , 'when'     => time()
    );

    $pdo->prepare("INSERT INTO blogs (title, article, category, published) VALUES (?, ?, ?, ?)")
        ->execute($entryData['title'], $entryData['article'], $entryData['category'], $entryData['when']);

    // This is our new stuff
    $context = new ZMQContext();
    $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
    $socket->connect("tcp://localhost:5555");

    $socket->send(json_encode($entryData));
```


# handling ZeroMQ messages

lets try calling this src/Pusher.php
```
<?php
namespace MyApp;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Pusher implements WampServerInterface {
    /**
     * A lookup of all the topics clients have subscribed to
     */
    protected $subscribedTopics = array();

    public function onSubscribe(ConnectionInterface $conn, $topic) {
        $this->subscribedTopics[$topic->getId()] = $topic;
    }

    /**
     * @param string JSON'ified string we'll receive from ZeroMQ
     */
    public function onBlogEntry($entry) {
        $entryData = json_decode($entry, true);

        // If the lookup topic object isn't set there is no one to publish to
        if (!array_key_exists($entryData['category'], $this->subscribedTopics)) {
            return;
        }

        $topic = $this->subscribedTopics[$entryData['category']];

        // re-send the data to all the clients subscribed to that category
        $topic->broadcast($entryData);
    }

    /* The rest of our methods were as they were, omitted from docs to save space */
}
```

# push-server.php

bin/push-server.php 

```
<?php
    require dirname(__DIR__) . '/vendor/autoload.php';

    $loop   = React\EventLoop\Factory::create();
    $pusher = new MyApp\Pusher;

    // Listen for the web server to make a ZeroMQ push after an ajax request
    $context = new React\ZMQ\Context($loop);
    $pull = $context->getSocket(ZMQ::SOCKET_PULL);
    $pull->bind('tcp://127.0.0.1:5555'); // Binding to 127.0.0.1 means the only client that can connect is itself
    $pull->on('message', array($pusher, 'onBlogEntry'));

    // Set up our WebSocket server for clients wanting real-time updates
    $webSock = new React\Socket\Server('0.0.0.0:8080', $loop); // Binding to 0.0.0.0 means remotes can connect
    $webServer = new Ratchet\Server\IoServer(
        new Ratchet\Http\HttpServer(
            new Ratchet\WebSocket\WsServer(
                new Ratchet\Wamp\WampServer(
                    $pusher
                )
            )
        ),
        $webSock
    );

    $loop->run();
```

now lets try it out 

```
php bin/push-server.php
```

## terminator terminal colours

```
shift + right click -> Preferences -> Profiles -> Colors -> Solarized light
```

## Pusher class 

```
> php bin/push-server.php

PHP Fatal error:  Class MyApp\Pusher contains 6 abstract methods and must therefore be declared abstract or implement the remaining methods (Ratchet\Wamp\WampServerInterface::onCall, Ratchet\Wamp\WampServerInterface::onUnSubscribe, Ratchet\Wamp\WampServerInterface::onPublish, ...) in /home/terry/code/php-ratchet/src/Pusher.php on line 6
```

refers to src/Pusher.php missing onCall , onUnSubscribe , onPublish 

re-reading documentation again the new (src/Pusher.php) should look like this 

```
<?php
namespace MyApp;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Pusher implements WampServerInterface {
    /**
     * A lookup of all the topics clients have subscribed to
     */
    protected $subscribedTopics = array();

    public function onSubscribe(ConnectionInterface $conn, $topic) {
        $this->subscribedTopics[$topic->getId()] = $topic;
    }

    /**
     * @param string JSON'ified string we'll receive from ZeroMQ
     */
    public function onBlogEntry($entry) {
        $entryData = json_decode($entry, true);

        // If the lookup topic object isn't set there is no one to publish to
        if (!array_key_exists($entryData['category'], $this->subscribedTopics)) {
            return;
        }

        $topic = $this->subscribedTopics[$entryData['category']];

        // re-send the data to all the clients subscribed to that category
        $topic->broadcast($entryData);
    }

    /* The rest of our methods were as they were, omitted from docs to save space */
    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
    }
    public function onOpen(ConnectionInterface $conn) {
    }
    public function onClose(ConnectionInterface $conn) {
    }
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->close();
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {
    }
}

```

lets try this again 

```
> php bin/push-server.php

php /usr/local/bin/composer update
Loading composer repositories with package information
Updating dependencies
Your requirements could not be resolved to an installable set of packages.

  Problem 1
    - Root composer.json requires react/zmq 0.2.*|0.3.* -> satisfiable by react/zmq[v0.2.0, v0.3.0].
    - react/zmq[v0.2.0, ..., v0.3.0] require ext-zmq * -> it is missing from your system. Install or enable PHP's zmq extension.

To enable extensions, verify that they are enabled in your .ini files:
    - /etc/php/8.3/cli/php.ini
    - /etc/php/8.3/cli/conf.d/10-opcache.ini
    - /etc/php/8.3/cli/conf.d/10-pdo.ini
    - /etc/php/8.3/cli/conf.d/20-calendar.ini
    - /etc/php/8.3/cli/conf.d/20-ctype.ini
    - /etc/php/8.3/cli/conf.d/20-curl.ini
    - /etc/php/8.3/cli/conf.d/20-exif.ini
    - /etc/php/8.3/cli/conf.d/20-ffi.ini
    - /etc/php/8.3/cli/conf.d/20-fileinfo.ini
    - /etc/php/8.3/cli/conf.d/20-ftp.ini
    - /etc/php/8.3/cli/conf.d/20-gettext.ini
    - /etc/php/8.3/cli/conf.d/20-iconv.ini
    - /etc/php/8.3/cli/conf.d/20-phar.ini
    - /etc/php/8.3/cli/conf.d/20-posix.ini
    - /etc/php/8.3/cli/conf.d/20-readline.ini
    - /etc/php/8.3/cli/conf.d/20-shmop.ini
    - /etc/php/8.3/cli/conf.d/20-sockets.ini
    - /etc/php/8.3/cli/conf.d/20-sysvmsg.ini
    - /etc/php/8.3/cli/conf.d/20-sysvsem.ini
    - /etc/php/8.3/cli/conf.d/20-sysvshm.ini
    - /etc/php/8.3/cli/conf.d/20-tokenizer.ini
You can also run `php --ini` in a terminal to see which files are used by PHP in CLI mode.
Alternatively, you can run Composer with `--ignore-platform-req=ext-zmq` to temporarily ignore these required extensions.
```

package php-zmq is missing as it is an executable , we can install it in debian using
```
sudo apt install php-zmq
```

we can now update the project directory

```
terry@terry-MS-7D96:~/code/php-ratchet$ php /usr/local/bin/composer update
Loading composer repositories with package information
Updating dependencies
Lock file operations: 2 installs, 6 updates, 0 removals
  - Downgrading evenement/evenement (v3.0.2 => v2.1.0)
  - Downgrading react/dns (v1.13.0 => v1.1.0)
  - Downgrading react/event-loop (v1.5.0 => v0.4.3)
  - Downgrading react/promise (v3.3.0 => v2.11.0)
  - Locking react/promise-timer (v1.6.0)
  - Downgrading react/socket (v1.16.0 => v1.3.0)
  - Downgrading react/stream (v1.4.0 => v1.1.1)
  - Locking react/zmq (v0.3.0)
Writing lock file
Installing dependencies from lock file (including require-dev)
Package operations: 2 installs, 6 updates, 0 removals
  - Downloading react/event-loop (v0.4.3)
  - Downloading evenement/evenement (v2.1.0)
  - Downloading react/stream (v1.1.1)
  - Downloading react/promise (v2.11.0)
  - Downloading react/promise-timer (v1.6.0)
  - Downloading react/dns (v1.1.0)
  - Downloading react/socket (v1.3.0)
  - Downloading react/zmq (v0.3.0)
 0/8 [>---------------------------]   0% 6/8 [=====================>------]  75% 8/8 [============================] 100%  - Downgrading react/event-loop (v1.5.0 => v0.4.3): Extracting archive
  - Downgrading evenement/evenement (v3.0.2 => v2.1.0): Extracting archive
  - Downgrading react/stream (v1.4.0 => v1.1.1): Extracting archive
  - Downgrading react/promise (v3.3.0 => v2.11.0): Extracting archive
  - Installing react/promise-timer (v1.6.0): Extracting archive
  - Downgrading react/dns (v1.13.0 => v1.1.0): Extracting archive
  - Downgrading react/socket (v1.16.0 => v1.3.0): Extracting archive
  - Installing react/zmq (v0.3.0): Extracting archive
 0/8 [>---------------------------]   0% 8/8 [============================] 100%Generating autoload files
8 packages you are using are looking for funding.
Use the `composer fund` command to find out more!
No security vulnerability advisories found.
terry@terry-MS-7D96:~/code/php-ratchet$ 
```

looks like it did something.

lets try again ..

```
> php bin/push-server.php
```
so this seems to be waiting for input 

# javascript kittens html file

src/kittens.html 

```
<script src="https://gist.githubusercontent.com/cboden/fcae978cfc016d506639c5241f94e772/raw/e974ce895df527c83b8e010124a034cfcf6c9f4b/autobahn.js"></script>
<script>
    /* ab.Session undefined 
    var conn = new ab.Session('ws://localhost:8080',
	 */
	 var conn = new autobahn.Session('ws://localhost:8080',
        function() {
            conn.subscribe('kittensCategory', function(topic, data) {
                // This is where you would add the new article to the DOM (beyond the scope of this tutorial)
                console.log('New article published to category "' + topic + '" : ' + data.title);
            });
        },
        function() {
            console.warn('WebSocket connection closed');
        },
        {'skipSubprotocolCheck': true}
    );
</script>
```

# curl POST requests

we can pass json data to make a post request

```
curl --json '{"name": "Jonn"}' --json '{"age": "36"}'  http://example.com/api/
```


```
curl --json '{"category": "Books"}' --json '{"title": "Robinson crusoe"}' --json '{"article": "Many a times..."}' http://localhost:8080/src/post.php 
```

# test post using php

```
<?php
    // post.php ???
    // This all was here before  ;)
    $entryData = array(
        'category' => 'the category goes here'
      , 'title'    => 'the title goes here'
      , 'article'  => 'this is my article contents'
      , 'when'     => time()
    );

    /*  not using a database just yet so we can comment out this code 	
	$pdo->prepare("INSERT INTO blogs (title, article, category, published) VALUES (?, ?, ?, ?)")
        ->execute($entryData['title'], $entryData['article'], $entryData['category'], $entryData['when']);
    */

    // This is our new stuff
    $context = new ZMQContext();
    $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
    $socket->connect("tcp://localhost:5555");

    $socket->send(json_encode($entryData));
```

# debugging , so what are we supposed to do ? 

lets have a simple html submit form that will use post.php 

src/simple-form.html
```
<!DOCTYPE HTML>
<html>  
<body>

<form action="post.php" method="post">
<!-- want a hidden category => 'kittensCategory' -->
<input type="hidden" id="category" name="category" value="kittensCategory" />
Title: <input type="text" name="title"><br>
Article: <input type="text" name="article"><br>
<!-- also published field (the time of post) but that filled in by post.php -->
<input type="submit">
</form>

</body>
</html>

```

lets start the php push server
```
php bin/push-server.php
```

lets start a php web server in directory of src 
starts localhost port 80 default for http traffic with document root . in current directory 
which will be the src directory
```
cd src
sudo php -S 127.0.0.1:80 -t .
```

open a web browser at 
```
localhost/kittens.html
```
and open javascript console


open a second web browser tab at 
```
localhost/simple-form.html
```
this will run html code at src/simple-form.html

fill in the title and article values should initiate a post request to (post.php)

if we now look back at kittens.html page we should see in console 

```
New article published to category "kittensCategory" : a simple title22222
```

# success on push integration stage ratchet php zeromq


# PHP Login 

cookies are on users computer can be compromised easily

using session stored on server may be little more secure

create initial database myDB 

```
<?php
$servername = "localhost";
$username = "username";
$password = "password";

// Create connection - this is a mysql database then 
$conn = new mysqli($servername, $username, $password);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Create database myDB
$sql = "CREATE DATABASE myDB";
if ($conn->query($sql) === TRUE) {
  echo "Database created successfully";
} else {
  echo "Error creating database: " . $conn->error;
}

$conn->close();
?>

```

# install MySQL executables

```
sudo apt install php-mysql
sudo apt install mysql-client mysql-server
```

## start mysql client

```
> mysql
ERROR 1045 (28000): Access denied for user 'terry'@'localhost' (using password: NO)
```

stop the mysql server
```
> service mysql status
> service mysql stop
... enter root password linux ...
```

make a text file 

set-root.txt
```
ALTER USER 'root'@'localhost' IDENTIFIED BY 'MyNewPass';
```

```
mysqld --init-file=set-root.txt
```

# on debian 

restart the mysql server
```
service mysql start
```

we can gain access using a defaults-file

```
sudo mysql --defaults-file=/etc/mysql/debian.cnf
```


now for some mysql magic
```
mysql> use mysql;
Reading table information for completion of table and column names
You can turn off this feature to get a quicker startup with -A

> select User , Host , plugin from mysql.user ;
+------------------+-----------+-----------------------+
| User             | Host      | plugin                |
+------------------+-----------+-----------------------+
| debian-sys-maint | localhost | caching_sha2_password |
| mysql.infoschema | localhost | caching_sha2_password |
| mysql.session    | localhost | caching_sha2_password |
| mysql.sys        | localhost | caching_sha2_password |
| root             | localhost | auth_socket           |
+------------------+-----------+-----------------------+
5 rows in set (0.00 sec)

```

```
> sudo mysql -uroot 
Welcome to the MySQL monitor.  Commands end with ; or \g.
Your MySQL connection id is 16
Server version: 8.0.43-0ubuntu0.24.04.1 (Ubuntu)

Copyright (c) 2000, 2025, Oracle and/or its affiliates.

Oracle is a registered trademark of Oracle Corporation and/or its
affiliates. Other names may be trademarks of their respective
owners.

Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.

```

so we need to create a new user with password

```
> CREATE USER 'user'@'localhost';
> CREATE DATABASE myDB;
```

set user password to secret
```
>ALTER USER 'user'@'localhost' IDENTIFIED BY 'secret';
Query OK, 0 rows affected (0.01 sec)

> FLUSH PRIVILEGES;
Query OK, 0 rows affected (0.00 sec)

```

# Mysql usage

ok so we have a user and password to access mysql 

```
mysql -uuser -p
Enter password: 
Welcome to the MySQL monitor.  Commands end with ; or \g.
Your MySQL connection id is 17
Server version: 8.0.43-0ubuntu0.24.04.1 (Ubuntu)

Copyright (c) 2000, 2025, Oracle and/or its affiliates.

Oracle is a registered trademark of Oracle Corporation and/or its
affiliates. Other names may be trademarks of their respective
owners.

Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.

mysql> use myDB;
Database changed
mysql> show tables;
Empty set (0.00 sec)
```

# Mysql login 

what tables need for authentication login ?

# PHP Login 

[https://codeshack.io/secure-login-system-php-mysql/#authenticatinguserswithphp]


```
cd phplogin
sudo php -S 127.0.0.1:80 -t .
```

on successful login - index.php will redirect to home.php 

will keep doing so until user logs out

# Additional Security Tips and Resources

```

shamelessly pulled from 
https://codeshack.io/secure-login-system-php-mysql/#authenticatinguserswithphp

Additional Tips and Resources
Further increase security with our tips and resources below.

Always use the htmlspecialchars() function to escape user input.
Place the connection details inside a single file that's outside of the webroot directory to further increase security.
Secure Session INI Settings: https://www.php.net/manual/en/session.security.ini.php
Never use XAMPP for production purposes because it's not designed for such.
Always use HTTPS and have a dedicated SSL certificate.
Use PHP's error_reporting(0) in production to suppress error messages and log errors to a file or database for review by developers.
Add CSRF tokens to your forms to prevent cross-site request forgery attacks.
Always use prepared statements to prevent SQL injection attacks.
Use password_hash() and password_verify() to hash passwords.
Use session_regenerate_id() to prevent session fixation attacks.
```






