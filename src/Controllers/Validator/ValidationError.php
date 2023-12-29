<?php

namespace Controllers\Validator;

class ValidationError
{

    /**
     * key
     *
     * @var mixed
     */
    private $key;
        
    /**
     * rule
     *
     * @var mixed
     */
    private $rule;
        
    /**
     * attributes
     *
     * @var array
     */
    private $attributes=[];

    /**
     * messages
     *
     * @var array
     */
    private $messages=[
        'required'=>'Le champs %s est requis',
        'empty'=>'Le champs %s ne peut être vide',
        'slug'=>'Le champs %s n\' est pas un slug valide',
        'betweenLength'=>'Le champs %s doit contenir entre %d et %d caractères',
        'minLength'=>'Le champs %s doit contenir plus de %d caractères',
        'date'=>'Le champs %s doit être une date valide (%s)',
        'maxLength'=>'Le champs %s doit contenir au moins de %d caractères',
        'exists'=>"Le champs %s n'existe pas dans la table %s",
        'filetype'=>"Le champs %s n'est pas au format valide (%s)",
        'upload'=>"Vous devez uploader un fichier",
        'unique'=>"Le champs %s existe déjà dans la base de donnée",
        'email'=>"Le champs %s n'est un bon email( user@user.ml)",
        'passwordComfirm' =>"Les deux mots de passe doivent être identique",
        'equals' =>"Les deux valeurs doivent être identique (%s n'est pas une bonne valeur)"
    ];
    
    /**
     * __construct
     *
     * @param  string $key
     * @param  string $rule
     * @param  array $attributes
     * @return void
     */
    public function __construct(string $key, string $rule, array $attributes = [])
    {

        $this->attributes=$attributes;
        $this->key=$key;
        $this->rule=$rule;
    }
    
    /**
     * __toString
     *
     * @return string
     */
    public function __toString():string
    {

        $params=array_merge([$this->messages[$this->rule],$this->key], $this->attributes);
        return (string)call_user_func_array('sprintf', $params);
    }
}
