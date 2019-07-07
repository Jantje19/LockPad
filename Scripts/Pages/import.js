// Check session value
if (sessionStorage.getItem('enc_token') === null)
	window.location = '/login.php?from=token_exp';

// Wait untill the DOM is loaded and the workers are initialized
Promise.all([
	workerPromisify(new Worker('Scripts/encryption-helper.js')),
	new Promise((resolve, reject) => {
		document.addEventListener('DOMContentLoaded', evt => {
			resolve();
		});
	}),
//
]).then(items => {
	const importsectionElem = document.getElementById('upload-section');
	const inputElem = document.querySelector('input[type=file]');
	const encHelper = items[0];

	/* Functions */
	const readFile = file => {
		return new Promise((resolve, reject) => {
			const reader = new FileReader();

			reader.onerror = errorHandler;
			reader.onabort = errorHandler;
			reader.onload = () => {
				resolve(reader.result);
			};

			reader.readAsText(file);
		});
	}
	const sendFile = (title, contents) => {
		const password = sessionStorage.getItem('enc_token');
		const promiseArr = [];

		promiseArr.push(encHelper.encrypt({
			text: title,
			password,
		}));
		promiseArr.push(encHelper.encrypt({
			text: contents,
			password,
		}));

		Promise.all(promiseArr).then(([ title, content ]) => {
			const postObj = {
				'content-iv': content.iv,
				content: content.data,
				'title-iv': title.iv,
				edited: Date.now(),
				title: title.data,
			};

			phpPostRequest('edit_note', postObj).then(async ({ data }) => {
				if (data.success)
					window.location = '/dashboard.php';
				else
					errorHandler(data.error);
			}).catch(errorHandler);
		}).catch(errorHandler);
	}
	const getFilename = name => {
		return name.split('.')[0];
	}
	const safeJsonParse = txt => {
		return new Promise((resolve, reject) => {
			let json;
			try {
				json = JSON.parse(txt);
			} catch (err) {
				reject(err);
			}

			if (json)
				resolve(json);
			else
				reject('Invalid JSON');
		});
	}

	const handlePlain = file => {
		readFile(file).then(txt => {
			sendFile(getFilename(file.name), txt);
		}).catch(errorHandler);
	}
	const handleJson = file => {
		const checkParameters = json => {
			if ('key-info' in json && 'encryption-info' in json) {
				const encryptionInfo = json['encryption-info'];
				const keyInfo = json['key-info'];

				if ('salt' in keyInfo && 'iv' in encryptionInfo)
					return true;
			}

			return false;
		}

		readFile(file).then(txt => {
			safeJsonParse(txt).then(json => {
				if (checkParameters(json)) {
					const password = prompt('Encryption password:');

					if (password && password.trim().length > 0) {
						encHelper.decrypt({
							data: json['key-info'].salt.data + '-' + json.data,
							iv: json['encryption-info'].iv.data,
							password: password,
						}).then(({ data }) => {
							sendFile(getFilename(file.name), data);
						}).catch(errorHandler);
					} else {
						errorHandler('Invalid password');
					}
				} else {
					errorHandler('The JSON file does not contain the needed parameters');
				}
			}).catch(errorHandler);
		}).catch(errorHandler);
	}

	const saveFile = file => {
		if (file) {
			if (file.name.trim().length > 0) {
				switch (file.type) {
					case 'text/plain':
					handlePlain(file);
					break;
					case 'application/json':
					handleJson(file);
					break;
					default:
					errorHandler('Invalid file type');
					break;
				}
			} else {
				errorHandler('Invalid filename');
			}
		} else {
			errorHandler('No file selected');
		}
	}
	/* */

	inputElem.addEventListener('change', evt => {
		saveFile(inputElem.files[0]);
	});

	importsectionElem.addEventListener('click', evt => {
		inputElem.click();
	});
	importsectionElem.addEventListener('drop', evt => {
		evt.preventDefault();

		if ('dataTransfer' in evt)
			saveFile(evt.dataTransfer.files[0]);
	});
	importsectionElem.addEventListener('dragover', evt => {
		evt.preventDefault();
	});
	importsectionElem.addEventListener('dragenter', evt => {
		importsectionElem.setAttribute('dragover', '');
	});
	importsectionElem.addEventListener('dragleave', evt => {
		importsectionElem.removeAttribute('dragover');
	});

	document.getElementById('cancel-btn').addEventListener('click', evt => {
		window.location = '/dashboard.php';
	});
}).catch(errorHandler);