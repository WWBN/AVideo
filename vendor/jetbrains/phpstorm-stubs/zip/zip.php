<?php

// Start of zip v.1.14.0

/**
 * A file archive, compressed with Zip.
 * @link https://php.net/manual/en/class.ziparchive.php
 */
class ZipArchive implements Countable {
	/**
	 * Zip library version
	 * @link https://php.net/manual/en/zip.constants.php
	 * @since 7.4.3
	 */
	const LIBZIP_VERSION = '1.5.1';

	/**
	 * Create the archive if it does not exist.
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const CREATE = 1;

	/**
	 * Error if archive already exists.
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const EXCL = 2;

	/**
	 * Perform additional consistency checks on the archive, and error if they fail.
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const CHECKCONS = 4;

	/**
	 * Always start a new archive, this mode will overwrite the file if
	 * it already exists.
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const OVERWRITE = 8;

	/**
	 * Ignore case on name lookup
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const FL_NOCASE = 1;

	/**
	 * Ignore directory component
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const FL_NODIR = 2;

	/**
	 * Read compressed data
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const FL_COMPRESSED = 4;

	/**
	 * Use original data, ignoring changes.
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const FL_UNCHANGED = 8;

	/**
	 * better of deflate or store.
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const CM_DEFAULT = -1;

	/**
	 * stored (uncompressed).
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const CM_STORE = 0;

	/**
	 * shrunk
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const CM_SHRINK = 1;

	/**
	 * reduced with factor 1
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const CM_REDUCE_1 = 2;

	/**
	 * reduced with factor 2
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const CM_REDUCE_2 = 3;

	/**
	 * reduced with factor 3
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const CM_REDUCE_3 = 4;

	/**
	 * reduced with factor 4
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const CM_REDUCE_4 = 5;

	/**
	 * imploded
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const CM_IMPLODE = 6;

	/**
	 * deflated
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const CM_DEFLATE = 8;

	/**
	 * deflate64
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const CM_DEFLATE64 = 9;

	/**
	 * PKWARE imploding
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const CM_PKWARE_IMPLODE = 10;

	/**
	 * BZIP2 algorithm
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const CM_BZIP2 = 12;
	const CM_LZMA = 14;
	const CM_TERSE = 18;
	const CM_LZ77 = 19;
	const CM_WAVPACK = 97;
	const CM_PPMD = 98;

	/**
	 * No error.
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_OK = 0;

	/**
	 * Multi-disk zip archives not supported.
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_MULTIDISK = 1;

	/**
	 * Renaming temporary file failed.
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_RENAME = 2;

	/**
	 * Closing zip archive failed
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_CLOSE = 3;

	/**
	 * Seek error
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_SEEK = 4;

	/**
	 * Read error
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_READ = 5;

	/**
	 * Write error
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_WRITE = 6;

	/**
	 * CRC error
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_CRC = 7;

	/**
	 * Containing zip archive was closed
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_ZIPCLOSED = 8;

	/**
	 * No such file.
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_NOENT = 9;

	/**
	 * File already exists
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_EXISTS = 10;

	/**
	 * Can't open file
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_OPEN = 11;

	/**
	 * Failure to create temporary file.
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_TMPOPEN = 12;

	/**
	 * Zlib error
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_ZLIB = 13;

	/**
	 * Memory allocation failure
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_MEMORY = 14;

	/**
	 * Entry has been changed
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_CHANGED = 15;

	/**
	 * Compression method not supported.
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_COMPNOTSUPP = 16;

	/**
	 * Premature EOF
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_EOF = 17;

	/**
	 * Invalid argument
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_INVAL = 18;

	/**
	 * Not a zip archive
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_NOZIP = 19;

	/**
	 * Internal error
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_INTERNAL = 20;

	/**
	 * Zip archive inconsistent
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_INCONS = 21;

	/**
	 * Can't remove file
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_REMOVE = 22;

	/**
	 * Entry has been deleted
	 * @link https://php.net/manual/en/zip.constants.php
	 */
	const ER_DELETED = 23;

	/**
	 * No encryption
	 * @link https://secure.php.net/manual/en/zip.constants.php
	 * @since 7.2
	 */
	const EM_NONE = 0;

	/**
	 * AES 128 encryption
	 * @link https://secure.php.net/manual/en/zip.constants.php
	 * @since 7.2
	 */
	const EM_AES_128 = 257;

	/**
	 * AES 192 encryption
	 * @link https://secure.php.net/manual/en/zip.constants.php
	 * @since 7.2
	 */
	const EM_AES_192 = 258;

	/**
	 * AES 256 encryption
	 * @link https://secure.php.net/manual/en/zip.constants.php
	 * @since 7.2
	 */
	const EM_AES_256 = 259;

