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

	static public function formBuilder( $fields, $formAttributes=[] ) {
		if ( !self::$formBuilder ) {
			self::$formBuilder = \arc\prototype::create([
				'fields' => [],
				'attributes' => [],
				':parseField' => function($self, $field, $key) {
					if ( !is_array($field) ) {
						$field = [ 'name' => $field, 'label' => $field ];
					}
					$defaults = [
						'type' => 'text',
						'name' => is_numeric($key) ? 'field_'.$key : $key,
						'label' => is_numeric($key) ? false : $key,
						'value' => '',
						'default' => null,
						'attributes' => [],
						'inputAttributes' => []
					];
					$defaults['label'] = $defaults['name'];
					return $field + $defaults;
				},
				':getValue' => function($self, $field) {
					$selected = null;
					if ( $field['value'] ) {
						$selected = $field['value'];
					} else if ( $field['default'] ) {
						$selected = $field['default'];
					}
					return $selected;				
				},
				':renderOptions' => function($self, $field) {
					$selected = $self->getValue($field);
					$options = '';
					foreach ( (array)$field['options'] as $key => $option ) {
						$attributes = [ 'value' => $key ];
						if ( $key === $selected ) {
							$attributes['selected'] = true;
						}
						$options .= \arc\html::option($attributes, $option);
					}
					return $options;
				},
				':renderInputSelect' => function($self, $field) {
					return \arc\html::select(
						[
							'id' => $field['name'],
							'name' => $field['name'],
						]+(array)$field['inputAttributes'],
						$self->renderOptions($field)
					);
				},
				':renderInputRadioGroup' => function($self, $field ) {
					$selected = $self->getValue($field);
					$radios = '';
					foreach( (array) $field['options'] as $key => $option ) {
						$attributes = $field['inputAttributes'];
						if ( $key === $selected ) {
							$attributes['checked'] = true;
						}
						$radios .= $self->renderInputRadio(['inputAttributes' => $attributes] + $field);
					}
					return \arc\html::div(
						[
							'class' => 'arc-form-radiogroup'
						],
						$radios
					);
				},
				':renderInputRadio' => function($self, $field ) {
					return \arc\html::input(
						[
							'type' => 'radio', 
							'id' => $field['name'], 
							'name' => $field['name'], 
							'value' => $field['value']
						] + (array)$field['inputAttributes']
					);
				},
				':renderInputTextarea' => function($self, $field) {
					return \arc\html::textarea(
						[
							'id' => $field['name'], 
							'name' => $field['name'], 
							'value' => $field['value']
						] + (array)$field['inputAttributes']
					);
				},
				':renderInputPassword' => function($self, $field) {
					return \arc\html::input(
						[
							'type' => 'password', 
							'id' => $field['name'], 
							'name' => $field['name'], 
							'value' => $field['value']
						] + (array)$field['inputAttributes']
					);
				},
				':renderInputText' => function($self, $field) {
					return \arc\html::input(
						[
							'type' => 'text', 
							'id' => $field['name'], 
							'name' => $field['name'], 
							'value' => $field['value']
						] + (array)$field['inputAttributes']
					);
				},
				':renderInputHidden' => function($self, $field) {
					return \arc\html::input(
						[
							'type' => 'hidden', 
							'id' => $field['name'], 
							'name' => $field['name'], 
							'value' => $field['value']
						] + (array)$field['inputAttributes']
					);
				},
				':renderInput' => function($self, $field) {
					$renderMethod = 'renderInput'.ucfirst($field['type']);
					if ( !isset($self->{$renderMethod}) ) {
						throw new \arc\ExceptionMethodNotFound('No render method for input type '.$field['type'], 404);
					}
					return $self->{$renderMethod}($field);
				},
				':renderLabel' => function($self, $field) {
					if ( $field['label'] ) {
						return \arc\html::label(['for' => $field['name']], $field['label']);
					} else {
						return '';
					}
				},
				':renderField' => function($self, $field, $key=null) {
					$field = $self->parseField($field, $key);
					$contents = $self->renderLabel($field);
					$contents .= $self->renderInput($field);
					$attributes = isset($field['attributes']) ? $field['attributes'] : [];
					if ( isset($field['class']) ) {
						$attributes['class'] = $field['class'];
					}
					return \arc\html::div( $attributes, $contents);
				},
				':__toString' => function($self) {
					$fields = '';
					foreach ( $self->fields as $key => $field ) {
						$fields .= $self->renderField($field, $key);
					}
					return \arc\html::form($self->attributes, $fields);
				}
			]);
		}
		return \arc\prototype::extend(self::$formBuilder, [
			'fields' => $fields,
			'attributes' => $formAttributes
		]);
	}
}
