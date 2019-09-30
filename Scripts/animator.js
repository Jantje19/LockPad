const boxAnimation = (beforeRect, afterRect, { backgroundColor, boxShadow }) => {
	return new Promise(resolve => {
		let div = document.getElementById('animator');

		if (!div) {
			const newDiv = document.createElement('div');

			newDiv.id = 'animator';
			newDiv.style.top = '0px';
			newDiv.style.left = '0px';
			newDiv.style.display = 'none';
			newDiv.style.position = 'fixed';
			newDiv.style.willChange = 'transform';
			newDiv.style.transformOrigin = 'top left';
			newDiv.style.transition = 'transform .3s ease';

			document.body.appendChild(newDiv);
			div = newDiv;
		}

		div.style.transform = `translate(${beforeRect.x}px, ${beforeRect.y}px)`;
		div.style.backgroundColor = backgroundColor;
		div.style.height = beforeRect.height + 'px';
		div.style.width = beforeRect.width + 'px';
		div.style.boxShadow = boxShadow;
		div.style.display = 'block';

		setTimeout(() => {
			const newHeight = afterRect.height / beforeRect.height;
			const newWidth = afterRect.width / beforeRect.width;

			div.addEventListener('transitionend', () => {
				setTimeout(() => {
					div.style.display = 'none';
					resolve();
				}, 10);
			}, { once: true });

			div.style.transform = `translate(${afterRect.x}px, ${afterRect.y}px) scale(${newWidth}, ${newHeight})`;
		}, 50);
	});
}