	/**
     * Open archive in read only mode
	 * @link https://secure.php.net/manual/en/zip.constants.php
	 * @since 7.4.3
	 */
	const RDONLY = 260;

    /**
     * Guess string encoding (is default)
     * @link https://secure.php.net/manual/en/zip.constants.php
     * @since 7.0.8
     */
	const FL_ENC_GUESS = 261;

    /**
     * Get unmodified string
     * @link https://secure.php.net/manual/en/zip.constants.php
     * @since 7.0.8
     */
	const FL_ENC_RAW = 262;

    /**
     * Follow specification strictly
     * @link https://secure.php.net/manual/en/zip.constants.php
     * @since 7.0.8
     */
	const FL_ENC_STRICT = 263;

    /**
     * String is UTF-8 encoded
     * @link https://secure.php.net/manual/en/zip.constants.php
     * @since 7.0.8
     */
	const FL_ENC_UTF_8 = 264;

    /**
     * String is CP437 encoded
     * @link https://secure.php.net/manual/en/zip.constants.php
     * @since 7.0.8
     */
	const FL_ENC_CP437 = 265;

    /**
     * LZMA2 algorithm
     * @link https://secure.php.net/manual/en/zip.constants.php
     * @since 7.4.3
     */
	const CM_LZMA2 = 266;

    /**
     * XZ algorithm
     * @link https://secure.php.net/manual/en/zip.constants.php
     * @since 7.4.3
     */
	const CM_XZ = 267;

    /**
     * Encryption method not support
     * @link https://secure.php.net/manual/en/zip.constants.php
     * @since 7.4.3
     */
	const ER_ENCRNOTSUPP = 268;

    /**
     * Read-only archive
     * @link https://secure.php.net/manual/en/zip.constants.php
     * @since 7.4.3
     */
	const ER_RDONLY = 269;

    /**
     * No password provided
     * @link https://secure.php.net/manual/en/zip.constants.php
     * @since 7.4.3
     */
	const ER_NOPASSWD = 270;

    /**
     * Wrong password provided
     * @link https://secure.php.net/manual/en/zip.constants.php
     * @since 7.4.3
     */
	const ER_WRONGPASSWD = 271;

    /**
     * Operation not supported
     * @link https://secure.php.net/manual/en/zip.constants.php
     * @since 7.4.3
     */
	const ER_OPNOTSUPP = 272;

    /**
     * Resource still in use
     * @link https://secure.php.net/manual/en/zip.constants.php
     * @since 7.4.3
     */
	const ER_INUSE = 273;

    /**
     * Tell error
     * @link https://secure.php.net/manual/en/zip.constants.php
     * @since 7.4.3
     */
	const ER_TELL = 274;

    /**
     * Compressed data invalid
     * @link https://secure.php.net/manual/en/zip.constants.php
     * @since 7.4.3
     */
	const ER_COMPRESSED_DATA = 275;

    /**
     * Operation cancelled
     * @link https://secure.php.net/manual/en/zip.constants.php
     * @since 7.4.3
     */
	const ER_CANCELLED = 276;

	/**
	 * @link https://www.php.net/manual/en/zip.constants.php#ziparchive.constants.opsys.default
	 * @since 5.6
	 */
	 const OPSYS_DOS = 0;

	/**
	 * @link https://www.php.net/manual/en/zip.constants.php#ziparchive.constants.opsys.default
	 * @since 5.6
	 */
	 const OPSYS_AMIGA = 1;

	/**
	 * @link https://www.php.net/manual/en/zip.constants.php#ziparchive.constants.opsys.default
	 * @since 5.6
	 */
	 const OPSYS_OPENVMS = 2;

	/**
	 * @link https://www.php.net/manual/en/zip.constants.php#ziparchive.constants.opsys.default
	 * @since 5.6
	 */
	 const OPSYS_UNIX = 3;

	/**
	 * @link https://www.php.net/manual/en/zip.constants.php#ziparchive.constants.opsys.default
	 * @since 5.6
	 */
	 const OPSYS_VM_CMS = 4;

	/**
	 * @link https://www.php.net/manual/en/zip.constants.php#ziparchive.constants.opsys.default
	 * @since 5.6
	 */
	 const OPSYS_ATARI_ST = 5;

	/**
	 * @link https://www.php.net/manual/en/zip.constants.php#ziparchive.constants.opsys.default
	 * @since 5.6
	 */
	 const OPSYS_OS_2 = 6;

	/**
	 * @link https://www.php.net/manual/en/zip.constants.php#ziparchive.constants.opsys.default
	 * @since 5.6
	 */
	 const OPSYS_MACINTOSH = 7;

	/**
	 * @link https://www.php.net/manual/en/zip.constants.php#ziparchive.constants.opsys.default
	 * @since 5.6
	 */
	 const OPSYS_Z_SYSTEM = 8;

