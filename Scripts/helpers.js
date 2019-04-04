const parseBody = bodyObj => {
	return Object.keys(bodyObj).map(k => {
		if (Array.isArray(bodyObj[k]))
			bodyObj[k] = bodyObj[k].join(',');

		return encodeURIComponent(k) + '=' + encodeURIComponent(bodyObj[k])
	}).join('&');
}

const phpPostRequest = (apiPath, body, type = 'json') => {
	type = type.toLowerCase();
	if (!apiPath.toLowerCase().endsWith('.php'))
		apiPath += '.php';

	return new Promise((resolve, reject) => {
		fetch('/API/' + apiPath, {
			credentials: "same-origin",
			body: parseBody(body),
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded'
			},
		}).then(response => {
			if (response.type === 'opaque')
				reject('Received a response, but it\'s opaque so can\'t examine it');
			else if (response.status !== 200)
				reject('Looks like there was a problem. Status Code: ' + response.status);
			else {
				if (response[type])
					response[type]().then(data => resolve({ response, data })).catch(reject);
				else
					reject('Invalid type: ' + type);
			}
		}).catch(reject);
	});
}

const logout = async () => {
	sessionStorage.clear();

	// Send logout request to server
	try {
		await phpPostRequest('logout', {});
	} catch (err) {}

	// Delete cookies
	document.cookie.split(';').forEach(val => {
		document.cookie = val.trim().split('=')[0] + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
	});

	// Redirect to login page
	window.location = '/login.php?from=logout';
}