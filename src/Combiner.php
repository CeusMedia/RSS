<?php
class CMM_RSS_Combiner{

	protected $feeds	= array();
	
	public function add( $rss ){
		$this->feeds[]	= CMM_RSS_Parser::parse( $rss );
	}

	public function addUrl( $url ){
		$this->add( Net_Reader::readUrl( $url ) );
	}

	public function combine( $limit = 3 ){
		$list	= array();
		foreach( $this->feeds as $channel ){
			foreach( $channel->getItems() as $item ){
				$timestamp	= 0;
				if( $item->getDate() )
					$timestamp	= $item->getDate();
				if( !isset( $list[$timestamp] ) )
					$list[$timestamp]	= array();
				$list[$timestamp][]	= $item;
			}
		}
		krsort( $list );
		$items	= array();
		foreach( $list as $timestamp => $entries ){
			foreach( $entries as $entry )
				$items[]	= $entry;
		}
		$items		= array_slice( $items, 0, $limit );
		return $items;
	}

	public function render( $channel, $limit = 3 ){
		foreach( $this->combine( $limit ) as $item )
			$channel->addItem( $item );
		return CMM_RSS_Renderer::render( $channel );
	}
}
?>