	/**
	 * @link https://www.php.net/manual/en/zip.constants.php#ziparchive.constants.opsys.default
	 * @removed 8.0 Use {@link ZipArchive::ZOPSYS_CPM} instead.
	 * @since 5.6
	 */
	 const OPSYS_Z_CPM = 9;

	/**
	 * @link https://www.php.net/manual/en/zip.constants.php#ziparchive.constants.opsys.default
	 * @since 5.6
	 */
	 const OPSYS_WINDOWS_NTFS = 10;

	/**
	 * @link https://www.php.net/manual/en/zip.constants.php#ziparchive.constants.opsys.default
	 * @since 5.6
	 */
	 const OPSYS_MVS = 11;

	/**
	 * @link https://www.php.net/manual/en/zip.constants.php#ziparchive.constants.opsys.default
	 * @since 5.6
	 */
	 const OPSYS_VSE = 12;

	/**
	 * @link https://www.php.net/manual/en/zip.constants.php#ziparchive.constants.opsys.default
	 * @since 5.6
	 */
	 const OPSYS_ACORN_RISC = 13;

	/**
	 * @link https://www.php.net/manual/en/zip.constants.php#ziparchive.constants.opsys.default
	 * @since 5.6
	 */
	 const OPSYS_VFAT = 14;

	/**
	 * @link https://www.php.net/manual/en/zip.constants.php#ziparchive.constants.opsys.default
	 * @since 5.6
	 */
	 const OPSYS_ALTERNATE_MVS = 15;

	/**
	 * @link https://www.php.net/manual/en/zip.constants.php#ziparchive.constants.opsys.default
	 * @since 5.6
	 */
	 const OPSYS_BEOS = 16;

	/**
	 * @link https://www.php.net/manual/en/zip.constants.php#ziparchive.constants.opsys.default
	 * @since 5.6
	 */
	 const OPSYS_TANDEM = 17;

	/**
	 * @link https://www.php.net/manual/en/zip.constants.php#ziparchive.constants.opsys.default
	 * @since 5.6
	 */
	 const OPSYS_OS_400 = 18;

	/**
	 * @link https://www.php.net/manual/en/zip.constants.php#ziparchive.constants.opsys.default
	 * @since 5.6
	 */
	 const OPSYS_OS_X = 19;

    /**
     * @link https://www.php.net/manual/en/zip.constants.php#ziparchive.constants.opsys.default
     */
    const OPSYS_CPM = 20;

	/**
	 * @link https://www.php.net/manual/en/zip.constants.php#ziparchive.constants.opsys.default
	 * @since 5.6
	 */
	 const OPSYS_DEFAULT = 3;

   /**
     * Status of the Zip Archive
     */
    public $status;
    /**
     * System status of the Zip Archive
     */
    public $statusSys;
    /**
     * Number of files in archive
     */
    public $numFiles;
    /**
     * File name in the file system
     */
    public $filename;
    /**
     * Comment for the archive
     */
    public $comment;

