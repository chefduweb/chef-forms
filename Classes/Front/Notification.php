<?php

namespace ChefForms\Front;

use Cuisine\Utilities\Url;

class Notification {



	/**
	 * Send this to
	 * 
	 * @var string ( email )
	 */
	private $to;


	/**
	 * Send this from
	 * 
	 * @var string ( email )
	 */
	private $from_email;


	/**
	 * Name to send this from
	 * 
	 * @var string ( email )
	 */
	private $from_name;


	/**
	 * Subject of the mail
	 * 
	 * @var string
	 */
	private $subject;

	/**
	 * Template file to send
	 * 
	 * @var string
	 */
	private $template;


	/**
	 * The eventual message
	 * 
	 * @var string
	 */
	private $message;


	/**
	 * Headers of this notification
	 *
	 * @var array
	 */
	private $headers;


	/**
	 * Attachments of this notification
	 *
	 * @var array
	 */
	private $attachments;


	/**
	 * All properties for this notification
	 * 
	 * @var array
	 */
	private $properties;

	/**
	 * All fields for this form
	 * 
	 * @var array
	 */
	private $fields;


	/**
	 * Current entry
	 * 
	 * @var array
	 */
	private $entry;



	/**
	 * Setup the basics
	 * 
	 * @param  array $notify
	 * @return void       
	 */
	function __construct(){

		$adminMail = get_bloginfo( 'admin_email' );
		$siteName = get_bloginfo( 'blogname' );
		$this->from_address = get_option( 'chef_forms_from_email', $adminMail );
		$this->from_name = get_option( 'chef_forms_from_name', $siteName );

	}

	/*======================================*/
	/*========= Public functions ===========*/
	/*======================================*/


	/**
	 * Create notification
	 * 
	 * @return ChefForms\Front\Notification
	 */
	public function make( $notify, $fields ){

		$this->fields = $fields;
		$this->properties = $this->getProperties( $notify );
		$this->entry = ( isset( $_POST['entry'] ) ? $_POST['entry'] : array() );

		$this->to = $this->properties['to'];
		$this->subject = $this->properties['title'];

		$this->createMessage();

		return $this;
	}


	/**
	 * Send the notification
	 * 
	 * @return void
	 */
	public function send(){

		add_filter( "wp_mail_from", array( &$this, 'from' ) );
		add_filter( "wp_mail_from_name", array( &$this, 'from_name' ) );
		add_filter( "wp_mail_content_type", array( &$this, 'mime_type' ) );

			wp_mail( 
				$this->to, 
				$this->subject, 
				$this->message, 
				$this->headers, 
				$this->attachments
			);


		remove_filter( "wp_mail_content_type", array( &$this, 'mime_type' ) );
		remove_filter( "wp_mail_from_name", array( &$this, 'from_name' ) );
		remove_filter( "wp_mail_from", array( &$this, 'from' ) );

	}



	/*======================================*/
	/*========= Message generation =========*/
	/*======================================*/


	/**
	 * Create the html message
	 * 
	 * @return void
	 */
	private function createMessage(){

		$msg = $this->properties['content'];

		if( $msg == '' )
			$msg = $this->generateDefaultMessage();

		$msg = str_replace( '{alle_velden}', $this->generateDefaultMessage(), $msg );

		foreach( $this->entry as $entry ){

			$name = $entry['name'];
			$msg = str_replace( '{'.$name.'}', $entry['value'], $msg );

		}

		ob_start();

			$path = Url::path( 'chef-forms', 'chef-forms/Templates/Email/' ).'Html.php';
			include( $path );

		$this->message = ob_get_clean();

	}


	/**
	 * Create all the fields
	 * 
	 * @return void
	 */
	private function generateDefaultMessage(){

		$entryItems = $_POST['entry'];
		$html = '<table>';

		foreach( $this->fields as $field ){

			foreach( $entryItems as $entry ){

				if( $field->name == $entry['name'] ){

					$label = $field->label;
					if( $label == '' && $field->properties['placeholder'] != '' )
						$label = $field->properties['placeholder'];


					$html .= '<tr><td><strong>'.$label.'</strong></td>';
					$html .= '<td>'.$entry['value'].'</td></tr>';

				}
				
			}
		}

		$html .= '</table>';

		return $html;
	}


	/*======================================*/
	/*========= Getters & Setters ==========*/
	/*======================================*/

	/**
	 * Get the properties, with a default fallback
	 *
	 * @param  array $properties
	 * @return array
	 */
	private function getProperties( $properties ){

		if( !isset( $properties['title'] ) )
			$properties['title'] = 'Bericht website';

		if( !isset( $properties['content'] ) )
			$properties['content'] = '{alle_velden}';

		return $properties;
	}



	/*======================================*/
	/*========= Filters ====================*/
	/*======================================*/


	/**
	 * Sets the right mime type
	 * 
	 * @return string
	 */
	function mime_type(){
		return "text/html";
	}

	/**
	 * Set the sendee's email
	 * 
	 * @return string
	 */
	function from(){
		return $this->from_email;
	}


	/**
	 * Set the sendee's name
	 *
	 * @return string
	 */
	function from_name(){
		return $this->from_name;
	}

}