This project was an assignment for Fontys FHICT Web1.
An implementation can be found [here](https://i409738.hera.fhict.nl/) for as long as I don't have any other assignments to be hosted on there.
Below you can find the simple documentation belonging to the project.
----------

# LockPad - WebApp
We are going to create a note taking web app where users can create an account and create notes in markdown.

## Features to implement
These features are what we came up with during the brainstorm. Due to a deadline only some of them are implemented.

1. [x] User login/sign up
2. [x] Creating/editing notes with markdown
3. [x] Auto save notes to local storage
4. [x] Encrypt notes
5. [ ] Sharing notes (with link)
6. [x] 2FA
7. [ ] Sharing notes between users
8. [x] Make the app work offline
9. [ ] When offline save notes that are not sent to the server yet
10. [x] High [Lighthouse](https://developers.google.com/web/tools/lighthouse/) score

## Target
This web app is for people who are concerned with privacy, because everything is encrypted on the client side.
The user also needs to be comfortable with markdown.
That's why we assume the target audience are people with a technical/security background.

### Why
Users who want a scalable note taking app that works offline and on every device.
The added benefit of client side encryption makes the app extra appealing for people concerned with privacy.
### Reach
Users who want to edit and manage their notes on any device anywhere and have proper security.
### Info
the web-app allowed the users to find and create their own notes so that they never forget anything anymore.


## Web features used for this project
- [Local storage API](https://developer.mozilla.org/en-US/docs/Web/API/Window/localStorage) for storing the encryption secret
- [WebWorkers](https://developer.mozilla.org/en-US/docs/Web/API/Web_Workers_API/Using_web_workers) for encryption and MarkDown parsing without blocking the main thread
- [Manifest.json](https://developers.google.com/web/fundamentals/web-app-manifest/) for the themes and icons
- [fetch API](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API/Using_Fetch) for JS web-requests
- ``<noscript>`` for ensuring JavaScript
- [2FA](https://github.com/RobThree/TwoFactorAuth) for the 2FA-authentication
- [CryptoJS](https://code.google.com/archive/p/crypto-js/) for cryptography
- [Showdown](https://github.com/showdownjs/showdown) for Markdown parsing
- [Gravatar](https://gravatar.com) for the user avatar
- [Identicons](https://identicon-1132.appspot.com) user avatar backup
- Authentication cookies for ensuring user security