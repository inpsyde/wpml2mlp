# Scripts for development

These scripts assist the development process by analyzing XML files.

## Validate XML file

```
php validate-xml.php path/to/file.xml
```

## Search for elements by ID

Search for post with id 12:
```
php search-item.php path/to/file.xml 12 
```

Search for comment with id 42:
```
php search-item.php path/to/file.xml 42 comment 
```

Possible element types are `comment`, `post` (the default), `term` and `user`.
