importScripts('/Scripts/sw-toolbox.js');

const noCacheEntryHandler = () => {
	return Response.redirect('/dashboard.php', 302);
}

toolbox.precache([
	'/dashboard.php',
	'/Stylesheets/main.css',
	'/Assets/Icons/favicon.ico',
	'/Stylesheets/dashboard.css',
	'/Stylesheets/loader.css',
	'/Stylesheets/markdown.css',
	'/Scripts/helpers.js',
	'/Scripts/Pages/dashboard.js',
	'/Scripts/workerPromisify.js',
	'/Scripts/localforage.min.js',
	'/Assets/ic_add_white.svg',
	'/Assets/ic_delete_white.svg'
	]);

toolbox.router.get('/dashboard.php', toolbox.networkFirst);
toolbox.router.get('/edit.php', toolbox.networkFirst);

toolbox.router.get('*', (...args) => {
	if (navigator.onLine)
		return toolbox.fastest(...args);
	else {
		return new Promise((resolve, reject) => {
			toolbox.cacheOnly(...args).then(response => {
				if (response)
					resolve(response);
				else
					resolve(noCacheEntryHandler());
			}).catch(err => {
				resolve(noCacheEntryHandler());
			});
		});
	}
});
toolbox.router.default = toolbox.fastest;