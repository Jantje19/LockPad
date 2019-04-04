const errorHandler = console.error;

// Check session value
if (sessionStorage.getItem('enc_token') === null)
	window.location = '/login.php?from=token_exp';

// Init localforage item
(async function() {
	if (!(await localforage.getItem('local')))
		localforage.setItem('local', {});
}());

// Wait untill the DOM is loaded and the workers are initialized
Promise.all([
	workerPromisify(new Worker('Scripts/encryption-helper.js')),
	workerPromisify(new Worker('Scripts/markdown-parser.js')),
	new Promise((resolve, reject) => {
		document.addEventListener('DOMContentLoaded', evt => {
			resolve();
		});
	}),
//
]).then(items => {
	const markdownElem = document.getElementById('markdown');
	const txtAreaElem = document.getElementById('textarea');
	const statusElem = document.getElementById('status');
	const encHelper = items[0];
	const mdParser = items[1];
	let iv, objToken;

	document.getElementById('cancel-btn').addEventListener('click', evt => {
		window.location = '/dashboard.php';
	});

	txtAreaElem.addEventListener('change', evt => {
		const txt = evt.currentTarget.value.trim();

		if (txt.length > 0) {
			// Save locally
			(function() {
				const encData = {
					password: sessionStorage.getItem('enc_token'),
					text: txt
				};

				if (iv)
					encData.iv = iv;

				encHelper.encrypt(encData).then(data => {
					localforage.getItem('local').then(async obj => {
						if (objToken) {
							obj[objToken].edited = Date.now();
							obj[objToken].content = data;
						} else {
							objToken = (await encHelper.randomString()).data;
							obj[objToken] = {
								created: Date.now(),
								edited: Date.now(),
								content: data,
								title: null,
							};
						}

						statusElem.innerText = 'Saving...';
						localforage.setItem('local', obj).then(r => {
							statusElem.innerText = 'Saved';
						}).catch(console.error);
					}).catch(errorHandler);

					iv = data.iv;
				}).catch(errorHandler);
			}());

			// Parse markdown
			mdParser.parse(txt).then(md => {
				markdownElem.innerHTML = md.data;
			}).catch(err => {
				markdownElem.innerHTML = `<i><b>There was an error:</b> ${err}</i>`;
			});
		}
	});

	txtAreaElem.addEventListener('keydown', evt => {
		if (evt.key.toLowerCase() == 'tab') {
			evt.preventDefault();

			const pos = textarea.selectionStart + 1;

			txtAreaElem.value = textarea.value.substring(0, textarea.selectionStart) + "\t" + textarea.value.substring(textarea.selectionStart, textarea.value.length);
			txtAreaElem.selectionStart = pos;
			txtAreaElem.selectionEnd = pos;
			txtAreaElem.focus();
		}
	});
}).catch(errorHandler);