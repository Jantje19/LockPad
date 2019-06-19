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

const hashPassword = async (pass, encryptionHelper, salt) => {
	return '[[PBKDF2]]' + (await encryptionHelper.PBKDF2KeyExport({ pass, salt })).data;
}

const checkPassword = async password => {
	const buf2hex = buffer => {
		return Array.prototype.slice
		.call(new Uint8Array(buffer))
		.map(x => [x >> 4, x & 15])
		.map(ab => ab.map(x => x.toString(16)).join(""))
		.join("");
	}

	if (password.length < 10)
		return 'Password contains less than 10 characters';
	if (!password.match(/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])/))
		return 'Password must contain at least one lower-case character, upper-case character, number, special character (!@#$%^&*)';

	// Check 'HaveIBeenPwned': https://haveibeenpwned.com/API/v2#PwnedPasswords
	const sha1 = buf2hex(await crypto.subtle.digest('SHA-1', (new TextEncoder("utf-8")).encode(password)));
	const resp = await fetch('https://api.pwnedpasswords.com/range/' + sha1.substr(0, 5));
	const findVal = (await resp.text()).split('\r\n').find(str => {
		return str.startsWith(sha1);
	});

	if (findVal)
		return 'The password was found in the \'haveibeenpwned.com\' database. Therefore you are not allowed to use it';

	return true;
}