    public $lastId;


	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.1.0)<br/>
	 * Open a ZIP file archive
	 * @link https://php.net/manual/en/ziparchive.open.php
	 * @param string $filename <p>
	 * The file name of the ZIP archive to open.
	 * </p>
	 * @param int $flags [optional] <p>
	 * The mode to use to open the archive.
	 * <p>
	 * <b>ZipArchive::OVERWRITE</b>
	 * </p>
	 * @return mixed <i>Error codes</i>
	 * <p>
	 * Returns <b>TRUE</b> on success or the error code.
	 * <p>
	 * <b>ZipArchive::ER_EXISTS</b>
	 * </p>
	 * <p>
	 * File already exists.
	 * </p>
	 * <p>
	 * <b>ZipArchive::ER_INCONS</b>
	 * </p>
	 * <p>
	 * Zip archive inconsistent.
	 * </p>
	 * <p>
	 * <b>ZipArchive::ER_INVAL</b>
	 * </p>
	 * <p>
	 * Invalid argument.
	 * </p>
	 * <p>
	 * <b>ZipArchive::ER_MEMORY</b>
	 * </p>
	 * <p>
	 * Malloc failure.
	 * </p>
	 * <p>
	 * <b>ZipArchive::ER_NOENT</b>
	 * </p>
	 * <p>
	 * No such file.
	 * </p>
	 * <p>
	 * <b>ZipArchive::ER_NOZIP</b>
	 * </p>
	 * <p>
	 * Not a zip archive.
	 * </p>
	 * <p>
	 * <b>ZipArchive::ER_OPEN</b>
	 * </p>
	 * <p>
	 * Can't open file.
	 * </p>
	 * <p>
	 * <b>ZipArchive::ER_READ</b>
	 * </p>
	 * <p>
	 * Read error.
	 * </p>
	 * <p>
	 * <b>ZipArchive::ER_SEEK</b>
	 * </p>
	 * <p>
	 * Seek error.
	 * </p>
	 * </p>
	 */
	public function open ($filename, $flags = null) {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.1.0)<br/>
	 * Close the active archive (opened or newly created)
	 * @link https://php.net/manual/en/ziparchive.close.php
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function close () {}

	/**
	 * (PHP 7 &gt;= 7.2.0, PECL zip &gt;= 1.15.0)<br/>
	 * Counts the number of files in the archive.
	 * @link https://www.php.net/manual/en/ziparchive.count.php
	 * @return int
	 * @since 7.2
	 */
	public function count() {}

	/**
	 * Returns the status error message, system and/or zip messages
	 * @link https://php.net/manual/en/ziparchive.getstatusstring.php
	 * @return string|false a string with the status message on success or <b>FALSE</b> on failure.
	 * @since 5.2.7
	 */
	public function getStatusString () {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.8.0)<br/>
	 * Add a new directory
	 * @link https://php.net/manual/en/ziparchive.addemptydir.php
	 * @param string $dirname <p>
	 * The directory to add.
	 * </p>
	 * @param int $flags [optional] Set how to manage name encoding (ZipArchive::FL_ENC_*) and entry replacement (ZipArchive::FL_OVERWRITE)
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function addEmptyDir ($dirname, $flags) {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.1.0)<br/>
	 * Add a file to a ZIP archive using its contents
	 * @link https://php.net/manual/en/ziparchive.addfromstring.php
	 * @param string $localname <p>
	 * The name of the entry to create.
	 * </p>
	 * @param string $contents <p>
	 * The contents to use to create the entry. It is used in a binary
	 * safe mode.
	 * </p>
	 * @param int $flags [optional] Set how to manage name encoding (ZipArchive::FL_ENC_*) and entry replacement (ZipArchive::FL_OVERWRITE)
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function addFromString ($localname, $contents, $flags) {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.1.0)<br/>
	 * Adds a file to a ZIP archive from the given path
	 * @link https://php.net/manual/en/ziparchive.addfile.php
	 * @param string $filename <p>
	 * The path to the file to add.
	 * </p>
	 * @param string $localname [optional] <p>
	 * If supplied, this is the local name inside the ZIP archive that will override the <i>filename</i>.
	 * </p>
	 * @param int $start [optional] <p>
	 * This parameter is not used but is required to extend <b>ZipArchive</b>.
	 * </p>
	 * @param int $length [optional] <p>
	 * This parameter is not used but is required to extend <b>ZipArchive</b>.
	 * </p>
	 * @param int $flags [optional] Set how to manage name encoding (ZipArchive::FL_ENC_*) and entry replacement (ZipArchive::FL_OVERWRITE)
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function addFile ($filename, $localname = null, $start = 0, $length = 0, $flags) {}

	/**
	 * (PHP 5 &gt;= 5.3.0, PECL zip &gt;= 1.9.0)<br/>
	 * Add files from a directory by glob pattern
	 * @link https://php.net/manual/en/ziparchive.addglob.php
	 * @param string $pattern <p>
	 * A <b>glob</b> pattern against which files will be matched.
	 * </p>
	 * @param int $flags [optional] <p>
	 * A bit mask of glob() flags.
	 * </p>
	 * @param array $options [optional] <p>
	 * An associative array of options. Available options are:
	 * <p>
	 * "add_path"
	 * </p>
	 * <p>
	 * Prefix to prepend when translating to the local path of the file within
	 * the archive. This is applied after any remove operations defined by the
	 * "remove_path" or "remove_all_path"
	 * options.
	 * </p>
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function addGlob ($pattern, $flags = 0, array $options = array()) {}

	/**
	 * (PHP 5 &gt;= 5.3.0, PECL zip &gt;= 1.9.0)<br/>
	 * Add files from a directory by PCRE pattern
	 * @link https://php.net/manual/en/ziparchive.addpattern.php
	 * @param string $pattern <p>
	 * A PCRE pattern against which files will be matched.
	 * </p>
	 * @param string $path [optional] <p>
	 * The directory that will be scanned. Defaults to the current working directory.
	 * </p>
	 * @param array $options [optional] <p>
	 * An associative array of options accepted by <b>ZipArchive::addGlob</b>.
	 * </p>
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function addPattern ($pattern, $path = '.', array $options = array()) {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.5.0)<br/>
	 * Renames an entry defined by its index
	 * @link https://php.net/manual/en/ziparchive.renameindex.php
	 * @param int $index <p>
	 * Index of the entry to rename.
	 * </p>
	 * @param string $newname <p>
	 * New name.
	 * </p>
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function renameIndex ($index, $newname) {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.5.0)<br/>
	 * Renames an entry defined by its name
	 * @link https://php.net/manual/en/ziparchive.renamename.php
	 * @param string $name <p>
	 * Name of the entry to rename.
	 * </p>
	 * @param string $newname <p>
	 * New name.
	 * </p>
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function renameName ($name, $newname) {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.4.0)<br/>
	 * Set the comment of a ZIP archive
	 * @link https://php.net/manual/en/ziparchive.setarchivecomment.php
	 * @param string $comment <p>
	 * The contents of the comment.
	 * </p>
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function setArchiveComment ($comment) {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.1.0)<br/>
	 * Returns the Zip archive comment
	 * @link https://php.net/manual/en/ziparchive.getarchivecomment.php
	 * @param int $flags [optional] <p>
	 * If flags is set to <b>ZipArchive::FL_UNCHANGED</b>, the original unchanged
	 * comment is returned.
	 * </p>
	 * @return string|false the Zip archive comment or <b>FALSE</b> on failure.
	 */
	public function getArchiveComment ($flags = null) {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.4.0)<br/>
	 * Set the comment of an entry defined by its index
	 * @link https://php.net/manual/en/ziparchive.setcommentindex.php
	 * @param int $index <p>
	 * Index of the entry.
	 * </p>
	 * @param string $comment <p>
	 * The contents of the comment.
	 * </p>
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function setCommentIndex ($index, $comment) {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.4.0)<br/>
	 * Set the comment of an entry defined by its name
	 * @link https://php.net/manual/en/ziparchive.setcommentname.php
	 * @param string $name <p>
	 * Name of the entry.
	 * </p>
	 * @param string $comment <p>
	 * The contents of the comment.
	 * </p>
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function setCommentName ($name, $comment) {}

	/**
	 * Set the compression method of an entry defined by its index
	 * @link https://php.net/manual/en/ziparchive.setcompressionindex.php
	 * @param int $index Index of the entry.
	 * @param int $comp_method The compression method. Either ZipArchive::CM_DEFAULT, ZipArchive::CM_STORE or ZipArchive::CM_DEFLATE.
	 * @param int $comp_flags [optional] Compression flags. Currently unused.
	 * @return bool Returns TRUE on success or FALSE on failure.
	 * @since 7.0
	 */
	public function setCompressionIndex ($index, $comp_method, $comp_flags = 0) {}

	/**
	 * Set the compression method of an entry defined by its name
	 * https://secure.php.net/manual/en/ziparchive.setcompressionname.php
	 * @param string $name Name of the entry.
	 * @param int $comp_method The compression method. Either ZipArchive::CM_DEFAULT, ZipArchive::CM_STORE or ZipArchive::CM_DEFLATE.
	 * @param int $comp_flags [optional] Compression flags. Currently unused.
	 * @return bool Returns TRUE on success or FALSE on failure.
	 * @since 7.0
	 */
	public function setCompressionName ($name, $comp_method, $comp_flags = 0){}

    /**
     * Set the encryption method of an entry defined by its index
     * @link https://php.net/manual/en/ziparchive.setencryptionindex.php
     * @param int $index Index of the entry.
     * @param string $method The encryption method defined by one of the ZipArchive::EM_ constants.
     * @param string $password [optional] Optional password, default used when missing.
     * @return bool Returns TRUE on success or FALSE on failure.
     * @since 7.2
     */
    public function setEncryptionIndex ($index, $method, $password = null) {}

    /**
     * Set the encryption method of an entry defined by its name
     * @link https://php.net/manual/en/ziparchive.setencryptionname.php
     * @param string $name Name of the entry.
     * @param int $method The encryption method defined by one of the ZipArchive::EM_ constants.
     * @param string $password [optional] Optional password, default used when missing.
     * @return bool Returns TRUE on success or FALSE on failure.
     * @since 7.2
     */
    public function setEncryptionName ($name, $method, $password = null) {}

    /**
     * (PHP 5 &gt;= 5.6.0, PECL zip &gt;= 1.12.0)<br/>
     * @param string $password
     * @return bool
     */
    public function setPassword($password) {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.4.0)<br/>
	 * Returns the comment of an entry using the entry index
	 * @link https://php.net/manual/en/ziparchive.getcommentindex.php
	 * @param int $index <p>
	 * Index of the entry
	 * </p>
	 * @param int $flags [optional] <p>
	 * If flags is set to <b>ZipArchive::FL_UNCHANGED</b>, the original unchanged
	 * comment is returned.
	 * </p>
	 * @return string|false the comment on success or <b>FALSE</b> on failure.
	 */
	public function getCommentIndex ($index, $flags = null) {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.4.0)<br/>
	 * Returns the comment of an entry using the entry name
	 * @link https://php.net/manual/en/ziparchive.getcommentname.php
	 * @param string $name <p>
	 * Name of the entry
	 * </p>
	 * @param int $flags [optional] <p>
	 * If flags is set to <b>ZipArchive::FL_UNCHANGED</b>, the original unchanged
	 * comment is returned.
	 * </p>
	 * @return string|false the comment on success or <b>FALSE</b> on failure.
	 */
	public function getCommentName ($name, $flags = null) {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.5.0)<br/>
	 * delete an entry in the archive using its index
	 * @link https://php.net/manual/en/ziparchive.deleteindex.php
	 * @param int $index <p>
	 * Index of the entry to delete.
	 * </p>
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function deleteIndex ($index) {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.5.0)<br/>
	 * delete an entry in the archive using its name
	 * @link https://php.net/manual/en/ziparchive.deletename.php
	 * @param string $name <p>
	 * Name of the entry to delete.
	 * </p>
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function deleteName ($name) {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.5.0)<br/>
	 * Get the details of an entry defined by its name.
	 * @link https://php.net/manual/en/ziparchive.statname.php
	 * @param string $name <p>
	 * Name of the entry
	 * </p>
	 * @param int $flags [optional] <p>
	 * The flags argument specifies how the name lookup should be done.
	 * Also, <b>ZipArchive::FL_UNCHANGED</b> may be ORed to it to request
	 * information about the original file in the archive,
	 * ignoring any changes made.
	 * <p>
	 * <b>ZipArchive::FL_NOCASE</b>
	 * </p>
	 * @return array|false an array containing the entry details or <b>FALSE</b> on failure.
	 */
	public function statName ($name, $flags = null) {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.1.0)<br/>
	 * Get the details of an entry defined by its index.
	 * @link https://php.net/manual/en/ziparchive.statindex.php
	 * @param int $index <p>
	 * Index of the entry
	 * </p>
	 * @param int $flags [optional] <p>
	 * <b>ZipArchive::FL_UNCHANGED</b> may be ORed to it to request
	 * information about the original file in the archive,
	 * ignoring any changes made.
	 * </p>
	 * @return array|false an array containing the entry details or <b>FALSE</b> on failure.
	 */
	public function statIndex ($index, $flags = null) {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.5.0)<br/>
	 * Returns the index of the entry in the archive
	 * @link https://php.net/manual/en/ziparchive.locatename.php
	 * @param string $name <p>
	 * The name of the entry to look up
	 * </p>
	 * @param int $flags [optional] <p>
	 * The flags are specified by ORing the following values,
	 * or 0 for none of them.
	 * <p>
	 * <b>ZipArchive::FL_NOCASE</b>
	 * </p>
	 * @return int|false the index of the entry on success or <b>FALSE</b> on failure.
	 */
	public function locateName ($name, $flags = null) {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.5.0)<br/>
	 * Returns the name of an entry using its index
	 * @link https://php.net/manual/en/ziparchive.getnameindex.php
	 * @param int $index <p>
	 * Index of the entry.
	 * </p>
	 * @param int $flags [optional] <p>
	 * If flags is set to <b>ZipArchive::FL_UNCHANGED</b>, the original unchanged
	 * name is returned.
	 * </p>
	 * @return string|false the name on success or <b>FALSE</b> on failure.
	 */
	public function getNameIndex ($index, $flags = null) {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.1.0)<br/>
	 * Revert all global changes done in the archive.
	 * @link https://php.net/manual/en/ziparchive.unchangearchive.php
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function unchangeArchive () {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.1.0)<br/>
	 * Undo all changes done in the archive
	 * @link https://php.net/manual/en/ziparchive.unchangeall.php
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function unchangeAll () {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.1.0)<br/>
	 * Revert all changes done to an entry at the given index
	 * @link https://php.net/manual/en/ziparchive.unchangeindex.php
	 * @param int $index <p>
	 * Index of the entry.
	 * </p>
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function unchangeIndex ($index) {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.5.0)<br/>
	 * Revert all changes done to an entry with the given name.
	 * @link https://php.net/manual/en/ziparchive.unchangename.php
	 * @param string $name <p>
	 * Name of the entry.
	 * </p>
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function unchangeName ($name) {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.1.0)<br/>
	 * Extract the archive contents
	 * @link https://php.net/manual/en/ziparchive.extractto.php
	 * @param string $destination <p>
	 * Location where to extract the files.
	 * </p>
	 * @param mixed $entries [optional] <p>
	 * The entries to extract. It accepts either a single entry name or
	 * an array of names.
	 * </p>
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function extractTo ($destination, $entries = null) {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.1.0)<br/>
	 * Returns the entry contents using its name
	 * @link https://php.net/manual/en/ziparchive.getfromname.php
	 * @param string $name <p>
	 * Name of the entry
	 * </p>
	 * @param int $length [optional] <p>
	 * The length to be read from the entry. If 0, then the
	 * entire entry is read.
	 * </p>
	 * @param int $flags [optional] <p>
	 * The flags to use to open the archive. the following values may
	 * be ORed to it.
	 * <p>
	 * <b>ZipArchive::FL_UNCHANGED</b>
	 * </p>
	 * @return string|false the contents of the entry on success or <b>FALSE</b> on failure.
	 */
	public function getFromName ($name, $length = 0, $flags = null) {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.3.0)<br/>
	 * Returns the entry contents using its index
	 * @link https://php.net/manual/en/ziparchive.getfromindex.php
	 * @param int $index <p>
	 * Index of the entry
	 * </p>
	 * @param int $length [optional] <p>
	 * The length to be read from the entry. If 0, then the
	 * entire entry is read.
	 * </p>
	 * @param int $flags [optional] <p>
	 * The flags to use to open the archive. the following values may
	 * be ORed to it.
	 * <p>
	 * <b>ZipArchive::FL_UNCHANGED</b>
	 * </p>
	 * @return string|false the contents of the entry on success or <b>FALSE</b> on failure.
	 */
	public function getFromIndex ($index, $length = 0, $flags = null) {}

	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.1.0)<br/>
	 * Get a file handler to the entry defined by its name (read only).
	 * @link https://php.net/manual/en/ziparchive.getstream.php
	 * @param string $name <p>
	 * The name of the entry to use.
	 * </p>
	 * @return resource|false a file pointer (resource) on success or <b>FALSE</b> on failure.
	 */
	public function getStream ($name) {}

    /**
     * Set the external attributes of an entry defined by its name
     * @link https://www.php.net/manual/en/ziparchive.setexternalattributesname.php
     * @param string $name Name of the entry
     * @param int $opsys The operating system code defined by one of the ZipArchive::OPSYS_ constants.
	 * @param int $attr The external attributes. Value depends on operating system.
	 * @param int $flags [optional] Optional flags. Currently unused.
	 * @return bool Returns <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
	public function setExternalAttributesName($name, $opsys, $attr, $flags = null) {}

    /**
     * Retrieve the external attributes of an entry defined by its name
     * @link https://www.php.net/manual/en/ziparchive.getexternalattributesname.php
     * @param string $name Name of the entry
     * @param int &$opsys On success, receive the operating system code defined by one of the ZipArchive::OPSYS_ constants.
     * @param int &$attr On success, receive the external attributes. Value depends on operating system.
     * @param int $flags [optional] If flags is set to ZipArchive::FL_UNCHANGED, the original unchanged attributes are returned.
     * @return bool Returns <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function getExternalAttributesName($name, &$opsys, &$attr, $flags = null) {}

    /**
     * Set the external attributes of an entry defined by its index
     * @link https://www.php.net/manual/en/ziparchive.setexternalattributesindex.php
     * @param int $index Index of the entry.
     * @param int $opsys The operating system code defined by one of the ZipArchive::OPSYS_ constants.
     * @param int $attr The external attributes. Value depends on operating system.
     * @param int $flags [optional] Optional flags. Currently unused.
     * @return bool Returns <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
	public function setExternalAttributesIndex($index, $opsys, $attr, $flags = null) {}

    /**
     * Retrieve the external attributes of an entry defined by its index
     * @link https://www.php.net/manual/en/ziparchive.getexternalattributesindex.php
     * @param int $index Index of the entry.
     * @param int &$opsys On success, receive the operating system code defined by one of the ZipArchive::OPSYS_ constants.
     * @param int &$attr On success, receive the external attributes. Value depends on operating system.
     * @param int $flags [optional] If flags is set to ZipArchive::FL_UNCHANGED, the original unchanged attributes are returned.
     * @return bool Returns <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function getExternalAttributesIndex($index, &$opsys, &$attr, $flags = null) {}
}

/**
 * (PHP 4 &gt;= 4.1.0, PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.0.0)<br/>
 * Open a ZIP file archive
 * @link https://php.net/manual/en/function.zip-open.php
 * @param string $filename <p>
 * The file name of the ZIP archive to open.
 * </p>
 * @return resource a resource handle for later use with
 * <b>zip_read</b> and <b>zip_close</b>
 * or returns the number of error if <i>filename</i> does not
 * exist or in case of other error.
 * @deprecated 8.0 Use {@link ZipArchive} instead.
 */
