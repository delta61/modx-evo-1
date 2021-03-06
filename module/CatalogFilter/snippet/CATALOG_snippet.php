<?php
//============================================================================
$catalog_koren= 17;
$catalog_template= 2;
$MaxItemsInPage= 100;
//============================================================================

	
$myid= $modx->documentIdentifier;
if( empty( $id ) ) $id= $myid;
//$doc= $modx->getDocument( $id, 'id,pagetitle,isfolder,content,introtext' );
$doc= $modx->runSnippet( 'GetDoc6', array( 'ids'=>$id, 'type'=>'this', 'fields'=>'pagetitle,isfolder,content,introtext,parent', 'tvfileds'=>'', 'isf'=>'all' ) );
$doc= $doc[ $id ];


//$lvl= $modx->runSnippet( 'GetLvl', array( 'koren'=>$catalog_koren, 'id'=>$id ) );
//$maincategory= $modx->runSnippet( 'GetIdOnLvl', array( 'koren'=>$catalog_koren, 'id'=>$id ) );


$ParentsPublishedList_flag= $modx->runSnippet( 'ParentsPublishedList', array( 'koren'=>$catalog_koren, 'id'=>$id ) );
if( $lvl && ! $ParentsPublishedList_flag ) $modx->sendErrorPage();


if( $modx->catalogFilterListX ) $filterpr= $modx->catalogFilterListX;
if( $filterpr )
{
	$filterparam_flag= true;
	$filter_ids= $modx->runSnippet( 'CatalogFilter_Items', array( 'id'=>$id, 'filterpr'=>$filterpr ) );
}
//$print .= print_r( $filter_ids, 1 );


$page= intval( $_GET[ 'p' ] );
if( $page > 1 && $page < 1000 ){}else{ $page= 1; }
$page_s= ( $page - 1 ) * $MaxItemsInPage;


if( $doc[ 'isfolder' ] == 1 )
{
	if( $filterparam_flag )
	{
		$docs= array();
		$docs2= $modx->runSnippet( 'GetDoc6', array( 'ids'=>$id, 'idslist'=>$filter_ids[0], 'type'=>'childs', 'fields'=>'pagetitle,isfolder,introtext,menuindex,parent',
											   'tvfileds'=>'cat_img,cat_price', 'isf'=>'0', 'sort'=>'menuindex', 'limit'=>$page_s.",".$MaxItemsInPage ) );
		
		$docs_items_count= $modx->runSnippet( 'GetDoc6', array( 'ids'=>$id, 'idslist'=>$filter_ids[0], 'type'=>'childs', 'isf'=>'0' ) );
		$docs_items_count= count( $docs_items_count );
		
	}else{
		$docs= $modx->runSnippet( 'GetDoc6', array( 'ids'=>$id, 'type'=>'childs', 'depth'=>'1', 'fields'=>'pagetitle,isfolder', 'isf'=>'1', 'sort'=>'menuindex' ) );
		
		$docs2= $modx->runSnippet( 'GetDoc6', array( 'ids'=>$id, 'type'=>'childs', 'depth'=>'1', 'fields'=>'pagetitle,isfolder,introtext,menuindex,parent',
												'tvfileds'=>'cat_img,cat_price', 'isf'=>'0', 'sort'=>'menuindex', 'limit'=>$page_s.",".$MaxItemsInPage ) );
		
		$docs_items_count= $modx->runSnippet( 'GetDoc6', array( 'ids'=>$id, 'type'=>'childs', 'depth'=>'1', 'isf'=>'0' ) );
		$docs_items_count= count( $docs_items_count );
	}
	
	if( $docs && $docs2 ) $docs= array_merge( $docs, $docs2 ); elseif( $docs2 ) $docs= $docs2;
	
	if( $docs )
	{
		$ii= 0;
		$iii= 0;
		foreach( $docs AS $row )
		{
			if( $row[ 'isfolder' ] == 1 || $itemprinttype == 'category' )
			{
				$ii++;
				$categories .= $modx->runSnippet( 'CAT_ITEM', array( 'type'=>'category', 'row'=>$row, 'last'=>( $ii % ( $indexcatalog || $id == 17 ? 4 : 3 ) == 0 ? true : false ), 'indexcatalog'=>$indexcatalog ) );
				
			}else{
				$iii++;
				$items .= $modx->runSnippet( 'CAT_ITEM', array( 'type'=>'item', 'row'=>$row, 'last'=>( $iii % 4 == 0 ? true : false ) ) );
			}
		}
	}
	
	if( ! empty( $doc[ 'content' ] ) ) $content .= $doc[ 'content' ];
	
}elseif( true ){
	$iteminfo= $modx->runSnippet( 'GetDoc6', array( 'ids'=>$id, 'type'=>'this', 'fields'=>'pagetitle,isfolder,content,introtext,parent', 'tvfileds'=>'cat_img,cat_price,cat-opis,har', 'isf'=>'all' ) );
	$item_page .= $modx->runSnippet( 'CAT_ITEM', array( 'type'=>'itempage', 'row'=>$iteminfo[ $id ] ) );
}


if( $type != 'content' ) $print .= '<div id="ajax_content">';


$print .= '<div id="catalog" class="catalog '.( $indexcatalog ? 'catalogindex' : 'catalogpage' ).'" itemscope itemtype="http://schema.org/Product">';
if( ! empty( $categories ) )
{
	$print .= $categories;
	$print .= '<div class="clr">&nbsp;</div>';
	if( ! empty( $items ) ) $print .= '<br /><br />';
}
if( ! empty( $items ) )
{
	$print .= $items;
	$print .= '<div class="clr">&nbsp;</div>';
}
if( ! empty( $item_page ) )
{
	$print .= $item_page;
}
$print .= '</div>';


if( $type != 'content' )
{
	$print .= '<div class="clr">&nbsp;</div>';
	$print .= '<div class="category_content">'. $content .'</div>';
	$print .= '<div class="clr">&nbsp;</div>';
}


if( $type != 'content' ) $print .= '</div>';


return $print;
?>
