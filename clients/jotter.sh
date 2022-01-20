#!/usr/bin/env bash

# TODO:
# documentation
# write out usage
# write out error messages
# IDEA:
# get rid of the --file flag and treat it as that automatically if a file of
# that name exists?
# only require password if user is actually making a request
# FIXME:
# `cat $file | jotter new` doesn't work with binary files

set -euo pipefail

if [ -f "$HOME/.config/jotter.rc" ]; then
    source "$HOME/.config/jotter.rc"
else
    echo "jotter.rc does not exist"
    exit 1
fi

request () {
    curl -sS \
         --fail \
         --user "${username:-}:${password:-}" \
         "$@"
}

new () {
    if [ -z "${1:-}" ]; then
        # request --data-urlencode "content=$(</dev/stdin)"
        request "$server/api/post.php" --form "content[]=@-"
    elif [ "${1:0:1}" = "-" ]; then
        case "$1" in
            -f|--files)
                shift
                request "$server/api/post.php" "${@/#/-Fcontent[]=@}"
                ;;
            *)
                echo "unknown option '$1'"
                exit 1
        esac
    else
        request "$server/api/post.php" --data-urlencode "content=$*"
    fi
}

latest () {
    request --get "$server" -d "count=1" -H "Accept: application/json" |
        jq -r '.notes[0] |
            if .type == "text"
            then .content
            else "File: \(.filepath)"
            end
        '
}

usage () {
    echo "display usage here"
}

main () {
    case "${1:-}" in
        new)
            shift
            new "$@"
            ;;
        latest)
            shift
            latest
            ;;
        -h|--help|"")
            usage
            ;;
        *)
            usage
            exit 1
            ;;
    esac
}

main "$@"
