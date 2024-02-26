<?php
/**
 * @link https://www.php.net/manual/en/ref.dio.php
 * Direct IO Functions
 * Table of Contents
 * dio_close — Closes the file descriptor given by fd
 * dio_fcntl — Performs a c library fcntl on fd
 * dio_open — Opens a file (creating it if necessary) at a lower level than the C library input/ouput stream functions allow
 * dio_read — Reads bytes from a file descriptor
 * dio_seek — Seeks to pos on fd from whence
 * dio_stat — Gets stat information about the file descriptor fd
 * dio_tcsetattr — Sets terminal attributes and baud rate for a serial port
 * dio_truncate — Truncates file descriptor fd to offset bytes
 * dio_write — Writes data to fd with optional truncation at length
 */

/**
 * dio_close ( resource $fd ) : void
 * The function dio_close() closes the file descriptor fd.
 * @link https://www.php.net/manual/en/function.dio-close.php
 * @param resource $fd The file descriptor returned by dio_open()
 * @return void
 */
function dio_close ($fd) {}

/**
 * dio_fcntl ( resource $fd , int $cmd [, mixed $args ] ) : mixed
 * The dio_fcntl() function performs the operation specified by cmd on the file descriptor fd.
 * Some commands require additional arguments args to be supplied.
 * @link https://www.php.net/manual/en/function.dio-fcntl.php
 * @param resource $fd The file descriptor returned by dio_open().
 * @param int $cmd The file descriptor returned by dio_open().
 * @param mixed ...$args args is an associative array, when cmd is F_SETLK or F_SETLLW, with the following keys:
 * start - offset where lock begins
 * length - size of locked area. zero means to end of file
 * whence - Where l_start is relative to: can be SEEK_SET, SEEK_END and SEEK_CUR
 * type - type of lock: can be F_RDLCK (read lock), F_WRLCK (write lock) or F_UNLCK (unlock)
 * @return mixed Returns the result of the C call.
 */
function dio_fcntl ($fd , int $cmd, ...$args) {}

/**
 * dio_open ( string $filename , int $flags [, int $mode = 0 ] ) : resource
 * The function dio_close() closes the file descriptor fd.
 * @link https://www.php.net/manual/en/function.dio-open.php
 * @param string $filename The pathname of the file to open.
 * @param int $flags
 * The flags parameter is a bitwise-ORed value comprising flags from the following list. This value must include one of O_RDONLY, O_WRONLY, or O_RDWR. Additionally, it may include any combination of the other flags from this list.
 * O_RDONLY - opens the file for read access.
 * O_WRONLY - opens the file for write access.
 * O_RDWR - opens the file for both reading and writing.
 * O_CREAT - creates the file, if it doesn't already exist.
 * O_EXCL - if both O_CREAT and O_EXCL are set and the file already exists, dio_open() will fail.
 * O_TRUNC - if the file exists and is opened for write access, the file will be truncated to zero length.
 * O_APPEND - write operations write data at the end of the file.
 * O_NONBLOCK - sets non blocking mode.
 * O_NOCTTY - prevent the OS from assigning the opened file as the process's controlling terminal when opening a TTY device file.
 * @param int $mode If flags contains O_CREAT, mode will set the permissions of the file (creation permissions). mode is required for correct operation when O_CREAT is specified in flags and is ignored otherwise.
 * The actual permissions assigned to the created file will be affected by the process's umask setting as per usual.
 * @return resource|false A file descriptor or FALSE on error.
 */
function dio_open (string $filename, int $flags, int $mode = 0) {}

/**
 * dio_read ( resource $fd [, int $len = 1024 ] ) : string
 * The function dio_read() reads and returns len bytes from file with descriptor fd.
 * @param resource $fd The file descriptor returned by dio_open().
 * @param int $len The number of bytes to read. If not specified, dio_read() reads 1K sized block.
 * @return int The bytes read from fd.
 * @link https://www.php.net/manual/en/function.dio-read.php
 */
function dio_read ($fd, int $len = 1024) {}

/**
 * dio_seek ( resource $fd , int $pos [, int $whence = SEEK_SET ] ) : int
 * dio_seek — Seeks to pos on fd from whence
 * @param resource $fd The file descriptor returned by dio_open().
 * @param int $pos The new position.
 * @return int $whence Specifies how the position pos should be interpreted:
 * SEEK_SET (default) - specifies that pos is specified from the beginning of the file.
 * SEEK_CUR - Specifies that pos is a count of characters from the current file position. This count may be positive or negative.
 * SEEK_END - Specifies that pos is a count of characters from the end of the file. A negative count specifies a position within the current extent of the file; a positive count specifies a position past the current end. If you set the position past the current end, and actually write data, you will extend the file with zeros up to that position.
 * @link https://www.php.net/manual/en/function.dio-seek.php
 */
function dio_seek ($fd, int $pos, int $whence = SEEK_SET) {}

/**
 * dio_stat ( resource $fd ) : array
 * dio_stat — Gets stat information about the file descriptor fd
 * @return array|null Returns an associative array with the following keys:
 * "device" - device
 * "inode" - inode
 * "mode" - mode
 * "nlink" - number of hard links
 * "uid" - user id
 * "gid" - group id
 * "device_type" - device type (if inode device)
 * "size" - total size in bytes
 * "blocksize" - blocksize
 * "blocks" - number of blocks allocated
 * "atime" - time of last access
 * "mtime" - time of last modification
 * "ctime" - time of last change
 * On error dio_stat() returns NULL.
 * @link https://www.php.net/manual/en/function.dio-stat.php
 */
function dio_stat ($fd) {}

/**
 * dio_tcsetattr ( resource $fd , array $options ) : bool
 * dio_tcsetattr — Sets terminal attributes and baud rate for a serial port
 * @param resource $fd The file descriptor returned by dio_open().
 * @param array $options The currently available options are:
 * 'baud' - baud rate of the port - can be 38400,19200,9600,4800,2400,1800, 1200,600,300,200,150,134,110,75 or 50, default value is 9600.
 * 'bits' - data bits - can be 8,7,6 or 5. Default value is 8.
 * 'stop' - stop bits - can be 1 or 2. Default value is 1.
 * 'parity' - can be 0,1 or 2. Default value is 0.
 * @return void
 * @link https://www.php.net/manual/en/function.dio-tcsetattr.php
 */
function dio_tcsetattr ($fd, array $options ) {}

/**
 * dio_truncate ( resource $fd , int $offset ) : bool
 * dio_truncate() truncates a file to at most offset bytes in size.
 * If the file previously was larger than this size, the extra data is lost.
 * If the file previously was shorter, it is unspecified whether the file is left unchanged or is extended.
 * In the latter case the extended part reads as zero bytes.
 * @param resource $fd The file descriptor returned by dio_open().
 * @param int $offset The offset in bytes.
 * @return bool Returns TRUE on success or FALSE on failure.
 * @link https://www.php.net/manual/en/function.dio-truncate.php
 */
function dio_truncate ($fd, int $offset) {}

/**
 * dio_write ( resource $fd , string $data [, int $len = 0 ] ) : int
 * dio_write — Writes data to fd with optional truncation at length
 * @link https://www.php.net/manual/en/function.dio-write.php
 * @param resource $fd The file descriptor returned by dio_open().
 * @param string $data The written data.
 * @param int $len The length of data to write in bytes. If not specified, the function writes all the data to the specified file.
 * @return int Returns the number of bytes written to fd.
 */
function dio_write ($fd, string $data, int $len = 0) {}
