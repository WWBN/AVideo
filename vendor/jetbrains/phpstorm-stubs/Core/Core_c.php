<?php

// Start of Core v.5.3.6-13ubuntu3.2
use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;

/**
 * Created by typecasting to object.
 * @link https://php.net/manual/en/reserved.classes.php
 */
class stdClass {
}

/**
 * @link https://wiki.php.net/rfc/iterable
 */
interface iterable {}

/**
 * Interface to detect if a class is traversable using foreach.
 * Abstract base interface that cannot be implemented alone.
 * Instead it must be implemented by either {@see IteratorAggregate} or {@see Iterator}.
 *
 * @link https://php.net/manual/en/class.traversable.php
 */
interface Traversable extends iterable {
}

/**
 * Interface to create an external Iterator.
 * @link https://php.net/manual/en/class.iteratoraggregate.php
 */
interface IteratorAggregate extends Traversable {

    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @throws Exception on failure.
     */
    public function getIterator();
}

/**
 * Interface for external iterators or objects that can be iterated
 * themselves internally.
 * @link https://php.net/manual/en/class.iterator.php
 */
interface Iterator extends Traversable {

    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current();

    /**
     * Move forward to next element
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next();

    /**
     * Return the key of the current element
     * @link https://php.net/manual/en/iterator.key.php
     * @return string|float|int|bool|null scalar on success, or null on failure.
     */
    public function key();

    /**
     * Checks if current position is valid
     * @link https://php.net/manual/en/iterator.valid.php
     * @return bool The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid();

    /**
     * Rewind the Iterator to the first element
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind();
}

/**
 * Interface to provide accessing objects as arrays.
 * @link https://php.net/manual/en/class.arrayaccess.php
 */
interface ArrayAccess {

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return bool true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset);

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset);

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value);

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset);
}

/**
 * Interface for customized serializing.
 * @link https://php.net/manual/en/class.serializable.php
 */
interface Serializable {

    /**
     * String representation of object.
     * @link https://php.net/manual/en/serializable.serialize.php
     * @return string|null The string representation of the object or null
     * @throws Exception Returning other type than string or null
     */
    public function serialize();

    /**
     * Constructs the object.
     * @link https://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized The string representation of the object.
     * @return void
     */
    public function unserialize($serialized);
}


/**
 * Throwable is the base interface for any object that can be thrown via a throw statement in PHP 7,
 * including Error and Exception.
 * @link https://php.net/manual/en/class.throwable.php
 * @since 7.0
 */
interface Throwable extends Stringable
{

    /**
     * Gets the message
     * @link https://php.net/manual/en/throwable.getmessage.php
     * @return string
     * @since 7.0
     */
    public function getMessage();

    /**
     * Gets the exception code
     * @link https://php.net/manual/en/throwable.getcode.php
     * @return int <p>
     * Returns the exception code as integer in
     * {@see Exception} but possibly as other type in
     * {@see Exception} descendants (for example as
     * string in {@see PDOException}).
     * </p>
     * @since 7.0
     */
    public function getCode();

    /**
     * Gets the file in which the exception occurred
     * @link https://php.net/manual/en/throwable.getfile.php
     * @return string Returns the name of the file from which the object was thrown.
     * @since 7.0
     */
    public function getFile();

    /**
     * Gets the line on which the object was instantiated
     * @link https://php.net/manual/en/throwable.getline.php
     * @return int Returns the line number where the thrown object was instantiated.
     * @since 7.0
     */
    public function getLine();

    /**
     * Gets the stack trace
     * @link https://php.net/manual/en/throwable.gettrace.php
     * @return array <p>
     * Returns the stack trace as an array in the same format as
     * {@see debug_backtrace()}.
     * </p>
     * @since 7.0
     */
    public function getTrace();

    /**
     * Gets the stack trace as a string
     * @link https://php.net/manual/en/throwable.gettraceasstring.php
     * @return string Returns the stack trace as a string.
     * @since 7.0
     */
    public function getTraceAsString();

    /**
     * Returns the previous Throwable
     * @link https://php.net/manual/en/throwable.getprevious.php
     * @return Throwable Returns the previous {@see Throwable} if available, or <b>NULL</b> otherwise.
     * @since 7.0
     */
    public function getPrevious();

