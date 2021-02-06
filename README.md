# Pandora

Pandora is a skin for those who made Myspace pages and for those who constantly tinker with skins and find them broken when they update MediaWiki.

Unlike other skins, Pandora is maintained completely on your wiki inside two pages: [[MediaWiki:Pandora.css]] and [[MediaWiki::Pandora.mustache]].

It is offered without any warranty and on the basis you know what you are doing by allowing arbitary HTML to be defined in a wiki page.

## Setup

Pandora has been made to be self explanatory.

The box (github repo) should be placed in your MediaWiki skins folder and the following added to LocalSettings.php:

```
wfLoadSkin( 'Pandora' );
$wgDefaultSkin = "pandora";
```

Once done, open your MediaWiki instance to open the box.
