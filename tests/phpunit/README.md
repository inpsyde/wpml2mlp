# WPML to MLP tests

## Setup tests 

1) Change to the root directory of the package:
```
$ cd /path/to/wpml2mlp
```

2) Install dependencies via composer:
```
$ composer install
```

### Unit tests

3) Run unit tests via phpunit:
```
$ phpunit 
```
PHPUnit will automatically use `phpunit.xml.dist` (or `phpunit.xml` if exists) as test config file.

### Integration tests

3) Create a test database

4) Copy `phpunit-integration.xml.dist` to `phpunit-integration.xml`:
```
$ cp phpunit-integration.xml.dist phpunit-integration.xml
```

5) Edit `phpunit-integration.xml` and insert your test database credentials:
```
<php>
	<const name="W2M\Test\DB_NAME" value="YOUR_TEST_DB_NAME" />
	<const name="W2M\Test\DB_USER" value="YOUR_TEST_DB_USER" />
	<const name="W2M\Test\DB_PASS" value="YOUR_TEST_DB_PASSWORD" />
</php>
```

6) Run the integration tests using this config file:
```
$ phpunit -c phpunit-integration.xml
```