    /**
     * Gets a string representation of the thrown object
     * @link https://php.net/manual/en/throwable.tostring.php
     * @return string <p>Returns the string representation of the thrown object.</p>
     * @since 7.0
     */
    public function __toString();
}
/**
 * Exception is the base class for
 * all Exceptions.
 * @link https://php.net/manual/en/class.exception.php
 */
class Exception implements Throwable {
    /** The error message */
    protected $message;
    /** The error code */
    protected $code;
    /** The filename where the error happened  */
    protected $file;
    /** The line where the error happened */
    protected $line;


    /**
     * Clone the exception
     * Tries to clone the Exception, which results in Fatal error.
     * @link https://php.net/manual/en/exception.clone.php
     * @return void
     */
    final private function __clone() { }

    /**
     * Construct the exception. Note: The message is NOT binary safe.
     * @link https://php.net/manual/en/exception.construct.php
     * @param string $message [optional] The Exception message to throw.
     * @param int $code [optional] The Exception code.
     * @param Throwable $previous [optional] The previous throwable used for the exception chaining.
     */
    #[Pure]
    public function __construct($message = "", $code = 0, Throwable $previous = null) { }

    /**
     * Gets the Exception message
     * @link https://php.net/manual/en/exception.getmessage.php
     * @return string the Exception message as a string.
     */
    #[Pure]
    final public function getMessage() { }

    /**
     * Gets the Exception code
     * @link https://php.net/manual/en/exception.getcode.php
     * @return mixed|int the exception code as integer in
     * <b>Exception</b> but possibly as other type in
     * <b>Exception</b> descendants (for example as
     * string in <b>PDOException</b>).
     */
    #[Pure]
    final public function getCode() { }

    /**
     * Gets the file in which the exception occurred
     * @link https://php.net/manual/en/exception.getfile.php
     * @return string the filename in which the exception was created.
     */
    #[Pure]
    final public function getFile() { }

    /**
     * Gets the line in which the exception occurred
     * @link https://php.net/manual/en/exception.getline.php
     * @return int the line number where the exception was created.
     */
    #[Pure]
    final public function getLine() { }

    /**
     * Gets the stack trace
     * @link https://php.net/manual/en/exception.gettrace.php
     * @return array the Exception stack trace as an array.
     */
    #[Pure]
    final public function getTrace() { }

    /**
     * Returns previous Exception
     * @link https://php.net/manual/en/exception.getprevious.php
     * @return Exception the previous <b>Exception</b> if available
     * or null otherwise.
     */
    #[Pure]
    final public function getPrevious() { }

    /**
     * Gets the stack trace as a string
     * @link https://php.net/manual/en/exception.gettraceasstring.php
     * @return string the Exception stack trace as a string.
     */
    #[Pure]
    final public function getTraceAsString() { }

    /**
     * String representation of the exception
     * @link https://php.net/manual/en/exception.tostring.php
     * @return string the string representation of the exception.
     */
    public function __toString() { }

    public function __wakeup() { }
}

/**
 * Error is the base class for all internal PHP error exceptions.
 * @link https://php.net/manual/en/class.error.php
 * @since 7.0
 */
class Error implements Throwable {

    /** The error message */
    protected $message;
    /** The error code */
    protected $code;
    /** The filename where the error happened  */
    protected $file;
    /** The line where the error happened */
    protected $line;

    /**
     * Construct the error object.
     * @link https://php.net/manual/en/error.construct.php
     * @param string $message [optional] The Error message to throw.
     * @param int $code [optional] The Error code.
     * @param Throwable $previous [optional] The previous throwable used for the exception chaining.
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
    }

    /***
     * Gets the message
     * @link https://php.net/manual/en/throwable.getmessage.php
     * @return string
     * @since 7.0
     */
    public final function getMessage()
    {
    }

    /**
     * Gets the exception code
     * @link https://php.net/manual/en/throwable.getcode.php
     * @return int <p>
     * Returns the exception code as integer in
     * {@see Exception} but possibly as other type in
     * {@see Exception} descendants (for example as
     * string in {@see PDOException}).
     * </p>
     * @since 7.0
     */
    public final function getCode(){}


    /**
     * Gets the file in which the exception occurred
     * @link https://php.net/manual/en/throwable.getfile.php
     * @return string Returns the name of the file from which the object was thrown.
     * @since 7.0
     */
    public final function getFile(){}


    /**
     * Gets the line on which the object was instantiated
     * @link https://php.net/manual/en/throwable.getline.php
     * @return int Returns the line number where the thrown object was instantiated.
     * @since 7.0
     */
    public final function getLine(){}


