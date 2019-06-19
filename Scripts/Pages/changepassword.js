/*
* Pretty much the same code as in login.php or register.html
*/

let formElem;
const setFormState = (enabled = true) => {
	Array.from(formElem.children).forEach(elem => {
		if (enabled)
			elem.removeAttribute('disabled');
		else
			elem.setAttribute('disabled', true);
	});
}
const errHandler = err => {
	// alert('An error occured: ' + err);
	const messageElem = document.getElementById('server_message');

	messageElem.innerText = err;
	messageElem.style.display = 'block';

	setFormState(true);
	console.error(err);
}

Promise.all([
	workerPromisify(new Worker('Scripts/encryption-helper.js')),
	new Promise((resolve, reject) => {
		document.addEventListener('DOMContentLoaded', evt => {
			resolve();
		});
	}),
//
]).then(items => {
	formElem = document.getElementsByTagName('form')[0];
	formElem.addEventListener('submit', evt => {
		setFormState(false);

		const newPassVer = document.getElementById('new-pass-ver').value.trim();
		const newPass = document.getElementById('new-pass').value.trim();
		const oldPass = document.getElementById('old-pass').value.trim();

		if (newPass === newPassVer) {
			checkPassword(newPass).then(resp => {
				if (resp === true) {
					Promise.all([
						hashPassword(newPass, items[0]),
						items[0].encrypt({
							text: sessionStorage.getItem('enc_token'),
							password: newPass
						}),
						phpPostRequest('get_password_salt', {
							email: document.getElementById('email').innerText
						})
					//
					]).then(([hashPass, data, passSalt]) => {
						passSalt = passSalt.data;

						if (!passSalt.success)
							errHandler('Could not verify password type');
						else {
							const handleTheRest = oldPass => {
								phpPostRequest('change_password', {
									'curr-pass': oldPass,
									'new-pass': hashPass,
									'token': data.data,
									'enc-iv': data.iv,
								}).then(({ data }) => {
									if (!data.success)
										errHandler(data.error);
									else
										logout();
								}).catch(errHandler);
							}

							if (passSalt.data === true)
								hashPassword(oldPass, items[0], passSalt.salt).then(handleTheRest);
							else
								handleTheRest(oldPass);
						}
					});
				} else {
					errHandler(resp);
				}
			}).catch();
		} else {
			errHandler('Passwords don\'t match');
		}
	});
});