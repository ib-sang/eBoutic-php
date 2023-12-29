<?php

namespace Controllers;

use Controllers\Validator\ValidationError;

class Validator
{

    private const MINE_TYPE=[
        'jpg'=>'image/jpeg',
        'png'=>'image/png',
        'pdf'=>'application/pdf'
    ];
    
    /**
     * params
     *
     * @var array
     */
    private $params;
        
    /**
     * errors
     *
     * @var array
     */
    private $errors=[];
    
    /**
     * __construct
     *
     * @param  array $params
     * @return void
     */
    public function __construct(array $params)
    {
        $this->params=$params;
    }
    
    /**
     * required
     *
     * @param  string $keys
     * @return self
     */
    public function required(string ...$keys):self
    {
        foreach ($keys as $key) {
            $value=$this->getValue($key);
            if (is_null($value)) {
                $this->addError($key, 'required');
            }
        }
        return $this;
    }
    
    /**
     * date
     *
     * @param  string $key
     * @param  string $format
     * @return self
     */
    public function date(string $key, string $format = 'Y-m-d H:i:s'):self
    {
        $value = $this->getValue($key) ?? '';
        $dateTime=\DateTime::createFromFormat($format, $value);
        $errors=\DateTime::getLastErrors();
        if ($errors['error_count']>0 || $errors['warning_count']>0 || $dateTime===false) {
            $this->addError($key, 'date', [$format]);
        }
        return $this;
    }
    
    /**
     * notEmpty
     *
     * @param  string $keys
     * @return self
     */
    public function notEmpty(string ...$keys):self
    {
        foreach ($keys as $key) {
            $value=$this->getValue($key);
            if (is_null($value) || empty($value)) {
                $this->addError($key, 'empty');
            }
        }
        return $this;
    }
    
    /**
     * champsLength
     *
     * @param  string $key
     * @param  int/null $min
     * @param  int/null $max
     * @return self
     */
    public function champsLength(string $key, ?int $min = null, ?int $max = null):self
    {
        $value=$this->getValue($key);
        $length=mb_strlen($value);
        if (!is_null($min) && !is_null($max) && ($length < $min || $length > $max)) {
            $this->addError($key, 'betweenLength', [$min,$max]);
              return $this;
        }

        if (!is_null($min) && $length<$min) {
            $this->addError($key, 'minLength', [$min]);
            return $this;
        }

        if (!is_null($max) && $length>$max) {
            $this->addError($key, 'maxLength', [$max]);
            return $this;
        }
        return $this;
    }
    
    /**
     * email
     *
     * @param  string $key
     * @return self
     */
    public function email(string $key):self
    {
        $value = $this->getValue($key) ?? '';
        if (is_null($value)) {
            return $this;
        }
        $patern='/^[a-z0-9.]+@[a-z0-9.]{2,}\.[a-z]{2,4}$/';
        if (!is_null($value) && !preg_match($patern, $value)) {
            $this->addError($key, 'email');
        }
        return $this;
    }
    
    /**
     * slug
     *
     * @param  string $key
     * @return self
     */
    public function slug(string $key):self
    {
        $value = $this->getValue($key) ?? '';
        if (is_null($value)) {
            return $this;
        }
        $patern='/^([a-z0-9]+-?)+$/';
        if (!is_null($value) && !preg_match($patern, $value)) {
             $this->addError($key, 'slug');
        }
        return $this;
    }

    /**
     * passwordConfirm
     *
     * @param  string $firstKey
     * @param  string $secondKey
     * @return self
     */
    public function fieldEquals(string $firstvalue, string $secondvalue):self
    {
        if (is_null($firstvalue) || is_null($secondvalue)) {
            return $this;
        }
        
        if ($firstvalue!==$secondvalue) {
             $this->addError($secondvalue, 'equals');
        }
        return $this;
    }
    
    /**
     * passwordConfirm
     *
     * @param  string $firstKey
     * @param  string $secondKey
     * @return self
     */
    public function passwordConfirm(string $firstKey, string $secondKey):self
    {
        $firstvalue=$this->getValue($firstKey);
        $secondvalue=$this->getValue($secondKey);
        if (is_null($firstvalue) || is_null($secondKey)) {
            return $this;
        }
        if ($firstvalue!==$secondvalue) {
             $this->addError($secondKey, 'passwordComfirm');
        }
        return $this;
    }
    
    /**
     * exists
     *
     * @param  string $key
     * @param  string $table
     * @param  PDO $pdo
     * @return self
     */
    public function exists(string $key, string $table, \PDO $pdo):self
    {
        $value=$this->getValue($key);
        $statement=$pdo->prepare("SELECT id FROM $table WHERE id=?");
        $statement->execute([$value]);
        if ($statement->fetchColumn() === false) {
            $this->addError($key, 'exists', [$table]);
        }
        return $this;
    }
        
    /**
     * uploaded
     *
     * @param  string $key
     * @return self
     */
    public function uploaded(string $key):self
    {
        $file=$this->getValue($key);
        if ($file===null || $file->getErrors()!==UPLOAD_ERR_OK) {
            $this->addError($key, 'upload');
        }
        return $this;
    }
    
    /**
     * extension
     *
     * @param  string $key
     * @param  array $extensions
     * @return self
     */
    public function extension(string $key, array $extensions):self
    {
        $file=$this->getValue($key);
        if ($file!==null && $file->getError()===UPLOAD_ERR_OK) {
            $type=$file->getClientMediaType();
            $extension=mb_strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
            $expectedType=self::MINE_TYPE[$extension];
            if (!in_array($extension, $extensions) || $expectedType!==$type) {
                $this->addError($key, 'filetype', [join(',', $extensions)]);
            }
        }

        return $this;
    }
    
    /**
     * unique
     *
     * @param  string $key
     * @param  string $table
     * @param  PDO $pdo
     * @param  int/null $exclude
     * @return self
     */
    public function unique(string $key, string $table, \PDO $pdo, ?int $exclude = null):self
    {
        $value=$this->getValue($key);
        $query="SELECT id FROM $table WHERE $key=?";
            $params=[$value];
        if ($exclude!==null) {
            $query.=" AND id !=?";
            $params[]=$exclude;
        }
        $statement=$pdo->prepare($query);
        $statement->execute($params);
        
        if ($statement->fetchColumn() !== false) {
            $this->addError($key, 'unique', [$value]);
        }
        return $this;
    }
    
    /**
     * isValid
     *
     * @return bool
     */
    public function isValid():bool
    {
        return empty($this->errors);
    }
    
    /**
     * getErrors
     *
     * @return array
     */
    public function getErrors():array
    {
        return $this->errors;
    }
    
    /**
     * addError
     *
     * @param  string $key
     * @param  string $rule
     * @param  array $attributes
     * @return void
     */
    private function addError(string $key, string $rule, ?array $attributes = []):void
    {
        $this->errors[$key]= new ValidationError($key, $rule, $attributes);
    }
    
    /**
     * getValue
     *
     * @param  string $key
     * @return string/null
     */
    private function getValue(string $key):?string
    {
        if (array_key_exists($key, $this->params)) {
            return $this->params[$key];
        }
        return null;
    }
}