    /**
     * Gets the stack trace
     * @link https://php.net/manual/en/throwable.gettrace.php
     * @return array <p>
     * Returns the stack trace as an array in the same format as
     * {@see debug_backtrace()}.
     * </p>
     * @since 7.0
     */
    public final function getTrace(){}

    /**
     * Gets the stack trace as a string
     * @link https://php.net/manual/en/throwable.gettraceasstring.php
     * @return string Returns the stack trace as a string.
     * @since 7.0
     */
    public final function getTraceAsString(){}

    /**
     * Returns the previous Throwable
     * @link https://php.net/manual/en/throwable.getprevious.php
     * @return Throwable Returns the previous {@see Throwable} if available, or <b>NULL</b> otherwise.
     * @since 7.0
     */
    public final function getPrevious(){}
    /**
     * Gets a string representation of the thrown object
     * @link https://php.net/manual/en/throwable.tostring.php
     * @return string <p>Returns the string representation of the thrown object.</p>
     * @since 7.0
     */
    public function __toString(){}

    /**
     * Clone the error
     * Error can not be clone, so this method results in fatal error.
     * @return void
     * @link https://php.net/manual/en/error.clone.php
     */
    private final function __clone(){}

    public function __wakeup(){}
}

class ValueError extends Error {}

/**
 * There are three scenarios where a TypeError may be thrown.
 * The first is where the argument type being passed to a function does not match its corresponding declared
 * parameter type. The second is where a value being returned from a function does not match the declared function return type. The third is where an
 * invalid number of arguments are passed to a built-in PHP function (strict mode only).
 * @link https://php.net/manual/en/class.typeerror.php
 * @since 7.0
 */
class TypeError extends Error {

}

/**
 * ParseError is thrown when an error occurs while parsing PHP code, such as when {@see eval()} is called.
 * @link https://php.net/manual/en/class.parseerror.php
 * @since 7.0
 */
class ParseError extends CompileError {

}

/**
 * ArgumentCountError is thrown when too few arguments are passed to a user
 * defined routine.
 *
 * @since 7.1
 * @see https://php.net/migration71.incompatible#migration71.incompatible.too-few-arguments-exception
 */
class ArgumentCountError extends TypeError {}

/**
 * ArithmeticError is thrown when an error occurs while performing mathematical operations.
 * In PHP 7.0, these errors include attempting to perform a bitshift by a negative amount,
 * and any call to {@see intdiv()} that would result in a value outside the possible bounds of an integer.
 * @link https://php.net/manual/en/class.arithmeticerror.php
 * @since 7.0
 */
class ArithmeticError extends Error {

}

/**
 * Class CompileError
 * @link https://secure.php.net/manual/en/class.compileerror.php
 * @since 7.3
 */
class CompileError extends Error {

}

/**
 * DivisionByZeroError is thrown when an attempt is made to divide a number by zero.
 * @link https://php.net/manual/en/class.divisionbyzeroerror.php
 * @since 7.0
 */
class DivisionByZeroError extends ArithmeticError {

}

/**
 * @since 8.0
 */
class UnhandledMatchError extends Error {}

/**
 * An Error Exception.
 * @link https://php.net/manual/en/class.errorexception.php
 */
class ErrorException extends Exception {

    protected $severity;


    /**
     * Constructs the exception
     * @link https://php.net/manual/en/errorexception.construct.php
     * @param string $message [optional] The Exception message to throw.
     * @param int $code [optional] The Exception code.
     * @param int $severity [optional] The severity level of the exception.
     * @param string $filename [optional] The filename where the exception is thrown.
     * @param int $line [optional] The line number where the exception is thrown.
     * @param Exception $previous [optional] The previous exception used for the exception chaining.
     */
    #[\JetBrains\PhpStorm\Pure]
    public function __construct($message = "", $code = 0, $severity = 1, $filename = __FILE__, $line = __LINE__, $previous = null) { }

    /**
     * Gets the exception severity
     * @link https://php.net/manual/en/errorexception.getseverity.php
     * @return int the severity level of the exception.
     */
    final public function getSeverity() { }
}

