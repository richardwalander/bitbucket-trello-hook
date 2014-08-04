<?php

namespace BitbucketTrelloHook;

/**
* 
*/
class Parser
{
	
	protected $defaults = [
		'close',
		'closes',
		'closed',
		'closing',
		'fix',
		'fixed',
		'fixes',
		'fixing',
		'resolve',
		'resolves',
		'resolved',
		'resolving'
	];

	function __construct($message, $keywords = [])
	{
		$this->message = $message;
		$this->keywords = array_merge($this->defaults, $keywords);
	}

	private function getHashTags($text) {
		//Match the hashtags
		preg_match_all('/(^|[^a-z0-9_])#([a-z0-9_]+)/i', $text, $matchedHashtags);
		$hashtag = '';
		// For each hashtag, strip all characters but alpha numeric
		if (!empty($matchedHashtags[0])) {
			foreach($matchedHashtags[0] as $match) {
				$hashtag .= preg_replace("/[^a-z0-9]+/i", "", $match).',';
			}
		}
		//to remove last comma in a string
		$hashtag = rtrim($hashtag, ',');
		return explode(',', $hashtag);
	}
	
	public function getCardIds()
	{
		return $this->getHashTags($this->message);
	}

	public function isDone()
	{
		return preg_match( '/(\b' . implode( '\b|\b', $this->keywords ) . '\b)/i', $this->message);
	}
}