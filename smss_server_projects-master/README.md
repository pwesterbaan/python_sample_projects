# smss_server_projects

## Directory layout
```
/var/www/mthsc/html/
├── app
│   ├── flask_session
│   ├── redirects.py
│   └── subdir
│       ├── subdir_page1.py
│       ├── subdir_page2.py
│       ├── subsubdir_a
│       │       ├── subsubdir_a_page1.py
│       │       └── subsubdir_a_page2.py
│       ├── subsubdir_b
│       │       ├── subsubdir_b_page1.py
│       │       ├── subsubdir_b_page2.py
│       │       └── subsubsubdir_b
│       │           └── ...
│       ├── static
│       │   ├── images
│       │   ├── reports
│       │   ├── scripts
│       │   └── styles
│       │       ├── root_style.css
│       │       ├── a_style.css
│       │       ├── b_style.css
│       │       └── c_style.css
│       └── templates
│           ├── subdir_template01.html
│           ├── subdir_template02.html
│           ├── subsubdir_a
│           │   ├── subsubdir_a_01.html
│           │   └── subsubdir_a_02.html
│           └── subsubdir_b
│               ├── subsubdir_b_01.html
│               ├── subsubdir_b_02.html
│               └── subsubsubdir_b
│                   └── ...
└── style
```

* The `app` directory contains the main file `__init__.py`:
    + creates the Flask app
    + defines global variables
    + imports blueprints from subdirectories (`from <page> import <page>_bp`)
* The file `redirects.py` removes the \*.py and redirects (e.g. view_page.py → view_page)
* The `*_lib.py` files contain functions that interact with the database and other shared resources
    + Each library inherits shared common functions which reside in `/var/www/mthsc/common/common_lib.py`
* The directory `templates/` contains:
    + the `.html` templates,
    + the `*_layout.html` files contain the blank structure of the pages
* Each subdirectory contains a version of `__init__.py` that:
    + creates a blueprint for that subdirectory defining:
        * the template directory (typically `templates/`)
        * the static directory (typically `static`)
        * the `url_prefix` (typically `/subdir/`)
    + imports blueprints (if any) from subdirectories (`from <page> import <page>_bp`)


Files will be located at the following links:

| filename| url |
| --- | --- |
|`.app/subdir/root_page1.py` | [mthsc.clemson.edu/subdir/root_page1](mthsc.clemson.edu/subdir/root_page1)|
|`.app/subdir/subsubdir_a/a_page1.py` | [mthsc.clemson.edu/subdir/subsubdir_a/a_page1](mthsc.clemson.edu/subdir/subsubdir_a/a_page1)|

<hr>

## Running Locally
Whether running locally or via apache, the flask application should be run within the virtual environment at `/var/www/mthsc/common/venv/`

* To run this page locally, run the following within the `html` directory:
````
export FLASK_APP=run.py
export FLASK_ENV=development
source /var/www/mthsc/common/venv/bin/activate
flask run
````
* To open the flask interactive shell, run the following within the `html` directory:
````
export FLASK_APP=run.py
export FLASK_ENV=development
source /var/www/mthsc/common/venv/bin/activate
flask shell
````
With the interactive `flask shell`, commands such as `app.url_map` are available.

Arguments that are passed via render template are placed in a
single dictionary called "kwargs". If a dictionary key is undefined,
the page renders without any KeyErrors. A special entry of "kwargs"
named `debug` can be uncommented in `*_layout.html` for debugging.
When running locally (`flask run`), the python `print` function will
display its output directly in the terminal.
<hr>

## Running via Apache
Whether running locally or via apache, the flask application should be run within the virtual environment at `/var/www/mthsc/common/venv/`


`/etc/httpd/conf.d/phpMyAdmin.conf`:

* Two `AliasMatch` lines are set to handle *.php pages:
    + Any *.php pages located within a directory of the flask project need to be matched with the correct directory (e.g. `cmpt/helloWorld.php` -> html/app/cmpt/helloWorld.php)
    + All other *.php pages are matched so that flask does not handle/serve these pages

`/etc/httpd/conf.d/mthsc.conf`:

* the block starting with `WSGIDaemonProcess` indicates which `wsgi.py` file to associate with the root directory.

`wsgi.py`:

* configures the communication between apache and the actual python application