/**
 * Class used to represent anonymous functions.
 * <p>Anonymous functions, implemented in PHP 5.3, yield objects of this type.
 * This fact used to be considered an implementation detail, but it can now be relied upon.
 * Starting with PHP 5.4, this class has methods that allow further control of the anonymous function after it has been created.
 * <p>Besides the methods listed here, this class also has an __invoke method.
 * This is for consistency with other classes that implement calling magic, as this method is not used for calling the function.
 * @link https://secure.php.net/manual/en/class.closure.php
 */
final class Closure {

    /**
     * This method exists only to disallow instantiation of the Closure class.
     * Objects of this class are created in the fashion described on the anonymous functions page.
     * @link https://secure.php.net/manual/en/closure.construct.php
     */
    private function __construct() { }

    /**
     * This is for consistency with other classes that implement calling magic,
     * as this method is not used for calling the function.
     * @param mixed ...$_ [optional]
     * @return mixed
     * @link https://secure.php.net/manual/en/class.closure.php
     */
    public function __invoke(...$_) { }

    /**
     * Duplicates the closure with a new bound object and class scope
     * @link https://secure.php.net/manual/en/closure.bindto.php
     * @param object|null $newThis The object to which the given anonymous function should be bound, or NULL for the closure to be unbound.
     * @param mixed $newScope The class scope to which associate the closure is to be associated, or 'static' to keep the current one.
     * If an object is given, the type of the object will be used instead.
     * This determines the visibility of protected and private methods of the bound object.
     * @return Closure|false Returns the newly created Closure object or FALSE on failure
     */
    function bindTo($newThis, $newScope = 'static') { }

    /**
     * This method is a static version of Closure::bindTo().
     * See the documentation of that method for more information.
     * @link https://secure.php.net/manual/en/closure.bind.php
     * @param Closure $closure The anonymous functions to bind.
     * @param object|null $newThis The object to which the given anonymous function should be bound, or NULL for the closure to be unbound.
     * @param mixed $newScope The class scope to which associate the closure is to be associated, or 'static' to keep the current one.
     * If an object is given, the type of the object will be used instead.
     * This determines the visibility of protected and private methods of the bound object.
     * @return Closure|false Returns the newly created Closure object or FALSE on failure
     */
    static function bind(Closure $closure, $newThis, $newScope = 'static') { }

    /**
     * Temporarily binds the closure to newthis, and calls it with any given parameters.
     * @link https://php.net/manual/en/closure.call.php
     * @param object $newThis The object to bind the closure to for the duration of the call.
     * @param mixed $args [optional] Zero or more parameters, which will be given as parameters to the closure.
     * @return mixed
     * @since 7.0
     */
    function call ($newThis, ...$args) {}

    /**
     * @param callable $callback
     * @return Closure
     * @since 7.1
     */
    public static function fromCallable (callable $callback) {}
}

/**
 * Classes implementing <b>Countable</b> can be used with the
 * <b>count</b> function.
 * @link https://php.net/manual/en/class.countable.php
 */
interface Countable {

    /**
     * Count elements of an object
     * @link https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count();
}

/**
 * Weak references allow the programmer to retain a reference to an
 * object which does not prevent the object from being destroyed.
 * They are useful for implementing cache like structures.
 * @link https://www.php.net/manual/en/class.weakreference.php
 */
class WeakReference {
    /**
     * This method exists only to disallow instantiation of the WeakReference
     * class. Weak references are to be instantiated with the factory method
     * <b>WeakReference::create()</b>.
     */
    public function __construct() {}

    /**
     * Create a new weak reference.
     * @link https://www.php.net/manual/en/weakreference.create.php
     * @param object $referent The object to be weakly referenced.
     * @return WeakReference the freshly instantiated object.
     * @since 7.4
     */
    public static function create($referent) {}

    /**
     * Gets a weakly referenced object. If the object has already been
     * destroyed, NULL is returned.
     * @link https://www.php.net/manual/en/weakreference.get.php
     * @return object|null
     * @since 7.4
     */
    public function get() {}
}

/**
 * Weak maps allow creating a map from objects to arbitrary values
 * (similar to SplObjectStorage) without preventing the objects that are used
 * as keys from being garbage collected. If an object key is garbage collected,
 * it will simply be removed from the map.
 *
 * @since 8.0
 */
final class WeakMap implements \ArrayAccess, \Countable, \IteratorAggregate {
    /**
     * Returns {@see true} if the value for the object is contained in
     * the {@see WeakMap} and {@see false} instead.
     *
     * @param object $object Any object
     * @return bool
     */
    public function offsetExists($object) {}

