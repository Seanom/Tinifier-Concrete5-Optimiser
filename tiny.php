<?php 
/*

	Tinifier
	---------

	@file 		tiny.php
	@date 		2011-05-27 22:20:36 -0400 (Fri, 27 May 2011)
	@author 	Jack Lightbody <jack.lightbody@gmail.com>
	Copyright (c) 2011 Jack Lightbody <12345j.co.cc>
	@license 	Mit Open Source
	@github https://github.com/12345j/Tinifier-Concrete5-Optimiser
*/
defined( 'C5_EXECUTE' ) or die( "Access Denied." );

class TinyHelper {
		public function tinify( $content ){		
			$jsFileMerge = DIRNAME_JAVASCRIPT."/merge.js";
			$cssFileMerge = DIRNAME_CSS."/merge.css";
			if(file_exists($cssFileMerge)){
				unlink($cssFileMerge);
			}
			if(file_exists($jsFileMerge)){
				unlink($jsFileMerge);
			}
			$jsCombine=array();
			$cssCombine=array();
			$unknownCss=array();
			$unknownJs=array();
			// Get all the javascript links to files and put their content in the merge js file			

						// get all the css links
			if ( preg_match_all( '#<\s*script\s*(type="text/javascript"\s*)?src=.+<\s*/script\s*>#smUi',$content,$jsLinks )) {
				foreach ( $jsLinks[0] as $jsLink ) {
					if(preg_match('/<script type="text\/javascript" src="(.*)"><\/script>/', $jsLink )){
         				$jsItem= preg_replace('/<script type="text\/javascript" src="(.*)"><\/script>/', '$1', $jsLink);// get whats in href attr  
         				array_push($jsCombine, $jsItem);
         			}else{
         				array_push($unknownJs, $jsLink);
         			}
				}	
				foreach ($jsCombine as $js){
						$jsFile=BASE_URL.$js;
			 			$jsFileContents=file_get_contents($jsFile);
						/*
						/Compressing the js takes way too long so we just insert the uncompressed stuff.
						/TODO: Speed it up- if its a not new version then don't compress it again
						/Do this with css too
						*/
						//Loader::library( '3rdparty/jsmin' );
						//$jsCompress=JSMin::minify( $jsFileContents );	
						file_put_contents($jsFileMerge, $jsFileContents, FILE_APPEND);
				}	
				}
			// get all the inline javascript and add it to the merge js file
			if ( preg_match( '#<\s*script\s*(type="text/javascript"\s*)?>(.+)<\s*/script\s*>#smUi',$content,$inlineJs )>0 ) {
				foreach ( $inlineJs as $Inlineitem ) {
					$Inlineitem=preg_replace('#<\s*script\s*(type="text/javascript"\s*)?>#smUi', "", $Inlineitem);
					$Inlineitem=preg_replace('#<\s*/script\s*>#smUi', "", $Inlineitem);
					file_put_contents($jsFileMerge, $Inlineitem, FILE_APPEND);
				}	
			}
			// get all the css links
			if ( preg_match_all( '#<\s*link\s*rel="?stylesheet"?.+>#smUi',$content,$cssLinks )) {
				foreach ( $cssLinks[0] as $cssLink ) {
					if(preg_match('/<link rel="stylesheet" type="text\/css" href="(.*)" \/>/', $cssLink )){
         				$cssItem= preg_replace('/<link rel="stylesheet" type="text\/css" href="(.*)" \/>/', '$1', $cssLink);// get whats in href attr  
         				array_push($cssCombine, $cssItem);
         			}else{
         				array_push($unknownCss, $cssLink);
         			}
				}	
					foreach($cssCombine as $css){				
						$cssFile=BASE_URL.$css;
			 			$cssFileContents=file_get_contents($cssFile);
			 			$cssCompress=cssCompress($cssFileContents);
						$coreCssFile   = '/concrete/css';
						$cssCoreFile = strpos($cssFile, $coreCssFile);
						file_put_contents($cssFileMerge, $cssCompress, FILE_APPEND);	
					}
				}
				// get all the inline css and add to merge
				if ( preg_match( '#<\s*style.*>.+<\s*/style\s*\/?>#smUi',$content,$inlineCss )>0 ) {
					foreach ( $inlineCss as $Inlinecssitem ) {
						$Inlinecssitem=preg_replace('#<\s*style.*>#smUi', "", $Inlinecssitem);
						$Inlinecssitem=preg_replace('#<\s*/style\s*\/?>#smUi', "", $Inlinecssitem);
						file_put_contents($cssFileMerge, $Inlinecssitem, FILE_APPEND);
					}	
				}
				$content =  preg_replace('#<\s*script\s*(type="text/javascript"\s*)?>(.+)<\s*/script\s*>#smUi',"",$content);	
				$content=preg_replace(  '#<\s*link\s*rel="?stylesheet"?.+>#smUi',"",$content);
				$content=preg_replace( '#<\s*style.*>.+<\s*/style\s*\/?>#smUi',"",$content );//remove the style tags from the html
				$content=preg_replace('#<\s*script\s*(type="text/javascript"\s*)?>(.+)<\s*/script\s*>#smUi', "",$content);//same for javascript
				foreach($unknownCss as $cssU){
				$content=str_ireplace( '</head>',$cssU.'</head>', $content );	// add the stylesheet link to the head					
				}
				$content =  str_ireplace( '</head>','<link rel="stylesheet" type="text/css" href="'.ASSETS_URL_WEB.'/css/merge.css" /><!--Compressed by Tinifier v1.2--></head>', $content );	// add the stylesheet link to the head
				$content =  str_ireplace( '</body>','<script type="text/javascript" src="'.ASSETS_URL_WEB.'/js/merge.js"></script></body>', $content );	// add the script link to the footer
				$content = preg_replace('/(?:(?<=\>)|(?<=\/\)))(\s+)(?=\<\/?)/','',$content);//remove html whitespace
				return $content;	
		}}
		function cssCompress($string) {
		/* remove comments */
		    $string = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $string);
		/* remove tabs, spaces, new lines, etc. */        
		    $string = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $string);
		/* remove unnecessary spaces */        
		    $string = str_replace('{ ', '{', $string);
		    $string = str_replace(' }', '}', $string);
		    $string = str_replace('; ', ';', $string);
		    $string = str_replace(', ', ',', $string);
		    $string = str_replace(' {', '{', $string);
		    $string = str_replace('} ', '}', $string);
		    $string = str_replace(': ', ':', $string);
		    $string = str_replace(' ,', ',', $string);
		    $string = str_replace(' ;', ';', $string); 
			if ($cssCoreFile !== false) {// check if its a core css file. If it is then we replace the link to the core images folder 
				$string = str_replace('../images/', BASE_URL.DIR_REL.'/concrete/images/', $string);
			} 
		return $string;
		}
