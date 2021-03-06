let mainFormElem, serverMessageElem;

/*
*	Enables or disables the elements inside the form element
*
*	@param {boolean=true} enabled 	The state
*/
const setFormState = (enabled = true, formElem = mainFormElem) => {
	Array.from(formElem.children).forEach(elem => {
		if (enabled)
			elem.removeAttribute('disabled');
		else
			elem.setAttribute('disabled', true);
	});
}
/*
*	Set message elem
*
*	@param {String} msg
*/
const setMessage = msg => {
	serverMessageElem.innerText = msg;
	serverMessageElem.style.display = 'block';
}
/*
*	Handles an error event
*
*	@param {Error/String} err 	The error message
*/
const errHandler = err => {
	console.error(err);
	serverMessageElem.style.color = '';
	setMessage('An error occured: ' + err);
	setFormState(true);
}

// Wait until the DOM is ready and the worker is loaded
Promise.all([
	workerPromisify(new Worker('Scripts/encryption-helper.js')),
	new Promise((resolve, reject) => {
		document.addEventListener('DOMContentLoaded', evt => {
			resolve();
		});
	}),
//
]).then(items => {
	const emailElem = document.querySelector('[name=email]');
	const passElem = document.querySelector('[name=pass]');
	const forms = document.getElementsByTagName('form');
	const worker = items[0];

	serverMessageElem = document.getElementById('server_message');
	mainFormElem = forms[0];

	// Check for message in url and set it in the DOM
	const { searchParams } = new URL(window.location);
	if (searchParams.has('from')) {
		switch(searchParams.get('from')) {
			case 'account_verification':
			serverMessageElem.style.color = 'var(--font-color)'
			setMessage('Thank you for verifing your account.\nYou can now log in.');
			break;
			case 'token_exp':
			serverMessageElem.style.color = 'var(--font-color)'
			setMessage('Your token expired. Please login again');
			break;
			case 'logout':
			serverMessageElem.style.color = 'var(--font-color)'
			setMessage('Successfully logged out');
			break;
		}
	}
	// Clear search params
	history.pushState({}, '', 'login.php');

	// Handle submit evt
	mainFormElem.addEventListener('submit', evt => {
		setFormState(false);

		const password = passElem.value.trim();
		const email = emailElem.value.trim();

		phpPostRequest('get_password_salt', {
			'g-recaptcha-response': grecaptcha.getResponse(),
			email
		}).then(passSalt => {
			passSalt = passSalt.data;

			if (!passSalt.success)
				errHandler('Invalid email address');
			else {
				const handleTheRest = passHash => {
					phpPostRequest('login', {
						pass: passHash,
						email
					}).then(({ data, response }) => {
						const successHandler = data => {
							const { enc_token, enc_iv } = data.data;

							worker.decrypt({
								data: enc_token,
								iv: enc_iv,
								password
							}).then(({ data }) => {
								sessionStorage.setItem('enc_token', data);
								window.location = '/dashboard.php';
							}).catch(errHandler);
						}

						if (data.success)
							successHandler(data);
						else {
							if (!data.twoFA)
								errHandler(data.error);
							else {
								const twoFAForm = forms[1];
								const inputElem = twoFAForm.getElementsByTagName('input')[0];

								twoFAForm.style.height = Math.round(mainFormElem.getBoundingClientRect().height) + 'px';
								mainFormElem.style.opacity = '0';
								mainFormElem.addEventListener('transitionend', evt => {
									mainFormElem.style.display = 'none';
									twoFAForm.style.display = 'block';
									setTimeout(() => {
										twoFAForm.style.opacity = '1';
										inputElem.focus();
										twoFAForm.addEventListener('submit', evt => {
											const twoFaToken = inputElem.value.trim();

											if (twoFaToken.length > 0) {
												setFormState(false, twoFAForm);
												phpPostRequest('login', {
													twoFA: twoFaToken,
													pass: passHash,
													email,
												}).then(({ data, response }) => {
													if (!data.success)
														errHandler(data.error);
													else
														successHandler(data);
												}).catch(errHandler);
											}
										}, { once: true });
									}, 10);
								}, { once: true });
							}
						}
					}).catch(errHandler);
				}

				if (passSalt.data === true)
					hashPassword(password, worker, passSalt.salt).then(handleTheRest);
				else
					handleTheRest(password);
			}
		}).catch(errHandler);
	}, { once: true });
}).catch(errHandler);