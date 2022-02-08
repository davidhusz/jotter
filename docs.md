# API reference

## Retrieve notes
	GET /

To retrieve notes from locations other than the main folder, change the request
URI to `/archive` or `/trash`. To retrieve notes from all locations, use `/all`.

This request returns a JSON object with an array of objects called `notes`, each
of which has the following attributes:

- `id`: unique identifier
- `type`: `text` or `file`
- `location`: `main`, `archive` or `trash`
- `filename`: file name as uploaded (for file notes), suggested file name (for
   text notes)
- `filesize`: size in bytes
- `created`: creation time in ISO 8601 format
- `lastModified`: last modification time in ISO 8601 format
- `content` (text notes only): content of note

The content of file notes needs to be [individually
requested](#retrieve-only-note-contents-no-metadata).

### Query string parameters
These are the parameters that can be appended to the URL, and they are listed
here in the order in which they will be evaluated. For example, given the query
string `count=3&after=xyz`, first all notes before and including the note with
id `xyz` will be discarded, and afterwards the list will the truncated to three
notes.

- `before`: retrieve only notes newer than the note with this id
- `after`: retrieve only notes older than the note with this id
- `count`: number of notes to retrieve
- `skip`: number of notes to skip before retrieving

### Example
Retrieve the three most recent notes as JSON:

	curl yourserver.com/?count=3

## Retrieve an individual note
	GET /note/<id>

### Retrieve only note contents (no metadata)
	GET /note/<id>/raw
	GET /note/<id>/download

The difference between these is that `download` will trigger a download when
opened in a web browser, while `raw` will not[^1].

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
	POST /trash

### Restore note from trash
	POST /restore

### Bump note up
	POST /bump

This updates the note's last modification time to the current time, like the
Unix `touch` command.

### Delete note permanently
	POST /delete-permanently

**Caution**: This action cannot be undone.

[^1]: In technical terms, the `Content-Disposition` header is set to `inline`
for `raw` and to `attachment` for `download`.
