<?php

// Start of FFI v.0.1.0

namespace {
    use FFI\CData;
    use FFI\CType;
    use FFI\ParserException;

    /**
     * Class FFI
     *
     * FFI class provides access to a simple way to call native functions,
     * access native variables and create/access data structures defined
     * in C language.
     *
     * @since 7.4
     */
    class FFI
    {
        /**
         * The method creates a binding on the existing C function.
         *
         * All variables and functions defined by first arguments are bound
         * to corresponding native symbols in DSO library and then may be
         * accessed as FFI object methods and properties. C types of argument,
         * return value and variables are automatically converted to/from PHP
         * types (if possible). Otherwise, they are wrapped in a special CData
         * proxy object and may be accessed by elements.
         *
         * @param string $code The collection of C declarations.
         * @param string|null $library DSO library.
         * @return FFI
         * @throws ParserException
         */
        public static function cdef(string $code, string $library = null): FFI {}

        /**
         * <p>Instead of embedding of a long C definition into PHP string,
         * and creating FFI through FFI::cdef(), it's possible to separate
         * it into a C header file. Note, that C preprocessor directives
         * (e.g. #define or #ifdef) are not supported. And only a couple of
         * special macros may be used especially for FFI.</p>
         *
         * <code>
         *  #define FFI_LIB "libc.so.6"
         *
         *  int printf(const char *format, ...);
         * </code>
         *
         * Here, FFI_LIB specifies, that the given library should be loaded.
         *
         * <code>
         *  $ffi = FFI::load(__DIR__ . "/printf.h");
         *  $ffi->printf("Hello world!\n");
         * </code>
         *
         * @param string $filename
         * @return FFI
         */
        public static function load(string $filename): FFI {}

        /**
         * FFI definition parsing and shared library loading may take
         * significant time. It's not useful to do it on each HTTP request in
         * WEB environment. However, it's possible to pre-load FFI definitions
         * and libraries at php startup, and instantiate FFI objects when
         * necessary. Header files may be extended with FFI_SCOPE define
         * (default pre-loading scope is "C"). This name is going to be
         * used as FFI::scope() argument. It's possible to pre-load few
         * files into a single scope.
         *
         * <code>
         *  #define FFI_LIB "libc.so.6"
         *  #define FFI_SCOPE "libc"
         *
         *  int printf(const char *format, ...);
         * </code>
         *
         * These files are loaded through the same FFI::load() load function,
         * executed from file loaded by opcache.preload php.ini directive.
         *
         * <code>
         *  ffi.preload=/etc/php/ffi/printf.h
         * </code>
         *
         * Finally, FFI::scope() instantiate an FFI object, that implements
         * all C definition from the given scope.
         *
         * <code>
         *  $ffi = FFI::scope("libc");
         *  $ffi->printf("Hello world!\n");
         * </code>
         *
         * @param string $name
         * @return FFI
         */
        public static function scope(string $name): FFI {}

        /**
         * Method that creates an arbitrary C structure.
         *
         * @param string|CData|mixed $type
         * @param bool $owned
         * @param bool $persistent
         * @return CData
         * @throws ParserException
         */
        public static function new($type, bool $owned = true, bool $persistent = false): CData {}

        /**
         * Manually removes previously created "not-owned" data structure.
         *
         * @param CData $pointer
         * @return void
         */
        public static function free(CData $pointer): void {}

        /**
         * Casts given $pointer to another C type, specified by C declaration
         * string or FFI\CType object.
         *
         * This function may be called statically and use only predefined
         * types, or as a method of previously created FFI object. In last
         * case the first argument may reuse all type and tag names
         * defined in FFI::cdef().
         *
         * @param mixed $type
         * @param CData $pointer
         * @return CData
         */
        public static function cast($type, CData $pointer): CData {}

        /**
         * This function creates and returns a FFI\CType object, representng
         * type of the given C type declaration string.
         *
         * FFI::type() may be called statically and use only predefined types,
         * or as a method of previously created FFI object. In last case the
         * first argument may reuse all type and tag names defined in
         * FFI::cdef().
         *
         * @param string|CType $type
         * @return CType
         */
        public static function type($type): CType {}

        /**
         * This function returns a FFI\CType object, representing the type of
         * the given FFI\CData object.
         *
         * @param CData $pointer
         * @return CType
         */
        public static function typeof(CData $pointer): CType {}

        /**
         * Constructs a new C array type with elements of $type and
         * dimensions specified by $dimensions.
         *
         * @param CType $type
         * @param array $dimensions
         * @return CType
         */
        public static function arrayType(CType $type, array $dimensions): CType {}

        /**
         * Returns C pointer to the given C data structure. The pointer is
         * not "owned" and won't be free. Anyway, this is a potentially
         * unsafe operation, because the life-time of the returned pointer
         * may be longer than life-time of the source object, and this may
         * cause dangling pointer dereference (like in regular C).
         *
         * @param CData $pointer
         * @return CData
         */
        public static function addr(CData $pointer): CData {}

        /**
         * Returns size of C data type of the given FFI\CData or FFI\CType.
         *
         * @param CData|CType &$pointer
         * @return int
         */
        public static function sizeof(&$pointer): int {}

        /**
         * Returns size of C data type of the given FFI\CData or FFI\CType.
         *
         * @param CData|CType &$pointer
         * @return int
         */
        public static function alignof(&$pointer): int {}

        /**
         * Copies $size bytes from memory area $source to memory area $target.
         * $source may be any native data structure (FFI\CData) or PHP string.
         *
         * @param CData $target
         * @param mixed &$source
         * @param int $size
         */
        public static function memcpy(CData $target, &$source, int $size): void {}

        /**
         * Compares $size bytes from memory area $a and $b.
         *
         * @param CData|string &$a
         * @param CData|string &$b
         * @param int $size
         * @return int
         */
        public static function memcmp(&$a, &$b, int $size): int {}

        /**
         * Fills the $size bytes of the memory area pointed to by $target with
         * the constant byte $byte.
         *
         * @param CData $target
         * @param int $byte
         * @param int $size
         */
        public static function memset(CData $target, int $byte, int $size): void {}

        /**
         * Creates a PHP string from $size bytes of memory area pointed by
         * $source. If size is omitted, $source must be zero terminated
         * array of C chars.
         *
         * @param CData $source
         * @param int $size [optional]
         * @return string
         */
        public static function string(CData $source, int $size = 0): string {}
    }
}

namespace FFI {
    /**
     * Class Exception
     *
     * @since 7.4
     */
    class Exception extends \Error
    {
    }

    /**
     * Class ParserException
     *
     * @since 7.4
     */
    class ParserException extends Exception
    {
    }

    /**
     * Class CData
     *
     * Proxy object that provides access to compiled structures.
     *
     * @since 7.4
     */
    class CData
    {
    }

    /**
     * Class CType
     *
     * Class containing C type information.
     *
     * @since 7.4
     */
    class CType
    {
    }
}
