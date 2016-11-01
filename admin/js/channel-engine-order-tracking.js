if(typeof(ce) === 'undefined'){
	(function (T, r, i, t, a, c) {
	    T.ce = T.ce || function () { T.ce.eq = T.ce.eq || []; T.ce.eq.push(arguments); }, T.ce.url = t;
	    a = r.createElement(i); a.async = 1; a.src = t + '/content/scripts/ce.js';
	    c = r.getElementsByTagName(i)[0]; c.parentNode.insertBefore(a, c);
	})(window, document, 'script', '//www.channelengine.net');
	ce('set:account', channel_engine_data.account_name);
	ce('track:click');
}

if(typeof(channel_engine_data.order) !== 'undefined') {
    ce('track:order', channel_engine_data.order);
}