    /**
     * Returns the existsing value by an object.
     *
     * @param object $object Any object
     * @return mixed Value associated with the key object
     */
    public function offsetGet($object)
    {
    }

    /**
     * Sets a new value for an object.
     *
     * @param object $object Any object
     * @param mixed $value Any value
     * @return void
     */
    public function offsetSet($object, $value)
    {
    }

    /**
     * Force removes an object value from the {@see WeakMap} instance.
     *
     * @param object $object Any object
     * @return void
     */
    public function offsetUnset($object)
    {
    }

    /**
     * Returns an iterator in the "[object => mixed]" format.
     *
     * @return Traversable
     */
    public function getIterator()
    {
    }

    /**
     * Returns the number of items in the {@see WeakMap} instance.
     *
     * @return int
     */
    public function count()
    {
    }
}

/**
 * Stringable interface marks classes as available for serialization
 * in a string.
 *
 * @since 8.0
 */
interface Stringable {
    /**
     * Magic method {@see https://www.php.net/manual/en/language.oop5.magic.php}
     * called during serialization to string.
     *
     * @return string Returns string representation of the object that
     * implements this interface (and/or "__toString" magic method).
     */
    public function __toString();
}

/**
 * @since 8.0
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Attribute {
    public int $flags;
    /**
     * Marks that attribute declaration is allowed only in classes.
     */
    const TARGET_CLASS = 1;

    /**
     * Marks that attribute declaration is allowed only in functions.
     */
    const TARGET_FUNCTION = 1 << 1;

    /**
     * Marks that attribute declaration is allowed only in class methods.
     */
    const TARGET_METHOD = 1 << 2;

    /**
     * Marks that attribute declaration is allowed only in class properties.
     */
    const TARGET_PROPERTY = 1 << 3;

    /**
     * Marks that attribute declaration is allowed only in class constants.
     */
    const TARGET_CLASS_CONSTANT = 1 << 4;

    /**
     * Marks that attribute declaration is allowed only in function or method parameters.
     */
    const TARGET_PARAMETER = 1 << 5;

    /**
     * Marks that attribute declaration is allowed anywhere.
     */
    const TARGET_ALL = (1 << 6) - 1;

    /**
     * Notes that an attribute declaration in the same place is
     * allowed multiple times.
     */
    const IS_REPEATABLE = 1 << 10;

    /**
     * @param int $flags A value in the form of a bitmask indicating the places
     * where attributes can be defined.
     */
    public function __construct(#[ExpectedValues(flagsFromClass: Attribute::class)] $flags = self::TARGET_ALL)
    {
    }
}

/**
 * A class for working with PHP tokens, which is an alternative to
 * the {@see token_get_all()} function.
 *
 * @since 8.0
 */
class PhpToken implements Stringable {
    /**
     * One of the T_* constants, or an integer < 256 representing a
     * single-char token.
     */
    public int $id;

    /**
     * The textual content of the token.
     */
    public string $text;

    /**
     * The starting line number (1-based) of the token.
     */
    public int $line;

    /**
     * The starting position (0-based) in the tokenized string.
     */
    public int $pos;

    /**
     * Same as {@see token_get_all()}, but returning array of {@see PhpToken}
     * or an instance of a child class.
     *
     * @param string $code An a PHP source code
     * @param int $flags
     * @return static[]
     */
    public static function getAll($code, $flags = 0)
    {
    }

    /**
     * @param int $id An integer identifier
     * @param string $text Textual content
     * @param int $line Strating line
     * @param int $pos Straring position (line offset)
     */
    final public function __construct($id, $text, $line = -1, $pos = -1)
    {
    }

    /**
     * Get the name of the token.
     *
     * @return string|null
     */
    public function getTokenName()
    {
    }

    /** @return static[] */
    public static function tokenize(string $code, int $flags = 0): array {}

    /**
     * Whether the token has the given ID, the given text, or has an ID/text
     * part of the given array.
     *
     * @param int|string|array $kind
     * @return bool
     */
    public function is($kind)
    {
    }

    /**
     * Whether this token would be ignored by the PHP parser.
     *
     * @return bool
     */
    public function isIgnorable()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
    }
}

/**
 * @since 8.0
 */
final class InternalIterator implements Iterator{
    private function __construct(){}
    public function current(){}

    public function next(){}

    public function key(){}

    public function valid(){}

    public function rewind(){}
}
