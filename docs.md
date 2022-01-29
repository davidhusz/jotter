# API reference

## Retrieve notes
	GET /

To retrieve notes from the trash folder, change the request URI to `/trash`. To
retrieve notes from both the main folder and the trash folder, use `/all`.

### Arguments
- `count` (optional): number of notes to retrieve
- `skip` (optional): number of notes to skip before retrieving

This returns a JSON object with an array of objects called `notes`, each of
which has the following attributes:

- `id`: unique identifier
- `type`: `text` or `file`
- `location`: `main` or `trash`
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

## Retrieve an individual note
	GET /note/<id>

## Create new note
	POST /post

Set the `Content-Type` request header to `application/x-www-form-urlencoded` for
text notes and `multipart/form-data` for file notes. If successful, a `201
Created` response is returned, the body of which contains a JSON representation
of the newly created note.

### Arguments
- `content`: note content (text or file contents)

### Examples
Create a new text note from standard input:

	curl yourserver.com/post --data-urlencode "content=$(cat)"

Save an image as a note:

	curl yourserver.com/post --form content=@image.png

Upload multiple files:

	curl yourserver.com/post --form content[]=@file1.txt content[]=@file2.txt

## Modify notes
All of the following commands take a single parameter `id` (see [Retrieve
notes](#retrieve-notes)).

### Move note to trash
	POST /delete

### Restore note from trash
	POST /restore

### Bump note up
This updates the note's last modification time to the current time, like the
Unix `touch` command.

	POST /bump
