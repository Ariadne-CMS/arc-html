<?php

/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace arc;

class html extends xml
{
	static private $formBuilder=null;
	static public $writer=null;	
		
	static public function __callStatic( $name, $args ) 
	{
		if ( !isset(self::$writer) ) {
			self::$writer = new html\Writer();
		}
		return call_user_func_array( [ self::$writer, $name ], $args );
	}

	static public function parse( $html=null, $encoding = null ) 
	{
		$parser = new html\Parser();
		return $parser->parse( $html, $encoding );
	}

}
