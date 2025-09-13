<?php
// post.php ???
// This all was here before  ;)
$entryData = array(
    'category' => 'kittensCategory'
    , 'title'    => 'second title'
    , 'article'  => 'a much larger article'
    , 'when'     => time()
);

/*what is pdo - where did it come from ? */

/*
  $pdo->prepare("INSERT INTO blogs (title, article, category, published) VALUES (?, ?, ?, ?)")
  ->execute($entryData['title'], $entryData['article'], $entryData['category'], $entryData['when']);
*/


// This is our new stuff
$context = new ZMQContext();
$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
$out = $socket->connect("tcp://localhost:5555");
if ($out) {
    echo "socket connected" ;    
}
else {
    echo "socket did not connect " ;    
}

$socket->send(json_encode($entryData));
echo "sent data" ;
    



        


