# API reference

## Retrieve notes
	GET /

### Arguments
- `count`: number of notes to retrieve (optional)
- `skip`: number of notes to skip before retrieving (optional)

This returns a JSON object with an array of objects called `notes`, each of
which has the following attributes:

- `id`: unique identifier
- `type`: `text` or `file`
- `filepath`: location on server
- `filesize`: size in bytes
- `originalFilename` (file notes only): name of file as uploaded
- `created`: creation time, human-readable
- `lastModified`: last modification time, human-readable
- `content` (text notes only): content of note

The content of file notes needs to be individually requested by following the
`filepath` attribute.

### Example
Retrieve the three most recent notes as JSON:

	curl yourserver.com/?count=3

## Create new note
	POST /api/post.php

Set the `Content-Type` request header to `application/x-www-form-urlencoded` for
text notes and `multipart/form-data` for file notes.

### Arguments
- `content`: note content (text or file contents)

### Examples
Create a new text note from standard input:

	curl yourserver.com/api/post.php --data-urlencode "content=$(cat)"

Save an image as a note:

	curl yourserver.com/api/post.php --form content[]=@image.png

## Modify notes
All of the following commands take a single parameter `id` (see [Retrieve
notes](#retrieve-notes)).

### Move note to trash
	POST /api/delete.php

### Restore note from trash
	POST /api/restore.php

### Bump note up
This updates the note's last modification time to the current time, like the
Unix `touch` command.

	POST /api/bump.php
