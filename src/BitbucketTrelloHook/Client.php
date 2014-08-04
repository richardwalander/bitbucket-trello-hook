<?php

namespace BitbucketTrelloHook;

use \GuzzleHttp\Client as GuzzleClient;
use \GuzzleHttp\Event\CompleteEvent;

/**
* 
*/
class Client
{
	
	protected $baseurl = 'https://api.trello.com/1';

	function __construct($boardId, $cardIds, $commit, $app, $bitbucketurl)
	{
		$this->boardId = $boardId;
		$this->cardIds = $cardIds;
		$this->commit = $commit;
		$this->client = new GuzzleClient();
		$this->app = $app;
		$this->listId = '';
		$this->bitbucketurl = $bitbucketurl;
	}

	private function signURL($url)
	{
		$url .= (count(explode('?', $url)) >= 2 ? "&token=".$this->app->config('token')."&key=".$this->app->config('key') : "?token=".$this->app->config('token')."&key=".$this->app->config('key')); 
		return $url;
	}

	public function moveCards()
	{
		$response = $this->client->get($this->signURL($this->baseurl.'/boards/'.$this->boardId.'/lists'));
		$lists = $response->json();
		foreach ($lists as $list) {
			if ($list['name'] == $this->app->config('list')) {
				$this->listId = $list['id'];
			}
		}

		if (!empty($this->listId)) {
			$requests = [];
			foreach ($this->cardIds as $cardId) {
				$response = $this->client->put($this->signURL($this->baseurl.'/cards/'.$cardId.'/idList?value='.$this->listId));
				echo $response;
			}
		} else {
			$this->app->halt(500, 'Could not find list with name '.$app->config('list'));
		}
	}

	public function addComment()
	{
		foreach ($this->cardIds as $cardId) {
			$msg = $this->app->config('emoji').' ['.$this->commit->node.']('.$this->bitbucketurl.$this->commit->node.') '.$this->commit->message;
			$response = $this->client->post($this->signURL($this->baseurl.'/cards/'.$cardId.'/actions/comments'), [
				'body' => [
					'text' => $msg
				]
			]);
			echo $response;
		}
	}
}