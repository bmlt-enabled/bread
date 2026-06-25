var defaultOptions = {
	timeout: 300000,
	jsonpCallback: 'callback',
	jsonpCallbackFunction: null,
};

function generateCallbackFunction() {
	return 'jsonp_' + Date.now().toString() + '_' + Math.ceil(Math.random() * 100000).toString();
}

function clearFunction(functionName) {
	// IE8 throws an exception when you try to delete a property on window
	// http://stackoverflow.com/a/1824228/751089
	try {
		delete window[functionName];
	} catch (e) {
		window[functionName] = undefined;
	}
}

function removeScript(scriptId) {
	var script = document.getElementById(scriptId);
	if (script) {
		document.getElementsByTagName('head')[0].removeChild(script);
	}
}

function fetchJsonp(_url, options) {
	if (!options) {
		options = {};
	}
	// to avoid param reassign
	var url = _url;
	var timeout = options.timeout || defaultOptions.timeout;
	var jsonpCallback = options.jsonpCallback || defaultOptions.jsonpCallback;

	var timeoutId;

	return new Promise(function (resolve, reject) {
		var callbackFunction = options.jsonpCallbackFunction || generateCallbackFunction();
		var scriptId = jsonpCallback + '_' + callbackFunction;

		window[callbackFunction] = function (response) {
			resolve({
				ok: true,
				// keep consistent with fetch API
				json: function () { return Promise.resolve(response); },
			});

			if (timeoutId) clearTimeout(timeoutId);

			removeScript(scriptId);

			clearFunction(callbackFunction);
		};

		// Check if the user set their own params, and if not add a ? to start a list of params
		url += (url.indexOf('?') === -1) ? '?' : '&';

		var jsonpScript = document.createElement('script');
		jsonpScript.setAttribute('src', url + jsonpCallback + '=' + callbackFunction);
		if (options.charset) {
			jsonpScript.setAttribute('charset', options.charset);
		}
		if (options.nonce) {
			jsonpScript.setAttribute('nonce', options.nonce);
		}
		if (options.referrerPolicy) {
			jsonpScript.setAttribute('referrerPolicy', options.referrerPolicy);
		}
		jsonpScript.id = scriptId;
		document.getElementsByTagName('head')[0].appendChild(jsonpScript);

		timeoutId = setTimeout(function () {
			reject(new Error('JSONP request to ' + _url + ' timed out'));

			clearFunction(callbackFunction);
			removeScript(scriptId);
			window[callbackFunction] = function () {
				clearFunction(callbackFunction);
			};
		}, timeout);

		// Caught if got 404/500
		jsonpScript.onerror = function () {
			reject(new Error('JSONP request to ' + _url + ' failed'));

			clearFunction(callbackFunction);
			removeScript(scriptId);
			if (timeoutId) clearTimeout(timeoutId);
		};
	});
}

// export as global function
/*
let local;
if (typeof global !== 'undefined') {
  local = global;
} else if (typeof self !== 'undefined') {
  local = self;
} else {
  try {
    local = Function('return this')();
  } catch (e) {
    throw new Error('polyfill failed because global object is unavailable in this environment');
  }
}
local.fetchJsonp = fetchJsonp;
*/

