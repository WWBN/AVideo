# Bunny Storage PHP library

The official PHP library used for interacting with the BunnyCDN Storage API.

## Install

```
composer require bunnycdn/storage
```

## Usage

Create an instance of the `\Bunny\Storage\Client` with the authentication details

```
$client = new \Bunny\Storage\Client('access-key', 'storage-zone', 'sg');
```

The BunnyCDNStorage constructor takes the following parameters:
- **apiAccessKey** - The API access key (password)
- **storageZoneName** - The name of your storage zone
- **storageZoneRegion** - The storage zone region code (de, ny, or sg)

### Navigation:

- [Upload](#uploading-objects)
- [Download](#downloading-objects)
- [List](#listing-objects)
- [Delete](#deleting-objects)

---

### Uploading objects

```
$client->upload('/path/to/local/file.txt', 'remote/path/hello-world.txt');
```

---

### Downloading objects

```
$client->download('remote/path/hello-world.txt', '/path/to/local/file.txt');
```

---

### Listing objects

```
$items = $client->listFiles('remote/path/');
```

The StorageObject contains the following properties:
- **Guid** - The unique GUID of the file
- **UserId** - The ID of the BunnyCDN user that holds the file
- **DateCreated** - The date when the file was created
- **LastChanged** - The date when the file was last modified
- **StorageZoneName** - The name of the storage zone to which the file is linked
- **Path** - The path to the object
- **ObjectName** - The name of the object
- **Length** - The total of the object in bytes
- **IsDirectory** - True if the object is a directory, otherwise false.
- **ServerId** - The ID of the storage server that the file resides on
- **StorageZoneId** - The ID of the storage zone that the object is linked to
- **FullPath** - The full path to the file

---

### Deleting objects

```
$client->delete('remote/path/hello-world.txt');
```