function zip_open ($filename) {}

/**
 * (PHP 4 &gt;= 4.1.0, PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.0.0)<br/>
 * Close a ZIP file archive
 * @link https://php.net/manual/en/function.zip-close.php
 * @param resource $zip <p>
 * A ZIP file previously opened with <b>zip_open</b>.
 * </p>
 * @return void No value is returned.
 * @deprecated 8.0 Use {@link ZipArchive} instead.
 */
function zip_close ($zip) {}

/**
 * (PHP 4 &gt;= 4.1.0, PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.0.0)<br/>
 * Read next entry in a ZIP file archive
 * @link https://php.net/manual/en/function.zip-read.php
 * @param resource $zip <p>
 * A ZIP file previously opened with <b>zip_open</b>.
 * </p>
 * @return resource|false a directory entry resource for later use with the
 * zip_entry_... functions, or <b>FALSE</b> if
 * there are no more entries to read, or an error code if an error
 * occurred.
 * @deprecated 8.0 Use {@link ZipArchive} instead.
 */
function zip_read ($zip) {}

/**
 * (PHP 4 &gt;= 4.1.0, PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.0.0)<br/>
 * Open a directory entry for reading
 * @link https://php.net/manual/en/function.zip-entry-open.php
 * @param resource $zip <p>
 * A valid resource handle returned by <b>zip_open</b>.
 * </p>
 * @param resource $zip_entry <p>
 * A directory entry returned by <b>zip_read</b>.
 * </p>
 * @param string $mode [optional] <p>
 * Any of the modes specified in the documentation of
 * <b>fopen</b>.
 * </p>
 * <p>
 * Currently, <i>mode</i> is ignored and is always
 * "rb". This is due to the fact that zip support
 * in PHP is read only access.
 * </p>
 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
 * </p>
 * <p>
 * Unlike <b>fopen</b> and other similar functions,
 * the return value of <b>zip_entry_open</b> only
 * indicates the result of the operation and is not needed for
 * reading or closing the directory entry.
 */
