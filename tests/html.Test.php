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
	private $i18n      = 'Iñtërnâtiônàlizætiøn';
	private $entities  = "I&ntilde;t&euml;rn&acirc;ti&ocirc;n&agrave;liz&aelig;ti&oslash;n";
	private $html1     = '';

	function setup() {
		$this->html1 = <<<EOS
<html>
	<head>
		<title>Example</title>
	</head>
	<body>
		<h1>Example Title</h1>
		<hr>
		<p>A paragraph</p>
		<p id="utf8">{$this->i18n}</p>
		<p id="entities">{$this->entities}</p>
	</body>
</html>
EOS;
	}

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
            \arc\html::li('menu 1 ',
            	\arc\html::input(['type' => 'radio', 'checked'])
            )
            ->li('menu 2')
        );
        $this->assertEquals(
            "<ul class=\"menu\">\r\n\t<li>\r\n\t\tmenu 1 <input type=\"radio\" checked>\r\n\t</li>"
            ."\r\n\t<li>menu 2</li>\r\n</ul>",
            ''.$html
        );
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

	function testEncoding() {
		$html = \arc\html::parse( $this->html1, 'UTF-8' );
		$elm = $html->find('#utf8')[0];
		$this->assertEquals($this->i18n, $elm->nodeValue );
	}

	function testEntities() {
		$html = \arc\html::parse( $this->html1, 'UTF-8' );
		$elm = $html->find('#entities')[0];
		$this->assertEquals($this->entities, $elm->nodeValue );
	}

}
