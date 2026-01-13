/*
 * jQuery and Bootsrap3 Plugin prettyFile
 *
 * version 2.0, Jan 20th, 2014
 * by episage, sujin2f
 * Git repository : https://github.com/episage/bootstrap-3-pretty-file-upload
 */
( function( $ ) {
	$.fn.extend({
		buttonFile: function( options ) {
			var defaults = {
				text : "Select Files"
			};

			var txtData = $(this).data("txt");
			var options =  $.extend(defaults, options);
			if(txtData !== undefined && txtData !== null && txtData !== '') options.text = txtData;

			var plugin = this;

			function make_form( $el, text ) {
				$el.wrap('<div></div>');

				$el.hide();
				$el.after(`
					<div class="separador_buttonFile">
						<button class="${isOldLayout ? 'btn btn-primary' : 'button-form confirm-button'}" type="button">` + text + `</button>
					</div>
				`);

				return $el.parent();
			};

			function bind_change( $wrap, multiple ) {
				$wrap.find( 'input[type="file"]' ).change(function () {
					// When original file input changes, get its value, show it in the fake input
					var files = $( this )[0].files,
					info = '';

					if ( files.length == 0 ) {
						info = options.text;
					} else if ( !multiple || files.length == 1 ) {
						var path = $( this ).val().replace('/', '\\');
						path = path.split('\\');

						info = path[path.length - 1];
					} else if ( files.length > 1 ) {
						// Display number of selected files instead of filenames
						info = files.length + ' files selected';
					}

					$wrap.find('.separador_buttonFile button').html( info );
				});
			};

			function bind_button( $wrap, multiple ) {
				$wrap.find( '.separador_buttonFile button' ).click( function( e ) {
					e.preventDefault();
					$wrap.find( 'input[type="file"]' ).click();
				});
			};

			return plugin.each( function() {
				$this = $( this );

				if ( $this ) {
					var multiple = $this.attr( 'multiple' );

					$wrap = make_form( $this, options.text );
					bind_change( $wrap, multiple );
					bind_button( $wrap );
				}
			});
		}
	});
}( jQuery ));

