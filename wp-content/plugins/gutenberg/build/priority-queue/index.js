this.wp=this.wp||{},this.wp.priorityQueue=function(e){var t={};function n(r){if(t[r])return t[r].exports;var i=t[r]={i:r,l:!1,exports:{}};return e[r].call(i.exports,i,i.exports,n),i.l=!0,i.exports}return n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{configurable:!1,enumerable:!0,get:r})},n.r=function(e){Object.defineProperty(e,"__esModule",{value:!0})},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=230)}({230:function(e,t,n){"use strict";n.r(t),n.d(t,"createQueue",function(){return i});var r=window.requestIdleCallback?window.requestIdleCallback:window.requestAnimationFrame,i=function(){var e=[],t=new WeakMap,n=!1,i=function i(u){do{if(0===e.length)return void(n=!1);var o=e.shift();t.get(o)(),t.delete(o)}while(u&&u.timeRemaining&&u.timeRemaining()>0);r(i)};return{add:function(u,o){t.has(u)||e.push(u),t.set(u,o),n||(n=!0,r(i))},flush:function(n){if(!t.has(n))return!1;t.delete(n);var r=e.indexOf(n);return e.splice(r,1),!0}}}}});