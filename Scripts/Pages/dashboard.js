// Add 2 home-screen stuff
window.addEventListener('beforeinstallprompt', e => {
	const deferredPrompt = e;
	e.preventDefault();

	document.addEventListener('DOMContentLoaded', evt => {
		const btn = document.getElementById('addtohomescreen-btn');

		btn.style.display = 'block';
		btn.addEventListener('click', () => {
			btn.style.display = 'none';

			deferredPrompt.prompt();
			deferredPrompt.userChoice.then(choiceResult => {
				if (choiceResult.outcome === 'accepted')
					console.log('User accepted the A2HS prompt');
				else
					console.log('User dismissed the A2HS prompt');

				deferredPrompt = null;
			});
		});
	});
});


// Main code
// Check session value
if (sessionStorage.getItem('enc_token') === null)
	window.location = '/login.php?from=token_exp';


// Wait until the DOM is ready and the workers are loaded
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
	const userPopupElem = document.getElementById('user-popup');
	const localSaveElem = document.getElementById('local-save');
	const mainElem = document.getElementsByTagName('main')[0];
	const searchElem = document.getElementById('search');
	const encryptionHelper = items[0];
	const markdownParser = items[1];
	const errorHandler = console.error;

	/*
	*	Check if an element is within another element
	*
	*	@param {Element} parent 	The container element
	*	@param {Element} child 		The child element
	*
	*	@return {Boolean} 	Returns if the element is within the other
	*/
	const isDescendant = (parent, child) => {
		let node = child.parentNode;
		let looped = 0;

		while (node != null && looped < 5) {
			if (node == parent)
				return true;

			looped++;
			node = node.parentNode;
		}

		return false;
	}
	/*
	*	Insert a note into the DOM
	*
	*	@param {Object} item 		Note data
	*	@param {String} token 		Random token string
	*	@param {Array} classList 	Class list of the element
	*	@param {Boolean} addToElem 	The parent of the notes
	*/
	const addNote = async (item, token, classList, addToElem) => {
		const containerElem = document.createElement('div');
		const selectElem = document.createElement('input');
		const growElem = document.createElement('button');
		const mdElem = document.createElement('div');
		const h3Elem = document.createElement('h3');

		selectElem.setAttribute('aria-label', 'Toggle note selection');
		token = token.replace(/\+/g, '➕'); // Replace all '+' characters because PHP can't handle that for some reason
		selectElem.type = 'checkbox';

		growElem.innerHTML = '<img title="Grow note" alt="grow icon" src="/Assets/ic_grow_black.svg" />';
		growElem.setAttribute('aria-label', 'Grow note to fullscreen');
		growElem.addEventListener('click', growElemClick);

		if (item.title)
			h3Elem.innerText = (await encryptionHelper.decrypt({
				password: sessionStorage.getItem('enc_token'),
				data: item.title.data,
				iv: item.title.iv,
			})).data;
		else
			h3Elem.innerHTML = '<i>Untitled</i>';

		mdElem.classList.add('markdown-body');
		mdElem.innerHTML = (await markdownParser.parse((
			await encryptionHelper.decrypt({
				password: sessionStorage.getItem('enc_token'),
				data: item.content.data,
				iv: item.content.iv,
			})).data)).data;

		containerElem.classList.add('note', ...classList);
		containerElem.id = 'note-' + token;

		containerElem.setAttribute('role', 'button');
		containerElem.setAttribute('tabindex', 0);

		containerElem.appendChild(selectElem);
		containerElem.appendChild(growElem);
		containerElem.appendChild(h3Elem);
		containerElem.appendChild(mdElem);

		containerElem.addEventListener('click', noteElemClick);
		containerElem.addEventListener('keypress', noteElemClick);

		addToElem.appendChild(containerElem)
	}

	const noteElemClick = evt => {
		if (navigator.onLine) {
			const nodeName = evt.target.nodeName.toLowerCase();
			let notGrowButton = true;

			if (nodeName == 'img') {
				if (evt.target.parentNode.nodeName.toLowerCase() == 'button')
					notGrowButton = false;
			}

			if (nodeName != 'input' && nodeName != 'button' && nodeName != 'a' && notGrowButton)
				window.location = `/edit.php?token=${evt.currentTarget.id.replace('note-', '')}&type=${evt.currentTarget.classList[1]}`;
		}
	}

	const growElemClick = evt => {
		const imgElem = evt.currentTarget.getElementsByTagName('img')[0];
		const containerElem = evt.currentTarget.parentNode;

		if (containerElem.hasAttribute('grow')) {
			imgElem.src = '/Assets/ic_grow_black.svg';
			imgElem.setAttribute('alt', 'grow icon');
			imgElem.setAttribute('title', 'Grow note');

			evt.currentTarget.setAttribute('aria-label', 'Grow note to fullscreen');
			searchElem.removeAttribute('disabled');
			containerElem.removeAttribute('grow');
		} else {
			imgElem.src = '/Assets/ic_shrink_black.svg';
			imgElem.setAttribute('alt', 'shrink icon');
			imgElem.setAttribute('title', 'Shrink note');

			evt.currentTarget.setAttribute('aria-label', 'Shrink note to normal');
			containerElem.setAttribute('grow', true);
			searchElem.setAttribute('disabled', true);
		}
	}

	// Render server notes
	// const domparser = new DOMParser();
	Array.from(document.querySelectorAll('div#server-save div.note')).forEach(elem => {
		const inputElem = elem.getElementsByTagName('input')[0];
		const mdElem = elem.querySelector('div.markdown-body');
		const growElem = elem.getElementsByTagName('button')[0];
		const titleElem = elem.getElementsByTagName('h3')[0];
		const promiseArr = [];

		// Decrypt content
		promiseArr.push(new Promise((resolve, reject) => {
			encryptionHelper.decrypt({
				password: sessionStorage.getItem('enc_token'),
				iv: elem.getAttribute('contentIv'),
				data: mdElem.innerText,
			}).then(({ data }) => {
				markdownParser.parse(data).then(data => {
					/*const htmlStr = domparser.parseFromString(data.data, 'text/html');

					Array.from(htmlStr.getElementsByTagName('a')).forEach(elem => {
						const urlElem = new URL(elem.href);

						if (urlElem.protocol == 'https:') {
							let id = '';

							if (urlElem.host == 'youtube.com' && urlElem.pathname == '/watch')
								id = urlElem.searchParams.get('v');
							else if (urlElem.host == 'youtu.be')
								id = urlElem.pathname.replace('/', '');

							if (id.length > 0)
								elem.parentNode.replaceChild(domparser.parseFromString(`<iframe width="560" height="315" src="https://www.youtube.com/embed/${id}" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`, 'text/html').body.children[0], elem);
						}
					});*/
					// resolve(Array.from(htmlStr.body.children));

					resolve(data);
				}).catch(reject);
			}).catch(reject);
		}));

		// Decrypt title
		const titleIv = elem.getAttribute('titleIv');
		if (titleIv !== '😒') {
			promiseArr.push(encryptionHelper.decrypt({
				password: sessionStorage.getItem('enc_token'),
				data: titleElem.innerText,
				iv: titleIv,
			}));
		}

		Promise.all(promiseArr).then(data => {
			mdElem.innerHTML = data[0].data;

			/*mdElem.innerHTML = '';
			data[0].forEach(elem => mdElem.appendChild(elem));*/

			if (data[1])
				titleElem.innerText = data[1].data;

			elem.addEventListener('click', noteElemClick);
			elem.addEventListener('keypress', noteElemClick);

			inputElem.setAttribute('tabindex', 0);
			growElem.setAttribute('tabindex', 0);
			elem.setAttribute('tabindex', 0);
			elem.removeAttribute('disabled');

			growElem.addEventListener('click', growElemClick);
		}).catch(errorHandler);
	});

	document.getElementById('user-icon').addEventListener('click', evt => {
		userPopupElem.style.display = 'initial';
		setTimeout(() => {
			function clickEvt(evt) {
				if (!isDescendant(userPopupElem, evt.target)) {
					userPopupElem.style.display = 'none';
					document.body.removeEventListener('click', clickEvt);
				}
			}

			document.body.addEventListener('click', clickEvt);
		}, 100);
	});

	document.getElementById('logout-btn').addEventListener('click', evt => {
		logout();
	});

	document.getElementById('add-btn').addEventListener('click', evt => {
		window.location = '/createnote.php';
	});

	document.getElementById('delete-btn').addEventListener('click', async evt => {
		const items = document.querySelectorAll('main div.note > input[type=checkbox]:checked');
		const tokenRegex = /^note-(.+)$/;

		if (items) {
			const dbName = 'local';
			const obj = await localforage.getItem(dbName);
			const arr = Array.from(items);
			const serverNoteArr = [];

			for (const item of arr) {
				const parent = item.parentNode;

				if (parent.classList.contains('server'))
					serverNoteArr.push(tokenRegex.exec(parent.id)[1]);
				if (parent.classList.contains('local'))
					delete obj[(tokenRegex.exec(parent.id)[1]).replace(/➕/g, '+')];
			}

			await localforage.setItem(dbName, obj);
			if (serverNoteArr.length > 0) {
				const response = await phpPostRequest('delete', {
					notes: serverNoteArr
				});

				if (response.success != true)
					console.error(response.error);

				window.location.reload();
			} else {
				window.location.reload();
			}
		}
	});

	searchElem.addEventListener('change', evt => {
		const searchVal = evt.currentTarget.value.trim().toLowerCase();
		const elemArr = mainElem.querySelectorAll('div.note');

		if (elemArr.length > 20) {
			if (!confirm('You have more than 20 notes. This could be slow.\nAre you sure you want to continue?'))
				return;
		}

		Array.from(elemArr).forEach(elem => {
			if (searchVal.length == 0)
				elem.style.display = '';
			else {
				if (elem.innerText.toLowerCase().indexOf(searchVal) < 0)
					elem.style.display = 'none';
			}
		});
	});

	/*document.getElementById('pass-message-close').addEventListener('click', evt => {
		evt.currentTarget.parentNode.remove();
	}, { once: true });*/

	localforage.getItem('local').then(obj => {
		if (!obj)
			obj = {};

		if (Object.keys(obj).length <= 0) {
			localSaveElem.innerText = 'You have no locally saved notes';

			setTimeout(() => {
				localSaveElem.style.display = 'none';
			}, 2000);
		} else {
			localSaveElem.innerHTML = '';

			for (let key in obj)
				addNote(obj[key], key, ['local'], localSaveElem);
		}
	}).catch(err => {
		localSaveElem.innerHTML = '<i>An error occured when retreiving the locally saved notes<i>';
		console.error(err);
	});
});


// Service worker
if ('serviceWorker' in navigator)
	navigator.serviceWorker.register('/serviceworker.js');