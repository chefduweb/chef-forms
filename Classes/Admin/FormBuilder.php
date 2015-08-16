<?php
namespace ChefForms\Admin;


class FormBuilder {

	/**
	 * Get the post id for this form:
	 * 
	 * @var integer
	 */
	var $id = 0;

	/**
	 * Title of this new form
	 * 
	 * @var string
	 */
	var $title;


	/**
	 * Boolean to see if this form already exists
	 * 
	 * @var boolean
	 */
	var $exists = false;


	/**
	 * An array of fields this form can have
	 * 
	 * @var array
	 */
	var $fields = array();	



	/**
	 * Init a form builder and return this object
	 * 
	 * @param  string $title
	 * @param  array  $options
	 * @return \ChefForms\Admin\FormBuilder
	 */
	public function make( $title, $options = array() ){

		$this->title = $title;
		$this->options = $this->sanitizeOptions( $options );

		//check if this form already exists:
		$this->exists = $this->checkExistence();

		return $this;

	}


	/**
	 * Trigger the save functions 
	 * 
	 * @param array $fields array of field objects
	 */
	public function set( $fields ){


		if( !$this->exists ){

			$this->fields = $fields;

			//create the post:
			$args = array(
				'post_title'	=> $this->title,
				'post_type'		=> 'form',
				'post_status'	=> 'publish'
			);

			$this->id = wp_insert_post( $args, true );

			//errors dont get any further:
			if( is_wp_error( $this->id ) )
				return false;


			//update the gatekeeper:
			$forms = get_option( 'createdForms', array() );
			$forms[] = $this->title;
			update_option( 'createdForms', $forms );


			//save the meta-data:
			$this->saveFields();
			$this->saveSettings();

		}

		return $this;
	}


	/**
	 * Save all fields 
	 * 
	 * @return void
	 */
	private function saveFields(){
	
		$fields = array();		

		$i = 0;
		foreach( $this->fields as $field ){

			$fields[ $i ] = array(

				'label'			=> $field->label,
				'type'			=> $field->type,
				'placeholder'	=> $field->getProperty( 'placeholder' ),
				'required'		=> $field->getProperty( 'required' ),
				'defaultValue'	=> $field->getDefault(),
				'postion'		=> $i + 1
			);

			$i++;
		}

		//save it:
		update_post_meta( $this->id, 'fields', $fields );

	}


	/**
	 * Save all settings
	 * 
	 * @return void
	 */
	private function saveSettings(){

		$settings = $this->options;

	}



	/**
	 * Check to see if this form is already build:
	 * 
	 * @return bool
	 */
	private function checkExistence(){

		$createdForms = get_option( 'exisitingForms', array() );

		if( in_array( $this->title, $createdForms ) )
			return true;

		return false;

	}


	/**
	 * Sanitize the form options and set the defaults:
	 * 
	 * @param  array $options 
	 * @return array          
	 */
	private function sanitizeOptions( $options ){


		if( !isset( $options['btn-text'] ) )
			$options['btn-text'] = 'Verstuur';

		if( !isset( $options['labels'] ) )
			$options['labels'] = 'top';

		if( !isset( $options['confirm'] ) )
			$options['confirm'] = '{{ alle_velden }}';


		return $options;
	} 


}
