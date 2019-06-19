let formElem, serverMessageElem;

/*
*	Get cryptographical random bytes
*
*	@param {int=16} length 	The amount of bytes returned
*	@return {Uint8Array}
*/
const randomBytes = (length = 16) => {
	return crypto.getRandomValues(new Uint8Array(length));
}
/*
*	Get the bytes from an Uint8Array (perhaps from the function above) and base64 encode them
*
*	@param {Uint8Array} bytes 	The array with bytes you want to convert
*	@return {String}
*/
const bytesToString = bytes => {
	return btoa(String.fromCharCode(...bytes));
}

/*
*	Enables or disables the elements inside the form element
*
*	@param {boolean=true} enabled 	The state
*/
const setFormState = (enabled = true) => {
	Array.from(formElem.children).forEach(elem => {
		if (enabled)
			elem.removeAttribute('disabled');
		else
			elem.setAttribute('disabled', true);
	});
}

/*
*	Handles an error event
*
*	@param {Error/String} err 	The error message
*/
const errHandler = err => {
	serverMessageElem.innerText = 'An error occured: ' + err;
	serverMessageElem.style.display = 'block';
	setFormState(true);
}

// Wait until the page is loaded and the worker is initialized
Promise.all([
	workerPromisify(new Worker('Scripts/encryption-helper.js')),
	new Promise((resolve, reject) => {
		document.addEventListener('DOMContentLoaded', evt => {
			resolve();
		});
	}),
//
]).then(items => {
	// Setup the element references
	const passVerElem = document.querySelector('[name=pass-ver]');
	const emailElem = document.querySelector('[name=email]');
	const passElem = document.querySelector('[name=pass]');
	const worker = items[0];

	serverMessageElem = document.getElementById('server_message');
	formElem = document.getElementsByTagName('form')[0];
	formElem.addEventListener('submit', evt => {
		const passVer = passVerElem.value.trim();
		const password = passElem.value.trim();

		// Set form state to false, so the user can't submit multiple times
		setFormState(false);
		if (passVer === password) {
			checkPassword(password).then(resp => {
				if (resp === true) {
					Promise.all([
						hashPassword(password, worker),
						// Create a new encryption token and encrypt that with the password
						worker.encrypt({
							text: bytesToString(randomBytes()),
							password
						})
					//
					]).then(([hashPass, data]) => {
						// Make a post request for creating an account
						phpPostRequest('create_account', {
							'g-recaptcha-response': grecaptcha.getResponse(),
							email: emailElem.value,
							'enc_token': data.data,
							'enc_iv': data.iv,
							pass: hashPass,
						}).then(({ data }) => {
							if (!data.success)
								errHandler(data.error);
							else
								location.pathname = '/mail_verification.html';
						}).catch(errHandler);
					}).catch(errHandler);
				} else {
					errHandler(resp);
				}
			})
		} else {
			errHandler('The passwords don\'t match');
		}
	});
}).catch(errHandler);