function zip_entry_open ($zip, $zip_entry, $mode = null) {}

/**
 * (PHP 4 &gt;= 4.1.0, PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.0.0)<br/>
 * Close a directory entry
 * @link https://php.net/manual/en/function.zip-entry-close.php
 * @param resource $zip_entry <p>
 * A directory entry previously opened <b>zip_entry_open</b>.
 * </p>
 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
 * @deprecated 8.0 Use {@link ZipArchive} instead.
 */
function zip_entry_close ($zip_entry) {}

/**
 * (PHP 4 &gt;= 4.1.0, PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.0.0)<br/>
 * Read from an open directory entry
 * @link https://php.net/manual/en/function.zip-entry-read.php
 * @param resource $zip_entry <p>
 * A directory entry returned by <b>zip_read</b>.
 * </p>
 * @param int $length [optional] <p>
 * The number of bytes to return.
 * </p>
 * <p>
 * This should be the uncompressed length you wish to read.
 * </p>
 * @return string|false the data read, empty string on end of a file, or <b>FALSE</b> on error.
 * @deprecated 8.0 Use {@link ZipArchive} instead.
 */
function zip_entry_read ($zip_entry, $length = 1024) {}

/**
 * (PHP 4 &gt;= 4.1.0, PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.0.0)<br/>
 * Retrieve the actual file size of a directory entry
 * @link https://php.net/manual/en/function.zip-entry-filesize.php
 * @param resource $zip_entry <p>
 * A directory entry returned by <b>zip_read</b>.
 * </p>
 * @return int The size of the directory entry.
 * @deprecated 8.0 Use {@link ZipArchive} instead.
 */
