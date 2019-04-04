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
	alert('An error occured: ' + err);
	setFormState(true);
	console.error(err);
}

document.addEventListener('DOMContentLoaded', evt => {
	formElem = document.getElementsByTagName('form')[0];
	formElem.addEventListener('submit', evt => {
		setFormState(false);
		phpPostRequest('change_password', {
			'new-pass-ver': document.getElementById('new-pass-ver').value.trim(),
			'curr-pass': document.getElementById('old-pass').value.trim(),
			'new-pass': document.getElementById('new-pass').value.trim(),
		}).then(({ data }) => {
			if (!data.success)
				errHandler(data.error);
			else
				logout();
		}).catch(errHandler);
	});
});