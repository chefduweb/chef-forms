define([

	'jquery',
	'cuisine-validate',

], function( $ ){


	$( document ).ready( function( $ ){
	
		$('.form').each( function( index, obj ){
			
			var _form = new FormObject();
			_form.init( obj );

		});

	});


	function FormObject(){

		var el = '';
		var formId = '';
		var fields = {};
		var submitted = false;


		/**
		 * Init this form object
		 * 
		 * @param  jQuerySelector obj 
		 * @return void
		 */
		this.init = function( obj ){

			var self = this;
			self.el = $( obj );
			self.submitted = false;

			//stop a form from initting if it's just an arbitrary .form class:
			if( self.el.attr('id') === undefined )
				return false;

			self.fields = self.el.find( '.field' );
			self.formId = parseInt( self.el.attr('id').replace( 'form_', '' ) );
			self.setEvents();
			self.setFields();

		}


		this.setEvents = function(){

			var self = this;

			//make the button clickable:
			self.el.find( '.submit-form' ).on( 'click', function( e ){

				e.preventDefault();
				self.el.trigger('submit');

			});

			//on submit:
			self.el.on('submit', function( e ){

				//don't prevent default in case of no-ajax
				if( self.el.data('no-ajax') === undefined )
					e.preventDefault();
				

				var allValidated = true;

				//validate all fields:
				self.fields.each( function(){

					if( self.validateField( jQuery( this ) ) === false ){
						allValidated = false;
					}

				});


				//if all fields are validated
				if( allValidated === true && self.submitted == false ){
							
					self.showLoader();
					self.send();
				
				}else{
					//nothing validated, return false nonetheless
					e.preventDefault();
					return false;

				}


				//only return false in the case of no ajax:
				if( self.el.data( 'no-ajax') === undefined )
					return false;
				
			});



			//field validation on blur:
			self.el.find( '.field' ).on( 'blur', function( e ){

				self.validate( e );
			
			});
		}

		/**
		 * Send the form, either with Ajax ( preferred ) or regularly
		 * 
		 * @return void
		 */
		this.send = function(){

			var self = this;

			//catch non FormData capable browsers ( <IE9 & Opera Mini )	
			if( window.FormData == undefined || self.el.data('no-ajax') !== undefined ){

				self.el.data( 'no-ajax', 'true' );
				self.submitted = true;
				self.el.trigger( 'submit' );

			}else{ 

				var _data = new FormData( self.el[0] );
				_data.append( 'action', 'sendForm' );
				_data.append( 'post_id', self.formId );

				self.el.trigger( 'beforeSubmit', _data, self );
				self.submitted = true;

				$.ajax({

					url: Cuisine.ajax,
					type: 'POST',
					data: _data,
					processData: false,
					contentType: false,
					success: function( response ){
	
						self.onSuccess( response, self );
	
					},
					error: function( response ){
	
					}
				});

			}
		}


		/**
		 * Function for succesful send handeling
		 * 
		 * @param  json response
		 * @param  FormObject self
		 * @return void
		 */
		this.onSuccess = function( response, self ){

			//used for debugging notifications:
			//self.el.append( response );
					
			if( Validate.json( response ) ){
						
				self.hideLoader();

				var response = JSON.parse( response );

				//check if we need to redirect;
				if( response.redirect == true ){

					self.el.trigger( 'beforeRedirect', response, self );
						
					window.location.href = response.redirect_url;

				}else{

					self.el.trigger( 'onResponse', response, self );

	
					//otherwise, clear the loader and display the message.
					self.el.addClass( 'msg' );
					self.el.append('<div class="message">'+ response.message +'</div>' );

					self.resetFields();						
					self.el.trigger( 'onComplete', response, self );

					//remove message after 3 seconds, if the form doesn't have a data attribute set:
					if( self.el.data( 'maintain-msg' ) === undefined ){

						self.resetFields();
							
						//remove message after 3 seconds, if the form doesn't have a data attribute set:
						if( self.el.data( 'maintain-msg' ) === undefined ){

							setTimeout( function(){
							
								self.el.removeClass( 'msg' );
								self.el.find('.message').remove();
							
							}, 5000 );

						}	
					}
				}
			}
		}



		/**
		 * Triggers the JS for our fields on the front-end:
		 *
		 * @return void
		 */
		this.setFields = function(){

			var self = this;

			//set the datepicker:
			if( self.el.find( '.datepicker' ).length > 0 ){
				requirejs( [ 'datepicker' ], function( datepicker ){

					$( ".datepicker" ).datepicker();

				});
			}
		}


		/**
		 * Reset all fields:
		 * 
		 * @return void
		 */
		this.resetFields = function(){

			var self = this;

			self.submitted = false;
			self.fields.each( function(){
				$( this ).val('');
				$( this ).removeClass('validated-false');
				$( this ).removeClass('validated-true');
			});
			
		}


		/**
		 * Figure out the jQuery object behind what we need to validate
		 * 
		 * @param  Event evt
		 * @return bool ( self.validateField )
		 */
		this.validate = function( evt ){

			var self = this;
			var obj = jQuery( evt.target );

			self.validateField( obj );

		}

		/**
		 * Actually validate a field
		 * 
		 * @param  jQueryObject obj 
		 * @return bool
		 */
		this.validateField = function( obj ){

			var self = this;
			var value = obj.val();

			var validated = true;
			var validateNothing = true;
			var type = '';

			//allow plugins to add their validation functions
			obj.trigger( 'validate' );

			if( obj.data('validate') !== undefined ){
				var validators = obj.data('validate').split(',');

				for( var i = 0; i < validators.length; i++ ){
	
					var criterium = validators[ i ];

					switch( criterium ){

						case 'required':
							if( Validate.empty( value ) === false ){
								validated = false;
								type = 'required';
								break;
							}

						break;
						case 'email':

							if( Validate.email( value ) === false ){
								validated = false;
								type = 'email';
							}

						break;
						case 'numerical':

							if( Validate.number( value ) === false ){
								validated = false;
								type = 'number';
							}

						break;
						case 'address':

							if( Validate.has_number( value ) === false ){
								validated = false;
								type = 'address';
							}

						break;
						case 'zipcode':

							if( Validate.zipcode( value ) === false ){
								validated = false;
								type = 'zipcode';
							}

						break;

					}

					if( obj.attr( 'type' ) === 'checkbox' && criterium == 'required' ){
						if( obj.is(':checked') === false ){
							validated = false;
							type = 'notchecked';
						}	
					}

				}
			}
			
			var valError = obj.parent().find( '.validation-error' );
			valError.remove();
			
			if( validated ){
	
				obj.removeClass('validated-false');
				obj.addClass('validated-true');


			}else if( validated === false ){
				
				obj.removeClass('validated-true');
				obj.addClass('validated-false');

				var valError = ValidationErrors[ type ];
				obj.after( '<span class="validation-error">'+ valError +'</span>' );

			}

			return validated;

		}


		/**
		 * Show a loader
		 * 
		 * @return void
		 */
		this.showLoader = function(){

			var self = this;
			self.el.addClass( 'active' );

		}

		/**
		 * Hide a loader
		 * 
		 * @return void
		 */
		this.hideLoader = function(){

			var self = this;
			self.el.removeClass( 'active' );

		}
	}
});