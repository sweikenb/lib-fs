# Filesystem library


## Add GIT commit hook

Execute in project root:

```bash
rm -f "$(pwd)/.git/hooks/pre-commit"
ln -s "$(pwd)/bin/codequality" "$(pwd)/.git/hooks/pre-commit"
```


## Basic Usage

Load the document tree into memory, modify if needed and persist at the end:

```php
$service = new \Sweikenb\Library\Filesystem\Service\DirectoryTreeService();
$tree = $service->fetchTree('/my/directory/to/fetch');

foreach($tree->getChildDirs() as $dir) {
    foreach ($dir->getFiles() as $file) {
        // recursive function needed to traverse the tree
    }
}
foreach ($tree->getFiles() as $file) {
    $content = $file->getContent();
    // do some mods to the content ...
    $file->setContent($content);
}

$tree->persist();
```
