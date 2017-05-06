module.exports = (function() {

  /*
    ---------------
    Variables
    ---------------
  */

  // cache document.documentElement
  var docElem = document.documentElement;

  // last used input type
  var currentInput = 'initial';

  // last used input intent
  var currentIntent = null;

  // form input types
  var formInputs = [
    'input',
    'select',
    'textarea'
  ];

  // list of modifier keys commonly used with the mouse and
  // can be safely ignored to prevent false keyboard detection
  var ignoreMap = [
    16, // shift
    17, // control
    18, // alt
    91, // Windows key / left Apple cmd
    93  // Windows menu / right Apple cmd
  ];

  // mapping of events to input types
  var inputMap = {
    'keyup': 'keyboard',
    'mousedown': 'mouse',
    'mousemove': 'mouse',
    'MSPointerDown': 'pointer',
    'MSPointerMove': 'pointer',
    'pointerdown': 'pointer',
    'pointermove': 'pointer',
    'touchstart': 'touch'
  };

  // array of all used input types
  var inputTypes = [];

  // boolean: true if touch buffer timer is running
  var isBuffering = false;

  // map of IE 10 pointer events
  var pointerMap = {
    2: 'touch',
    3: 'touch', // treat pen like touch
    4: 'mouse'
  };

  // touch buffer timer
  var touchTimer = null;


  /*
    ---------------
    Set up
    ---------------
  */

  var setUp = function() {

    // add correct mouse wheel event mapping to `inputMap`
    inputMap[detectWheel()] = 'mouse';

    addListeners();
    setInput();
  };


  /*
    ---------------
    Events
    ---------------
  */

  var addListeners = function() {

    // `pointermove`, `MSPointerMove`, `mousemove` and mouse wheel event binding
    // can only demonstrate potential, but not actual, interaction
    // and are treated separately

    // pointer events (mouse, pen, touch)
    if (window.PointerEvent) {
      docElem.addEventListener('pointerdown', updateInput);
      docElem.addEventListener('pointermove', setIntent);
    } else if (window.MSPointerEvent) {
      docElem.addEventListener('MSPointerDown', updateInput);
      docElem.addEventListener('MSPointerMove', setIntent);
    } else {

      // mouse events
      docElem.addEventListener('mousedown', updateInput);
      docElem.addEventListener('mousemove', setIntent);

      // touch events
      if ('ontouchstart' in window) {
        docElem.addEventListener('touchstart', touchBuffer);
      }
    }

    // mouse wheel
    docElem.addEventListener(detectWheel(), setIntent);

    // keyboard events
    docElem.addEventListener('keydown', updateInput);
    docElem.addEventListener('keyup', updateInput);
  };

  // checks conditions before updating new input
  var updateInput = function(event) {

    // only execute if the touch buffer timer isn't running
    if (!isBuffering) {
      var eventKey = event.which;
      var value = inputMap[event.type];
      if (value === 'pointer') value = pointerType(event);

      if (
        currentInput !== value ||
        currentIntent !== value
      ) {

        var activeInput = (
          document.activeElement &&
          formInputs.indexOf(document.activeElement.nodeName.toLowerCase()) === -1
        ) ? true : false;

        if (
          value === 'touch' ||

          // ignore mouse modifier keys
          (value === 'mouse' && ignoreMap.indexOf(eventKey) === -1) ||

          // don't switch if the current element is a form input
          (value === 'keyboard' && activeInput)
        ) {

          // set the current and catch-all variable
          currentInput = currentIntent = value;

          setInput();
        }
      }
    }
  };

  // updates the doc and `inputTypes` array with new input
  var setInput = function() {
    docElem.setAttribute('data-whatinput', currentInput);
    docElem.setAttribute('data-whatintent', currentInput);

    if (inputTypes.indexOf(currentInput) === -1) {
      inputTypes.push(currentInput);
      docElem.className += ' whatinput-types-' + currentInput;
    }
  };

  // updates input intent for `mousemove` and `pointermove`
  var setIntent = function(event) {

    // only execute if the touch buffer timer isn't running
    if (!isBuffering) {
      var value = inputMap[event.type];
      if (value === 'pointer') value = pointerType(event);

      if (currentIntent !== value) {
        currentIntent = value;

        docElem.setAttribute('data-whatintent', currentIntent);
      }
    }
  };

  // buffers touch events because they frequently also fire mouse events
  var touchBuffer = function(event) {

    // clear the timer if it happens to be running
    window.clearTimeout(touchTimer);

    // set the current input
    updateInput(event);

    // set the isBuffering to `true`
    isBuffering = true;

    // run the timer
    touchTimer = window.setTimeout(function() {

      // if the timer runs out, set isBuffering back to `false`
      isBuffering = false;
    }, 200);
  };


  /*
    ---------------
    Utilities
    ---------------
  */

  var pointerType = function(event) {
   if (typeof event.pointerType === 'number') {
      return pointerMap[event.pointerType];
   } else {
      return (event.pointerType === 'pen') ? 'touch' : event.pointerType; // treat pen like touch
   }
  };

  // detect version of mouse wheel event to use
  // via https://developer.mozilla.org/en-US/docs/Web/Events/wheel
  var detectWheel = function() {
    return 'onwheel' in document.createElement('div') ?
      'wheel' : // Modern browsers support "wheel"

      document.onmousewheel !== undefined ?
        'mousewheel' : // Webkit and IE support at least "mousewheel"
        'DOMMouseScroll'; // let's assume that remaining browsers are older Firefox
  };


  /*
    ---------------
    Init

    don't start script unless browser cuts the mustard
    (also passes if polyfills are used)
    ---------------
  */

  if (
    'addEventListener' in window &&
    Array.prototype.indexOf
  ) {
    setUp();
  }


  /*
    ---------------
    API
    ---------------
  */

  return {

    // returns string: the current input type
    // opt: 'loose'|'strict'
    // 'strict' (default): returns the same value as the `data-whatinput` attribute
    // 'loose': includes `data-whatintent` value if it's more current than `data-whatinput`
    ask: function(opt) { return (opt === 'loose') ? currentIntent : currentInput; },

    // returns array: all the detected input types
    types: function() { return inputTypes; }

  };

}());
