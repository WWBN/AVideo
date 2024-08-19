# Bunny Storage PHP library

The official PHP library used for interacting with the BunnyCDN Storage API.

## Install

```
composer require bunnycdn/storage
```

## Usage

Create an instance of the `\Bunny\Storage\Client` with the authentication details

```php
$client = new \Bunny\Storage\Client('access-key', 'storage-zone', \Bunny\Storage\Region::SINGAPORE);
```

The BunnyCDNStorage constructor takes the following parameters:
- **apiAccessKey** - The API access key (password)
- **storageZoneName** - The name of your storage zone
- **storageZoneRegion** - The storage zone region [code](src/Region.php#L9-L17) (de, ny, or sg)

### Navigation:

- [upload](#uploading-objects)
- [download](#downloading-objects)
- [listFiles](#listing-objects)
- [info](#get-an-objects-details)
- [delete](#deleting-objects)
- [deleteMultiple](#delete-multiple-objects)
- [putContents](#put-file-contents)
- [getContents](#get-file-contents)

---

### Uploading objects

```php
$client->upload('/path/to/local/file.txt', 'remote/path/hello-world.txt');
```

The checksum can be disabled using the `$withChecksum` parameter:

```php
$client->upload('/path/to/local/file.txt', 'remote/path/hello-world.txt', false);
```

> [!NOTE]
> Async uploads are supported with `$client->uploadAsync()`. It will return a `GuzzleHttp\Promise\PromiseInterface`.

---

### Downloading objects

```php
$client->download('remote/path/hello-world.txt', '/path/to/local/file.txt');
```

---

### Listing objects

```php
$items = $client->listFiles('remote/path/');
```

Returns an array of [FileInfo](src/FileInfo.php) objects.

---

### Get an object's details

```php
$item = $client->info('remote/path/hello-world.txt');
```

Returns an instance of [FileInfo](src/FileInfo.php).

---

### Deleting objects

```php
$client->delete('remote/path/hello-world.txt');
```

---

### Delete multiple objects

```php
$errors = $client->deleteMultiple(['file1.txt', 'file2.txt', 'non-existing.txt']);
var_dump($errors);

/*
array(1) {
  'non-existing.txt' =>
  string(16) "Object not found"
}
*/
```

---

### Put file contents

```php
$content = 'Hello, world!';
$client->putContents('hello-world.txt', $content);
```

The checksum can be disabled using the `$withChecksum` parameter:

```php
$content = 'Hello, world!';
$client->putContents('hello-world.txt', $content, false);
```

---

### Get file contents

```php
$content = $client->getContents('hello-world.txt');
echo $content; // Hello, world!
```
