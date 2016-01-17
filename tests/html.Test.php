<?php

/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

 
class TestHTML extends PHPUnit_Framework_TestCase 
{

	var $html1 = '<html>
	<head>
		<title>Example</title>
	</head>
	<body>
		<h1>Example Title</h1>
		<hr>
		<p>A paragraph</p>
	</body>
</html>
';

	function testHTMLBasics() 
	{
		$doctype = \arc\html::doctype();
		$this->assertEquals( (string) $doctype, '<!doctype html>' );
		$comment = \arc\html::comment('A comment');
		$this->assertEquals( (string) $comment, '<!-- A comment -->' );
	}

	function testHTMLWriter() 
	{
		$html = \arc\html::ul( [ 'class' => 'menu' ],
			\arc\html::li('menu 1')
			->li('menu 2')
		);
		$this->assertEquals( ''.$html, "<ul class=\"menu\"><li>menu 1</li><li>menu 2</li></ul>" );
	}

	function testHTMLParsing() 
	{
		$html = \arc\html::parse( $this->html1 );
		$error = null;
		$htmlString = ''.$html;
		$html2 = \arc\html::parse( $htmlString );
		$this->assertEquals( $html->head->title, '<title>Example</title>' );
		$this->assertTrue( $html->head->title->nodeValue == 'Example' );
		$this->assertEquals( $html->head->title.'', $html2->head->title.'' );
		$this->assertTrue( $html->head->title->nodeValue == 'Example' );
	}

	function testHTMLFind() 
	{
		$html = \arc\html::parse( $this->html1 );
		$title = $html->find('head title')[0];
		$this->assertEquals( $title->nodeValue, 'Example' );
	}

	function testDomMethods()
	{
		$html = \arc\html::parse( $this->html1 );
		$title = $html->getElementsByTagName('title')[0];
		$this->assertEquals( $title->nodeValue, 'Example' );
	}

	function testHTMLFormBuilder()
	{
		$formFields = [
			'Name',
			'name2' => 'A label for name2',
			'textarea' => [
				'type' => 'textarea',
				'label' => 'A textarea'
			] 
		];
		$form = \arc\html::formBuilder($formFields);
		$formHtml = (string) $form;
		$parsed = \arc\html::parse($formHtml);
		$this->assertEquals( 3, count($parsed->children()) );
		$Name = $parsed->find('#Name')[0];
		$this->assertEquals( $Name['id'], 'Name');
	}
	
}