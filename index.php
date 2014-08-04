<?php
require 'vendor/autoload.php';

// generate appKey: https://trello.com/1/appKey/generate
// generate token: https://trello.com/1/authorize?response_type=token&name=BitBucket+Trello+Hook&scope=read,write&expiration=never&key=[your-key-here]

$app = new \Slim\Slim();

$app->configureMode(
    'production', 
    function () use ($app) {
        $config = json_decode(file_get_contents('config.json'), true);
        $app->config($config);
    }
);

// Only invoked if mode is "development"
$app->configureMode(
    'dev', 
    function () use ($app) {
        $config = json_decode(file_get_contents('config.json'), true);
        $app->config($config);
    }
);

$app->get('/', function () {
	echo "Hello BitbucketTrelloHook!";
});

$app->post('/webhook/:boardId', function ($boardId) use ($app) {
	// $date = date('Y-m-d-His');
	// file_put_contents("posts/post-$date.txt", json_encode($app->request->post('payload')));
	$payload = json_decode($app->request->post('payload'));
	if ($payload != null) {
		$bitbucketurl = $payload->canon_url.$payload->repository->absolute_url.'commits/';
		foreach ($payload->commits as $commit) {
			$parser = new \BitbucketTrelloHook\Parser($commit->message);
			$isdone = $parser->isDone();
			$cardIds = $parser->getCardIds();

			if($isdone && !empty($cardIds)) {
				$client = new \BitbucketTrelloHook\Client($boardId, $cardIds, $commit, $app, $bitbucketurl);
				$client->moveCards();
				$client->addComment();
			} else if (!empty($cardIds)) {
				$client->addComment();
			} else {
				$app->halt(400, 'No action and no cardId found');
			}
			
		}
	} else {
		$app->halt(400, 'No commit payload recieved');
	}
});

$app->run();