function zip_entry_filesize ($zip_entry) {}

/**
 * (PHP 4 &gt;= 4.1.0, PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.0.0)<br/>
 * Retrieve the name of a directory entry
 * @link https://php.net/manual/en/function.zip-entry-name.php
 * @param resource $zip_entry <p>
 * A directory entry returned by <b>zip_read</b>.
 * </p>
 * @return string The name of the directory entry.
 * @deprecated 8.0 Use {@link ZipArchive} instead.
 */
function zip_entry_name ($zip_entry) {}

/**
 * (PHP 4 &gt;= 4.1.0, PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.0.0)<br/>
 * Retrieve the compressed size of a directory entry
 * @link https://php.net/manual/en/function.zip-entry-compressedsize.php
 * @param resource $zip_entry <p>
 * A directory entry returned by <b>zip_read</b>.
 * </p>
 * @return int The compressed size.
 * @deprecated 8.0 Use {@link ZipArchive} instead.
 */
function zip_entry_compressedsize ($zip_entry) {}

/**
 * (PHP 4 &gt;= 4.1.0, PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.0.0)<br/>
 * Retrieve the compression method of a directory entry
 * @link https://php.net/manual/en/function.zip-entry-compressionmethod.php
 * @param resource $zip_entry <p>
 * A directory entry returned by <b>zip_read</b>.
 * </p>
 * @return string The compression method.
 * @deprecated 8.0 Use {@link ZipArchive} instead.
 */
function zip_entry_compressionmethod ($zip_entry) {}

// End of zip v.1.11.0
?>
