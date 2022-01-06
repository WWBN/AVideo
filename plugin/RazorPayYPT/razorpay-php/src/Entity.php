<?php

namespace Razorpay\Api;

use Razorpay\Api\Errors;

class Entity extends Resource implements ArrayableInterface
{
    protected $attributes = array();
 /**
     * Create method 
     *
     * @param array $attributes 
     * 
     */
    protected function create($attributes = null) 
    {
        $entityUrl = $this->getEntityUrl();
        
        return $this->request('POST', $entityUrl, $attributes);
    }

    protected function fetch($id)
    {
        $entityUrl = $this->getEntityUrl();

        $this->validateIdPresence($id);

        $relativeUrl = $entityUrl . $id;
       
        return $this->request('GET', $relativeUrl);
    }

    protected function validateIdPresence($id)
    {
        if ($id !== null)
        {
            return;
        }

        $path = explode('\\', get_class($this));
        $class = strtolower(array_pop($path));

        $message = 'The ' . $class . ' id provided is null';

        $code = Errors\ErrorCode::BAD_REQUEST_ERROR;

        throw new Errors\BadRequestError($message, $code, 500);
    }

    protected function all($options = array())
    {
        $entityUrl = $this->getEntityUrl();
        
        return $this->request('GET', $entityUrl, $options);
    }

    protected function getEntityUrl()
    {
        $fullClassName = get_class($this);
        $pos = strrpos($fullClassName, '\\');
        $className = substr($fullClassName, $pos + 1);
        $className = $this->snakeCase($className);
        return $className.'s/';
    }

    protected function snakeCase($input)
    {
        $delimiter = '_';
        $output = preg_replace('/\s+/u', '', ucwords($input));
        $output = preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $output);
        $output = strtolower($output);
        return $output;
    }

    /**
     * Makes a HTTP request using Request class and assuming the API returns
     * formatted entity or collection result, wraps the returned JSON as entity
     * and returns.
     *
     * @param string $method
     * @param string $relativeUrl
     * @param array  $data
     * @param array  $additionHeader
     *
     * @return Entity
     */
    protected function request($method, $relativeUrl, $data = null)
    {
        $request = new Request();

        $response = $request->request($method, $relativeUrl, $data);

        if ((isset($response['entity'])) and ($response['entity'] == $this->getEntity()))
        {
            $this->fill($response);

            return $this;
        }
        else
        {
            return static::buildEntity($response);
        }
    }
    
    /**
     * Given the JSON response of an API call, wraps it to corresponding entity
     * class or a collection and returns the same.
     *
     * @param array $data
     *
     * @return Entity
     */
    protected static function buildEntity($data)
    {
        $entities = static::getDefinedEntitiesArray();

        if (isset($data['entity']))
        {
            if (in_array($data['entity'], $entities))
            {
                $class = static::getEntityClass($data['entity']);
                $entity = new $class;
            }
            else
            {
                $entity = new static;
            }
        }
        else
        {
            $entity = new static;
        }

        $entity->fill($data);

        return $entity;
    }

    protected static function getDefinedEntitiesArray()
    {
        return array(
            'collection',
            'payment',
            'refund',
            'order',
            'customer',
            'token',
            'settlement');
    }

    protected static function getEntityClass($name)
    {
        return __NAMESPACE__.'\\'.ucfirst($name);
    }

    protected function getEntity()
    {
        $class = get_class($this);
        $pos = strrpos($class, '\\');
        $entity = strtolower(substr($class, $pos));

        return $entity;
    }

    public function fill($data)
    {
        $attributes = array();
        
     if(is_array($data))
     {   
        foreach ($data as $key => $value)
        {
            if (is_array($value))
            {
                if  (static::isAssocArray($value) === false)
                {
                    $collection = array();
                    foreach ($value as $v)
                    {
                        if (is_array($v))
                        {
                            $entity = static::buildEntity($v);
                            array_push($collection, $entity);
                        }
                        else
                        {
                            array_push($collection, $v);
                        }
                    }
                    $value = $collection;
                }
                else
                {
                    $value = static::buildEntity($value);
                }
            }

            $attributes[$key] = $value;
        }
      }
        $this->attributes = $attributes;
    }

    public static function isAssocArray($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public function toArray()
    {
        return $this->convertToArray($this->attributes);
    }

    protected function convertToArray($attributes)
    {
        $array = $attributes;

        foreach ($attributes as $key => $value)
        {
            if (is_object($value))
            {
                $array[$key] = $value->toArray();
            }
            else if (is_array($value) and self::isAssocArray($value) == false)
            {
                $array[$key] = $this->convertToArray($value);
            }
        }

        return $array;
    }
}
