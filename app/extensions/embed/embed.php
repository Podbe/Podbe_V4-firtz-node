<?php 

	function embed_init() {
		
		global $f3;
		$f3->set('XFRAME','GOFORIT');
		
	}

	function embed_run() {
		global $f3;
		

		if ($f3->get('epi') == '') $f3->set('epi','latest');
		if ($f3->get('epi') == 'latest') {
			$newitems = $f3->get('items');
			$f3->set('items',array($newitems[0]));
			return;
		}	
		
		$newitems = array();
		$epi_slug = $f3->get('epi');
		foreach ($f3->get('items') as $item) {
		
			if ($item['slug'] == $epi_slug) {
				$newitems[] = $item;
				break;
			}
		
		}	
		$f3->set('items',$newitems);
		
	}
	
	
	

?>