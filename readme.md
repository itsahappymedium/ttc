# Twig Template Compiler

[![packagist package version](https://img.shields.io/packagist/v/itsahappymedium/ttc.svg?style=flat-square)](https://packagist.org/packages/itsahappymedium/ttc)
[![packagist package downloads](https://img.shields.io/packagist/dt/itsahappymedium/ttc.svg?style=flat-square)](https://packagist.org/packages/itsahappymedium/ttc)
[![license](https://img.shields.io/github/license/itsahappymedium/ttc.svg?style=flat-square)](license.md)

A PHP Command Line tool for compiling Twig template files.


## Installation

### To a package (local)

```
composer require-dev itsahappymedium/ttc
./vendor/bin/ttc help
```

### To your system (global)

```
composer global require itsahappymedium/ttc
ttc help
```


## Usage

### Example

```sh
# Compiles all .twig files in the `templates` directory recursively
# Using variables from the `staging-server` object in `config.yml`
# Placing all generated files in the `public` directory
ttc -r -s templates -d public -f config.yml -b staging-server
```

At the very least, one of `input` or `source` options are required.


### Options

  - `--arguments-file` / `-f` - A file to load twig arguments/variables from.

  - `--arguments-file-type` / `-t` - The type of file `arguments-file` is. (Possible values: `env`,`json`,`yml`) (If omitted, the file type will be automatically determined based on the file extension.)

  - `--arguments-file-base` / `-b` - A dot-notation path inside of `arguments-file` where the arguments/variables should be loaded from.

  - `--destination` / `-d` - The directory to place the generated files in. (If omitted, the files will be placed in the same directory as the twig files)

  - `--input` / `-i` - An individual twig file to compile.

  - `--output` / `-o` - The path where the individual compiled file should go. (Used in combination with `input`) (If omitted, the compiled file will be placed in the same directory as the twig file)

  - `--source` / `-s` - The directory where the twig files are.


### Flags

  - `--recursive` / `-r` - Sets whether or not the `source` directory should be searched recursively. (Only used when the `source` option is used)


## Related

 - [FEC](https://github.com/itsahappymedium/fec) - A PHP Command Line tool that makes it easy to compile, concat, and minify front-end Javascript and CSS/SCSS dependencies.

 - [GPM](https://github.com/itsahappymedium/gpm) - A PHP Command Line tool that makes it easy to download dependencies from GitHub.


## License

MIT. See the [license.md file](license.md) for more info.