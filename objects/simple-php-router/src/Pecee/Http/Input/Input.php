<?php

namespace Pecee\Http\Input;

use Pecee\Exceptions\InvalidArgumentException;
use Pecee\Http\Request;

class Input
{
    /**
     * @var array
     */
    public $get = [];

    /**
     * @var array
     */
    public $post = [];

    /**
     * @var array
     */
    public $file = [];

    /**
     * @var Request
     */
    protected $request;

    /**
     * Input constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->parseInputs();
    }

    /**
     * Parse input values
     *
     */
    public function parseInputs()
    {
        /* Parse get requests */
        if (count($_GET) !== 0) {
            $this->get = $this->handleGetPost($_GET);
        }

        /* Parse post requests */
        $postVars = $_POST;

        if (in_array($this->request->getMethod(), ['put', 'patch', 'delete'], false) === true) {
            parse_str(file_get_contents('php://input'), $postVars);
        }

        if (count($postVars) !== 0) {
            $this->post = $this->handleGetPost($postVars);
        }

        /* Parse get requests */
        if (count($_FILES) !== 0) {
            $this->file = $this->parseFiles();
        }
    }

    /**
     * @return array
     */
    public function parseFiles()
    {
        $list = [];

        foreach ((array)$_FILES as $key => $value) {

            // Handle array input
            if (is_array($value['name']) === false) {
                $values['index'] = $key;
                try {
                    $list[$key] = InputFile::createFromArray($values + $value);
                } catch(InvalidArgumentException $e ){

                }
                continue;
            }

            $keys = [$key];

            $files = $this->rearrangeFiles($value['name'], $keys, $value);

            if (isset($list[$key]) === true) {
                $list[$key][] = $files;
            } else {
                $list[$key] = $files;
            }

        }

        return $list;
    }

    protected function rearrangeFiles(array $values, &$index, $original)
    {

        $originalIndex = $index[0];
        array_shift($index);

        $output = [];

        foreach ($values as $key => $value) {

            if (is_array($original['name'][$key]) === false) {

                try {

                    $file = InputFile::createFromArray([
                        'index'    => (empty($key) === true && empty($originalIndex) === false) ? $originalIndex : $key,
                        'name'     => $original['name'][$key],
                        'error'    => $original['error'][$key],
                        'tmp_name' => $original['tmp_name'][$key],
                        'type'     => $original['type'][$key],
                        'size'     => $original['size'][$key],
                    ]);

                    if (isset($output[$key]) === true) {
                        $output[$key][] = $file;
                        continue;
                    }

                    $output[$key] = $file;
                    continue;

                } catch(InvalidArgumentException $e) {

                }
            }

            $index[] = $key;

            $files = $this->rearrangeFiles($value, $index, $original);

            if (isset($output[$key]) === true) {
                $output[$key][] = $files;
            } else {
                $output[$key] = $files;
            }

        }

        return $output;
    }

    protected function handleGetPost(array $array)
    {
        $list = [];

        foreach ($array as $key => $value) {

            // Handle array input
            if (is_array($value) === false) {
                $list[$key] = new InputItem($key, $value);
                continue;
            }

            $output = $this->handleGetPost($value);

            $list[$key] = $output;
        }

        return $list;
    }

    /**
     * Find post-value by index or return default value.
     *
     * @param string $index
     * @param string|null $defaultValue
     * @return InputItem|string
     */
    public function findPost($index, $defaultValue = null)
    {
        return isset($this->post[$index]) ? $this->post[$index] : $defaultValue;
    }

    /**
     * Find file by index or return default value.
     *
     * @param string $index
     * @param string|null $defaultValue
     * @return InputFile|string
     */
    public function findFile($index, $defaultValue = null)
    {
        return isset($this->file[$index]) ? $this->file[$index] : $defaultValue;
    }

    /**
     * Find parameter/query-string by index or return default value.
     *
     * @param string $index
     * @param string|null $defaultValue
     * @return InputItem|string
     */
    public function findGet($index, $defaultValue = null)
    {
        return isset($this->get[$index]) ? $this->get[$index] : $defaultValue;
    }

    /**
     * Get input object
     *
     * @param string $index
     * @param string|null $defaultValue
     * @param array|string|null $methods
     * @return IInputItem|string
     */
    public function getObject($index, $defaultValue = null, $methods = null)
    {
        if ($methods !== null && is_string($methods) === true) {
            $methods = [$methods];
        }

        $element = null;

        if ($methods === null || in_array('get', $methods, false) === true) {
            $element = $this->findGet($index);
        }

        if (($element === null && $methods === null) || ($methods !== null && in_array('post', $methods, false) === true)) {
            $element = $this->findPost($index);
        }

        if (($element === null && $methods === null) || ($methods !== null && in_array('file', $methods, false) === true)) {
            $element = $this->findFile($index);
        }

        return ($element !== null) ? $element : $defaultValue;
    }

    /**
     * Get input element value matching index
     *
     * @param string $index
     * @param string|null $defaultValue
     * @param array|string|null $methods
     * @return InputItem|string
     */
    public function get($index, $defaultValue = null, $methods = null)
    {
        $input = $this->getObject($index, $defaultValue, $methods);

        if ($input instanceof InputItem) {
            return (trim($input->getValue()) === '') ? $defaultValue : $input->getValue();
        }

        return $input;
    }

    /**
     * Check if a input-item exist
     *
     * @param string $index
     * @return bool
     */
    public function exists($index)
    {
        return ($this->getObject($index) !== null);
    }

    /**
     * Get all get/post items
     * @param array|null $filter Only take items in filter
     * @return array
     */
    public function all(array $filter = null)
    {
        $output = $_GET + $_POST;

        if ($this->request->getMethod() === 'post') {

            $contents = file_get_contents('php://input');

            if (strpos(trim($contents), '{') === 0) {
                $post = json_decode($contents, true);
                if ($post !== false) {
                    $output += $post;
                }
            }
        }

        return ($filter !== null) ? array_intersect_key($output, array_flip($filter)) : $output;
    }

}