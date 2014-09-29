# PHP Automated Webpagetest

A set of functionalities to schedule tests with webpagetest.

## Installation

- Download [latest master version](https://github.com/andou/pagetest/archive/master.zip) and place it in a folder with writing permission, at least from you.
- Unzip what you've dowloaded.
- Update the packaged `composer.phar`
```shell
$ php composer.phar self-update
```
- Install dependencies through composer
```shell
$ php composer.phar install
```
- Give run permission to the application
```shell
$ chmod +x pagestest
```
- Install the application
```shell
$ ./pagetest install -v
```
- Create the config file and add your private key
```shell
$ mv config/sample.config.ini.sample config/config.ini
```
- Create your first test by adding a `test.json` file into your test folder
```json
{
  "name": "Aol Test",
  "author": "andou",
  "basepath": "www.aol.com",
  "test_cases": [
    {
      "url": "/",
      "location": "Dulles",
      "browser": "Chrome",
      "connectivity": "Cable"
    }
  ]
}
```  
- Schedule your tests
```shell
$ ./pagetest run -vf
```
- Check for results
```shell
$ ./pagetest run -vc
```
- Generate reports
```shell
$ ./pagetest run -vr
```

