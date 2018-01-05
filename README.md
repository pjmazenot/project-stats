# Project Stats

*Project stats is a PHP script scanning one or more target folders in order to display statistics. You can for instance use it to know the number of lines in a project, including for instance the number of comments*


## Table of Contents

  1. [Documentation](#documentation)  
    a. [CLI](#cli)  
      1. [Usage](#usage)
      1. [Options](#options)
      1. [Examples](#examples)
  1. [Changelog](#changelog)
  1. [TODO](#todo)
  1. [Contact](#contact)
  1. [License](#license)


## Documentation

### CLI

If the file **project-stats.php** is in the directory you want to scan, you can use the command

```bash
php project-stats.php -d .
```

#### Options

* ***[required]*** `-d`  
Set the target directory(ies)  

* *[optional]* `-h`
If present, display the script history   

* *[optional]* `--exclude-dirs`
If present, skip all directories with the specified names or paths (comma delimited list). When using the path mode this 
option format must match the corresponding path in the `-d` option: if the path in `-d` is relative, exclude a relative 
path, if the path in `-d` is absolute, exclude an absolute path. In the case of multiple target paths with different 
formats duplicate your excludes to match each target path format.   
 
* *[optional]* `--exclude-files-ext`
If present, skip all files with the specified extensions (comma delimited list)  

* *[optional]* `--exclude-files`
If present, skip all files with the specified names or paths (comma delimited list) (see `--exclude-dirs` for edge cases) 

#### Examples

1. Scan the current folder, except the files with the **.log** extension

```bash
php project-stats.php -d . --exclude-files-ext=log
```

2. Scan the **/var/www** folder, except the **/var/www/project2** and **/var/www/project3** directories

```bash
php project-stats.php -d . --exclude-dirs=/var/www/project2,/var/www/project3
```

3. Scan the **app** folder (relative to the script), except the **.log** and **.sql** files and the files named **.gitignore**

```bash
php project-stats.php -d ./app --exclude-files-ext=log,sql --exclude-files=**/.gitignore
```

4. Scan the **app** and the **/var/www** folders, except the dir with the path **/var/www/project2** the file with the path **./app/.gitignore**

```bash
php project-stats.php -d ./app --exclude-dirs=/var/www/project2 --exclude-files=./app/.gitignore
```

Example of output

```
[INFO] Generating project stats...
[HISTORY] Processing dir: ...
[HISTORY] Processing file: ...
...
[INFO] Generation completed

-------------------------------------------------------
|                    PROJECT STATS                    |
-------------------------------------------------------
| > Directories                                  1079 |
|                                                     |
| Included dirs scanned                          1076 |
| Skipped dirs (excluded)                           3 |
-------------------------------------------------------
| > Files                                       28028 |
|                                                     |
| Included files scanned                         3985 |
| Skipped files (excluded)                      24043 |
-------------------------------------------------------
| > Lines                                      591557 |
|                                                     |
| Code lines scanned                           462312 |
| Empty lines scanned                           74005 |
| Comment lines scanned                         55240 |
-------------------------------------------------------
| > Sizes                                             |
|                                                     |
| Total chars                                56186478 |
| Total size                                 56186478 |
-------------------------------------------------------

```

## Changelog

```

2018-01-04
* Create the main script
* Support call from CLI
* Support for directories (name and path) exclusion
* Support for file extensions exclusion
* Support for file (name and path) exclusion
* Support for the option "-h" to display or not the script history
* Add documentation
* Add README

```

## TODO

```

* Add support for inclusive commands (extensions)
* Add presets by language for exclusions
* Add case insensitive option
* Add an UI mode

Your suggestions are welcome :)

```

## Contact

For any suggestion or request, please send a message at pj.mazenot@gmail.com

## License

© 2017 Pierre-Julien Mazenot

[MIT](https://github.com/pjmazenot/project-stats/blob/master/LICENSE)