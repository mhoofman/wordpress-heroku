/* global _wpCustomizeWidgetsSettings */
(function( wp, $ ){

	if ( ! wp || ! wp.customize ) { return; }

	// Set up our namespace...
	var api = wp.customize,
		l10n, OldPreviewer;

	api.Widgets = api.Widgets || {};

	// Link settings
	api.Widgets.data = _wpCustomizeWidgetsSettings || {};
	l10n = api.Widgets.data.l10n;
	delete api.Widgets.data.l10n;

	/**
	 * wp.customize.Widgets.WidgetModel
	 *
	 * A single widget model.
	 *
	 * @constructor
	 * @augments Backbone.Model
	 */
	api.Widgets.WidgetModel = Backbone.Model.extend({
		id: null,
		temp_id: null,
		classname: null,
		control_tpl: null,
		description: null,
		is_disabled: null,
		is_multi: null,
		multi_number: null,
		name: null,
		id_base: null,
		transport: 'refresh',
		params: [],
		width: null,
		height: null,
		search_matched: true
	});

	/**
	 * wp.customize.Widgets.WidgetCollection
	 *
	 * Collection for widget models.
	 *
	 * @constructor
	 * @augments Backbone.Model
	 */
	api.Widgets.WidgetCollection = Backbone.Collection.extend({
		model: api.Widgets.WidgetModel,

		// Controls searching on the current widget collection
		// and triggers an update event
		doSearch: function( value ) {

			// Don't do anything if we've already done this search
			// Useful because the search handler fires multiple times per keystroke
			if ( this.terms === value ) {
				return;
			}

			// Updates terms with the value passed
			this.terms = value;

			// If we have terms, run a search...
			if ( this.terms.length > 0 ) {
				this.search( this.terms );
			}

			// If search is blank, show all themes
			// Useful for resetting the views when you clean the input
			if ( this.terms === '' ) {
				this.each( function ( widget ) {
					widget.set( 'search_matched', true );
				} );
			}
		},

		// Performs a search within the collection
		// @uses RegExp
		search: function( term ) {
			var match, haystack;

			// Escape the term string for RegExp meta characters
			term = term.replace( /[-\/\\^$*+?.()|[\]{}]/g, '\\$&' );

			// Consider spaces as word delimiters and match the whole string
			// so matching terms can be combined
			term = term.replace( / /g, ')(?=.*' );
			match = new RegExp( '^(?=.*' + term + ').+', 'i' );

			this.each( function ( data ) {
				haystack = [ data.get( 'name' ), data.get( 'id' ), data.get( 'description' ) ].join( ' ' );
				data.set( 'search_matched', match.test( haystack ) );
			} );
		}
	});
	api.Widgets.availableWidgets = new api.Widgets.WidgetCollection( api.Widgets.data.availableWidgets );

	/**
	 * wp.customize.Widgets.SidebarModel
	 *
	 * A single sidebar model.
	 *
	 * @constructor
	 * @augments Backbone.Model
	 */
	api.Widgets.SidebarModel = Backbone.Model.extend({
		after_title: null,
		after_widget: null,
		before_title: null,
		before_widget: null,
		'class': null,
		description: null,
		id: null,
		name: null,
		is_rendered: false
	});

	/**
	 * wp.customize.Widgets.SidebarCollection
	 *
	 * Collection for sidebar models.
	 *
	 * @constructor
	 * @augments Backbone.Collection
	 */
	api.Widgets.SidebarCollection = Backbone.Collection.extend({
		model: api.Widgets.SidebarModel
	});
	api.Widgets.registeredSidebars = new api.Widgets.SidebarCollection( api.Widgets.data.registeredSidebars );

	/**
	 * wp.customize.Widgets.AvailableWidgetsPanelView
	 *
	 * View class for the available widgets panel.
	 *
	 * @constructor
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 */
	api.Widgets.AvailableWidgetsPanelView = wp.Backbone.View.extend({

		el: '#available-widgets',

		events: {
			'input #widgets-search': 'search',
			'keyup #widgets-search': 'search',
			'change #widgets-search': 'search',
			'search #widgets-search': 'search',
			'focus .widget-tpl' : 'focus',
			'click .widget-tpl' : '_submit',
			'keypress .widget-tpl' : '_submit',
			'keydown' : 'keyboardAccessible'
		},

		// Cache current selected widget
		selected: null,

		// Cache sidebar control which has opened panel
		currentSidebarControl: null,
		$search: null,

		initialize: function() {
			var self = this;

			this.$search = $( '#widgets-search' );

			_.bindAll( this, 'close' );

			this.listenTo( this.collection, 'change', this.updateList );

			this.updateList();

			// If the available widgets panel is open and the customize controls are
			// interacted with (i.e. available widgets panel is blurred) then close the
			// available widgets panel.
			$( '#customize-controls' ).on( 'click keydown', function( e ) {
				var isAddNewBtn = $( e.target ).is( '.add-new-widget, .add-new-widget *' );
				if ( $( 'body' ).hasClass( 'adding-widget' ) && ! isAddNewBtn ) {
					self.close();
				}
			} );

			// Close the panel if the URL in the preview changes
			api.Widgets.Previewer.bind( 'url', this.close );
		},

		// Performs a search and handles selected widget
		search: function( event ) {
			var firstVisible;

			this.collection.doSearch( event.target.value );

			// Remove a widget from being selected if it is no longer visible
			if ( this.selected && ! this.selected.is( ':visible' ) ) {
				this.selected.removeClass( 'selected' );
				this.selected = null;
			}

			// If a widget was selected but the filter value has been cleared out, clear selection
			if ( this.selected && ! event.target.value ) {
				this.selected.removeClass( 'selected' );
				this.selected = null;
			}

			// If a filter has been entered and a widget hasn't been selected, select the first one shown
			if ( ! this.selected && event.target.value ) {
				firstVisible = this.$el.find( '> .widget-tpl:visible:first' );
				if ( firstVisible.length ) {
					this.select( firstVisible );
				}
			}
		},

		// Changes visibility of available widgets
		updateList: function() {
			this.collection.each( function( widget ) {
				var widgetTpl = $( '#widget-tpl-' + widget.id );
				widgetTpl.toggle( widget.get( 'search_matched' ) && ! widget.get( 'is_disabled' ) );
				if ( widget.get( 'is_disabled' ) && widgetTpl.is( this.selected ) ) {
					this.selected = null;
				}
			} );
		},

		// Highlights a widget
		select: function( widgetTpl ) {
			this.selected = $( widgetTpl );
			this.selected.siblings( '.widget-tpl' ).removeClass( 'selected' );
			this.selected.addClass( 'selected' );
		},

		// Highlights a widget on focus
		focus: function( event ) {
			this.select( $( event.currentTarget ) );
		},

		// Submit handler for keypress and click on widget
		_submit: function( event ) {
			// Only proceed with keypress if it is Enter or Spacebar
			if ( event.type === 'keypress' && ( event.which !== 13 && event.which !== 32 ) ) {
				return;
			}

			this.submit( $( event.currentTarget ) );
		},

		// Adds a selected widget to the sidebar
		submit: function( widgetTpl ) {
			var widgetId, widget;

			if ( ! widgetTpl ) {
				widgetTpl = this.selected;
			}

			if ( ! widgetTpl || ! this.currentSidebarControl ) {
				return;
			}

			this.select( widgetTpl );

			widgetId = $( this.selected ).data( 'widget-id' );
			widget = this.collection.findWhere( { id: widgetId } );
			if ( ! widget ) {
				return;
			}

			this.currentSidebarControl.addWidget( widget.get( 'id_base' ) );

			this.close();
		},

		// Opens the panel
		open: function( sidebarControl ) {
			this.currentSidebarControl = sidebarControl;

			// Wide widget controls appear over the preview, and so they need to be collapsed when the panel opens
			_( this.currentSidebarControl.getWidgetFormControls() ).each( function( control ) {
				if ( control.params.is_wide ) {
					control.collapseForm();
				}
			} );

			$( 'body' ).addClass( 'adding-widget' );

			this.$el.find( '.selected' ).removeClass( 'selected' );

			// Reset search
			this.collection.doSearch( '' );

			this.$search.focus();
		},

		// Closes the panel
		close: function( options ) {
			options = options || {};

			if ( options.returnFocus && this.currentSidebarControl ) {
				this.currentSidebarControl.container.find( '.add-new-widget' ).focus();
			}

			this.currentSidebarControl = null;
			this.selected = null;

			$( 'body' ).removeClass( 'adding-widget' );

			this.$search.val( '' );
		},

		// Add keyboard accessiblity to the panel
		keyboardAccessible: function( event ) {
			var isEnter = ( event.which === 13 ),
				isEsc = ( event.which === 27 ),
				isDown = ( event.which === 40 ),
				isUp = ( event.which === 38 ),
				selected = null,
				firstVisible = this.$el.find( '> .widget-tpl:visible:first' ),
				lastVisible = this.$el.find( '> .widget-tpl:visible:last' ),
				isSearchFocused = $( event.target ).is( this.$search );

			if ( isDown || isUp ) {
				if ( isDown ) {
					if ( isSearchFocused ) {
						selected = firstVisible;
					} else if ( this.selected && this.selected.nextAll( '.widget-tpl:visible' ).length !== 0 ) {
						selected = this.selected.nextAll( '.widget-tpl:visible:first' );
					}
				} else if ( isUp ) {
					if ( isSearchFocused ) {
						selected = lastVisible;
					} else if ( this.selected && this.selected.prevAll( '.widget-tpl:visible' ).length !== 0 ) {
						selected = this.selected.prevAll( '.widget-tpl:visible:first' );
					}
				}

				this.select( selected );

				if ( selected ) {
					selected.focus();
				} else {
					this.$search.focus();
				}

				return;
			}

			// If enter pressed but nothing entered, don't do anything
			if ( isEnter && ! this.$search.val() ) {
				return;
			}

			if ( isEnter ) {
				this.submit();
			} else if ( isEsc ) {
				this.close( { returnFocus: true } );
			}
		}
	});

	/**
	 * Handlers for the widget-synced event, organized by widget ID base.
	 * Other widgets may provide their own update handlers by adding
	 * listeners for the widget-synced event.
	 */
	api.Widgets.formSyncHandlers = {

		/**
		 * @param {jQuery.Event} e
		 * @param {jQuery} widget
		 * @param {String} newForm
		 */
		rss: function( e, widget, newForm ) {
			var oldWidgetError = widget.find( '.widget-error:first' ),
				newWidgetError = $( '<div>' + newForm + '</div>' ).find( '.widget-error:first' );

			if ( oldWidgetError.length && newWidgetError.length ) {
				oldWidgetError.replaceWith( newWidgetError );
			} else if ( oldWidgetError.length ) {
				oldWidgetError.remove();
			} else if ( newWidgetError.length ) {
				widget.find( '.widget-content:first' ).prepend( newWidgetError );
			}
		}
	};

	/**
	 * wp.customize.Widgets.WidgetControl
	 *
	 * Customizer control for widgets.
	 * Note that 'widget_form' must match the WP_Widget_Form_Customize_Control::$type
	 *
	 * @constructor
	 * @augments wp.customize.Control
	 */
	api.Widgets.WidgetControl = api.Control.extend({
		/**
		 * Set up the control
		 */
		ready: function() {
			this._setupModel();
			this._setupWideWidget();
			this._setupControlToggle();
			this._setupWidgetTitle();
			this._setupReorderUI();
			this._setupHighlightEffects();
			this._setupUpdateUI();
			this._setupRemoveUI();
		},

		/**
		 * Handle changes to the setting
		 */
		_setupModel: function() {
			var self = this, rememberSavedWidgetId;

			api.Widgets.savedWidgetIds = api.Widgets.savedWidgetIds || [];

			// Remember saved widgets so we know which to trash (move to inactive widgets sidebar)
			rememberSavedWidgetId = function() {
				api.Widgets.savedWidgetIds[self.params.widget_id] = true;
			};
			api.bind( 'ready', rememberSavedWidgetId );
			api.bind( 'saved', rememberSavedWidgetId );

			this._updateCount = 0;
			this.isWidgetUpdating = false;
			this.liveUpdateMode = true;

			// Update widget whenever model changes
			this.setting.bind( function( to, from ) {
				if ( ! _( from ).isEqual( to ) && ! self.isWidgetUpdating ) {
					self.updateWidget( { instance: to } );
				}
			} );
		},

		/**
		 * Add special behaviors for wide widget controls
		 */
		_setupWideWidget: function() {
			var self = this, $widgetInside, $widgetForm, $customizeSidebar,
				$themeControlsContainer, positionWidget;

			if ( ! this.params.is_wide ) {
				return;
			}

			$widgetInside = this.container.find( '.widget-inside' );
			$widgetForm = $widgetInside.find( '> .form' );
			$customizeSidebar = $( '.wp-full-overlay-sidebar-content:first' );
			this.container.addClass( 'wide-widget-control' );

			this.container.find( '.widget-content:first' ).css( {
				'max-width': this.params.width,
				'min-height': this.params.height
			} );

			/**
			 * Keep the widget-inside positioned so the top of fixed-positioned
			 * element is at the same top position as the widget-top. When the
			 * widget-top is scrolled out of view, keep the widget-top in view;
			 * likewise, don't allow the widget to drop off the bottom of the window.
			 * If a widget is too tall to fit in the window, don't let the height
			 * exceed the window height so that the contents of the widget control
			 * will become scrollable (overflow:auto).
			 */
			positionWidget = function() {
				var offsetTop = self.container.offset().top,
					windowHeight = $( window ).height(),
					formHeight = $widgetForm.outerHeight(),
					top;
				$widgetInside.css( 'max-height', windowHeight );
				top = Math.max(
					0, // prevent top from going off screen
					Math.min(
						Math.max( offsetTop, 0 ), // distance widget in panel is from top of screen
						windowHeight - formHeight // flush up against bottom of screen
					)
				);
				$widgetInside.css( 'top', top );
			};

			$themeControlsContainer = $( '#customize-theme-controls' );
			this.container.on( 'expand', function() {
				positionWidget();
				$customizeSidebar.on( 'scroll', positionWidget );
				$( window ).on( 'resize', positionWidget );
				$themeControlsContainer.on( 'expanded collapsed', positionWidget );
			} );
			this.container.on( 'collapsed', function() {
				$customizeSidebar.off( 'scroll', positionWidget );
				$( window ).off( 'resize', positionWidget );
				$themeControlsContainer.off( 'expanded collapsed', positionWidget );
			} );

			// Reposition whenever a sidebar's widgets are changed
			api.each( function( setting ) {
				if ( 0 === setting.id.indexOf( 'sidebars_widgets[' ) ) {
					setting.bind( function() {
						if ( self.container.hasClass( 'expanded' ) ) {
							positionWidget();
						}
					} );
				}
			} );
		},

		/**
		 * Show/hide the control when clicking on the form title, when clicking
		 * the close button
		 */
		_setupControlToggle: function() {
			var self = this, $closeBtn;

			this.container.find( '.widget-top' ).on( 'click', function( e ) {
				e.preventDefault();
				var sidebarWidgetsControl = self.getSidebarWidgetsControl();
				if ( sidebarWidgetsControl.isReordering ) {
					return;
				}
				self.toggleForm();
			} );

			$closeBtn = this.container.find( '.widget-control-close' );
			$closeBtn.on( 'click', function( e ) {
				e.preventDefault();
				self.collapseForm();
				self.container.find( '.widget-top .widget-action:first' ).focus(); // keyboard accessibility
			} );
		},

		/**
		 * Update the title of the form if a title field is entered
		 */
		_setupWidgetTitle: function() {
			var self = this, updateTitle;

			updateTitle = function() {
				var title = self.setting().title,
					inWidgetTitle = self.container.find( '.in-widget-title' );

				if ( title ) {
					inWidgetTitle.text( ': ' + title );
				} else {
					inWidgetTitle.text( '' );
				}
			};
			this.setting.bind( updateTitle );
			updateTitle();
		},

		/**
		 * Set up the widget-reorder-nav
		 */
		_setupReorderUI: function() {
			var self = this, selectSidebarItem, $moveWidgetArea,
				$reorderNav, updateAvailableSidebars;

			/**
			 * select the provided sidebar list item in the move widget area
			 *
			 * @param {jQuery} li
			 */
			selectSidebarItem = function( li ) {
				li.siblings( '.selected' ).removeClass( 'selected' );
				li.addClass( 'selected' );
				var isSelfSidebar = ( li.data( 'id' ) === self.params.sidebar_id );
				self.container.find( '.move-widget-btn' ).prop( 'disabled', isSelfSidebar );
			};

			/**
			 * Add the widget reordering elements to the widget control
			 */
			this.container.find( '.widget-title-action' ).after( $( api.Widgets.data.tpl.widgetReorderNav ) );
			$moveWidgetArea = $(
				_.template( api.Widgets.data.tpl.moveWidgetArea, {
					sidebars: _( api.Widgets.registeredSidebars.toArray() ).pluck( 'attributes' )
				} )
			);
			this.container.find( '.widget-top' ).after( $moveWidgetArea );

			/**
			 * Update available sidebars when their rendered state changes
			 */
			updateAvailableSidebars = function() {
				var $sidebarItems = $moveWidgetArea.find( 'li' ), selfSidebarItem;

				selfSidebarItem = $sidebarItems.filter( function(){
					return $( this ).data( 'id' ) === self.params.sidebar_id;
				} );

				$sidebarItems.each( function() {
					var li = $( this ),
						sidebarId,
						sidebar;

					sidebarId = li.data( 'id' );
					sidebar = api.Widgets.registeredSidebars.get( sidebarId );

					li.toggle( sidebar.get( 'is_rendered' ) );

					if ( li.hasClass( 'selected' ) && ! sidebar.get( 'is_rendered' ) ) {
						selectSidebarItem( selfSidebarItem );
					}
				} );
			};

			updateAvailableSidebars();
			api.Widgets.registeredSidebars.on( 'change:is_rendered', updateAvailableSidebars );

			/**
			 * Handle clicks for up/down/move on the reorder nav
			 */
			$reorderNav = this.container.find( '.widget-reorder-nav' );
			$reorderNav.find( '.move-widget, .move-widget-down, .move-widget-up' ).on( 'click keypress', function( event ) {
				if ( event.type === 'keypress' && ( event.which !== 13 && event.which !== 32 ) ) {
					return;
				}
				$( this ).focus();

				if ( $( this ).is( '.move-widget' ) ) {
					self.toggleWidgetMoveArea();
				} else {
					var isMoveDown = $( this ).is( '.move-widget-down' ),
						isMoveUp = $( this ).is( '.move-widget-up' ),
						i = self.getWidgetSidebarPosition();

					if ( ( isMoveUp && i === 0 ) || ( isMoveDown && i === self.getSidebarWidgetsControl().setting().length - 1 ) ) {
						return;
					}

					if ( isMoveUp ) {
						self.moveUp();
					} else {
						self.moveDown();
					}

					$( this ).focus(); // re-focus after the container was moved
				}
			} );

			/**
			 * Handle selecting a sidebar to move to
			 */
			this.container.find( '.widget-area-select' ).on( 'click keypress', 'li', function( e ) {
				if ( event.type === 'keypress' && ( event.which !== 13 && event.which !== 32 ) ) {
					return;
				}
				e.preventDefault();
				selectSidebarItem( $( this ) );
			} );

			/**
			 * Move widget to another sidebar
			 */
			this.container.find( '.move-widget-btn' ).click( function() {
				self.getSidebarWidgetsControl().toggleReordering( false );

				var oldSidebarId = self.params.sidebar_id,
					newSidebarId = self.container.find( '.widget-area-select li.selected' ).data( 'id' ),
					oldSidebarWidgetsSetting, newSidebarWidgetsSetting,
					oldSidebarWidgetIds, newSidebarWidgetIds, i;

				oldSidebarWidgetsSetting = api( 'sidebars_widgets[' + oldSidebarId + ']' );
				newSidebarWidgetsSetting = api( 'sidebars_widgets[' + newSidebarId + ']' );
				oldSidebarWidgetIds = Array.prototype.slice.call( oldSidebarWidgetsSetting() );
				newSidebarWidgetIds = Array.prototype.slice.call( newSidebarWidgetsSetting() );

				i = self.getWidgetSidebarPosition();
				oldSidebarWidgetIds.splice( i, 1 );
				newSidebarWidgetIds.push( self.params.widget_id );

				oldSidebarWidgetsSetting( oldSidebarWidgetIds );
				newSidebarWidgetsSetting( newSidebarWidgetIds );

				self.focus();
			} );
		},

		/**
		 * Highlight widgets in preview when interacted with in the customizer
		 */
		_setupHighlightEffects: function() {
			var self = this;

			// Highlight whenever hovering or clicking over the form
			this.container.on( 'mouseenter click', function() {
				self.setting.previewer.send( 'highlight-widget', self.params.widget_id );
			} );

			// Highlight when the setting is updated
			this.setting.bind( function() {
				self.setting.previewer.send( 'highlight-widget', self.params.widget_id );
			} );
		},

		/**
		 * Set up event handlers for widget updating
		 */
		_setupUpdateUI: function() {
			var self = this, $widgetRoot, $widgetContent,
				$saveBtn, updateWidgetDebounced, formSyncHandler;

			$widgetRoot = this.container.find( '.widget:first' );
			$widgetContent = $widgetRoot.find( '.widget-content:first' );

			// Configure update button
			$saveBtn = this.container.find( '.widget-control-save' );
			$saveBtn.val( l10n.saveBtnLabel );
			$saveBtn.attr( 'title', l10n.saveBtnTooltip );
			$saveBtn.removeClass( 'button-primary' ).addClass( 'button-secondary' );
			$saveBtn.on( 'click', function( e ) {
				e.preventDefault();
				self.updateWidget( { disable_form: true } ); // @todo disable_form is unused?
			} );

			updateWidgetDebounced = _.debounce( function() {
				self.updateWidget();
			}, 250 );

			// Trigger widget form update when hitting Enter within an input
			$widgetContent.on( 'keydown', 'input', function( e ) {
				if ( 13 === e.which ) { // Enter
					e.preventDefault();
					self.updateWidget( { ignoreActiveElement: true } );
				}
			} );

			// Handle widgets that support live previews
			$widgetContent.on( 'change input propertychange', ':input', function( e ) {
				if ( self.liveUpdateMode ) {
					if ( e.type === 'change' ) {
						self.updateWidget();
					} else if ( this.checkValidity && this.checkValidity() ) {
						updateWidgetDebounced();
					}
				}
			} );

			// Remove loading indicators when the setting is saved and the preview updates
			this.setting.previewer.channel.bind( 'synced', function() {
				self.container.removeClass( 'previewer-loading' );
			} );

			api.Widgets.Previewer.bind( 'widget-updated', function( updatedWidgetId ) {
				if ( updatedWidgetId === self.params.widget_id ) {
					self.container.removeClass( 'previewer-loading' );
				}
			} );

			// Update widget control to indicate whether it is currently rendered
			api.Widgets.Previewer.bind( 'rendered-widgets', function( renderedWidgets ) {
				var isRendered = !! renderedWidgets[self.params.widget_id];

				self.container.toggleClass( 'widget-rendered', isRendered );
			} );

			formSyncHandler = api.Widgets.formSyncHandlers[ this.params.widget_id_base ];
			if ( formSyncHandler ) {
				$( document ).on( 'widget-synced', function( e, widget ) {
					if ( $widgetRoot.is( widget ) ) {
						formSyncHandler.apply( document, arguments );
					}
				} );
			}
		},

		/**
		 * Set up event handlers for widget removal
		 */
		_setupRemoveUI: function() {
			var self = this, $removeBtn, replaceDeleteWithRemove;

			// Configure remove button
			$removeBtn = this.container.find( 'a.widget-control-remove' );
			$removeBtn.on( 'click', function( e ) {
				e.preventDefault();

				// Find an adjacent element to add focus to when this widget goes away
				var $adjacentFocusTarget;
				if ( self.container.next().is( '.customize-control-widget_form' ) ) {
					$adjacentFocusTarget = self.container.next().find( '.widget-action:first' );
				} else if ( self.container.prev().is( '.customize-control-widget_form' ) ) {
					$adjacentFocusTarget = self.container.prev().find( '.widget-action:first' );
				} else {
					$adjacentFocusTarget = self.container.next( '.customize-control-sidebar_widgets' ).find( '.add-new-widget:first' );
				}

				self.container.slideUp( function() {
					var sidebarsWidgetsControl = api.Widgets.getSidebarWidgetControlContainingWidget( self.params.widget_id ),
						sidebarWidgetIds, i;

					if ( ! sidebarsWidgetsControl ) {
						return;
					}

					sidebarWidgetIds = sidebarsWidgetsControl.setting().slice();
					i = _.indexOf( sidebarWidgetIds, self.params.widget_id );
					if ( -1 === i ) {
						return;
					}

					sidebarWidgetIds.splice( i, 1 );
					sidebarsWidgetsControl.setting( sidebarWidgetIds );

					$adjacentFocusTarget.focus(); // keyboard accessibility
				} );
			} );

			replaceDeleteWithRemove = function() {
				$removeBtn.text( l10n.removeBtnLabel ); // wp_widget_control() outputs the link as "Delete"
				$removeBtn.attr( 'title', l10n.removeBtnTooltip );
			};

			if ( this.params.is_new ) {
				api.bind( 'saved', replaceDeleteWithRemove );
			} else {
				replaceDeleteWithRemove();
			}
		},

		/**
		 * Find all inputs in a widget container that should be considered when
		 * comparing the loaded form with the sanitized form, whose fields will
		 * be aligned to copy the sanitized over. The elements returned by this
		 * are passed into this._getInputsSignature(), and they are iterated
		 * over when copying sanitized values over to the the form loaded.
		 *
		 * @param {jQuery} container element in which to look for inputs
		 * @returns {jQuery} inputs
		 * @private
		 */
		_getInputs: function( container ) {
			return $( container ).find( ':input[name]' );
		},

		/**
		 * Iterate over supplied inputs and create a signature string for all of them together.
		 * This string can be used to compare whether or not the form has all of the same fields.
		 *
		 * @param {jQuery} inputs
		 * @returns {string}
		 * @private
		 */
		_getInputsSignature: function( inputs ) {
			var inputsSignatures = _( inputs ).map( function( input ) {
				var $input = $( input ), signatureParts;

				if ( $input.is( ':checkbox, :radio' ) ) {
					signatureParts = [ $input.attr( 'id' ), $input.attr( 'name' ), $input.prop( 'value' ) ];
				} else {
					signatureParts = [ $input.attr( 'id' ), $input.attr( 'name' ) ];
				}

				return signatureParts.join( ',' );
			} );

			return inputsSignatures.join( ';' );
		},

		/**
		 * Get the property that represents the state of an input.
		 *
		 * @param {jQuery|DOMElement} input
		 * @returns {string}
		 * @private
		 */
		_getInputStatePropertyName: function( input ) {
			var $input = $( input );

			if ( $input.is( ':radio, :checkbox' ) ) {
				return 'checked';
			} else {
				return 'value';
			}
		},

		/***********************************************************************
		 * Begin public API methods
		 **********************************************************************/

		/**
		 * @return {wp.customize.controlConstructor.sidebar_widgets[]}
		 */
		getSidebarWidgetsControl: function() {
			var settingId, sidebarWidgetsControl;

			settingId = 'sidebars_widgets[' + this.params.sidebar_id + ']';
			sidebarWidgetsControl = api.control( settingId );

			if ( ! sidebarWidgetsControl ) {
				return;
			}

			return sidebarWidgetsControl;
		},

		/**
		 * Submit the widget form via Ajax and get back the updated instance,
		 * along with the new widget control form to render.
		 *
		 * @param {object} [args]
		 * @param {Object|null} [args.instance=null]  When the model changes, the instance is sent here; otherwise, the inputs from the form are used
		 * @param {Function|null} [args.complete=null]  Function which is called when the request finishes. Context is bound to the control. First argument is any error. Following arguments are for success.
		 * @param {Boolean} [args.ignoreActiveElement=false] Whether or not updating a field will be deferred if focus is still on the element.
		 */
		updateWidget: function( args ) {
			var self = this, instanceOverride, completeCallback, $widgetRoot, $widgetContent,
				updateNumber, params, data, $inputs, processing, jqxhr, isChanged;

			args = $.extend( {
				instance: null,
				complete: null,
				ignoreActiveElement: false
			}, args );

			instanceOverride = args.instance;
			completeCallback = args.complete;

			this._updateCount += 1;
			updateNumber = this._updateCount;

			$widgetRoot = this.container.find( '.widget:first' );
			$widgetContent = $widgetRoot.find( '.widget-content:first' );

			// Remove a previous error message
			$widgetContent.find( '.widget-error' ).remove();

			this.container.addClass( 'widget-form-loading' );
			this.container.addClass( 'previewer-loading' );
			processing = api.state( 'processing' );
			processing( processing() + 1 );

			if ( ! this.liveUpdateMode ) {
				this.container.addClass( 'widget-form-disabled' );
			}

			params = {};
			params.action = 'update-widget';
			params.wp_customize = 'on';
			params.nonce = api.Widgets.data.nonce;
			params.theme = api.settings.theme.stylesheet;

			data = $.param( params );
			$inputs = this._getInputs( $widgetContent );

			// Store the value we're submitting in data so that when the response comes back,
			// we know if it got sanitized; if there is no difference in the sanitized value,
			// then we do not need to touch the UI and mess up the user's ongoing editing.
			$inputs.each( function() {
				var input = $( this ),
					property = self._getInputStatePropertyName( this );
				input.data( 'state' + updateNumber, input.prop( property ) );
			} );

			if ( instanceOverride ) {
				data += '&' + $.param( { 'sanitized_widget_setting': JSON.stringify( instanceOverride ) } );
			} else {
				data += '&' + $inputs.serialize();
			}
			data += '&' + $widgetContent.find( '~ :input' ).serialize();

			jqxhr = $.post( wp.ajax.settings.url, data );

			jqxhr.done( function( r ) {
				var message, sanitizedForm,	$sanitizedInputs, hasSameInputsInResponse,
					isLiveUpdateAborted = false;

				// Check if the user is logged out.
				if ( '0' === r ) {
					api.Widgets.Previewer.preview.iframe.hide();
					api.Widgets.Previewer.login().done( function() {
						self.updateWidget( args );
						api.Widgets.Previewer.preview.iframe.show();
					} );
					return;
				}

				// Check for cheaters.
				if ( '-1' === r ) {
					api.Widgets.Previewer.cheatin();
					return;
				}

				if ( r.success ) {
					sanitizedForm = $( '<div>' + r.data.form + '</div>' );
					$sanitizedInputs = self._getInputs( sanitizedForm );
					hasSameInputsInResponse = self._getInputsSignature( $inputs ) === self._getInputsSignature( $sanitizedInputs );

					// Restore live update mode if sanitized fields are now aligned with the existing fields
					if ( hasSameInputsInResponse && ! self.liveUpdateMode ) {
						self.liveUpdateMode = true;
						self.container.removeClass( 'widget-form-disabled' );
						self.container.find( 'input[name="savewidget"]' ).hide();
					}

					// Sync sanitized field states to existing fields if they are aligned
					if ( hasSameInputsInResponse && self.liveUpdateMode ) {
						$inputs.each( function( i ) {
							var $input = $( this ),
								$sanitizedInput = $( $sanitizedInputs[i] ),
								property = self._getInputStatePropertyName( this ),
								submittedState, sanitizedState,	canUpdateState;

							submittedState = $input.data( 'state' + updateNumber );
							sanitizedState = $sanitizedInput.prop( property );
							$input.data( 'sanitized', sanitizedState );

							canUpdateState = ( submittedState !== sanitizedState && ( args.ignoreActiveElement || ! $input.is( document.activeElement ) )	);
							if ( canUpdateState ) {
								$input.prop( property, sanitizedState );
							}
						} );

						$( document ).trigger( 'widget-synced', [ $widgetRoot, r.data.form ] );

					// Otherwise, if sanitized fields are not aligned with existing fields, disable live update mode if enabled
					} else if ( self.liveUpdateMode ) {
						self.liveUpdateMode = false;
						self.container.find( 'input[name="savewidget"]' ).show();
						isLiveUpdateAborted = true;

					// Otherwise, replace existing form with the sanitized form
					} else {
						$widgetContent.html( r.data.form );

						self.container.removeClass( 'widget-form-disabled' );

						$( document ).trigger( 'widget-updated', [ $widgetRoot ] );
					}

					/**
					 * If the old instance is identical to the new one, there is nothing new
					 * needing to be rendered, and so we can preempt the event for the
					 * preview finishing loading.
					 */
					isChanged = ! isLiveUpdateAborted && ! _( self.setting() ).isEqual( r.data.instance );
					if ( isChanged ) {
						self.isWidgetUpdating = true; // suppress triggering another updateWidget
						self.setting( r.data.instance );
						self.isWidgetUpdating = false;
					} else {
						// no change was made, so stop the spinner now instead of when the preview would updates
						self.container.removeClass( 'previewer-loading' );
					}

					if ( completeCallback ) {
						completeCallback.call( self, null, { noChange: ! isChanged, ajaxFinished: true } );
					}
				} else {
					// General error message
					message = l10n.error;

					if ( r.data && r.data.message ) {
						message = r.data.message;
					}

					if ( completeCallback ) {
						completeCallback.call( self, message );
					} else {
						$widgetContent.prepend( '<p class="widget-error"><strong>' + message + '</strong></p>' );
					}
				}
			} );

			jqxhr.fail( function( jqXHR, textStatus ) {
				if ( completeCallback ) {
					completeCallback.call( self, textStatus );
				}
			} );

			jqxhr.always( function() {
				self.container.removeClass( 'widget-form-loading' );

				$inputs.each( function() {
					$( this ).removeData( 'state' + updateNumber );
				} );

				processing( processing() - 1 );
			} );
		},

		/**
		 * Expand the accordion section containing a control
		 */
		expandControlSection: function() {
			var $section = this.container.closest( '.accordion-section' );

			if ( ! $section.hasClass( 'open' ) ) {
				$section.find( '.accordion-section-title:first' ).trigger( 'click' );
			}
		},

		/**
		 * Expand the widget form control
		 */
		expandForm: function() {
			this.toggleForm( true );
		},

		/**
		 * Collapse the widget form control
		 */
		collapseForm: function() {
			this.toggleForm( false );
		},

		/**
		 * Expand or collapse the widget control
		 *
		 * @param {boolean|undefined} [showOrHide] If not supplied, will be inverse of current visibility
		 */
		toggleForm: function( showOrHide ) {
			var self = this, $widget, $inside, complete;

			$widget = this.container.find( 'div.widget:first' );
			$inside = $widget.find( '.widget-inside:first' );
			if ( typeof showOrHide === 'undefined' ) {
				showOrHide = ! $inside.is( ':visible' );
			}

			// Already expanded or collapsed, so noop
			if ( $inside.is( ':visible' ) === showOrHide ) {
				return;
			}

			if ( showOrHide ) {
				// Close all other widget controls before expanding this one
				api.control.each( function( otherControl ) {
					if ( self.params.type === otherControl.params.type && self !== otherControl ) {
						otherControl.collapseForm();
					}
				} );

				complete = function() {
					self.container.removeClass( 'expanding' );
					self.container.addClass( 'expanded' );
					self.container.trigger( 'expanded' );
				};

				if ( self.params.is_wide ) {
					$inside.fadeIn( 'fast', complete );
				} else {
					$inside.slideDown( 'fast', complete );
				}

				self.container.trigger( 'expand' );
				self.container.addClass( 'expanding' );
			} else {
				complete = function() {
					self.container.removeClass( 'collapsing' );
					self.container.removeClass( 'expanded' );
					self.container.trigger( 'collapsed' );
				};

				self.container.trigger( 'collapse' );
				self.container.addClass( 'collapsing' );

				if ( self.params.is_wide ) {
					$inside.fadeOut( 'fast', complete );
				} else {
					$inside.slideUp( 'fast', function() {
						$widget.css( { width:'', margin:'' } );
						complete();
					} );
				}
			}
		},

		/**
		 * Expand the containing sidebar section, expand the form, and focus on
		 * the first input in the control
		 */
		focus: function() {
			this.expandControlSection();
			this.expandForm();
			this.container.find( '.widget-content :focusable:first' ).focus();
		},

		/**
		 * Get the position (index) of the widget in the containing sidebar
		 *
		 * @returns {Number}
		 */
		getWidgetSidebarPosition: function() {
			var sidebarWidgetIds, position;

			sidebarWidgetIds = this.getSidebarWidgetsControl().setting();
			position = _.indexOf( sidebarWidgetIds, this.params.widget_id );

			if ( position === -1 ) {
				return;
			}

			return position;
		},

		/**
		 * Move widget up one in the sidebar
		 */
		moveUp: function() {
			this._moveWidgetByOne( -1 );
		},

		/**
		 * Move widget up one in the sidebar
		 */
		moveDown: function() {
			this._moveWidgetByOne( 1 );
		},

		/**
		 * @private
		 *
		 * @param {Number} offset 1|-1
		 */
		_moveWidgetByOne: function( offset ) {
			var i, sidebarWidgetsSetting, sidebarWidgetIds,	adjacentWidgetId;

			i = this.getWidgetSidebarPosition();

			sidebarWidgetsSetting = this.getSidebarWidgetsControl().setting;
			sidebarWidgetIds = Array.prototype.slice.call( sidebarWidgetsSetting() ); // clone
			adjacentWidgetId = sidebarWidgetIds[i + offset];
			sidebarWidgetIds[i + offset] = this.params.widget_id;
			sidebarWidgetIds[i] = adjacentWidgetId;

			sidebarWidgetsSetting( sidebarWidgetIds );
		},

		/**
		 * Toggle visibility of the widget move area
		 *
		 * @param {Boolean} [showOrHide]
		 */
		toggleWidgetMoveArea: function( showOrHide ) {
			var self = this, $moveWidgetArea;

			$moveWidgetArea = this.container.find( '.move-widget-area' );

			if ( typeof showOrHide === 'undefined' ) {
				showOrHide = ! $moveWidgetArea.hasClass( 'active' );
			}

			if ( showOrHide ) {
				// reset the selected sidebar
				$moveWidgetArea.find( '.selected' ).removeClass( 'selected' );

				$moveWidgetArea.find( 'li' ).filter( function() {
					return $( this ).data( 'id' ) === self.params.sidebar_id;
				} ).addClass( 'selected' );

				this.container.find( '.move-widget-btn' ).prop( 'disabled', true );
			}

			$moveWidgetArea.toggleClass( 'active', showOrHide );
		},

		/**
		 * Highlight the widget control and section
		 */
		highlightSectionAndControl: function() {
			var $target;

			if ( this.container.is( ':hidden' ) ) {
				$target = this.container.closest( '.control-section' );
			} else {
				$target = this.container;
			}

			$( '.highlighted' ).removeClass( 'highlighted' );
			$target.addClass( 'highlighted' );

			setTimeout( function() {
				$target.removeClass( 'highlighted' );
			}, 500 );
		}
	} );

	/**
	 * wp.customize.Widgets.SidebarControl
	 *
	 * Customizer control for widgets.
	 * Note that 'sidebar_widgets' must match the WP_Widget_Area_Customize_Control::$type
	 *
	 * @constructor
	 * @augments wp.customize.Control
	 */
	api.Widgets.SidebarControl = api.Control.extend({
		/**
		 * Set up the control
		 */
		ready: function() {
			this.$controlSection = this.container.closest( '.control-section' );
			this.$sectionContent = this.container.closest( '.accordion-section-content' );

			this._setupModel();
			this._setupSortable();
			this._setupAddition();
			this._applyCardinalOrderClassNames();
		},

		/**
		 * Update ordering of widget control forms when the setting is updated
		 */
		_setupModel: function() {
			var self = this,
				registeredSidebar = api.Widgets.registeredSidebars.get( this.params.sidebar_id );

			this.setting.bind( function( newWidgetIds, oldWidgetIds ) {
				var widgetFormControls, $sidebarWidgetsAddControl, finalControlContainers, removedWidgetIds;

				removedWidgetIds = _( oldWidgetIds ).difference( newWidgetIds );

				// Filter out any persistent widget IDs for widgets which have been deactivated
				newWidgetIds = _( newWidgetIds ).filter( function( newWidgetId ) {
					var parsedWidgetId = parseWidgetId( newWidgetId );

					return !! api.Widgets.availableWidgets.findWhere( { id_base: parsedWidgetId.id_base } );
				} );

				widgetFormControls = _( newWidgetIds ).map( function( widgetId ) {
					var widgetFormControl = api.Widgets.getWidgetFormControlForWidget( widgetId );

					if ( ! widgetFormControl ) {
						widgetFormControl = self.addWidget( widgetId );
					}

					return widgetFormControl;
				} );

				// Sort widget controls to their new positions
				widgetFormControls.sort( function( a, b ) {
					var aIndex = _.indexOf( newWidgetIds, a.params.widget_id ),
						bIndex = _.indexOf( newWidgetIds, b.params.widget_id );

					if ( aIndex === bIndex ) {
						return 0;
					}

					return aIndex < bIndex ? -1 : 1;
				} );

				// Append the controls to put them in the right order
				finalControlContainers = _( widgetFormControls ).map( function( widgetFormControls ) {
					return widgetFormControls.container[0];
				} );

				$sidebarWidgetsAddControl = self.$sectionContent.find( '.customize-control-sidebar_widgets' );
				$sidebarWidgetsAddControl.before( finalControlContainers );

				// Re-sort widget form controls (including widgets form other sidebars newly moved here)
				self._applyCardinalOrderClassNames();

				// If the widget was dragged into the sidebar, make sure the sidebar_id param is updated
				_( widgetFormControls ).each( function( widgetFormControl ) {
					widgetFormControl.params.sidebar_id = self.params.sidebar_id;
				} );

				// Cleanup after widget removal
				_( removedWidgetIds ).each( function( removedWidgetId ) {

					// Using setTimeout so that when moving a widget to another sidebar, the other sidebars_widgets settings get a chance to update
					setTimeout( function() {
						var removedControl, wasDraggedToAnotherSidebar, inactiveWidgets, removedIdBase,
							widget, isPresentInAnotherSidebar = false;

						// Check if the widget is in another sidebar
						api.each( function( otherSetting ) {
							if ( otherSetting.id === self.setting.id || 0 !== otherSetting.id.indexOf( 'sidebars_widgets[' ) || otherSetting.id === 'sidebars_widgets[wp_inactive_widgets]' ) {
								return;
							}

							var otherSidebarWidgets = otherSetting(), i;

							i = _.indexOf( otherSidebarWidgets, removedWidgetId );
							if ( -1 !== i ) {
								isPresentInAnotherSidebar = true;
							}
						} );

						// If the widget is present in another sidebar, abort!
						if ( isPresentInAnotherSidebar ) {
							return;
						}

						removedControl = api.Widgets.getWidgetFormControlForWidget( removedWidgetId );

						// Detect if widget control was dragged to another sidebar
						wasDraggedToAnotherSidebar = removedControl && $.contains( document, removedControl.container[0] ) && ! $.contains( self.$sectionContent[0], removedControl.container[0] );

						// Delete any widget form controls for removed widgets
						if ( removedControl && ! wasDraggedToAnotherSidebar ) {
							api.control.remove( removedControl.id );
							removedControl.container.remove();
						}

						// Move widget to inactive widgets sidebar (move it to trash) if has been previously saved
						// This prevents the inactive widgets sidebar from overflowing with throwaway widgets
						if ( api.Widgets.savedWidgetIds[removedWidgetId] ) {
							inactiveWidgets = api.value( 'sidebars_widgets[wp_inactive_widgets]' )().slice();
							inactiveWidgets.push( removedWidgetId );
							api.value( 'sidebars_widgets[wp_inactive_widgets]' )( _( inactiveWidgets ).unique() );
						}

						// Make old single widget available for adding again
						removedIdBase = parseWidgetId( removedWidgetId ).id_base;
						widget = api.Widgets.availableWidgets.findWhere( { id_base: removedIdBase } );
						if ( widget && ! widget.get( 'is_multi' ) ) {
							widget.set( 'is_disabled', false );
						}
					} );

				} );
			} );

			// Update the model with whether or not the sidebar is rendered
			api.Widgets.Previewer.bind( 'rendered-sidebars', function( renderedSidebars ) {
				var isRendered = !! renderedSidebars[self.params.sidebar_id];

				registeredSidebar.set( 'is_rendered', isRendered );
			} );

			// Show the sidebar section when it becomes visible
			registeredSidebar.on( 'change:is_rendered', function( ) {
				var sectionSelector = '#accordion-section-sidebar-widgets-' + this.get( 'id' ), $section;

				$section = $( sectionSelector );
				if ( this.get( 'is_rendered' ) ) {
					$section.stop().slideDown( function() {
						$( this ).css( 'height', 'auto' ); // so that the .accordion-section-content won't overflow
					} );

				} else {
					// Make sure that hidden sections get closed first
					if ( $section.hasClass( 'open' ) ) {
						// it would be nice if accordionSwitch() in accordion.js was public
						$section.find( '.accordion-section-title' ).trigger( 'click' );
					}

					$section.stop().slideUp();
				}
			} );
		},

		/**
		 * Allow widgets in sidebar to be re-ordered, and for the order to be previewed
		 */
		_setupSortable: function() {
			var self = this;

			this.isReordering = false;

			/**
			 * Update widget order setting when controls are re-ordered
			 */
			this.$sectionContent.sortable( {
				items: '> .customize-control-widget_form',
				handle: '.widget-top',
				axis: 'y',
				connectWith: '.accordion-section-content:has(.customize-control-sidebar_widgets)',
				update: function() {
					var widgetContainerIds = self.$sectionContent.sortable( 'toArray' ), widgetIds;

					widgetIds = $.map( widgetContainerIds, function( widgetContainerId ) {
						return $( '#' + widgetContainerId ).find( ':input[name=widget-id]' ).val();
					} );

					self.setting( widgetIds );
				}
			} );

			/**
			 * Expand other customizer sidebar section when dragging a control widget over it,
			 * allowing the control to be dropped into another section
			 */
			this.$controlSection.find( '.accordion-section-title' ).droppable({
				accept: '.customize-control-widget_form',
				over: function() {
					if ( ! self.$controlSection.hasClass( 'open' ) ) {
						self.$controlSection.addClass( 'open' );
						self.$sectionContent.toggle( false ).slideToggle( 150, function() {
							self.$sectionContent.sortable( 'refreshPositions' );
						} );
					}
				}
			});

			/**
			 * Keyboard-accessible reordering
			 */
			this.container.find( '.reorder-toggle' ).on( 'click keydown', function( event ) {
				if ( event.type === 'keydown' && ! ( event.which === 13 || event.which === 32 ) ) { // Enter or Spacebar
					return;
				}

				self.toggleReordering( ! self.isReordering );
			} );
		},

		/**
		 * Set up UI for adding a new widget
		 */
		_setupAddition: function() {
			var self = this;

			this.container.find( '.add-new-widget' ).on( 'click keydown', function( event ) {
				if ( event.type === 'keydown' && ! ( event.which === 13 || event.which === 32 ) ) { // Enter or Spacebar
					return;
				}

				if ( self.$sectionContent.hasClass( 'reordering' ) ) {
					return;
				}

				if ( ! $( 'body' ).hasClass( 'adding-widget' ) ) {
					api.Widgets.availableWidgetsPanel.open( self );
				} else {
					api.Widgets.availableWidgetsPanel.close();
				}
			} );
		},

		/**
		 * Add classes to the widget_form controls to assist with styling
		 */
		_applyCardinalOrderClassNames: function() {
			this.$sectionContent.find( '.customize-control-widget_form' )
				.removeClass( 'first-widget' )
				.removeClass( 'last-widget' )
				.find( '.move-widget-down, .move-widget-up' ).prop( 'tabIndex', 0 );

			this.$sectionContent.find( '.customize-control-widget_form:first' )
				.addClass( 'first-widget' )
				.find( '.move-widget-up' ).prop( 'tabIndex', -1 );

			this.$sectionContent.find( '.customize-control-widget_form:last' )
				.addClass( 'last-widget' )
				.find( '.move-widget-down' ).prop( 'tabIndex', -1 );
		},


		/***********************************************************************
		 * Begin public API methods
		 **********************************************************************/

		/**
		 * Enable/disable the reordering UI
		 *
		 * @param {Boolean} showOrHide to enable/disable reordering
		 */
		toggleReordering: function( showOrHide ) {
			showOrHide = Boolean( showOrHide );

			if ( showOrHide === this.$sectionContent.hasClass( 'reordering' ) ) {
				return;
			}

			this.isReordering = showOrHide;
			this.$sectionContent.toggleClass( 'reordering', showOrHide );

			if ( showOrHide ) {
				_( this.getWidgetFormControls() ).each( function( formControl ) {
					formControl.collapseForm();
				} );
			}
		},

		/**
		 * @return {wp.customize.controlConstructor.widget_form[]}
		 */
		getWidgetFormControls: function() {
			var formControls;

			formControls = _( this.setting() ).map( function( widgetId ) {
				var settingId = widgetIdToSettingId( widgetId ),
					formControl = api.control( settingId );

				if ( ! formControl ) {
					return;
				}

				return formControl;
			} );

			return formControls;
		},

		/**
		 * @param {string} widgetId or an id_base for adding a previously non-existing widget
		 * @returns {object|false} widget_form control instance, or false on error
		 */
		addWidget: function( widgetId ) {
			var self = this, controlHtml, $widget, controlType = 'widget_form', $control, controlConstructor,
				parsedWidgetId = parseWidgetId( widgetId ),
				widgetNumber = parsedWidgetId.number,
				widgetIdBase = parsedWidgetId.id_base,
				widget = api.Widgets.availableWidgets.findWhere( {id_base: widgetIdBase} ),
				settingId, isExistingWidget, widgetFormControl,	sidebarWidgets,	settingArgs;

			if ( ! widget ) {
				return false;
			}

			if ( widgetNumber && ! widget.get( 'is_multi' ) ) {
				return false;
			}

			// Set up new multi widget
			if ( widget.get( 'is_multi' ) && ! widgetNumber ) {
				widget.set( 'multi_number', widget.get( 'multi_number' ) + 1 );
				widgetNumber = widget.get( 'multi_number' );
			}

			controlHtml = $.trim( $( '#widget-tpl-' + widget.get( 'id' ) ).html() );
			if ( widget.get( 'is_multi' ) ) {
				controlHtml = controlHtml.replace( /<[^<>]+>/g, function( m ) {
					return m.replace( /__i__|%i%/g, widgetNumber );
				} );
			} else {
				widget.set( 'is_disabled', true ); // Prevent single widget from being added again now
			}

			$widget = $( controlHtml );

			$control = $( '<li/>' )
				.addClass( 'customize-control' )
				.addClass( 'customize-control-' + controlType )
				.append( $widget );

			// Remove icon which is visible inside the panel
			$control.find( '> .widget-icon' ).remove();

			if ( widget.get( 'is_multi' ) ) {
				$control.find( 'input[name="widget_number"]' ).val( widgetNumber );
				$control.find( 'input[name="multi_number"]' ).val( widgetNumber );
			}

			widgetId = $control.find( '[name="widget-id"]' ).val();

			$control.hide(); // to be slid-down below

			settingId = 'widget_' + widget.get( 'id_base' );
			if ( widget.get( 'is_multi' ) ) {
				settingId += '[' + widgetNumber + ']';
			}
			$control.attr( 'id', 'customize-control-' + settingId.replace( /\]/g, '' ).replace( /\[/g, '-' ) );

			this.container.after( $control );

			// Only create setting if it doesn't already exist (if we're adding a pre-existing inactive widget)
			isExistingWidget = api.has( settingId );
			if ( ! isExistingWidget ) {
				settingArgs = {
					transport: 'refresh',
					previewer: this.setting.previewer
				};
				api.create( settingId, settingId, {}, settingArgs );
			}

			controlConstructor = api.controlConstructor[controlType];
			widgetFormControl = new controlConstructor( settingId, {
				params: {
					settings: {
						'default': settingId
					},
					sidebar_id: self.params.sidebar_id,
					widget_id: widgetId,
					widget_id_base: widget.get( 'id_base' ),
					type: controlType,
					is_new: ! isExistingWidget,
					width: widget.get( 'width' ),
					height: widget.get( 'height' ),
					is_wide: widget.get( 'is_wide' )
				},
				previewer: self.setting.previewer
			} );
			api.control.add( settingId, widgetFormControl );

			// Make sure widget is removed from the other sidebars
			api.each( function( otherSetting ) {
				if ( otherSetting.id === self.setting.id ) {
					return;
				}

				if ( 0 !== otherSetting.id.indexOf( 'sidebars_widgets[' ) ) {
					return;
				}

				var otherSidebarWidgets = otherSetting().slice(),
					i = _.indexOf( otherSidebarWidgets, widgetId );

				if ( -1 !== i ) {
					otherSidebarWidgets.splice( i );
					otherSetting( otherSidebarWidgets );
				}
			} );

			// Add widget to this sidebar
			sidebarWidgets = this.setting().slice();
			if ( -1 === _.indexOf( sidebarWidgets, widgetId ) ) {
				sidebarWidgets.push( widgetId );
				this.setting( sidebarWidgets );
			}

			$control.slideDown( function() {
				if ( isExistingWidget ) {
					widgetFormControl.expandForm();
					widgetFormControl.updateWidget( {
						instance: widgetFormControl.setting(),
						complete: function( error ) {
							if ( error ) {
								throw error;
							}
							widgetFormControl.focus();
						}
					} );
				} else {
					widgetFormControl.focus();
				}
			} );

			$( document ).trigger( 'widget-added', [ $widget ] );

			return widgetFormControl;
		}
	} );

	/**
	 * Extends wp.customizer.controlConstructor with control constructor for
	 * widget_form and sidebar_widgets.
	 */
	$.extend( api.controlConstructor, {
		widget_form: api.Widgets.WidgetControl,
		sidebar_widgets: api.Widgets.SidebarControl
	});

	/**
	 * Capture the instance of the Previewer since it is private
	 */
	OldPreviewer = api.Previewer;
	api.Previewer = OldPreviewer.extend({
		initialize: function( params, options ) {
			api.Widgets.Previewer = this;
			OldPreviewer.prototype.initialize.call( this, params, options );
			this.bind( 'refresh', this.refresh );
		}
	} );

	/**
	 * Init Customizer for widgets.
	 */
	api.bind( 'ready', function() {
		// Set up the widgets panel
		api.Widgets.availableWidgetsPanel = new api.Widgets.AvailableWidgetsPanelView({
			collection: api.Widgets.availableWidgets
		});

		// Highlight widget control
		api.Widgets.Previewer.bind( 'highlight-widget-control', api.Widgets.highlightWidgetFormControl );

		// Open and focus widget control
		api.Widgets.Previewer.bind( 'focus-widget-control', api.Widgets.focusWidgetFormControl );
	} );

	/**
	 * Highlight a widget control.
	 *
	 * @param {string} widgetId
	 */
	api.Widgets.highlightWidgetFormControl = function( widgetId ) {
		var control = api.Widgets.getWidgetFormControlForWidget( widgetId );

		if ( control ) {
			control.highlightSectionAndControl();
		}
	},

	/**
	 * Focus a widget control.
	 *
	 * @param {string} widgetId
	 */
	api.Widgets.focusWidgetFormControl = function( widgetId ) {
		var control = api.Widgets.getWidgetFormControlForWidget( widgetId );

		if ( control ) {
			control.focus();
		}
	},

	/**
	 * Given a widget control, find the sidebar widgets control that contains it.
	 * @param {string} widgetId
	 * @return {object|null}
	 */
	api.Widgets.getSidebarWidgetControlContainingWidget = function( widgetId ) {
		var foundControl = null;

		// @todo this can use widgetIdToSettingId(), then pass into wp.customize.control( x ).getSidebarWidgetsControl()
		api.control.each( function( control ) {
			if ( control.params.type === 'sidebar_widgets' && -1 !== _.indexOf( control.setting(), widgetId ) ) {
				foundControl = control;
			}
		} );

		return foundControl;
	};

	/**
	 * Given a widget ID for a widget appearing in the preview, get the widget form control associated with it.
	 *
	 * @param {string} widgetId
	 * @return {object|null}
	 */
	api.Widgets.getWidgetFormControlForWidget = function( widgetId ) {
		var foundControl = null;

		// @todo We can just use widgetIdToSettingId() here
		api.control.each( function( control ) {
			if ( control.params.type === 'widget_form' && control.params.widget_id === widgetId ) {
				foundControl = control;
			}
		} );

		return foundControl;
	};

	/**
	 * @param {String} widgetId
	 * @returns {Object}
	 */
	function parseWidgetId( widgetId ) {
		var matches, parsed = {
			number: null,
			id_base: null
		};

		matches = widgetId.match( /^(.+)-(\d+)$/ );
		if ( matches ) {
			parsed.id_base = matches[1];
			parsed.number = parseInt( matches[2], 10 );
		} else {
			// likely an old single widget
			parsed.id_base = widgetId;
		}

		return parsed;
	}

	/**
	 * @param {String} widgetId
	 * @returns {String} settingId
	 */
	function widgetIdToSettingId( widgetId ) {
		var parsed = parseWidgetId( widgetId ), settingId;

		settingId = 'widget_' + parsed.id_base;
		if ( parsed.number ) {
			settingId += '[' + parsed.number + ']';
		}

		return settingId;
	}

})( window.wp, jQuery );
