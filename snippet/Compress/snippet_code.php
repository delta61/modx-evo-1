<?php
$varsion= 'v11';
//18.06.2016
//Compress
/*	&compress=true/false
	&file - компрессит в filename.compress.css один файл
	&files - компрессит в all.compress.css все указанные файлы
	&tofile - файл, в который комперссить все указанные файлы
	&print=false/true - выводить код, а не путь к файлу
	&r=false/true - принудительно пересоздает компресс-файлы
	&rvars=false/true - замена переменных
	[!Compress? &file=`css/styles.css`!]
	[!Compress? &files=`css: styles.css, catalog.css; css2: shop.css; css3/dop.css` &tofile=`css/all.compress.css`!]
*/
//============================================================================
$strtr[ '.css' ]= array(
);
$strtr[ '.js' ]= array(
);
$pregreplace[ '.css' ][0]= array(
	"/\/\*(.*)\*\//sU" => "",
	"/[\s]{2,}/" => " ",
	"/[\s]*([\(\){\}\[\];:])[\s]*/" => '${1}',
	"/[\s]*([,>])[\s]*/" => '${1}',
	"/([^0-9])0px/" => '${1}0',
	"/;\}/" => '}',
);
$pregreplace[ '.css' ][1]= array(
);
$pregreplace[ '.js' ][0]= array(
	"/\/\/(.*)$/mU" => "",
	"/\/\*(.*)\*\//sU" => "",
);
$pregreplace[ '.js' ][1]= array(
	"/[\s]{2,}/" => " ",
	"/[\s]*([\(\){\}\[\];:])[\s]*/" => '${1}',
	"/[\s]*([<,:>])[\s]*/" => '${1}',
	"/[\s]*([=+!\/-])[\s]*/" => '${1}',
	"/[\s]*([?|\*])[\s]*/" => '${1}',
);
//============================================================================
if( true )
{
	$slash= ( substr( ( $file ? $file : $files ), 0, 1 ) == "/" ? false : true );
	$root= rtrim( MODX_BASE_PATH, "/\\" ) . ( $slash ? '/' : '' );
	if( $file )
	{
		$filetype= substr( $file, strrpos( $file, '.' ) );
		$file_to= substr( $file, 0, strrpos( $file, '.' ) ) .'.compress'. $filetype;
		$filesarray[]= $file;
		if( ! file_exists( $root . $file_to ) || filemtime( $root . $file ) > filemtime( $root . $file_to ) ) $refresh= true;
	}else{
		$filetype= substr( $files, strrpos( $files, '.' ) );
		$file_to= ( $tofile ? $tofile : 'all.compress'.$filetype );
		$tmp1= explode( ';', $files );
		foreach( $tmp1 AS $row1 )
		{
			$tmp2= explode( ':', trim( $row1 ) );
			if( count( $tmp2 ) == 1 )
			{
				$filepath= trim( $row1 );
				$filesarray[]= $filepath;
				if( ! file_exists( $root . $file_to ) || filemtime( $root . $filepath ) > filemtime( $root . $file_to ) ) $refresh= true;
			}else{
				$tmp3= explode( ',', $tmp2[ 1 ] );
				foreach( $tmp3 AS $row3 )
				{
					$filepath= $tmp2[ 0 ] . trim( $row3 );
					$filesarray[]= $tmp2[ 0 ] . trim( $row3 );
					if( ! file_exists( $root . $file_to ) || filemtime( $root . $filepath ) > filemtime( $root . $file_to ) ) $refresh= true;
				}
			}
		}
	}
	if( isset( $strtr[ $filetype ] ) ) $strtr_type= $strtr[ $filetype ];
	if( isset( $pregreplace[ $filetype ][0] ) ) $pregreplace_type_0= $pregreplace[ $filetype ][0];
	if( isset( $pregreplace[ $filetype ][1] ) ) $pregreplace_type_1= $pregreplace[ $filetype ][1];
}
//============================================================================
$refresh= ( $refresh || ! empty( $r ) ? true : false );
if( $refresh && $filesarray )
{
	$size_before= 0;
	$file_to_handle= fopen( $root . $file_to, 'w' );
	if( $files ) fwrite( $file_to_handle, "/*{$files}*/\n\n" );
	foreach( $filesarray AS $filerow )
	{
		$size_before += filesize( $root . $filerow );
	}
	foreach( $filesarray AS $filerow )
	{
		$filecontent= "";
		$file_handle= fopen( $root . $filerow, 'r' );
		if( $file_handle )
		{
			while( ! feof( $file_handle ) ) $filecontent .= fread( $file_handle, 1024*64 );
			fclose( $file_handle );
			if( $filecontent )
			{
				if( $compress !== 'false' )
				{
					if( $pregreplace_type_0 )
					{
						foreach( $pregreplace_type_0 AS $pattern => $replacement )
							$filecontent= preg_replace( $pattern, $replacement, $filecontent );
					}
					if( $filetype == '.css' ) if( $strtr_type ) $filecontent= strtr( $filecontent, $strtr_type );
					
					if( $filetype != '.css' )
					{
						$parts= array();
						$kovpos= $curpos= 0;
						$string_flag= false;
						while( true )
						{
							$kov1= ( $string_flag === '2' ? false : strpos( $filecontent, "\"", $curpos+1 ) );
							$kov2= ( $string_flag === '1' ? false : strpos( $filecontent, "'", $curpos+1 ) );
							if( $kov1 === false && $kov2 === false )
							{
								$parts[]= array( substr( $filecontent, $kovpos ).( $string_flag === '1' ? "\"" : ( $string_flag === '2' ? "'" : '' ) ), ( $string_flag ? $string_flag : false ) );
								break;
							}else{
								if( $kov1 === false ) $kov1= $kov2 + 1;
								if( $kov2 === false ) $kov2= $kov1 + 1;
								$curpos= ( $kov1 < $kov2 ? $kov1 : $kov2 );
								$ii= 1; $cc= 0;
								if( $string_flag )
								{
									while( substr( $filecontent, $curpos-$ii, 1 ) == "\\" )
									{
										$ii++; $cc++;
									}
								}
								$vse_eshe_text= ( $string_flag && $cc%2!=0 ? true : false );
								if( ! $string_flag || ( ! $parts[count($parts)-1][1] && ! $vse_eshe_text ) )
								{
									$parts[]= array( substr( $filecontent, $kovpos+( $string_flag ? 1 : 0 ), $curpos-($kovpos+( $string_flag ? 1 : -1 )) ),
												( $string_flag ? $string_flag : false ) );
									$string_flag= ( $string_flag ? false : ( $kov1 < $kov2 ? '1' : '2' ) );
									$kovpos= $curpos;
								}
							}
						}
						
						if( $rvars === 'true' )
						{
							preg_match_all( "/var [a-zA-Z0-9_]+?/U", $filecontent, $matches );
							if( $matches )
							{
								foreach( $matches[0] AS $row )
								{
									$var= str_replace( 'var ', '', $row );
									$vars[ $var ]= true;
								}
								foreach( $matches[0] AS $row )
								{
									$var= str_replace( 'var ', '', $row );
									do{ $varnum++; }while( $vars[ '_'.$varnum ] );
									$pregreplace_type_1[ "/([^a-zA-Z0-9_])(". $var .")([^a-zA-Z0-9_])/U" ]= '${1}_'.$varnum.'${3}';
								}
							}
						}
						
						$filecontent= '';
						if( $parts )
						{
							foreach( $parts AS $part )
							{
								if( ! $part[1] )
								{
									if( $pregreplace_type_1 )
									{
										foreach( $pregreplace_type_1 AS $pattern => $replacement )
											$part[0]= preg_replace( $pattern, $replacement, $part[0] );
									}
									if( $strtr_type ) $part[0]= strtr( $part[0], $strtr_type );
								}
								$filecontent .= $part[0];
							}
						}
					}
				}
				fwrite( $file_to_handle, "/*{$filerow}*/\n".$filecontent."\n\n" );
			}
		}
	}
	$size_after= filesize( $root . $file_to );
	//$md5_after= md5_file( $root . $file_to );
	fwrite( $file_to_handle, "/*Compress {$varsion} - ".round( $size_after * 100 / $size_before )."%".( $md5_after ? " - ".$md5_after : "" )."*/" );
	fclose( $file_to_handle );
}
//============================================================================
if( $print === 'true' )
{
	$filecontent= '';
	$file_to_handle= fopen( $root . $file_to, 'r' );
	while( ! feof( $file_to_handle ) ) $filecontent .= fread( $file_to_handle, 1024*64 );
	fclose( $file_to_handle );
	return $filecontent;
}else return $file_to;
?>
