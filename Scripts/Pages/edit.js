const errorHandler = console.error;
const bytesToString = bytes => {
	return btoa(String.fromCharCode(...bytes));
}

// Check session value
if (sessionStorage.getItem('enc_token') === null)
	window.location = '/login.php?from=token_exp';

// Wait untill DOM is ready and the workers are initialized
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
	const saveBtn = document.getElementById('save-btn');
	const titleElem = document.getElementById('title');
	const encHelper = items[0];
	const mdParser = items[1];

	/*
	*	Get the values out of the input elements and save them
	*/
	const saveNote = () => {
		const text = txtAreaElem.value.trim();
		const title = titleElem.value.trim();

		// Check if the text value is set
		if (text.length > 0) {
			if (type == 'local') {
				// Save locally
				(function() {
					const password = sessionStorage.getItem('enc_token');
					const promiseArr = [];

					promiseArr.push(encHelper.encrypt({ password, text }));
					if (title.length > 0)
						promiseArr.push(encHelper.encrypt({ password, text: title }));


					Promise.all(promiseArr).then(vals => {
						const content = vals[0];
						const title = vals[1];

						localforage.getItem('local').then(async obj => {
							obj[token].edited = Date.now();
							obj[token].content = content;

							if (title)
								obj[token].title = title;
							else
								obj[token].title = null;

							statusElem.innerText = 'Saving...';
							localforage.setItem('local', obj).then(r => {
								statusElem.innerText = 'Saved';
							}).catch(console.error);
						}).catch(errorHandler);
					}).catch(errorHandler);
				}());
			}
		}
	}
	/*
	*	Get the data out of the element and parse the markdown
	*/
	const parseMarkdown = () => {
		// Parse markdown
		mdParser.parse(txtAreaElem.value.trim()).then(md => {
			markdownElem.innerHTML = md.data;
		}).catch(err => {
			markdownElem.innerHTML = `<i><b>There was an error:</b> ${err}</i>`;
		});
	}

	// Check type
	(async function() {
		const promiseArr = [];

		if (type == 'local') {
			await (async function() {
				const forageItem = await localforage.getItem('local');

				if (!forageItem) {
					window.location = '/dashboard.php';
				} else {
					if (!(token in forageItem))
						window.location = '/dashboard.php';
					else {
						const item = forageItem[token];

						promiseArr.push(encHelper.decrypt({
							password: sessionStorage.getItem('enc_token'),
							data: item.content.data,
							iv: item.content.iv
						}));

						if (item.title) {
							promiseArr.push(encHelper.decrypt({
								password: sessionStorage.getItem('enc_token'),
								data: item.title.data,
								iv: item.title.iv
							}));
						}
					}
				}
			}());
		} else if (type == 'server') {
			const title = titleElem.value.trim();

			promiseArr.push(await encHelper.decrypt({
				password: sessionStorage.getItem('enc_token'),
				iv: txtAreaElem.getAttribute('contentIv'),
				data: txtAreaElem.value.trim(),
			}));

			if (title.length > 0) {
				promiseArr.push(await encHelper.decrypt({
					password: sessionStorage.getItem('enc_token'),
					iv: titleElem.getAttribute('titleIv'),
					data: title,
				}));
			}
		} else {
			alert('Wrong type');
			window.location = '/dashboard.php';
		}

		Promise.all(promiseArr).then(data => {
			const title = data[1];
			const text = data[0];

			txtAreaElem.textContent = text.data;

			if (title)
				titleElem.value = title.data;

			txtAreaElem.removeAttribute('disabled');
			titleElem.removeAttribute('disabled');
			saveBtn.removeAttribute('disabled');

			parseMarkdown();
		}).catch(errorHandler);
	}());

	document.getElementById('cancel-btn').addEventListener('click', evt => {
		window.location = '/dashboard.php';
	});

	saveBtn.addEventListener('click', async evt => {
		saveBtn.setAttribute('disabled', true);

		if (type == 'local') {
			const obj = await localforage.getItem('local');
			const item = obj[token];
			const postObj = {
				'content-iv': bytesToString(item.content.iv),
				content: item.content.data,
				created: item.created,
				edited: item.edited
			};

			if (item.title) {
				postObj['title-iv'] = bytesToString(item.title.iv);
				postObj.title = item.title.data;
			}

			phpPostRequest('edit_note', postObj).then(async ({data}) => {
				if (data.success) {
					// Delete value from local storage
					delete obj[token];
					await localforage.setItem('local', obj);

					window.location = '/dashboard.php';
				} else {
					errorHandler(data.error);
				}
			}).catch(errorHandler);
		} else if (type == 'server') {
			const promiseArr = [];
			const postObj = {
				edited: Date.now(),
				id: token
			};

			promiseArr.push(encHelper.encrypt({
				password: sessionStorage.getItem('enc_token'),
				text: txtAreaElem.value.trim()
			}));

			if (titleElem.value.trim().length > 0) {
				promiseArr.push(encHelper.encrypt({
					password: sessionStorage.getItem('enc_token'),
					text: titleElem.value.trim()
				}));
			}

			Promise.all(promiseArr).then(data => {
				const contentData = data[0];

				postObj['content-iv'] = contentData.iv;
				postObj.content = contentData.data;

				if (data[1]) {
					const titleData = data[1];
					postObj['title-iv'] = titleData.iv;
					postObj.title = titleData.data;
				}

				phpPostRequest('edit_note', postObj).then(({data}) => {
					if (data.success)
						window.location = '/dashboard.php';
					else
						errorHandler(data.error);
				}).catch(errorHandler);
			}).catch(errorHandler);
		}
	});

	txtAreaElem.addEventListener('keydown', evt => {
		if (evt.key.toLowerCase() == 'tab') {
			evt.preventDefault();

			const pos = textarea.selectionStart + 1;

			txtAreaElem.value = textarea.value.substring(0, textarea.selectionStart) + "\t" + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
			txtAreaElem.selectionStart = pos;
			txtAreaElem.selectionEnd = pos;
			txtAreaElem.focus();
		}
	});

	txtAreaElem.addEventListener('change', evt => {
		parseMarkdown();
		saveNote();
	});
	titleElem.addEventListener('change', evt => {
		saveNote();
	});
}).catch(errorHandler);