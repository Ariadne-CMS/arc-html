<?php

namespace arc\html;

class Parser
{
    public $options = [
        'libxml_options' => 0
    ];

    public function __construct( $options = array() )
    {
        $optionList = [ 'libxml_options' ];
        foreach( $options as $option => $optionValue ) {
            if ( in_array( $option, $optionList ) ) {
                $this->{$option} = $optionValue;
            }
        }
    }

    public function parse( $html, $encoding = 'UTF-8' )
    {
        if ( !$html ) {
            return \arc\html\Proxy( null );
        }
        if ( $html instanceof Proxy ) { // already parsed
            return $html;
        }
        $html = (string) $html;
        if ( stripos($html, '<html>')!==false ) {
            return $this->parseFull( $html, $encoding );
        } else {
            return $this->parsePartial( $html, $encoding );
        }
    }

    private function parsePartial( $html, $encoding = 'UTF-8')
    {
        $result = $this->parseFull( '<div id="ArcPartialHTML">'.$html.'</div>', $encoding );
        if ( $result ) {
            $result = new \arc\html\Proxy( $result->find('#ArcPartialHTML')[0]->children(), $this );
        } else {
            throw new \arc\Exception('parse error');
        }
        return $result;
    }

    private function parseFull( $html, $encoding = 'UTF-8')
    {
        $dom = new \DomDocument('1.0', $encoding);
        $prefix = '';
        if ( isset($encoding) ) {
            $prefix  = <<<EOS
<head id='ar_html_parser_encoding_header'>
<meta http-equiv="content-type" content="text/html; charset=$encoding">
</head>
EOS;
        }
        libxml_disable_entity_loader(); // prevents XXE attacks
        $prevErrorSetting = libxml_use_internal_errors(true);
        if ( $dom->loadHTML( $prefix . $html, $this->options['libxml_options'] ) ) {
            if ( isset($encoding) ) {
                $elm  = $dom->getElementById('ar_html_parser_encoding_header');
                if ( isset($elm) ) {
                    $elm->parentNode->removeChild($elm);
                }
            }
            libxml_use_internal_errors( $prevErrorSetting );
            return new \arc\html\Proxy( simplexml_import_dom( $dom ), $this );
        }
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors( $prevErrorSetting );
        $message = 'Incorrect html passed.';
        foreach ( $errors as $error ) {
            $message .= "\nline: ".$error->line."; column: ".$error->column."; ".$error->message;
        }
        throw new \arc\Exception( $message, \arc\exceptions::ILLEGAL_ARGUMENT );
    }

}
