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

    function testEncoding()
    {
        $euro = "\xc2\x80";
        $htmlString = "<html><head><title>Encodingtest</title></head><body>$euro</body></html>";
        $html = \arc\html::parse($htmlString, "utf-8");
        $euroEl = $html->body->nodeValue;
        $this->assertEquals( $euro, (string) $euroEl );
        $htmlString = "<html><body>$euro</body></html>";
        $html = \arc\html::parse($htmlString, "utf-8");
        $euroEl = $html->body->nodeValue;
        $this->assertEquals( $euro, (string) $euroEl );
        $htmlString = "<ul><li>$euro</li></ul>";
        $html = \arc\html::parse($htmlString, "utf-8");
        $euroEl = $html->ul->li->nodeValue;
        $this->assertEquals( $euro, (string) $euroEl );
    }

}