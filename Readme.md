# ExternBib

Mediawiki extension to convert BibTeX files to SQLite databases and
search engine queries to SQL queries.

## Requirements

- PHP >= 7.0.0
- Mediawiki >= 1.23

## Usage

### Command-line interface

To populate a SQLite database with a collection of BibTeX files:

```sh
php updatedb.php -o bibliography.db file1.bib file2.bib
```

To read specific entries from a SQLite database:

```sh
php dumpdb.php bibliography.db einstein1905 einstein1906
```

To run the testsuite:

```sh
php test/test_tex2html.php
php test/test_db.php
```

### Continuous delivery

If the BibTeX files are version-controlled in a git repository,
database generation can be automatized with a `post-receive` hook,
such as:

```sh
#!/bin/bash

read commit_old commit_new branch_name

if [ "${branch_name}" != "refs/heads/main" ]; then
    echo "Only changes committed to the main branch are copied to the wiki!" >&2
    echo "You have sent changes for ${branch_name}." >&2
    echo "Skipping." >&2
    exit 0
fi

echo "Copying changes to the wiki... Please wait." >&2

result=$(curl -Ls --data old=${commit_old} --data new=${commit_new} \
                  --data branch=${branch_name} --data repo=$(realpath "${PWD}") \
                  https://www2.icp.uni-stuttgart.de/gitlab-hooks/copy-bibtex.php)
if [ "${result}" != "Success" ]; then
    echo "An error occured while copying to the wiki:" >&2
    echo ${result} >&2
    exit 1
fi

echo "Successfully copied changes to the wiki." >&2
exit 0
```

### Mediawiki integration

To set up the BibTeX search engine in the wiki, copy the contents of the
project to `/path/to/mediawiki/extensions/ExternBib` and copy the database
file(s) to `/path/to/mediawiki/extensions/ExternBib/work`.

#### New HTML tags

To show citations, use `<bibentry>einstein1905a, einstein1905b</bibentry>`.
The comma is optional and can be replaced with a whitespace or newline character.
To show search results, use `<bibsearch>author=einstein</bibsearch>`.

The following properties are supported:

* `abstract="true"`: show abstract
* `bibtex="true"`: show raw BibTeX
* `compact="true"`: don't insert line breaks between paragraphs
* `meta="true"`: add BibTeX timestamp, owner and citation key
* `filelink="true"`: add links to files
* `fullentrylink="true"`: add link to full entry

#### New special pages

All records in the database(s) can be dynamically accessed via special
subpages. For example, to display einstein1905a, open subpage
`Special:ExternBibShowEntry/einstein1905a`.

The database(s) can be queries in the special page `Special:ExternBibSearch`.
There is a radio button to select the database to query. The query takes the
form `author=einstein and title="Wärme" and year<1906` where "=" is handled
as "contains". The search is case-insensitive and diacritics-insensitive.
Only the "and" logical operator is implemented.

## License

[BSD 3-clause "Modified" License](https://opensource.org/licenses/BSD-3-Clause New BSD License)
(C) The Authors, unless stated otherwise.
