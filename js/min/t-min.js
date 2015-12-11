/*
		 _     _
		| |   (_)
		| |_   _ ___
		| __| | / __|
		| |_ _| \__ \
		 \__(_) |___/
		     _/ |
		    |__/

	t.js
	a micro-templating framework in ~400 bytes gzipped

	@author  Jason Mooberry <jasonmoo@me.com>
	@license MIT
	@version 0.1.0

*/
!function(){function n(n){this.t=n}function r(n){return new Option(n).innerHTML.replace(/"/g,"&quot;")}function t(n,r){for(var t=r.split(".");t.length;){if(!(t[0]in n))return!1;n=n[t.shift()]}return n}function e(n,o){return n.replace(i,function(n,r,i,u,f,a,c,l){var p=t(o,u),s="",v;if(!p)return"!"==i?e(f,o):c?e(l,o):"";if(!i)return e(a,o);if("@"==i){n=o._key,r=o._val;for(v in p)p.hasOwnProperty(v)&&(o._key=v,o._val=p[v],s+=e(f,o));return o._key=n,o._val=r,s}}).replace(u,function(n,e,i){var u=t(o,i);return u||0===u?"%"==e?r(u):u:""})}var i=/\{\{(([@!]?)(.+?))\}\}(([\s\S]+?)(\{\{:\1\}\}([\s\S]+?))?)\{\{\/\1\}\}/g,u=/\{\{([=%])(.+?)\}\}/g;n.prototype.render=function(n){return e(this.t,n)},window.t=n}();