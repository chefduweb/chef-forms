define([

	'jquery',
	'cuisine-validate',

], function( $, CuisineValidate ){


	$( document ).ready( function( $ ){

		console.log( CuisineValidate );

	
		$('.form').each( function( index, obj ){
			
			var _form = new FormObject();
			_form.init( obj );

		});

	});


	function FormObject(){

		var el = '';
		var formId = '';
		var fields = {};


		this.init = function( obj ){

			var self = this;
			self.el = $( obj );
			self.formId = parseInt( self.el.attr('id').replace( 'form_', '' ) );
			self.setEvents();

		}


		this.setEvents = function(){

			var self = this;

			self.el.find( '.submit-form' ).on( 'click', function( e ){

				e.preventDefault();

				self.showLoader();
				self.send();

				return false;
			});

			//validation:
			self.el.find( '.field' ).on( 'blur', function( e ){
				self.validate( e );
			});

		}


		this.send = function(){

			var self = this;
			var data = {
				action: 'sendForm',
				post_id: self.formId,
				entry: self.el.serializeArray()
			}


			$.post( Cuisine.ajax, data, function( response ){

				console.log( response );

			});


		}


		this.validate = function( evt ){

			var self = this;
			var obj = jQuery( evt.target );

			self.validateField( obj );

		}

		this.validateField = function( obj ){

			var self = this;
			var value = obj.val();

			var validated = true;
			var validateNothing = true;
			var type = '';

			if( obj.data('validate').length > 0 ){
				var validators = obj.data('validate').split(',');

				for( var i = 0; i < validators.length; i++ ){
	
					var criterium = validators[ i ];

					switch( criterium ){

						case 'required':
							if( CuisineValidate.empty( value ) === false ){
								validated = false;
								type = 'required';
								break;
							}

						break;
						case 'email':

							if( CuisineValidate.email( value ) === false ){
								validated = false;
								type = 'email';
							}

						break;
						case 'address':

							if( CuisineValidate.has_number( value ) === false ){
								validated = false;
								type = 'address';
							}

						break;
						case 'zipcode':

							if( CuisineValidate.zipcode( value ) === false ){
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
			

			if( validated ){
			
				obj.removeClass('validated-false');
				obj.addClass('validated-true');
//				obj.removeClass('please-fill');
//				obj.removeData( 'error' );

			}else if( validated === false ){
				
				obj.removeClass('validated-true');
				obj.addClass('validated-false');
				alert( type );
//				obj.data( 'error', type );				
			}

			return validated;

		}


		this.showLoader = function(){

			var self = this;
			alert('wop');
			self.el.addClass( 'active' );

		}


	}




});