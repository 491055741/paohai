/*!
 * classie v1.0.1
 * class helper functions
 * from bonzo https://github.com/ded/bonzo
 * MIT license
 * 
 * classie.has( elem, 'my-class' ) -> true/false
 * classie.add( elem, 'my-new-class' )
 * classie.remove( elem, 'my-unwanted-class' )
 * classie.toggle( elem, 'my-class' )
 */

/*jshint browser: true, strict: true, undef: true, unused: true */
/*global define: false, module: false */

( function( window ) {

'use strict';

// class helper functions from bonzo https://github.com/ded/bonzo

function classReg( className ) {
  return new RegExp("(^|\\s+)" + className + "(\\s+|$)");
}

// classList support for class management
// altho to be fair, the api sucks because it won't accept multiple classes at once
var hasClass, addClass, removeClass;

if ( 'classList' in document.documentElement ) {
  hasClass = function( elem, c ) {
    return elem.classList.contains( c );
  };
  addClass = function( elem, c ) {
    elem.classList.add( c );
  };
  removeClass = function( elem, c ) {
    elem.classList.remove( c );
  };
}
else {
  hasClass = function( elem, c ) {
    return classReg( c ).test( elem.className );
  };
  addClass = function( elem, c ) {
    if ( !hasClass( elem, c ) ) {
      elem.className = elem.className + ' ' + c;
    }
  };
  removeClass = function( elem, c ) {
    elem.className = elem.className.replace( classReg( c ), ' ' );
  };
}

function toggleClass( elem, c ) {
  var fn = hasClass( elem, c ) ? removeClass : addClass;
  fn( elem, c );
}

var classie = {
  // full names
  hasClass: hasClass,
  addClass: addClass,
  removeClass: removeClass,
  toggleClass: toggleClass,
  // short names
  has: hasClass,
  add: addClass,
  remove: removeClass,
  toggle: toggleClass
};

// transport
if ( typeof define === 'function' && define.amd ) {
  // AMD
  define( classie );
} else if ( typeof exports === 'object' ) {
  // CommonJS
  module.exports = classie;
} else {
  // browser global
  window.classie = classie;
}

})( window );

/**
 * @author huangchun
 * 事件通用处理封装
 *
 * */
var EventUtil = {
    addLoadEvent : function (fn,data) { //加载在函数，有待改进
        var oldLoadFn = window.onload;
        if (typeof oldLoadFn != "function") {
            window.onload = fn(data);
        }else{
            window.onload = function () {
                oldLoadFn();
                fn(data);
            };
        }
    },
    addhandler : function (element,type,handler) {//事件监听
        if (element.addEventListener) {
            element.addEventListener(type,handler,false);
        }else if (element.attachEvent) {
            element.attachEvent("on"+type,handler);
        }else {
            element["on"+type] = handler;
        }
    },
    removeHandler : function (element,type,handler) {//事件监听取消
        if (element.removeEventListener) {
            element.removeEventListener(type,handler,false)
        }else if (element.detachEvent) {
            element.detachEvent(type,handler);
        }else {
            element["on"+type] = null;
        }
    }
};