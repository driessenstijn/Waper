# Waper

Website Logo Scraper

### Installation

```sh
composer require driessenstijn/waper/
```

## Why use waper?

Waper will allow you to crawl any given website to find out what logo it uses. It's simple and it should always return a logo for you.

## The logic

The logic is simple and straightforward. It will do the following checks in sequence to find a logo

- It will first try to find a logo named image on the website
- If not found, it will continue to check all CSS files of the website to find a logo in
- If all above methods fail, it will use Google to find it

## Fair Use Policy

Please use this package to add a logo to a given website when displaying or whenever you want to assist a user to add a logo for him on basis of a given website. Do note that the last step is to crawl also Google for a logo file. Google will block excessive attempts on the Google website so please be aware of this and use it wisely.

## Usage example

```php
require_once __DIR__ . '/../vendor/autoload.php';

use Waper\Waper;

$waper = new Waper('http://www.hourpendulum.com');
$image = $waper->fetch();
echo '<img src="'.$image.'" />';
```