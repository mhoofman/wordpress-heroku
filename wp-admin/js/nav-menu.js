/**
 * WordPress Administration Navigation Menu
 * Interface JS functions
 *
 * @version 2.0.0
 *
 * @package WordPress
 * @subpackage Administration
 */

var wpNavMenu;

(function($) {

	var api = wpNavMenu = {

		options : {
			menuItemDepthPerLevel : 30, // Do not use directly. Use depthToPx and pxToDepth instead.
			globalMaxDepth : 11
		},

		menuList : undefined,	// Set in init.
		targetList : undefined, // Set in init.
		menusChanged : false,
		isRTL: !! ( 'undefined' != typeof isRtl && isRtl ),
		negateIfRTL: ( 'undefined' != typeof isRtl && isRtl ) ? -1 : 1,

		// Functions that run on init.
		init : function() {
			api.menuList = $('#menu-to-edit');
			api.targetList = api.menuList;

			this.jQueryExtensions();

			this.attachMenuEditListeners();

			this.setupInputWithDefaultTitle();
			this.attachQuickSearchListeners();
			this.attachThemeLocationsListeners();

			this.attachTabsPanelListeners();

			this.attachUnsavedChangesListener();

			if ( api.menuList.length )
				this.initSortables();

			if ( menus.oneThemeLocationNoMenus )
				$( '#posttype-page' ).addSelectedToMenu( api.addMenuItemToBottom );

			this.initManageLocations();

			this.initAccessibility();

			this.initToggles();
		},

		jQueryExtensions : function() {
			// jQuery extensions
			$.fn.extend({
				menuItemDepth : function() {
					var margin = api.isRTL ? this.eq(0).css('margin-right') : this.eq(0).css('margin-left');
					return api.pxToDepth( margin && -1 != margin.indexOf('px') ? margin.slice(0, -2) : 0 );
				},
				updateDepthClass : function(current, prev) {
					return this.each(function(){
						var t = $(this);
						prev = prev || t.menuItemDepth();
						$(this).removeClass('menu-item-depth-'+ prev )
							.addClass('menu-item-depth-'+ current );
					});
				},
				shiftDepthClass : function(change) {
					return this.each(function(){
						var t = $(this),
							depth = t.menuItemDepth();
						$(this).removeClass('menu-item-depth-'+ depth )
							.addClass('menu-item-depth-'+ (depth + change) );
					});
				},
				childMenuItems : function() {
					var result = $();
					this.each(function(){
						var t = $(this), depth = t.menuItemDepth(), next = t.next();
						while( next.length && next.menuItemDepth() > depth ) {
							result = result.add( next );
							next = next.next();
						}
					});
					return result;
				},
				shiftHorizontally : function( dir ) {
					return this.each(function(){
						var t = $(this),
							depth = t.menuItemDepth(),
							newDepth = depth + dir;

						// Change .menu-item-depth-n class
						t.moveHorizontally( newDepth, depth );
					});
				},
				moveHorizontally : function( newDepth, depth ) {
					return this.each(function(){
						var t = $(this),
							children = t.childMenuItems(),
							diff = newDepth - depth,
							subItemText = t.find('.is-submenu');

						// Change .menu-item-depth-n class
						t.updateDepthClass( newDepth, depth ).updateParentMenuItemDBId();

						// If it has children, move those too
						if ( children ) {
							children.each(function( index ) {
								var t = $(this),
									thisDepth = t.menuItemDepth(),
									newDepth = thisDepth + diff;
								t.updateDepthClass(newDepth, thisDepth).updateParentMenuItemDBId();
							});
						}

						// Show "Sub item" helper text
						if (0 === newDepth)
							subItemText.hide();
						else
							subItemText.show();
					});
				},
				updateParentMenuItemDBId : function() {
					return this.each(function(){
						var item = $(this),
							input = item.find( '.menu-item-data-parent-id' ),
							depth = parseInt( item.menuItemDepth() ),
							parentDepth = depth - 1,
							parent = item.prevAll( '.menu-item-depth-' + parentDepth ).first();

						if ( 0 == depth ) { // Item is on the top level, has no parent
							input.val(0);
						} else { // Find the parent item, and retrieve its object id.
							input.val( parent.find( '.menu-item-data-db-id' ).val() );
						}
					});
				},
				hideAdvancedMenuItemFields : function() {
					return this.each(function(){
						var that = $(this);
						$('.hide-column-tog').not(':checked').each(function(){
							that.find('.field-' + $(this).val() ).addClass('hidden-field');
						});
					});
				},
				/**
				 * Adds selected menu items to the menu.
				 *
				 * @param jQuery metabox The metabox jQuery object.
				 */
				addSelectedToMenu : function(processMethod) {
					if ( 0 == $('#menu-to-edit').length ) {
						return false;
					}

					return this.each(function() {
						var t = $(this), menuItems = {},
							checkboxes = ( menus.oneThemeLocationNoMenus && 0 == t.find('.tabs-panel-active .categorychecklist li input:checked').length ) ? t.find('#page-all li input[type="checkbox"]') : t.find('.tabs-panel-active .categorychecklist li input:checked'),
							re = new RegExp('menu-item\\[(\[^\\]\]*)');

						processMethod = processMethod || api.addMenuItemToBottom;

						// If no items are checked, bail.
						if ( !checkboxes.length )
							return false;

						// Show the ajax spinner
						t.find('.spinner').show();

						// Retrieve menu item data
						$(checkboxes).each(function(){
							var t = $(this),
								listItemDBIDMatch = re.exec( t.attr('name') ),
								listItemDBID = 'undefined' == typeof listItemDBIDMatch[1] ? 0 : parseInt(listItemDBIDMatch[1], 10);

							if ( this.className && -1 != this.className.indexOf('add-to-top') )
								processMethod = api.addMenuItemToTop;
							menuItems[listItemDBID] = t.closest('li').getItemData( 'add-menu-item', listItemDBID );
						});

						// Add the items
						api.addItemToMenu(menuItems, processMethod, function(){
							// Deselect the items and hide the ajax spinner
							checkboxes.removeAttr('checked');
							t.find('.spinner').hide();
						});
					});
				},
				getItemData : function( itemType, id ) {
					itemType = itemType || 'menu-item';

					var itemData = {}, i,
					fields = [
						'menu-item-db-id',
						'menu-item-object-id',
						'menu-item-object',
						'menu-item-parent-id',
						'menu-item-position',
						'menu-item-type',
						'menu-item-title',
						'menu-item-url',
						'menu-item-description',
						'menu-item-attr-title',
						'menu-item-target',
						'menu-item-classes',
						'menu-item-xfn'
					];

					if( !id && itemType == 'menu-item' ) {
						id = this.find('.menu-item-data-db-id').val();
					}

					if( !id ) return itemData;

					this.find('input').each(function() {
						var field;
						i = fields.length;
						while ( i-- ) {
							if( itemType == 'menu-item' )
								field = fields[i] + '[' + id + ']';
							else if( itemType == 'add-menu-item' )
								field = 'menu-item[' + id + '][' + fields[i] + ']';

							if (
								this.name &&
								field == this.name
							) {
								itemData[fields[i]] = this.value;
							}
						}
					});

					return itemData;
				},
				setItemData : function( itemData, itemType, id ) { // Can take a type, such as 'menu-item', or an id.
					itemType = itemType || 'menu-item';

					if( !id && itemType == 'menu-item' ) {
						id = $('.menu-item-data-db-id', this).val();
					}

					if( !id ) return this;

					this.find('input').each(function() {
						var t = $(this), field;
						$.each( itemData, function( attr, val ) {
							if( itemType == 'menu-item' )
								field = attr + '[' + id + ']';
							else if( itemType == 'add-menu-item' )
								field = 'menu-item[' + id + '][' + attr + ']';

							if ( field == t.attr('name') ) {
								t.val( val );
							}
						});
					});
					return this;
				}
			});
		},

		countMenuItems : function( depth ) {
			return $( '.menu-item-depth-' + depth ).length;
		},

		moveMenuItem : function( $this, dir ) {

			var menuItems = $('#menu-to-edit li');
				menuItemsCount = menuItems.length,
				thisItem = $this.parents( 'li.menu-item' ),
				thisItemChildren = thisItem.childMenuItems(),
				thisItemData = thisItem.getItemData(),
				thisItemDepth = parseInt( thisItem.menuItemDepth() ),
				thisItemPosition = parseInt( thisItem.index() ),
				nextItem = thisItem.next(),
				nextItemChildren = nextItem.childMenuItems(),
				nextItemDepth = parseInt( nextItem.menuItemDepth() ) + 1,
				prevItem = thisItem.prev(),
				prevItemDepth = parseInt( prevItem.menuItemDepth() ),
				prevItemId = prevItem.getItemData()['menu-item-db-id'];

			switch ( dir ) {
			case 'up':
				var newItemPosition = thisItemPosition - 1;

				// Already at top
				if ( 0 === thisItemPosition )
					break;

				// If a sub item is moved to top, shift it to 0 depth
				if ( 0 === newItemPosition && 0 !== thisItemDepth )
					thisItem.moveHorizontally( 0, thisItemDepth );

				// If prev item is sub item, shift to match depth
				if ( 0 !== prevItemDepth )
					thisItem.moveHorizontally( prevItemDepth, thisItemDepth );

				// Does this item have sub items?
				if ( thisItemChildren ) {
					var items = thisItem.add( thisItemChildren );
					// Move the entire block
					items.detach().insertBefore( menuItems.eq( newItemPosition ) ).updateParentMenuItemDBId();
				} else {
					thisItem.detach().insertBefore( menuItems.eq( newItemPosition ) ).updateParentMenuItemDBId();
				}
				break;
			case 'down':
				// Does this item have sub items?
				if ( thisItemChildren ) {
					var items = thisItem.add( thisItemChildren ),
						nextItem = menuItems.eq( items.length + thisItemPosition ),
						nextItemChildren = 0 !== nextItem.childMenuItems().length;

					if ( nextItemChildren ) {
						var newDepth = parseInt( nextItem.menuItemDepth() ) + 1;
						thisItem.moveHorizontally( newDepth, thisItemDepth );
					}

					// Have we reached the bottom?
					if ( menuItemsCount === thisItemPosition + items.length )
						break;

					items.detach().insertAfter( menuItems.eq( thisItemPosition + items.length ) ).updateParentMenuItemDBId();
				} else {
					// If next item has sub items, shift depth
					if ( 0 !== nextItemChildren.length )
						thisItem.moveHorizontally( nextItemDepth, thisItemDepth );

					// Have we reached the bottom
					if ( menuItemsCount === thisItemPosition + 1 )
						break;
					thisItem.detach().insertAfter( menuItems.eq( thisItemPosition + 1 ) ).updateParentMenuItemDBId();
				}
				break;
			case 'top':
				// Already at top
				if ( 0 === thisItemPosition )
					break;
				// Does this item have sub items?
				if ( thisItemChildren ) {
					var items = thisItem.add( thisItemChildren );
					// Move the entire block
					items.detach().insertBefore( menuItems.eq( 0 ) ).updateParentMenuItemDBId();
				} else {
					thisItem.detach().insertBefore( menuItems.eq( 0 ) ).updateParentMenuItemDBId();
				}
				break;
			case 'left':
				// As far left as possible
				if ( 0 === thisItemDepth )
					break;
				thisItem.shiftHorizontally( -1 );
				break;
			case 'right':
				// Can't be sub item at top
				if ( 0 === thisItemPosition )
					break;
				// Already sub item of prevItem
				if ( thisItemData['menu-item-parent-id'] === prevItemId )
					break;
				thisItem.shiftHorizontally( 1 );
				break;
			}
			$this.focus();
			api.registerChange();
			api.refreshKeyboardAccessibility();
			api.refreshAdvancedAccessibility();
		},

		initAccessibility : function() {
			api.refreshKeyboardAccessibility();
			api.refreshAdvancedAccessibility();

			// Events
			$( '.menus-move-up' ).on( 'click', function ( e ) {
				api.moveMenuItem( $( this ).parents( 'li.menu-item' ).find( 'a.item-edit' ), 'up' );
				e.preventDefault();
			});
			$( '.menus-move-down' ).on( 'click', function ( e ) {
				api.moveMenuItem( $( this ).parents( 'li.menu-item' ).find( 'a.item-edit' ), 'down' );
				e.preventDefault();
			});
			$( '.menus-move-top' ).on( 'click', function ( e ) {
				api.moveMenuItem( $( this ).parents( 'li.menu-item' ).find( 'a.item-edit' ), 'top' );
				e.preventDefault();
			});
			$( '.menus-move-left' ).on( 'click', function ( e ) {
				api.moveMenuItem( $( this ).parents( 'li.menu-item' ).find( 'a.item-edit' ), 'left' );
				e.preventDefault();
			});
			$( '.menus-move-right' ).on( 'click', function ( e ) {
				api.moveMenuItem( $( this ).parents( 'li.menu-item' ).find( 'a.item-edit' ), 'right' );
				e.preventDefault();
			});
		},

		refreshAdvancedAccessibility : function() {

			// Hide all links by default
			$( '.menu-item-settings .field-move a' ).css( 'display', 'none' );

			$( '.item-edit' ).each( function() {
				var $this = $(this),
					movement = [],
					availableMovement = '',
					menuItem = $this.closest( 'li.menu-item' ).first(),
					depth = menuItem.menuItemDepth(),
					isPrimaryMenuItem = ( 0 === depth ),
					itemName = $this.closest( '.menu-item-handle' ).find( '.menu-item-title' ).text(),
					position = parseInt( menuItem.index() ),
					prevItemDepth = ( isPrimaryMenuItem ) ? depth : parseInt( depth - 1 ),
					prevItemNameLeft = menuItem.prevAll('.menu-item-depth-' + prevItemDepth).first().find( '.menu-item-title' ).text(),
					prevItemNameRight = menuItem.prevAll('.menu-item-depth-' + depth).first().find( '.menu-item-title' ).text(),
					totalMenuItems = $('#menu-to-edit li').length,
					hasSameDepthSibling = menuItem.nextAll( '.menu-item-depth-' + depth ).length;

				// Where can they move this menu item?
				if ( 0 !== position ) {
					var thisLink = menuItem.find( '.menus-move-up' );
					thisLink.prop( 'title', menus.moveUp ).css( 'display', 'inline' );
				}

				if ( 0 !== position && isPrimaryMenuItem ) {
					var thisLink = menuItem.find( '.menus-move-top' );
					thisLink.prop( 'title', menus.moveToTop ).css( 'display', 'inline' );
				}

				if ( position + 1 !== totalMenuItems && 0 !== position ) {
					var thisLink = menuItem.find( '.menus-move-down' );
					thisLink.prop( 'title', menus.moveDown ).css( 'display', 'inline' );
				}

				if ( 0 === position && 0 !== hasSameDepthSibling ) {
					var thisLink = menuItem.find( '.menus-move-down' );
					thisLink.prop( 'title', menus.moveDown ).css( 'display', 'inline' );
				}

				if ( ! isPrimaryMenuItem ) {
					var thisLink = menuItem.find( '.menus-move-left' ),
						thisLinkText = menus.outFrom.replace( '%s', prevItemNameLeft );
					thisLink.prop( 'title', menus.moveOutFrom.replace( '%s', prevItemNameLeft ) ).html( thisLinkText ).css( 'display', 'inline' );
				}

				if ( 0 !== position ) {
					if ( menuItem.find( '.menu-item-data-parent-id' ).val() !== menuItem.prev().find( '.menu-item-data-db-id' ).val() ) {
						var thisLink = menuItem.find( '.menus-move-right' ),
							thisLinkText = menus.under.replace( '%s', prevItemNameRight );
						thisLink.prop( 'title', menus.moveUnder.replace( '%s', prevItemNameRight ) ).html( thisLinkText ).css( 'display', 'inline' );
					}
				}

				if ( isPrimaryMenuItem ) {
					var primaryItems = $( '.menu-item-depth-0' ),
						itemPosition = primaryItems.index( menuItem ) + 1,
						totalMenuItems = primaryItems.length,

						// String together help text for primary menu items
						title = menus.menuFocus.replace( '%1$s', itemName ).replace( '%2$d', itemPosition ).replace( '%3$d', totalMenuItems );
				} else {
					var parentItem = menuItem.prevAll( '.menu-item-depth-' + parseInt( depth - 1 ) ).first(),
						parentItemId = parentItem.find( '.menu-item-data-db-id' ).val(),
						parentItemName = parentItem.find( '.menu-item-title' ).text(),
						subItems = $( '.menu-item .menu-item-data-parent-id[value="' + parentItemId + '"]' ),
						itemPosition = $( subItems.parents('.menu-item').get().reverse() ).index( menuItem ) + 1;

						// String together help text for sub menu items
						title = menus.subMenuFocus.replace( '%1$s', itemName ).replace( '%2$d', itemPosition ).replace( '%3$s', parentItemName );
				}

				$this.prop('title', title).html( title );
			});
		},

		refreshKeyboardAccessibility : function() {
			$( '.item-edit' ).off( 'focus' ).on( 'focus', function(){
				$(this).off( 'keydown' ).on( 'keydown', function(e){

					var $this = $(this);

					// Bail if it's not an arrow key
					if ( 37 != e.which && 38 != e.which && 39 != e.which && 40 != e.which )
						return;

					// Avoid multiple keydown events
					$this.off('keydown');

					// Bail if there is only one menu item
					if ( 1 === $('#menu-to-edit li').length )
						return;

					// If RTL, swap left/right arrows
					var arrows = { '38' : 'up', '40' : 'down', '37' : 'left', '39' : 'right' };
					if ( $('body').hasClass('rtl') )
						arrows = { '38' : 'up', '40' : 'down', '39' : 'left', '37' : 'right' };

					switch ( arrows[e.which] ) {
					case 'up':
						api.moveMenuItem( $this, 'up' );
						break;
					case 'down':
						api.moveMenuItem( $this, 'down' );
						break;
					case 'left':
						api.moveMenuItem( $this, 'left' );
						break;
					case 'right':
						api.moveMenuItem( $this, 'right' );
						break;
					}
					// Put focus back on same menu item
					$( '#edit-' + thisItemData['menu-item-db-id'] ).focus();
					return false;
				});
			});
		},

		initToggles : function() {
			// init postboxes
			postboxes.add_postbox_toggles('nav-menus');

			// adjust columns functions for menus UI
			columns.useCheckboxesForHidden();
			columns.checked = function(field) {
				$('.field-' + field).removeClass('hidden-field');
			}
			columns.unchecked = function(field) {
				$('.field-' + field).addClass('hidden-field');
			}
			// hide fields
			api.menuList.hideAdvancedMenuItemFields();

			$('.hide-postbox-tog').click(function () {
				var hidden = $( '.accordion-container li.accordion-section' ).filter(':hidden').map(function() { return this.id; }).get().join(',');
				$.post(ajaxurl, {
					action: 'closed-postboxes',
					hidden: hidden,
					closedpostboxesnonce: jQuery('#closedpostboxesnonce').val(),
					page: 'nav-menus'
				});
			});
		},

		initSortables : function() {
			var currentDepth = 0, originalDepth, minDepth, maxDepth,
				prev, next, prevBottom, nextThreshold, helperHeight, transport,
				menuEdge = api.menuList.offset().left,
				body = $('body'), maxChildDepth,
				menuMaxDepth = initialMenuMaxDepth();

			if( 0 != $( '#menu-to-edit li' ).length )
				$( '.drag-instructions' ).show();

			// Use the right edge if RTL.
			menuEdge += api.isRTL ? api.menuList.width() : 0;

			api.menuList.sortable({
				handle: '.menu-item-handle',
				placeholder: 'sortable-placeholder',
				start: function(e, ui) {
					var height, width, parent, children, tempHolder;

					// handle placement for rtl orientation
					if ( api.isRTL )
						ui.item[0].style.right = 'auto';

					transport = ui.item.children('.menu-item-transport');

					// Set depths. currentDepth must be set before children are located.
					originalDepth = ui.item.menuItemDepth();
					updateCurrentDepth(ui, originalDepth);

					// Attach child elements to parent
					// Skip the placeholder
					parent = ( ui.item.next()[0] == ui.placeholder[0] ) ? ui.item.next() : ui.item;
					children = parent.childMenuItems();
					transport.append( children );

					// Update the height of the placeholder to match the moving item.
					height = transport.outerHeight();
					// If there are children, account for distance between top of children and parent
					height += ( height > 0 ) ? (ui.placeholder.css('margin-top').slice(0, -2) * 1) : 0;
					height += ui.helper.outerHeight();
					helperHeight = height;
					height -= 2; // Subtract 2 for borders
					ui.placeholder.height(height);

					// Update the width of the placeholder to match the moving item.
					maxChildDepth = originalDepth;
					children.each(function(){
						var depth = $(this).menuItemDepth();
						maxChildDepth = (depth > maxChildDepth) ? depth : maxChildDepth;
					});
					width = ui.helper.find('.menu-item-handle').outerWidth(); // Get original width
					width += api.depthToPx(maxChildDepth - originalDepth); // Account for children
					width -= 2; // Subtract 2 for borders
					ui.placeholder.width(width);

					// Update the list of menu items.
					tempHolder = ui.placeholder.next();
					tempHolder.css( 'margin-top', helperHeight + 'px' ); // Set the margin to absorb the placeholder
					ui.placeholder.detach(); // detach or jQuery UI will think the placeholder is a menu item
					$(this).sortable( "refresh" ); // The children aren't sortable. We should let jQ UI know.
					ui.item.after( ui.placeholder ); // reattach the placeholder.
					tempHolder.css('margin-top', 0); // reset the margin

					// Now that the element is complete, we can update...
					updateSharedVars(ui);
				},
				stop: function(e, ui) {
					var children, depthChange = currentDepth - originalDepth;

					// Return child elements to the list
					children = transport.children().insertAfter(ui.item);

					// Add "sub menu" description
					var subMenuTitle = ui.item.find( '.item-title .is-submenu' );
					if ( 0 < currentDepth )
						subMenuTitle.show();
					else
						subMenuTitle.hide();

					// Update depth classes
					if( depthChange != 0 ) {
						ui.item.updateDepthClass( currentDepth );
						children.shiftDepthClass( depthChange );
						updateMenuMaxDepth( depthChange );
					}
					// Register a change
					api.registerChange();
					// Update the item data.
					ui.item.updateParentMenuItemDBId();

					// address sortable's incorrectly-calculated top in opera
					ui.item[0].style.top = 0;

					// handle drop placement for rtl orientation
					if ( api.isRTL ) {
						ui.item[0].style.left = 'auto';
						ui.item[0].style.right = 0;
					}

					api.refreshKeyboardAccessibility();
					api.refreshAdvancedAccessibility();
				},
				change: function(e, ui) {
					// Make sure the placeholder is inside the menu.
					// Otherwise fix it, or we're in trouble.
					if( ! ui.placeholder.parent().hasClass('menu') )
						(prev.length) ? prev.after( ui.placeholder ) : api.menuList.prepend( ui.placeholder );

					updateSharedVars(ui);
				},
				sort: function(e, ui) {
					var offset = ui.helper.offset(),
						edge = api.isRTL ? offset.left + ui.helper.width() : offset.left,
						depth = api.negateIfRTL * api.pxToDepth( edge - menuEdge );
					// Check and correct if depth is not within range.
					// Also, if the dragged element is dragged upwards over
					// an item, shift the placeholder to a child position.
					if ( depth > maxDepth || offset.top < prevBottom ) depth = maxDepth;
					else if ( depth < minDepth ) depth = minDepth;

					if( depth != currentDepth )
						updateCurrentDepth(ui, depth);

					// If we overlap the next element, manually shift downwards
					if( nextThreshold && offset.top + helperHeight > nextThreshold ) {
						next.after( ui.placeholder );
						updateSharedVars( ui );
						$(this).sortable( "refreshPositions" );
					}
				}
			});

			function updateSharedVars(ui) {
				var depth;

				prev = ui.placeholder.prev();
				next = ui.placeholder.next();

				// Make sure we don't select the moving item.
				if( prev[0] == ui.item[0] ) prev = prev.prev();
				if( next[0] == ui.item[0] ) next = next.next();

				prevBottom = (prev.length) ? prev.offset().top + prev.height() : 0;
				nextThreshold = (next.length) ? next.offset().top + next.height() / 3 : 0;
				minDepth = (next.length) ? next.menuItemDepth() : 0;

				if( prev.length )
					maxDepth = ( (depth = prev.menuItemDepth() + 1) > api.options.globalMaxDepth ) ? api.options.globalMaxDepth : depth;
				else
					maxDepth = 0;
			}

			function updateCurrentDepth(ui, depth) {
				ui.placeholder.updateDepthClass( depth, currentDepth );
				currentDepth = depth;
			}

			function initialMenuMaxDepth() {
				if( ! body[0].className ) return 0;
				var match = body[0].className.match(/menu-max-depth-(\d+)/);
				return match && match[1] ? parseInt(match[1]) : 0;
			}

			function updateMenuMaxDepth( depthChange ) {
				var depth, newDepth = menuMaxDepth;
				if ( depthChange === 0 ) {
					return;
				} else if ( depthChange > 0 ) {
					depth = maxChildDepth + depthChange;
					if( depth > menuMaxDepth )
						newDepth = depth;
				} else if ( depthChange < 0 && maxChildDepth == menuMaxDepth ) {
					while( ! $('.menu-item-depth-' + newDepth, api.menuList).length && newDepth > 0 )
						newDepth--;
				}
				// Update the depth class.
				body.removeClass( 'menu-max-depth-' + menuMaxDepth ).addClass( 'menu-max-depth-' + newDepth );
				menuMaxDepth = newDepth;
			}
		},

		initManageLocations : function () {
			$('#menu-locations-wrap form').submit(function(){
				window.onbeforeunload = null;
			});
			$('.menu-location-menus select').on('change', function () {
				var editLink = $(this).closest('tr').find('.locations-edit-menu-link');
				if ($(this).find('option:selected').data('orig'))
					editLink.show();
				else
					editLink.hide();
			});
		},

		attachMenuEditListeners : function() {
			var that = this;
			$('#update-nav-menu').bind('click', function(e) {
				if ( e.target && e.target.className ) {
					if ( -1 != e.target.className.indexOf('item-edit') ) {
						return that.eventOnClickEditLink(e.target);
					} else if ( -1 != e.target.className.indexOf('menu-save') ) {
						return that.eventOnClickMenuSave(e.target);
					} else if ( -1 != e.target.className.indexOf('menu-delete') ) {
						return that.eventOnClickMenuDelete(e.target);
					} else if ( -1 != e.target.className.indexOf('item-delete') ) {
						return that.eventOnClickMenuItemDelete(e.target);
					} else if ( -1 != e.target.className.indexOf('item-cancel') ) {
						return that.eventOnClickCancelLink(e.target);
					}
				}
			});
			$('#add-custom-links input[type="text"]').keypress(function(e){
				if ( e.keyCode === 13 ) {
					e.preventDefault();
					$("#submit-customlinkdiv").click();
				}
			});
		},

		/**
		 * An interface for managing default values for input elements
		 * that is both JS and accessibility-friendly.
		 *
		 * Input elements that add the class 'input-with-default-title'
		 * will have their values set to the provided HTML title when empty.
		 */
		setupInputWithDefaultTitle : function() {
			var name = 'input-with-default-title';

			$('.' + name).each( function(){
				var $t = $(this), title = $t.attr('title'), val = $t.val();
				$t.data( name, title );

				if( '' == val ) $t.val( title );
				else if ( title == val ) return;
				else $t.removeClass( name );
			}).focus( function(){
				var $t = $(this);
				if( $t.val() == $t.data(name) )
					$t.val('').removeClass( name );
			}).blur( function(){
				var $t = $(this);
				if( '' == $t.val() )
					$t.addClass( name ).val( $t.data(name) );
			});

			$( '.blank-slate .input-with-default-title' ).focus();
		},

		attachThemeLocationsListeners : function() {
			var loc = $('#nav-menu-theme-locations'), params = {};
			params['action'] = 'menu-locations-save';
			params['menu-settings-column-nonce'] = $('#menu-settings-column-nonce').val();
			loc.find('input[type="submit"]').click(function() {
				loc.find('select').each(function() {
					params[this.name] = $(this).val();
				});
				loc.find('.spinner').show();
				$.post( ajaxurl, params, function(r) {
					loc.find('.spinner').hide();
				});
				return false;
			});
		},

		attachQuickSearchListeners : function() {
			var searchTimer;

			$('.quick-search').keypress(function(e){
				var t = $(this);

				if( 13 == e.which ) {
					api.updateQuickSearchResults( t );
					return false;
				}

				if( searchTimer ) clearTimeout(searchTimer);

				searchTimer = setTimeout(function(){
					api.updateQuickSearchResults( t );
				}, 400);
			}).attr('autocomplete','off');
		},

		updateQuickSearchResults : function(input) {
			var panel, params,
			minSearchLength = 2,
			q = input.val();

			if( q.length < minSearchLength ) return;

			panel = input.parents('.tabs-panel');
			params = {
				'action': 'menu-quick-search',
				'response-format': 'markup',
				'menu': $('#menu').val(),
				'menu-settings-column-nonce': $('#menu-settings-column-nonce').val(),
				'q': q,
				'type': input.attr('name')
			};

			$('.spinner', panel).show();

			$.post( ajaxurl, params, function(menuMarkup) {
				api.processQuickSearchQueryResponse(menuMarkup, params, panel);
			});
		},

		addCustomLink : function( processMethod ) {
			var url = $('#custom-menu-item-url').val(),
				label = $('#custom-menu-item-name').val();

			processMethod = processMethod || api.addMenuItemToBottom;

			if ( '' == url || 'http://' == url )
				return false;

			// Show the ajax spinner
			$('.customlinkdiv .spinner').show();
			this.addLinkToMenu( url, label, processMethod, function() {
				// Remove the ajax spinner
				$('.customlinkdiv .spinner').hide();
				// Set custom link form back to defaults
				$('#custom-menu-item-name').val('').blur();
				$('#custom-menu-item-url').val('http://');
			});
		},

		addLinkToMenu : function(url, label, processMethod, callback) {
			processMethod = processMethod || api.addMenuItemToBottom;
			callback = callback || function(){};

			api.addItemToMenu({
				'-1': {
					'menu-item-type': 'custom',
					'menu-item-url': url,
					'menu-item-title': label
				}
			}, processMethod, callback);
		},

		addItemToMenu : function(menuItem, processMethod, callback) {
			var menu = $('#menu').val(),
				nonce = $('#menu-settings-column-nonce').val();

			processMethod = processMethod || function(){};
			callback = callback || function(){};

			params = {
				'action': 'add-menu-item',
				'menu': menu,
				'menu-settings-column-nonce': nonce,
				'menu-item': menuItem
			};

			$.post( ajaxurl, params, function(menuMarkup) {
				var ins = $('#menu-instructions');

				menuMarkup = $.trim( menuMarkup ); // Trim leading whitespaces
				processMethod(menuMarkup, params);

				// Make it stand out a bit more visually, by adding a fadeIn
				$( 'li.pending' ).hide().fadeIn('slow');
				$( '.drag-instructions' ).show();
				if( ! ins.hasClass( 'menu-instructions-inactive' ) && ins.siblings().length )
					ins.addClass( 'menu-instructions-inactive' );

				callback();
			});
		},

		/**
		 * Process the add menu item request response into menu list item.
		 *
		 * @param string menuMarkup The text server response of menu item markup.
		 * @param object req The request arguments.
		 */
		addMenuItemToBottom : function( menuMarkup, req ) {
			$(menuMarkup).hideAdvancedMenuItemFields().appendTo( api.targetList );
			api.refreshKeyboardAccessibility();
			api.refreshAdvancedAccessibility();
		},

		addMenuItemToTop : function( menuMarkup, req ) {
			$(menuMarkup).hideAdvancedMenuItemFields().prependTo( api.targetList );
			api.refreshKeyboardAccessibility();
			api.refreshAdvancedAccessibility();
		},

		attachUnsavedChangesListener : function() {
			$('#menu-management input, #menu-management select, #menu-management, #menu-management textarea, .menu-location-menus select').change(function(){
				api.registerChange();
			});

			if ( 0 != $('#menu-to-edit').length || 0 != $('.menu-location-menus select').length ) {
				window.onbeforeunload = function(){
					if ( api.menusChanged )
						return navMenuL10n.saveAlert;
				};
			} else {
				// Make the post boxes read-only, as they can't be used yet
				$( '#menu-settings-column' ).find( 'input,select' ).end().find( 'a' ).attr( 'href', '#' ).unbind( 'click' );
			}
		},

		registerChange : function() {
			api.menusChanged = true;
		},

		attachTabsPanelListeners : function() {
			$('#menu-settings-column').bind('click', function(e) {
				var selectAreaMatch, panelId, wrapper, items,
					target = $(e.target);

				if ( target.hasClass('nav-tab-link') ) {

					panelId = target.data( 'type' );

					wrapper = target.parents('.accordion-section-content').first();

					// upon changing tabs, we want to uncheck all checkboxes
					$('input', wrapper).removeAttr('checked');

					$('.tabs-panel-active', wrapper).removeClass('tabs-panel-active').addClass('tabs-panel-inactive');
					$('#' + panelId, wrapper).removeClass('tabs-panel-inactive').addClass('tabs-panel-active');

					$('.tabs', wrapper).removeClass('tabs');
					target.parent().addClass('tabs');

					// select the search bar
					$('.quick-search', wrapper).focus();

					e.preventDefault();
				} else if ( target.hasClass('select-all') ) {
					selectAreaMatch = /#(.*)$/.exec(e.target.href);
					if ( selectAreaMatch && selectAreaMatch[1] ) {
						items = $('#' + selectAreaMatch[1] + ' .tabs-panel-active .menu-item-title input');
						if( items.length === items.filter(':checked').length )
							items.removeAttr('checked');
						else
							items.prop('checked', true);
						return false;
					}
				} else if ( target.hasClass('submit-add-to-menu') ) {
					api.registerChange();

					if ( e.target.id && 'submit-customlinkdiv' == e.target.id )
						api.addCustomLink( api.addMenuItemToBottom );
					else if ( e.target.id && -1 != e.target.id.indexOf('submit-') )
						$('#' + e.target.id.replace(/submit-/, '')).addSelectedToMenu( api.addMenuItemToBottom );
					return false;
				} else if ( target.hasClass('page-numbers') ) {
					$.post( ajaxurl, e.target.href.replace(/.*\?/, '').replace(/action=([^&]*)/, '') + '&action=menu-get-metabox',
						function( resp ) {
							if ( -1 == resp.indexOf('replace-id') )
								return;

							var metaBoxData = $.parseJSON(resp),
							toReplace = document.getElementById(metaBoxData['replace-id']),
							placeholder = document.createElement('div'),
							wrap = document.createElement('div');

							if ( ! metaBoxData['markup'] || ! toReplace )
								return;

							wrap.innerHTML = metaBoxData['markup'] ? metaBoxData['markup'] : '';

							toReplace.parentNode.insertBefore( placeholder, toReplace );
							placeholder.parentNode.removeChild( toReplace );

							placeholder.parentNode.insertBefore( wrap, placeholder );

							placeholder.parentNode.removeChild( placeholder );

						}
					);

					return false;
				}
			});
		},

		eventOnClickEditLink : function(clickedEl) {
			var settings, item,
			matchedSection = /#(.*)$/.exec(clickedEl.href);
			if ( matchedSection && matchedSection[1] ) {
				settings = $('#'+matchedSection[1]);
				item = settings.parent();
				if( 0 != item.length ) {
					if( item.hasClass('menu-item-edit-inactive') ) {
						if( ! settings.data('menu-item-data') ) {
							settings.data( 'menu-item-data', settings.getItemData() );
						}
						settings.slideDown('fast');
						item.removeClass('menu-item-edit-inactive')
							.addClass('menu-item-edit-active');
					} else {
						settings.slideUp('fast');
						item.removeClass('menu-item-edit-active')
							.addClass('menu-item-edit-inactive');
					}
					return false;
				}
			}
		},

		eventOnClickCancelLink : function(clickedEl) {
			var settings = $( clickedEl ).closest( '.menu-item-settings' ),
				thisMenuItem = $( clickedEl ).closest( '.menu-item' );
			thisMenuItem.removeClass('menu-item-edit-active').addClass('menu-item-edit-inactive');
			settings.setItemData( settings.data('menu-item-data') ).hide();
			return false;
		},

		eventOnClickMenuSave : function(clickedEl) {
			var locs = '',
			menuName = $('#menu-name'),
			menuNameVal = menuName.val();
			// Cancel and warn if invalid menu name
			if( !menuNameVal || menuNameVal == menuName.attr('title') || !menuNameVal.replace(/\s+/, '') ) {
				menuName.parent().addClass('form-invalid');
				return false;
			}
			// Copy menu theme locations
			$('#nav-menu-theme-locations select').each(function() {
				locs += '<input type="hidden" name="' + this.name + '" value="' + $(this).val() + '" />';
			});
			$('#update-nav-menu').append( locs );
			// Update menu item position data
			api.menuList.find('.menu-item-data-position').val( function(index) { return index + 1; } );
			window.onbeforeunload = null;

			return true;
		},

		eventOnClickMenuDelete : function(clickedEl) {
			// Delete warning AYS
			if ( confirm( navMenuL10n.warnDeleteMenu ) ) {
				window.onbeforeunload = null;
				return true;
			}
			return false;
		},

		eventOnClickMenuItemDelete : function(clickedEl) {
			var itemID = parseInt(clickedEl.id.replace('delete-', ''), 10);
			api.removeMenuItem( $('#menu-item-' + itemID) );
			api.registerChange();
			return false;
		},

		/**
		 * Process the quick search response into a search result
		 *
		 * @param string resp The server response to the query.
		 * @param object req The request arguments.
		 * @param jQuery panel The tabs panel we're searching in.
		 */
		processQuickSearchQueryResponse : function(resp, req, panel) {
			var matched, newID,
			takenIDs = {},
			form = document.getElementById('nav-menu-meta'),
			pattern = new RegExp('menu-item\\[(\[^\\]\]*)', 'g'),
			$items = $('<div>').html(resp).find('li'),
			$item;

			if( ! $items.length ) {
				$('.categorychecklist', panel).html( '<li><p>' + navMenuL10n.noResultsFound + '</p></li>' );
				$('.spinner', panel).hide();
				return;
			}

			$items.each(function(){
				$item = $(this);

				// make a unique DB ID number
				matched = pattern.exec($item.html());

				if ( matched && matched[1] ) {
					newID = matched[1];
					while( form.elements['menu-item[' + newID + '][menu-item-type]'] || takenIDs[ newID ] ) {
						newID--;
					}

					takenIDs[newID] = true;
					if ( newID != matched[1] ) {
						$item.html( $item.html().replace(new RegExp(
							'menu-item\\[' + matched[1] + '\\]', 'g'),
							'menu-item[' + newID + ']'
						) );
					}
				}
			});

			$('.categorychecklist', panel).html( $items );
			$('.spinner', panel).hide();
		},

		removeMenuItem : function(el) {
			var children = el.childMenuItems();

			el.addClass('deleting').animate({
					opacity : 0,
					height: 0
				}, 350, function() {
					var ins = $('#menu-instructions');
					el.remove();
					children.shiftDepthClass( -1 ).updateParentMenuItemDBId();
					if( 0 == $( '#menu-to-edit li' ).length ) {
						$( '.drag-instructions' ).hide();
						ins.removeClass( 'menu-instructions-inactive' );
					}
				});
		},

		depthToPx : function(depth) {
			return depth * api.options.menuItemDepthPerLevel;
		},

		pxToDepth : function(px) {
			return Math.floor(px / api.options.menuItemDepthPerLevel);
		}

	};

	$(document).ready(function(){ wpNavMenu.init(); });

})(jQuery);
