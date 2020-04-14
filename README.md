# Snapshot Testing
[![Build Status](https://travis-ci.org/KigaRoo/snapshot-testing.svg?branch=master)](https://travis-ci.org/KigaRoo/snapshot-testing)

Provides PHPUnit assertions for snapshot testing

### Installation
```
composer require --dev kigaroo/snapshot-testing
```

### Basic Usage
```php
use KigaRoo\SnapshotTesting\MatchesSnapshots;

final class MyUnitTest extends TestCase
{
    use MatchesSnapshots;
    
    public function testJson()
    {
        $myJsonData = json_encode([
            'foo' => 'bar',
        ]);
        
        $this->assertMatchesJsonSnapshot($myJsonData);
    }    
    
    public function testXml()
    {
        $myXmlData = "<?xml version="1.0" encoding="UTF-8"?><root><id>7d644cc6-70fa-11e9-89e1-220d3e3a2561</id></root>";
        
        $this->assertMatchesXmlSnapshot($myJsonData);
    }
}
```

### Using Wildcards
If you have content in your data which changes intentionally you can use wildcards:
```php
use KigaRoo\SnapshotTesting\MatchesSnapshots;
use KigaRoo\SnapshotTesting\Wildcard\UuidWildcard;

final class MyUnitTest extends TestCase
{
    use MatchesSnapshots;
    
    public function testJson()
    {
        $myJsonData = json_encode([
            'id' => '7d644cc6-70fa-11e9-89e1-220d3e3a2561',
            'foo' => 'bar',
        ]);
        
        $this->assertMatchesJsonSnapshot($myJsonData, [
            new UuidWildcard('id'),
        ]);
    }    
    
    public function testXml()
    {
        $myXmlData = '<?xml version="1.0" encoding="UTF-8"?><root><id>7d644cc6-70fa-11e9-89e1-220d3e3a2561</id></root>';

        $this->assertMatchesXmlSnapshot($myXmlData, [
            new UuidWildcard('id'),
        ]);
    }
}
```

This ignores the concrete uuid given for the field "id" and only checks that a valid uuid is provided.

The library currently supports the following wildcards:
- BooleanWildcard
- IntegerWildcard
- UuidWildcard
- DateTimeWildcard
- DateTimeOrNullWildcard
- StringWildcard

### Usage in CI
When running your tests in Continuous Integration you would possibly want to disable the creation of snapshots.

By using the --without-creating-snapshots parameter, PHPUnit will fail if the snapshots don't exist.

```
> ./vendor/bin/phpunit -d --without-creating-snapshots

1) ExampleTest::test_it_matches_a_string
Snapshot "ExampleTest__test_it_matches_a_string__1.txt" does not exist. 
You can automatically create it by removing `-d --without-creating-snapshots` of PHPUnit's CLI arguments.
```