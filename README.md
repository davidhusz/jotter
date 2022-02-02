# jotter

`jotter` is a self-hosted service for all those "I don't know where to put this
right now" notes and files. It provides a dedicated space for temporary clutter
so you can sort through it at a more convenient time. (Or not. I understand.)

![Screenshot](https://demos.davidhuszmusic.com/jotter/screenshot.png)

Features:
- Simple drag-and-drop installation on any PHP-enabled server
- No database required - files are stored as is
- A straightforward HTTP API

# Installation
Download or `git clone` the repository onto a PHP-enabled server. It must be a
server that understands `.htaccess` files (such as Apache). The root of the app
is the `src` directory.

# API reference
See [docs.md](docs.md).

# Roadmap
- Note categories
	- Important (appearing highlighted at the top of the page)
	- Reminders
- Note archive and trash folders
- Bulk processing
- In-place editing
- Encrypted notes?

# FAQ
## Is there a mobile app?
There is not, however there is a preset for the [`HTTP Shortcuts`
app](https://http-shortcuts.rmy.ch) for Android that can provide you with
helpful widgets which work with `jotter`. You can find more information on the
[preset repository page](https://github.com/davidhusz/jotter-http-shortcuts).

Although I do not have any previous experience with app development, I may still
attempt to tackle the creation of a dedicated `jotter` app in the future.
