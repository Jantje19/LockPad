const workerPromisify = worker => {
	const handlers = {};
	const randomString = (length = 16) => {
		return btoa(String.fromCharCode(...crypto.getRandomValues(new Uint8Array(length))));
	}

	return new Promise((resolve, reject) => {
		worker.onmessage = evt => {
			if ('data' in evt.data && 'err' in evt.data.data)
				reject(evt.data.data.err);
			else if ('type' in evt.data && evt.data.type.toLowerCase() === 'getfunctions') {
				const returnObj = {};

				evt.data.data.forEach(func => {
					returnObj[func] = data => {
						return new Promise((res, rej) => {
							const token = randomString();
							worker.postMessage({
								func: func,
								token,
								data
							});

							handlers[token] = {
								resolve: res,
								reject: rej
							}
						});
					}
				});

				resolve(Object.freeze(returnObj));
			} else {
				const token = evt.data.token;

				if (token in handlers) {
					if ('err' in evt.data.data)
						handlers[token].reject(evt.data.data.err);
					else
						handlers[token].resolve(evt.data.data);

					delete handlers[token];
				}
			}
		}

		worker.postMessage({
			func: 'getFunctions'
		});
	});
}